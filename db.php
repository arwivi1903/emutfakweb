<?php
/**
 * Prolyn Kurumsal Web Sitesi - Veritabanı Bağlantısı
 * Bu bağlantı sadece okuma amaçlı (Pricing, Blogs, vb.) kullanılır.
 * Kimlik bilgileri kaynak koddan çıkarıldı; .env dosyasından okunur.
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/partials/onbellek.php';
require_once __DIR__ . '/partials/performans.php';

// Yavaş istekleri günlüğe yazar (ayrıntı: partials/performans.php).
prolynweb_perf_baslat();

$appDebug = filter_var(prolynweb_env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

/**
 * Veritabanı bağlantısı (istek başına en fazla bir kez; başarısızlık da hatırlanır, böylece
 * DB kapalıyken aynı istekte tekrar tekrar zaman aşımı beklenmez). Hata fırlatır.
 */
function prolynweb_baglan(): PDO
{
    static $pdo = null;
    static $hata = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if ($hata instanceof \Throwable) {
        throw $hata;
    }
    $host    = prolynweb_env('DB_HOST', '127.0.0.1');
    $db      = prolynweb_env('DB_NAME', 'prolyn_master');
    $user    = prolynweb_env('DB_USER', '');
    $pass    = prolynweb_env('DB_PASS', '');
    $charset = prolynweb_env('DB_CHARSET', 'utf8mb4');
    $dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5, // takılırsa sonsuza kadar bekleme
    ];
    $t0 = microtime(true);
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\Throwable $e) {
        $hata = $e;
        prolynweb_perf_isaretle('db_baglanti_ms', (int)round((microtime(true) - $t0) * 1000));
        throw $e;
    }
    prolynweb_perf_isaretle('db_baglanti_ms', (int)round((microtime(true) - $t0) * 1000));
    return $pdo;
}

/*
 * Vitrin sayfaları (ana sayfa, fiyat, modüller, sektör sayfası) db.php'den ÖNCE
 *   define('PROLYNWEB_LAZY_DB', true);
 * derse: ayarlar önbellekteyse veritabanına HİÇ bağlanılmaz ($pdo = null). Paket verisi gerekirse
 * prolynweb_paketler() kendisi bağlanır. Formlu sayfalar (register, contact...) bunu tanımlamaz;
 * onlarda $pdo her zaman hazırdır, yalnızca ayar sorgusu önbellekten gelebilir.
 */
$lazyDb      = defined('PROLYNWEB_LAZY_DB') && PROLYNWEB_LAZY_DB;
$pdo         = null;
$appSettings = [];

$ayarOnbellek = prolynweb_onbellek_oku('ayarlar');
if ($ayarOnbellek !== null) {
    $appSettings = $ayarOnbellek;
    prolynweb_perf_isaretle('ayar', 'onbellek');
}

if (!($lazyDb && $ayarOnbellek !== null)) {
    try {
        $pdo = prolynweb_baglan();
    } catch (\PDOException $e) {
        // Hata detayı kullanıcıya gösterilmez; sadece loglanır.
        error_log('[prolynweb] DB bağlantı hatası: ' . $e->getMessage());
        // Vitrin sayfasıysa ve eski bir ayar önbelleği varsa bakım ekranı yerine onu kullan.
        $bayat = $lazyDb ? prolynweb_onbellek_oku('ayarlar', 0, true) : null;
        if ($bayat !== null) {
            $appSettings = $bayat;
            prolynweb_perf_isaretle('ayar', 'bayat');
        } else {
            if ($appDebug) {
                die('DB bağlantı hatası: ' . htmlspecialchars($e->getMessage()));
            }
            die("Sistem bakımı devam etmektedir. Lütfen daha sonra tekrar deneyiniz.");
        }
    }

    // Tüm ayarları çekip global bir diziye atalım (önbellek yoksa)
    if ($pdo instanceof PDO && $ayarOnbellek === null) {
        prolynweb_perf_isaretle('ayar', 'db');
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE is_public = 1 OR category IN ('seo', 'social', 'contact', 'general')");
            while ($row = $stmt->fetch()) {
                $appSettings[$row['setting_key']] = $row['setting_value'];
            }
            prolynweb_onbellek_yaz('ayarlar', $appSettings);
        } catch (\Exception $e) {
            // Ayarlar çekilemezse boş dizi kalsın
            error_log('[prolynweb] Ayarlar okunamadı: ' . $e->getMessage());
        }
    }
}

// Mevcut müşteri girişi (ERP paneli) — üretimde app.prolyn.net, yerelde
// localhost/prolyn; nav'daki "Giriş Yap" bağlantısı bunu kullanır.
$appPanelUrl = rtrim((string)prolynweb_env('APP_PANEL_URL', '/prolyn'), '/') . '/login.php';

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
