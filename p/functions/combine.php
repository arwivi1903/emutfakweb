<?php

// functions klasöründeki tüm dosyaları dahil et
$functionsDir = __DIR__ . "/"; // functions klasörünün tam yolu

// echo "functions klasörü yolu: " . $functionsDir . "<br>";

if (is_dir($functionsDir)) {
    // Klasördeki tüm .php dosyalarını al
    $phpFiles = glob($functionsDir . "*.php"); // php dosyalarını al
    
    // Dosya listesi var mı kontrol et
    if ($phpFiles) {
        foreach ($phpFiles as $functionFile) {
            // combine.php'yi kendi kendini include etmesini engelle
            if (basename($functionFile) === 'combine.php') {
                continue;
            }
            // echo "Dahil edilen dosya: " . $functionFile . "<br>";
            require_once $functionFile; // Dosyayı dahil et
        }
    } else {
        echo "PHP dosyaları bulunamadı!";
    }
} else {
    echo "Functions klasörü bulunamadı!";
}

// var_dump($phpFiles); // Tüm bulunan dosyaları kontrol et

?>