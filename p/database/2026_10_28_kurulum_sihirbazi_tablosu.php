<?php
/**
 * 28.10.2026 — kurulum_sihirbazi tablosunu şablon DB'ye ve tüm kiracı DB'lerine ekler (SADECE CLI)
 *
 * Kullanım:
 *   php p/database/2026_10_28_kurulum_sihirbazi_tablosu.php            # rapor (hiçbir şey değiştirmez)
 *   php p/database/2026_10_28_kurulum_sihirbazi_tablosu.php --uygula   # eksik yerlerde CREATE TABLE IF NOT EXISTS
 *
 * Şablon: TENANT_TEMPLATE_DB (.env). Kiracılar: master `kurumlar.database_name`.
 * cPanel modunda tüm DB'ler aynı uygulama kullanıcısıyla (DB_USER) erişilebilir olduğundan ayrı yetki gerekmez.
 * Veri değiştirmez; yalnızca tablo yoksa oluşturur.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../env.php';

$uygula = in_array('--uygula', $argv ?? [], true);
$host = prolynweb_env('DB_HOST', '127.0.0.1');
$user = prolynweb_env('DB_USER', '');
$pass = prolynweb_env('DB_PASS', '');
$opt  = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$sql = "CREATE TABLE IF NOT EXISTS `kurulum_sihirbazi` (
  `FirmaID` int(11) NOT NULL COMMENT 'firmalar.FirmaID — kurulum sihirbazı durumu firma başına tutulur',
  `Durum`   varchar(12) NOT NULL COMMENT 'tamamlandi | atlandi',
  `UserID`  int(11) DEFAULT NULL COMMENT 'Sihirbazı bitiren/atlayan kullanıcı',
  `Tarih`   datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Bitirme/atlama zamanı',
  PRIMARY KEY (`FirmaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='İlk kurulum sihirbazı durumu'";

echo $uygula ? "== UYGULAMA MODU ==\n" : "== RAPOR MODU (değişiklik yapılmaz; uygulamak için --uygula) ==\n";

$master = new PDO("mysql:host={$host};dbname=" . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4', $user, $pass, $opt);
$hedefler = [prolynweb_env('TENANT_TEMPLATE_DB', 'prolyn_default') => 'şablon'];
foreach ($master->query("SELECT customer_id, database_name FROM kurumlar WHERE database_name IS NOT NULL AND database_name <> '' ORDER BY customer_id") as $r) {
    $hedefler[$r['database_name']] = 'kiracı #' . $r['customer_id'];
}

foreach ($hedefler as $dbAd => $etiket) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $dbAd)) { echo "[atlandı] geçersiz ad: {$dbAd}\n"; continue; }
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$dbAd};charset=utf8mb4", $user, $pass, $opt);
        $var = (bool)$pdo->query("SHOW TABLES LIKE 'kurulum_sihirbazi'")->fetchColumn();
        if ($var) { echo "[var]   {$dbAd} ({$etiket})\n"; continue; }
        echo "[eksik] {$dbAd} ({$etiket})\n";
        if ($uygula) { $pdo->exec($sql); echo "        -> oluşturuldu\n"; }
    } catch (Throwable $e) {
        echo "[HATA]  {$dbAd} ({$etiket}): " . $e->getMessage() . "\n";
    }
}
if (!$uygula) { echo "\nRapor bitti. Uygulamak için: --uygula\n"; }
