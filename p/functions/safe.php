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

/**
 * Modern, bağımsız (harici CSS/JS gerektirmeyen) hata sayfası.
 * AJAX/JSON isteklerinde JSON döner. Çıktıdan sonra exit eder.
 */
function prolyn_hata_sayfasi(int $kod, string $baslik, string $mesaj, string $ipucu = ''): void
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $ctype  = $_SERVER['CONTENT_TYPE'] ?? '';
    $xhr    = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    http_response_code($kod);
    if ($xhr || stripos($accept, 'application/json') !== false || stripos($ctype, 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $baslik . '. ' . $mesaj . ($ipucu !== '' ? ' ' . $ipucu : '')], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $e = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    $geri = $_SERVER['HTTP_REFERER'] ?? '';
    $geriGuvenli = ($geri !== '' && preg_match('#^https?://#i', $geri)) ? $geri : '';
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . $e($baslik) . '</title><style>'
       . ':root{color-scheme:light dark;--bg:#f4f6fb;--card:#fff;--tx:#1e2433;--mut:#6b7385;--ac:#e5484d;--pr:#3b6cf6;--bd:#e4e8f1}'
       . '@media(prefers-color-scheme:dark){:root{--bg:#0f1220;--card:#181c2e;--tx:#eef0f7;--mut:#9aa3b8;--bd:#272c44}}'
       . '*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;'
       . 'background:radial-gradient(1200px 600px at 10% -10%,rgba(59,108,246,.15),transparent),radial-gradient(900px 500px at 110% 110%,rgba(229,72,77,.12),transparent),var(--bg);'
       . 'font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:var(--tx)}'
       . '.c{max-width:460px;width:100%;background:var(--card);border:1px solid var(--bd);border-radius:20px;padding:40px 32px;text-align:center;box-shadow:0 20px 50px rgba(15,18,32,.12)}'
       . '.i{width:68px;height:68px;border-radius:50%;margin:0 auto 20px;display:grid;place-items:center;background:rgba(229,72,77,.12);color:var(--ac)}'
       . '.k{display:inline-block;font-size:12px;font-weight:700;letter-spacing:.08em;color:var(--ac);background:rgba(229,72,77,.1);padding:4px 10px;border-radius:999px;margin-bottom:12px}'
       . 'h1{font-size:22px;margin:0 0 10px;font-weight:700}p{margin:0 0 8px;color:var(--mut);line-height:1.6;font-size:15px}'
       . '.h{margin-top:14px;padding:12px 14px;border-radius:12px;background:rgba(59,108,246,.08);color:var(--tx);font-size:14px}'
       . '.b{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:26px}'
       . '.b a{font:inherit;font-weight:600;font-size:14px;padding:11px 20px;border-radius:12px;border:1px solid var(--bd);background:transparent;color:var(--tx);cursor:pointer;text-decoration:none}'
       . '.b .p{background:var(--pr);border-color:var(--pr);color:#fff}.b a:hover{opacity:.88}'
       . '</style></head><body><main class="c" role="alert"><div class="i"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 8.3-8 9-4.8-.7-8-4.5-8-9V6l8-3z"/><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg></div>'
       . '<span class="k">HATA ' . (int)$kod . '</span><h1>' . $e($baslik) . '</h1><p>' . $e($mesaj) . '</p>'
       . ($ipucu !== '' ? '<div class="h">' . $e($ipucu) . '</div>' : '')
       . '<div class="b">'
       . ($geriGuvenli !== '' ? '<a class="p" href="' . $e($geriGuvenli) . '">Sayfaya dön</a>' : '<a class="p" href="/">Ana sayfa</a>')
       . '<a href="">Yenile</a></div></main></body></html>';
    exit;
}

// CSRF token doğrulama yardımcı fonksiyonu (POST isteklerinde kullanılır)
// Normal formlar $_POST['csrf_token'] gönderir; application/json ile POST
// eden fetch() çağrıları ise token'ı X-CSRF-Token başlığında taşır
// (bkz. csrfTokenMeta() ve header.php'deki meta etiketi).
function checkCSRF()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!validateCSRFToken($token)) {
            prolyn_hata_sayfasi(
                419,
                'Oturum doğrulaması başarısız',
                'Güvenlik doğrulaması tamamlanamadı. Sayfa uzun süre açık kaldığı ya da oturum süresi dolduğu için olabilir.',
                'Sayfayı yenileyip işlemi tekrar deneyin.'
            );
        }
    }
}
 





// Otomatik CSRF Koruması Devrede
checkCSRF();
