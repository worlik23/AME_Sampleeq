<?php
if (isset($_GET['path'])) {
    $path = $_GET['path'];
    if (file_exists($path) && exif_imageType($path)) {
        header('Content-Type: ' . mime_content_type($path));
        readfile($path);
        exit();
    }
}

if (isset($_GET['docs'])) {
    $doc = urldecode($_GET['docs']);
    $ext = $_GET['ext'];
    if (file_exists($doc)) {
        header('Content-Type: '. mime_content_type($doc));
        header('Content-Disposition: inline; filename="'.basename($doc).'"');
        readfile($doc);
        exit();
    }   else {  echo ('File not exists');    }
}

if (isset($_GET['testplan'])) {
    $path = $_GET['testplan'];
    if (file_exists($path)) {
        echo ('<style>body {background-color: black; color: white; font-size: 14pt;}</style><body>');
        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (strpos($line, 'Panel$ =') !== false) {
                echo ('<span style="color: green; font-weight: bold; font-size: 14pt;">'.$line.'</span><br>');
            }
        }
        foreach ($lines as $line) {
            echo ($line . '<br>');
        }
        echo ('</body>');
        exit();
    }
}
?>