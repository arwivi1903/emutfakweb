<?php



// Şifreleme - Session için statik hash (zamanı içermez)
function sifreleme($mail)
{
	$gizlianahtar = '6932877ce2dcc881d70859ea0ec556e1';
	// NOT: date('d.m.Y H:i:s') silinmiştir çünkü session'da sabit hash gerekir
	// Değişen zaman değeri her seferde farklı hash üretir ve DB sorgusunda eşleşmez
	return md5(sha1(md5($_SERVER['REMOTE_ADDR'] . $gizlianahtar . $mail . "ekolaymutfak" . $_SERVER['HTTP_USER_AGENT'])));
}

//post - get kontrol - Deprecated'den korunmuş versiyon
function security($text)
{
    
    if (!isset($_POST[$text]) || $_POST[$text] === null) {
        return '';
    }
    
    $text = trim((string)$_POST[$text]);
    $text = stripcslashes($text);
    $text = htmlspecialchars($text);

    return $text;
}

// Güvenli veri alma fonksiyonu
function getSafeData($fieldName, $type = 'string', $default = null) 
{
    if (!isset($_POST[$fieldName])) {
        return $default;
    }
    
    $value = $_POST[$fieldName];
    
    switch($type) {
        case 'int':
        case 'integer':
            return intval($value);
            
        case 'float':
        case 'decimal':
            $value = str_replace(['.', ','], ['', '.'], $value);
            return floatval($value);
            
        case 'string':
        case 'text':
        default:
            $value = trim($value);
            $value = stripcslashes($value);
            $value = htmlspecialchars($value);
            return $value;
    }
}

// Genel parametre alma fonksiyonu
function getParam($name, $type = 'string', $source = 'auto', $default = null)
{
    $src = null;
    if ($source === 'post') {
        $src = isset($_POST[$name]) ? $_POST : null;
    } elseif ($source === 'get') {
        $src = isset($_GET[$name]) ? $_GET : null;
    } else { // auto
        if (isset($_POST[$name])) {
            $src = $_POST;
        } elseif (isset($_GET[$name])) {
            $src = $_GET;
        }
    }

    if ($src === null) return $default;
    $value = $src[$name];

    switch ($type) {
        case 'int':
        case 'integer':
            return intval($value);
        case 'float':
        case 'decimal':
            // 1.234,56 -> 1234.56
            $value = str_replace(['.', ','], ['', '.'], (string)$value);
            return floatval($value);
        case 'bool':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'string':
        case 'text':
        default:
            $value = trim((string)$value);
            $value = stripcslashes($value);
            $value = htmlspecialchars($value);
            return $value;
    }
}

// büyük harf türkçe
function tr_strtoupper($text)
{
    $search=array("ç","i","ı","ğ","ö","ş","ü");
    $replace=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
    $text=str_replace($search,$replace,$text);
    $text=strtoupper($text);
    return $text;
}

// küçük harf türkçe
function tr_strtolower($text)
{
    $search=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
    $replace=array("ç","i","ı","ğ","ö","ş","ü");
    $text=str_replace($search,$replace,$text); 
    $text=strtolower($text);
    return $text;
} 

// Seo Link
function seourl($str)
{
    $str = trim((string)$str);
    // Türkçe karakter normalizasyonu
    $map = [
        'ç'=>'c','Ç'=>'c','ğ'=>'g','Ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','Ö'=>'o','ş'=>'s','Ş'=>'s','ü'=>'u','Ü'=>'u'
    ];
    $str = strtr($str, $map);
    // Küçültme
    $str = mb_strtolower($str, 'UTF-8');
    // Harf-rakam dışı karakterleri tireye çevir
    $str = preg_replace('/[^a-z0-9]+/u', '-', $str);
    // Birden fazla tireyi tek tireye indir
    $str = preg_replace('/-+/', '-', $str);
    // Baş-sondaki tireleri temizle
    $str = trim($str, '-');
    return $str;
}

// Sayfa adı alma
function getPageName()
{
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    return $uri === '' ? 'home' : $uri;
}

/**
 * CSRF Token Sistemi
 * Session tabanlı CSRF koruması
 */

// CSRF token oluştur ve session'a kaydet
function generateCSRFToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// CSRF token'ı doğrula
function validateCSRFToken($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// CSRF token input alanı HTML olarak döndür
function csrfTokenField()
{
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// CSRF token meta tag olarak döndür (AJAX için)
function csrfTokenMeta()
{
    $token = generateCSRFToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// CSRF token doğrulama yardımcı fonksiyonu (POST isteklerinde kullanılır)
function checkCSRF()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token doğrulaması başarısız. Lütfen sayfayı yenileyin ve tekrar deneyin.');
        }
    }
}
 




