<?php
/**
 * Prolyn - Kampanya iletilerinden çıkış (ret) sayfası
 * GET: onay ekranı · POST: reddi kaydeder (List-Unsubscribe-Post tek tık desteği dahil)
 */
require_once __DIR__ . '/env.php';
require_once 'db.php';

header('X-Robots-Tag: noindex, nofollow');
$token = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$token = preg_match('/^[a-f0-9]{32}$/', $token) ? $token : '';
$durum = 'onay';
$firma = '';

try {
    $lead = null;
    if ($token !== '') {
        $st = $pdo->prepare("SELECT lead_id, firma_adi, ret_tarihi FROM potansiyel_musteriler WHERE ret_token = ? LIMIT 1");
        $st->execute([$token]);
        $lead = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    if (!$lead) {
        $durum = 'gecersiz';
    } else {
        $firma = (string)$lead['firma_adi'];
        if (!empty($lead['ret_tarihi'])) {
            $durum = 'zaten';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo->prepare("UPDATE potansiyel_musteriler SET durum = 'ret', ret_tarihi = NOW() WHERE lead_id = ?")->execute([(int)$lead['lead_id']]);
            $pdo->prepare("INSERT INTO lead_aktiviteler (lead_id, tur, aciklama) VALUES (?, 'sistem', 'Alıcı ret sayfasından iletişim istemediğini bildirdi.')")->execute([(int)$lead['lead_id']]);
            // Bekleyen kampanya e-postalarını iptal et
            $pdo->prepare("UPDATE mail_kuyrugu SET status = 'cancelled', last_error = 'Alıcı reddetti' WHERE status = 'pending' AND source = 'lead_kampanya' AND payload LIKE ?")
                ->execute(['%"lead_id":' . (int)$lead['lead_id'] . ',%']);
            $durum = 'tamam';
        }
    }
} catch (Throwable $e) {
    error_log('[ret] ' . $e->getMessage());
    $durum = 'hata';
}

// Tek tık (RFC 8058) istekleri gövde beklemez
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['List-Unsubscribe'])) {
    http_response_code($durum === 'tamam' || $durum === 'zaten' ? 200 : 400);
    exit;
}

$metin = [
    'onay'     => ['İletişim listesinden çıkmak istiyor musunuz?', ($firma !== '' ? $firma . ' için ' : '') . 'Prolyn tanıtım e-postaları ve mesajları durdurulacak.'],
    'tamam'    => ['Çıkışınız alındı', 'Size bir daha tanıtım e-postası veya mesajı göndermeyeceğiz. İlginiz için teşekkürler.'],
    'zaten'    => ['Zaten listeden çıktınız', 'Bu adres için tanıtım iletileri daha önce durduruldu.'],
    'gecersiz' => ['Bağlantı geçersiz', 'Bağlantı eksik ya da hatalı. İletilerin durdurulmasını istiyorsanız bu e-postaya "istemiyorum" yazarak yanıt verin.'],
    'hata'     => ['Şu an işlenemedi', 'Lütfen biraz sonra tekrar deneyin ya da e-postaya "istemiyorum" yazarak yanıt verin.'],
][$durum];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>İletişim tercihi · Prolyn</title>
<style>
:root{color-scheme:light dark;--bg:#0f1220;--card:#181c2e;--tx:#eef0f7;--mut:#9aa3b8;--pr:#3b6cf6;--ok:#22c55e;--bd:#272c44}
@media (prefers-color-scheme: light){:root{--bg:#f4f6fb;--card:#fff;--tx:#1e2433;--mut:#6b7385;--bd:#e4e8f1}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(1000px 500px at 10% -10%,rgba(59,108,246,.18),transparent),var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:var(--tx)}
.c{max-width:460px;width:100%;background:var(--card);border:1px solid var(--bd);border-radius:20px;padding:40px 32px;text-align:center}
h1{font-size:22px;margin:0 0 12px}p{color:var(--mut);line-height:1.6;margin:0 0 24px}
button,a.b{font:inherit;font-weight:600;padding:12px 22px;border-radius:12px;border:0;background:var(--pr);color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
a.l{color:var(--mut);font-size:14px}.ok{color:var(--ok);font-size:40px;margin-bottom:8px}
</style>
</head>
<body>
<main class="c">
  <?php if ($durum === 'tamam' || $durum === 'zaten'): ?><div class="ok" aria-hidden="true">✓</div><?php endif; ?>
  <h1><?= $e($metin[0]) ?></h1>
  <p><?= $e($metin[1]) ?></p>
  <?php if ($durum === 'onay'): ?>
    <form method="post" action="ret">
      <input type="hidden" name="t" value="<?= $e($token) ?>">
      <button type="submit">Evet, listeden çıkar</button>
    </form>
  <?php else: ?>
    <a class="l" href="/">prolyn.net</a>
  <?php endif; ?>
</main>
</body>
</html>
