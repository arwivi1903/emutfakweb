<?php
/**
 * Prolyn Kurumsal Web Sitesi - Veritabanı Bağlantısı
 * Bu bağlantı sadece okuma amaçlı (Pricing, Blogs, vb.) kullanılır.
 * Kimlik bilgileri kaynak koddan çıkarıldı; .env dosyasından okunur.
 */

require_once __DIR__ . '/env.php';

$host    = prolynweb_env('DB_HOST', '127.0.0.1');
$db      = prolynweb_env('DB_NAME', 'prolyn_master');
$user    = prolynweb_env('DB_USER', '');
$pass    = prolynweb_env('DB_PASS', '');
$charset = prolynweb_env('DB_CHARSET', 'utf8mb4');

$appDebug = filter_var(prolynweb_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Hata detayı kullanıcıya gösterilmez; sadece loglanır.
    error_log('[prolynweb] DB bağlantı hatası: ' . $e->getMessage());
    if ($appDebug) {
        die('DB bağlantı hatası: ' . htmlspecialchars($e->getMessage()));
    }
    die("Sistem bakımı devam etmektedir. Lütfen daha sonra tekrar deneyiniz.");
}

// Tüm ayarları çekip global bir diziye atalım
$appSettings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE is_public = 1 OR category IN ('seo', 'social', 'contact', 'general')");
    while ($row = $stmt->fetch()) {
        $appSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch (\Exception $e) {
    // Ayarlar çekilemezse boş dizi kalsın
    error_log('[prolynweb] Ayarlar okunamadı: ' . $e->getMessage());
}

/** Geçerli sayfanın tam (canonical/og:url için) URL'sini döndürür. */
function prolynweb_current_url(): string
{
    // BASE_URL'in yalnızca şema+host kısmı kullanılır; REQUEST_URI zaten
    // gerçek yolu (dev ortamında /prolynweb/... alt yolu dahil) içerir.
    // İkisini birleştirmek BASE_URL bir alt yol içerdiğinde yolu
    // (ör. /prolynweb/prolynweb/...) ikiye katlıyordu.
    $parts = parse_url(prolynweb_env('BASE_URL', ''));
    $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    if (!empty($parts['port'])) {
        $origin .= ':' . $parts['port'];
    }
    $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $origin . $path;
}
