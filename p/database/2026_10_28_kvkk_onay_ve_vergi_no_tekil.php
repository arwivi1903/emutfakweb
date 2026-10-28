<?php
/**
 * 28.10.2026 — Kayıt onayları ve vergi/TC no tekilliği (SADECE CLI)
 *
 * Kullanım:
 *   php p/database/2026_10_28_kvkk_onay_ve_vergi_no_tekil.php            # yalnızca rapor (hiçbir şey değiştirmez)
 *   php p/database/2026_10_28_kvkk_onay_ve_vergi_no_tekil.php --uygula   # kolonları ekler, numaraları temizler, tekil indeks kurar
 *
 * 1) kurumlar tablosuna onay kolonlarını ekler (yoksa).
 * 2) tax_number içindeki boşluk/tire/nokta karakterlerini temizler (yalnızca rakam kalır).
 * 3) Mükerrer vergi/TC no varsa LİSTELER ve indeks KURMAZ (önce test/deneme kayıtlarını temizleyin).
 * 4) Mükerrer yoksa tax_number üzerine tekil indeks kurar; böylece aynı anda gelen iki istek bile
 *    aynı numarayla ikinci kayıt açamaz (register.php 23000 hatasını yakalar).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../env.php';

$uygula = in_array('--uygula', $argv ?? [], true);
$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
    prolynweb_env('DB_USER', ''), prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo $uygula ? "== UYGULAMA MODU ==\n" : "== RAPOR MODU (değişiklik yapılmaz; uygulamak için --uygula) ==\n";

function kolon_var(PDO $pdo, string $t, string $c): bool {
    $q = $pdo->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $q->execute([$t, $c]);
    return (bool)$q->fetchColumn();
}

/* 1) Onay kolonları */
$kolonlar = [
    'kvkk_onay_tarihi'  => "DATETIME NULL COMMENT 'KVKK Aydınlatma Metni okundu onayı zamanı'",
    'kosul_onay_tarihi' => "DATETIME NULL COMMENT 'Kullanım Koşulları kabul zamanı'",
    'ticari_ileti_izni' => "TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ticari elektronik ileti izni (isteğe bağlı açık rıza)'",
    'onay_ip'           => "VARCHAR(45) NULL COMMENT 'Onayların verildiği IP adresi'",
    'onay_surumu'       => "VARCHAR(20) NULL COMMENT 'Onaylanan yasal metinlerin sürümü'",
];
foreach ($kolonlar as $ad => $tanim) {
    if (kolon_var($pdo, 'kurumlar', $ad)) { echo "[var]   kurumlar.$ad\n"; continue; }
    echo "[eksik] kurumlar.$ad\n";
    if ($uygula) { $pdo->exec("ALTER TABLE kurumlar ADD COLUMN `$ad` $tanim"); echo "        -> eklendi\n"; }
}

/* 2) Numara temizliği */
$temiz = "REPLACE(REPLACE(REPLACE(REPLACE(tax_number,' ',''),'-',''),'.',''),'/','')";
$kirli = (int)$pdo->query("SELECT COUNT(*) FROM kurumlar WHERE tax_number IS NOT NULL AND tax_number <> $temiz")->fetchColumn();
echo "Biçimi bozuk (boşluk/tire içeren) vergi/TC no sayısı: $kirli\n";

/* 3) Mükerrer raporu (temizlenmiş değer üzerinden) */
$dup = $pdo->query("SELECT $temiz AS no, COUNT(*) AS adet, GROUP_CONCAT(customer_id ORDER BY customer_id) AS ids
                    FROM kurumlar WHERE tax_number IS NOT NULL AND $temiz <> ''
                    GROUP BY no HAVING adet > 1")->fetchAll(PDO::FETCH_ASSOC);
$bos = (int)$pdo->query("SELECT COUNT(*) FROM kurumlar WHERE tax_number IS NULL OR $temiz = ''")->fetchColumn();
echo "Boş vergi/TC no'lu kurum sayısı: $bos\n";
if ($dup) {
    echo "MÜKERRER numaralar (indeks kurulamaz — önce bu kayıtları gözden geçirin):\n";
    foreach ($dup as $d) { echo "  no={$d['no']}  adet={$d['adet']}  customer_id: {$d['ids']}\n"; }
}

if (!$uygula) { echo "\nRapor bitti. Uygulamak için: --uygula\n"; exit(0); }

if ($kirli > 0) {
    $pdo->exec("UPDATE kurumlar SET tax_number = $temiz WHERE tax_number IS NOT NULL AND tax_number <> $temiz");
    echo "Numaralar temizlendi.\n";
}
if ($dup || $bos > 0) {
    echo "\nTekil indeks KURULMADI (mükerrer veya boş numara var). Düzelttikten sonra tekrar çalıştırın.\n";
    echo "register.php yine de uygulama seviyesinde mükerrer kaydı engeller; indeks yalnızca yarış durumuna karşı ek güvencedir.\n";
    exit(2);
}
$var = $pdo->query("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kurumlar' AND INDEX_NAME = 'uq_kurumlar_tax_number'")->fetchColumn();
if ($var) { echo "Tekil indeks zaten var.\n"; exit(0); }
$pdo->exec("ALTER TABLE kurumlar ADD UNIQUE KEY `uq_kurumlar_tax_number` (`tax_number`)");
echo "Tekil indeks kuruldu: uq_kurumlar_tax_number\n";
