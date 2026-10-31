<?php
/**
 * 31.10.2026 — Blog kategorileri ve video rehberi alanları (SADECE CLI)
 *
 * Kullanım:
 *   php p/database/2026_10_31_blog_video_gelistirme.php            # yalnızca rapor
 *   php p/database/2026_10_31_blog_video_gelistirme.php --uygula   # eksik kolon/indeksleri ekler
 *
 * blog_yazilari : kategori (liste süzgeci), (status, published_at) ve kategori indeksleri
 * videolar      : description (rehber açıklaması), kategori, sure (ör. 04:35)
 * Veri silinmez/değiştirilmez; yalnızca eksik olanlar eklenir (tekrar çalıştırmak güvenlidir).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../env.php';

$uygula = in_array('--uygula', $argv ?? [], true);
$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
    prolynweb_env('DB_USER', ''), prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo $uygula ? "== UYGULAMA MODU ==\n" : "== RAPOR MODU (uygulamak için --uygula) ==\n";

function kolon_var(PDO $pdo, string $t, string $c): bool {
    $q = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $q->execute([$t, $c]);
    return (bool)$q->fetchColumn();
}
function indeks_var(PDO $pdo, string $t, string $i): bool {
    $q = $pdo->prepare("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $q->execute([$t, $i]);
    return (bool)$q->fetchColumn();
}

$kolonlar = [
    'blog_yazilari' => [
        'kategori' => "VARCHAR(60) NULL COMMENT 'Blog kategorisi (liste süzgeci)' AFTER excerpt",
    ],
    'videolar' => [
        'description' => "TEXT NULL COMMENT 'Video rehberi açıklaması' AFTER title",
        'kategori'    => "VARCHAR(60) NULL COMMENT 'Video kategorisi' AFTER description",
        'sure'        => "VARCHAR(8) NULL COMMENT 'Süre, ör. 04:35' AFTER kategori",
    ],
];
foreach ($kolonlar as $tablo => $liste) {
    foreach ($liste as $ad => $tanim) {
        if (kolon_var($pdo, $tablo, $ad)) { echo "[var]   $tablo.$ad\n"; continue; }
        echo "[eksik] $tablo.$ad\n";
        if ($uygula) { $pdo->exec("ALTER TABLE `$tablo` ADD COLUMN `$ad` $tanim"); echo "        -> eklendi\n"; }
    }
}

$indeksler = [
    ['blog_yazilari', 'idx_durum_tarih', '(status, published_at)'],
    ['blog_yazilari', 'idx_kategori',    '(kategori)'],
    ['videolar',      'idx_durum_sira',  '(status, order_no)'],
];
foreach ($indeksler as [$t, $i, $k]) {
    if (indeks_var($pdo, $t, $i)) { echo "[var]   indeks $t.$i\n"; continue; }
    echo "[eksik] indeks $t.$i\n";
    if ($uygula) { $pdo->exec("ALTER TABLE `$t` ADD INDEX `$i` $k"); echo "        -> eklendi\n"; }
}
echo "Bitti.\n";
