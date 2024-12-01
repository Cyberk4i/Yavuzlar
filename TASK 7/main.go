package main

import (
	"fmt"
	"log"
	"os"

	"github.com/gocolly/colly"
)

func main() {
	var choice int
	for {

		fmt.Println("-----YAVUZLAR-----")
		fmt.Println("Web Scraper Aracı")
		fmt.Println("Haber Çekmek İstediğiniz Web Sitesini Seçin :)")
		fmt.Println("1) thehackernews")
		fmt.Println("2) social.cyware")
		fmt.Println("3) cybersecuritynews")
		fmt.Println("4) Çıkış")
		fmt.Print("Seçiminizi yapın: ")
		_, err := fmt.Scanln(&choice)
		if err != nil {
			log.Println("Geçersiz giriş, lütfen tekrar deneyin.")
			continue
		}

		var url string
		switch choice {
		case 1:
			url = "https://thehackernews.com/"
		case 2:
			url = "https://social.cyware.com/category/malware-and-vulnerabilities-news"
		case 3:
			url = "https://cybersecuritynews.com/category/zero-day/"
		case 4:
			fmt.Println("Çıkılıyor...")
			return
		default:
			fmt.Println("Lütfen 1-4 arasında bir değer girin...")
			continue
		}

		scrapeSite(url)
	}
}

func scrapeSite(url string) {
	c := colly.NewCollector(
		colly.AllowedDomains("thehackernews.com", "social.cyware.com", "cybersecuritynews.com"),
	)

	c.OnHTML("h2,h1,h3", func(e *colly.HTMLElement) {
		title := e.Text
		content := e.DOM.Parent().Find(".home-desc").Text() + e.DOM.Parent().Parent().Find(".cy-card__description").Text() + e.DOM.Parent().Find(".td-excerpt").Text()
		date := e.DOM.Parent().Find(".h-datetime").Text() + e.DOM.Parent().Parent().Find("span.cy-card__meta").Text() + e.DOM.Parent().Find(".td-post-date").Text()

		file, err := os.OpenFile("Haberler.txt", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)

		if err != nil {
			log.Println("Dosya açılırken hata oluştu:", err)
			return
		}
		defer file.Close()

		file.WriteString("Başlık: " + title + "\n" + "İçerik: " + content + "\n" + "Tarihi: " + date + "\n" + "\n" + "-------------------------------------------------------------------------" + "\n" + "\n")

	})

	c.OnError(func(r *colly.Response, err error) {
		log.Println("Hata:", err)
	})

	err := c.Visit(url)
	if err != nil {
		log.Fatal("Siteye gidilemedi:", err)
	}
}
