<?php

// Tüm .class.php dosyalarını otomatik yükle
$classDir = __DIR__;
if (is_dir($classDir)) {
    $files = scandir($classDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php' && strpos($file, '.class.php') !== false) {
            require_once $classDir . '/' . $file;
        }
    }
}

// Fallback: Istenilen sınıf dosyasını yükle
spl_autoload_register(function($class){
    $path = __DIR__."/".$class.".class.php";
    if(file_exists($path)){
        require_once $path;
    }
});

?>