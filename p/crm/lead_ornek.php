<?php
/**
 * Lead içe aktarma için örnek CSV (Excel'de açılır).
 * Başlıklar lead_import.php'nin desteklediği sütunlarla birebir aynıdır; tek kaynak burasıdır.
 * Yalnızca yetkili yönetici indirebilir; veri tabanına yazmaz.
 */
require_once __DIR__ . '/../functions/auth.php';
start_secure_session();
require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
$db = new Database();
oturumkontrol();
Auth::requireModule('manage_tenants');

$basliklar = ['firma_adi', 'telefon', 'whatsapp', 'eposta', 'web', 'il', 'ilce', 'adres', 'segment', 'imalat_montaj', 'buyuk_marka', 'google_puan', 'puan', 'oncelik', 'kaynak', 'place_id', 'maps_url'];
// Açıkça uydurma örnekler: yanlışlıkla içe aktarılırsa kolayca fark edilip silinsin
$satirlar = [
    ['ÖRNEK Mutfak Atölyesi', '+905320000001', '1', 'ornek1@example.com', 'https://www.ornek-mutfak.example', 'Kocaeli', 'İzmit', 'Örnek Mah. Örnek Sok. No:1', 'mutfak', '1', '0', '4.6', '80', 'A', 'google_maps', 'ORNEK_PLACE_ID_1', 'https://maps.google.com/?cid=1'],
    ['ÖRNEK Banyo Dekorasyon', '+902620000002', '0', '', '', 'İstanbul', 'Kadıköy', '', 'banyo', '0', '0', '', '60', 'B', 'manuel', '', ''],
    ['ÖRNEK Dolap Mobilya', '', '', 'ornek3@example.com', 'https://instagram.com/ornekdolap', 'Ankara', 'Çankaya', '', 'dolap', '1', '0', '4.2', '', 'C', 'csv', '', ''],
];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="prolyn-lead-ornek.csv"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // Excel Türkçe karakterleri doğru okusun (UTF-8 BOM)
fputcsv($out, $basliklar, ';', '"', '\\');
foreach ($satirlar as $s) { fputcsv($out, $s, ';', '"', '\\'); }
fclose($out);
exit;
