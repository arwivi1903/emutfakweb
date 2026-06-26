<?php
/**
 * Prolyn Kurumsal Web Sitesi - Veritabanı Bağlantısı
 * Bu bağlantı sadece okuma amaçlı (Pricing, Blogs, vb.) kullanılır.
 */

$host = '127.0.0.1';
$db   = 'prolyn_master';
$user = 'root';
$pass = 'RootPass1903!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Sitede DB hatası varsa kritik bir hata göster
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
}
