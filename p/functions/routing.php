<?php 

function go($url, $time=0){
    
    // URL'yi normalize et - .php eklenmemişse ve tam path değilse düzenle
    if (strpos($url, '.php') === false && strpos($url, '/') !== 0 && strpos($url, 'http') !== 0) {
        $url = '/prolynweb/p/' . $url . '.php';
    }
    
    if(!headers_sent()) {
        if($time != 0){
            header("Refresh:".(int)$time.";url=".$url);
        }else{
            header("Location: ".$url, true);
        }
        exit;
    } else {
        // Header zaten gönderildiyse konsol uyarısı
        error_log("[GO] Uyarı: Header zaten gönderildi. Yönlendirme başarısız: " . $url);
        // Alternatif: JavaScript ile yönlendir
        echo '<script>window.location.href="' . $url . '";</script>';
        exit;
    }
}

function comeBack($time=0){
    
    $url = isset($_SERVER["HTTP_REFERER"]) ? filter_var($_SERVER["HTTP_REFERER"], FILTER_SANITIZE_URL) : "/";
    if(!headers_sent()) {
        if($time != 0 ){
            header("Refresh:".(int)$time.";url=".$url);
        }else{
            header("Location:".$url);
        }
        exit;
    }
}

function swallOk($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=ok");
        exit;
    }
}

function swallError($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=no");
        exit;
    }
}

function swallPass($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=pass");
        exit;
    }
}

function swallType($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=type");
        exit;
    }
}

function swallSifre($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=hata");
        exit;
    }
}

function swallSifreAyni($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=sifreayni");
        exit;
    }
}

function swallUserPasif($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=noaccess");
        exit;
    }
}

function swallUser($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=user");
        exit;
    }
}

function swallMsg($url, $status){
    if(!empty($status) && preg_match('/^[a-z0-9]+$/', $status) && !headers_sent()) {
        // URL'yi normalize et - .php eklenmemişse ve tam path değilse düzenle
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=".urlencode($status));
        exit;
    }
}

function swallYetki($url){
    if(!headers_sent()) {
        // URL'yi normalize et
        if (strpos($url, '.php') === false && strpos($url, '/') !== 0) {
            $url = '/prolynweb/p/' . $url . '.php';
        }
        header("Location:".$url."?islem=yetki");
        exit;
    }
}

function swallNotFound($message = "Sayfa bulunamadı"){
    if(!headers_sent()) {
        header("HTTP/1.1 404 Not Found");
        header("Location:/404.php?msg=".urlencode($message));
        exit;
    }
}

function swallSystemError($message = "Sistem hatası oluştu"){
    if(!headers_sent()) {
        header("HTTP/1.1 500 Internal Server Error");
        header("Location:/error.php?msg=".urlencode($message));
        exit;
    }
}