<?php
/**
 * GEÇİCİ TANI ARACI (yalnızca yerel geliştirme): panel sayfalarını sizin oturumunuzla GET ile açar,
 * her sayfada kaç tablo satırı geldiğini, hata/uyarı metni olup olmadığını ve ilgili master tabloların
 * var olup olmadığını, sütunlarını ve satır sayısını raporlar. Sonuç ekranda ve p/cache/dashboard/sayfa_tarama.json dosyasında.
 * Hiçbir kayıt değiştirmez (yalnızca GET + SHOW/COUNT). İşiniz bitince bu dosyayı silin.
 */
require_once __DIR__ . '/../functions/auth.php';
start_secure_session();
require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
$db = new Database();
oturumkontrol();
Auth::requireModule('manage_tenants');

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Üretimde çalışmasın: yalnızca yerel adresler
$host = strtolower(explode(':', (string)($_SERVER['HTTP_HOST'] ?? ''))[0]);
if (!in_array($host, ['localhost', '127.0.0.1', '[::1]'], true) && substr($host, -10) !== '.localhost') {
    http_response_code(403);
    exit('Bu araç yalnızca yerel geliştirme ortamında çalışır.');
}

$sayfalar = [
    'crm/feature_usage', 'crm/subscriptions', 'crm/subscription_history', 'crm/tickets', 'crm/announcements', 'crm/coupons',
    'content/sales', 'finance/invoices', 'finance/invoice_addresses', 'finance/payments',
    'website/modules', 'website/blog', 'website/videos',
    'settings/admins', 'settings/api_keys', 'settings/email_queue', 'settings/files', 'settings/logs_archive',
    'logs/audit', 'logs/login_attempts', 'logs/password_resets', 'logs/system_errors', 'logs/system_events', 'logs/maintenance',
    'logs/activity', 'logs/user_activity', 'logs/notifications', 'logs/notification_queue', 'logs/webhooks', 'logs/webhook_logs',
];
$tablolar = [
    'kurumlar', 'abonelikler', 'abonelik_gecmisi', 'odemeler', 'paket_ozellikleri', 'destek_talepleri', 'duyurular', 'kuponlar',
    'satis_icerigi', 'faturalar', 'fatura_adresleri', 'website_modules', 'blog_yazilari', 'videolar', 'roller', 'yonetici_rolleri',
    'yoneticiler', 'api_anahtarlari', 'mail_kuyrugu', 'yonetici_loglari_yedek', 'denetim_loglari', 'giris_denemeleri', 'sifre_sifirlama',
    'sistem_hatalari', 'sistem_olaylari', 'bakim_loglari', 'aktivite_loglari', 'kullanici_aktivite_loglari', 'bildirimler',
    'bildirim_kuyrugu', 'webhooklar', 'webhook_loglari',
];

$sonuc = ['tarih' => date('c'), 'sayfalar' => [], 'tablolar' => []];

// ---- Tablolar: var mı, kaç satır, hangi sütunlar ----
$mevcut = array_map('strtolower', array_column((array)$db->getRowsAssoc("SHOW TABLES"), 'Tables_in_' . $db->MYSQL_DB));
if (!$mevcut) { // sütun adı farklıysa ilk sütunu al
    foreach ((array)$db->getRowsAssoc("SHOW TABLES") as $r) { $mevcut[] = strtolower((string)reset($r)); }
}
foreach ($tablolar as $t) {
    if (!in_array($t, $mevcut, true)) { $sonuc['tablolar'][$t] = ['var' => false]; continue; }
    $kolonlar = array_column((array)$db->getRowsAssoc("SHOW COLUMNS FROM `$t`"), 'Type', 'Field');
    $adet = $db->getColumn("SELECT COUNT(*) FROM `$t`");
    $sonuc['tablolar'][$t] = ['var' => true, 'satir' => (int)$adet, 'sutunlar' => $kolonlar];
}

// ---- Sayfalar: oturum çerezi ile sunucudan kendine GET ----
$oturumAdi = session_name();
$oturumId = session_id();
session_write_close(); // aynı oturumu kilitlemesin
$sema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$taban = $sema . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
foreach ($sayfalar as $s) {
    $ch = curl_init($taban . $s)  /* temiz adresler: .php uzantısız */;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIE => $oturumAdi . '=' . $oturumId, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $html = (string)curl_exec($ch);
    $kod = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $loc = (string)curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    $govde = preg_replace('~<script\b.*?</script>~is', '', $html);
    preg_match_all('~<tbody\b.*?</tbody>~is', $govde, $tb);
    $satir = 0; $bos = false;
    foreach ($tb[0] as $blok) {
        $satir += preg_match_all('~<tr\b~i', $blok);
        if (preg_match('~<td[^>]*colspan~i', $blok)) { $bos = true; }
    }
    $sonuc['sayfalar'][$s] = [
        'http' => $kod, 'yonlendirme' => $loc, 'bayt' => strlen($html), 'tablo' => preg_match_all('~<table\b~i', $govde),
        'tbody_satir' => $satir, 'bos_mesaji_var' => $bos, 'sayfalama' => (bool)preg_match('~class="[^"]*\bpagination\b~i', $govde),
        'hata_metni' => (bool)preg_match('~(Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught|Stack trace|SQLSTATE)~i', $html),
        'hata_ornek' => preg_match('~(Fatal error|Parse error|Warning:|Notice:|Uncaught|SQLSTATE)[^<]{0,200}~i', $html, $m) ? trim($m[0]) : '',
    ];
}

$yol = __DIR__ . '/../cache/dashboard/sayfa_tarama.json';
$yazildi = @file_put_contents($yol, json_encode($sonuc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
?><!DOCTYPE html>
<html lang="tr"><head><meta charset="utf-8"><title>Sayfa tarama</title>
<style>body{font:14px system-ui;margin:24px}table{border-collapse:collapse;margin-bottom:28px}td,th{border:1px solid #ddd;padding:6px 10px;text-align:left}th{background:#f4f6fb}.k{color:#c00;font-weight:600}.o{color:#0a7d3b}</style></head><body>
<h2>Sayfalar</h2>
<table><tr><th>Sayfa</th><th>HTTP</th><th>Tablo</th><th>Satır</th><th>Boş mesajı</th><th>Sayfalama</th><th>Hata metni</th></tr>
<?php foreach ($sonuc['sayfalar'] as $s => $r): ?>
<tr><td><?= $e($s) ?></td><td class="<?= $r['http'] === 200 ? 'o' : 'k' ?>"><?= $e($r['http'] . ($r['yonlendirme'] ? ' → ' . $r['yonlendirme'] : '')) ?></td><td><?= (int)$r['tablo'] ?></td><td><?= (int)$r['tbody_satir'] ?></td><td><?= $r['bos_mesaji_var'] ? 'evet' : '-' ?></td><td><?= $r['sayfalama'] ? 'evet' : '-' ?></td><td class="<?= $r['hata_metni'] ? 'k' : '' ?>"><?= $r['hata_metni'] ? $e($r['hata_ornek'] ?: 'var') : '-' ?></td></tr>
<?php endforeach; ?></table>
<h2>Tablolar</h2>
<table><tr><th>Tablo</th><th>Durum</th><th>Satır</th><th>Sütunlar</th></tr>
<?php foreach ($sonuc['tablolar'] as $t => $r): ?>
<tr><td><?= $e($t) ?></td><td class="<?= $r['var'] ? 'o' : 'k' ?>"><?= $r['var'] ? 'var' : 'YOK' ?></td><td><?= $r['var'] ? (int)$r['satir'] : '-' ?></td><td style="font-size:12px"><?= $r['var'] ? $e(implode(', ', array_keys($r['sutunlar']))) : '' ?></td></tr>
<?php endforeach; ?></table>
<p><?= $yazildi !== false ? 'Ayrıntılı sonuç p/cache/dashboard/sayfa_tarama.json dosyasına yazıldı.' : 'Dosya yazılamadı (p/cache/dashboard yazma izni yok); bu ekranı paylaşın.' ?></p>
</body></html>
