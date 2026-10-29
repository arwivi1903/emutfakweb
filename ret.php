<?php
/**
 * Prolyn - Kampanya iletilerinden çıkış (ret) sayfası
 *
 * Akışlar:
 *  1) Geçerli token  → GET: onay ekranı · POST: reddi kaydeder (List-Unsubscribe-Post tek tık dahil)
 *  2) Geçersiz/eksik → e-posta adresiyle çıkış talebi formu. Talep hem anında uygulanır
 *     (eşleşen kayıt durdurulur, bekleyen kampanya e-postaları iptal edilir) hem de
 *     "Gelen Mesajlar"a düşer, böylece ekip izleyebilir.
 *  3) Örnek token (şablon önizlemesi) → bilgilendirme, işlem yapılmaz
 */
require_once __DIR__ . '/env.php';
require_once 'db.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || prolynweb_env('APP_ENV', 'development') === 'production';
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $secureCookie,
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}
if (empty($_SESSION['ret_csrf'])) {
    $_SESSION['ret_csrf'] = bin2hex(random_bytes(32));
}

$token = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$token = preg_match('/^[a-f0-9]{32}$/', $token) ? $token : '';
$ornekToken = ($token === str_repeat('0', 32)); // şablon önizlemesindeki sahte bağlantı
$durum = 'onay';
$firma = '';
$formHata = '';
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

/** Bir e-posta adresi için çıkışı uygular; etkilenen kayıt sayısını döner (sızıntı olmaması için kullanıcıya gösterilmez). */
$epostaIleCik = static function (PDO $pdo, string $eposta): int {
    $leadler = $pdo->prepare("SELECT lead_id FROM potansiyel_musteriler WHERE LOWER(eposta) = ? AND ret_tarihi IS NULL");
    $leadler->execute([$eposta]);
    $idler = $leadler->fetchAll(PDO::FETCH_COLUMN);
    foreach ($idler as $lid) {
        $pdo->prepare("UPDATE potansiyel_musteriler SET durum = 'ret', ret_tarihi = NOW() WHERE lead_id = ?")->execute([(int)$lid]);
        $pdo->prepare("INSERT INTO lead_aktiviteler (lead_id, tur, aciklama) VALUES (?, 'sistem', 'Alıcı ret sayfasından e-posta adresiyle iletişim istemediğini bildirdi.')")->execute([(int)$lid]);
    }
    // Aynı adrese bekleyen tüm kampanya e-postalarını iptal et
    $pdo->prepare("UPDATE mail_kuyrugu SET status = 'cancelled', last_error = 'Alıcı reddetti' WHERE status = 'pending' AND source = 'lead_kampanya' AND LOWER(recipient) = ?")
        ->execute([$eposta]);
    return count($idler);
};

try {
    $lead = null;
    if ($token !== '' && !$ornekToken) {
        $st = $pdo->prepare("SELECT lead_id, firma_adi, ret_tarihi FROM potansiyel_musteriler WHERE ret_token = ? LIMIT 1");
        $st->execute([$token]);
        $lead = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if ($ornekToken) {
        $durum = 'ornek';
    } elseif (!$lead) {
        $durum = 'gecersiz';

        // E-posta ile çıkış talebi (yalnızca tarayıcıdan gelen form; tek tık POST'ları ayrıdır)
        if ($isPost && !isset($_POST['List-Unsubscribe']) && isset($_POST['eposta'])) {
            $eposta = mb_strtolower(trim((string)$_POST['eposta']));
            $_SESSION['ret_deneme'] = (int)($_SESSION['ret_deneme'] ?? 0) + 1;
            if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['ret_csrf'], (string)$_POST['csrf_token'])) {
                $formHata = 'Oturum doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
            } elseif (!empty($_POST['website'])) {
                $durum = 'talep'; // honeypot: bota başarılıymış gibi görün, hiçbir şey yazma
            } elseif ($_SESSION['ret_deneme'] > 5) {
                $formHata = 'Çok fazla deneme yapıldı. Lütfen daha sonra tekrar deneyin.';
            } elseif (!filter_var($eposta, FILTER_VALIDATE_EMAIL) || mb_strlen($eposta) > 191) {
                $formHata = 'Lütfen geçerli bir e-posta adresi yazın.';
            } else {
                $pdo->exec("CREATE TABLE IF NOT EXISTS iletisim_mesajlari (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ad VARCHAR(191) NOT NULL,
                    email VARCHAR(191) NOT NULL,
                    konu VARCHAR(255) NOT NULL,
                    mesaj TEXT NOT NULL,
                    ip VARCHAR(45) DEFAULT NULL,
                    user_agent VARCHAR(255) DEFAULT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'new',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_status (status),
                    INDEX idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $pdo->beginTransaction();
                $etkilenen = $epostaIleCik($pdo, $eposta);
                $pdo->prepare("INSERT INTO iletisim_mesajlari (ad, email, konu, mesaj, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([
                        'Çıkış talebi',
                        $eposta,
                        '[Çıkış talebi] İletişim istemiyorum',
                        "Alıcı ret sayfasından bu adres için iletişimin durdurulmasını talep etti.\nEşleşen kayıt sayısı: {$etkilenen}\n(Eşleşen kayıt yoksa adres listemizde olmayabilir; yine de talep kayıt altındadır.)",
                        $_SERVER['REMOTE_ADDR'] ?? null,
                        mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                    ]);
                $pdo->commit();
                $_SESSION['ret_csrf'] = bin2hex(random_bytes(32));
                $durum = 'talep';
            }
        }
    } else {
        $firma = (string)$lead['firma_adi'];
        if (!empty($lead['ret_tarihi'])) {
            $durum = 'zaten';
        } elseif ($isPost) {
            $pdo->prepare("UPDATE potansiyel_musteriler SET durum = 'ret', ret_tarihi = NOW() WHERE lead_id = ?")->execute([(int)$lead['lead_id']]);
            $pdo->prepare("INSERT INTO lead_aktiviteler (lead_id, tur, aciklama) VALUES (?, 'sistem', 'Alıcı ret sayfasından iletişim istemediğini bildirdi.')")->execute([(int)$lead['lead_id']]);
            // Bekleyen kampanya e-postalarını iptal et
            $pdo->prepare("UPDATE mail_kuyrugu SET status = 'cancelled', last_error = 'Alıcı reddetti' WHERE status = 'pending' AND source = 'lead_kampanya' AND payload LIKE ?")
                ->execute(['%"lead_id":' . (int)$lead['lead_id'] . ',%']);
            $durum = 'tamam';
        }
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) { $pdo->rollBack(); }
    error_log('[ret] ' . $e->getMessage());
    $durum = 'hata';
}

// Tek tık (RFC 8058) istekleri gövde beklemez
if ($isPost && isset($_POST['List-Unsubscribe'])) {
    http_response_code($durum === 'tamam' || $durum === 'zaten' ? 200 : 400);
    exit;
}

$metin = [
    'onay'     => ['İletişim listesinden çıkmak istiyor musunuz?', ($firma !== '' ? $firma . ' için ' : '') . 'Prolyn tanıtım e-postaları ve mesajları durdurulacak.'],
    'tamam'    => ['Çıkışınız alındı', 'Size bir daha tanıtım e-postası veya mesajı göndermeyeceğiz. İlginiz için teşekkürler.'],
    'zaten'    => ['Zaten listeden çıktınız', 'Bu adres için tanıtım iletileri daha önce durduruldu.'],
    'talep'    => ['Talebiniz alındı', 'Bu e-posta adresine tanıtım iletileri gönderilmeyecek. Bekleyen iletiler varsa iptal edildi.'],
    'ornek'    => ['Bu bir örnek bağlantıdır', 'Şablon önizlemesinde gösterilen bağlantı çalışmaz. Gerçek e-postalarda her alıcıya özel bir çıkış bağlantısı gönderilir.'],
    'gecersiz' => ['Bu bağlantı kullanılamıyor', 'Bağlantı eski ya da eksik olabilir. Tanıtım iletilerinin durdurulmasını istiyorsanız aşağıya e-posta adresinizi yazın, hemen işleme alalım.'],
    'hata'     => ['Şu an işlenemedi', 'Geçici bir sorun oluştu. Lütfen biraz sonra tekrar deneyin.'],
][$durum];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$basari = in_array($durum, ['tamam', 'zaten', 'talep'], true);
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>İletişim tercihi · Prolyn</title>
<style>
:root{color-scheme:light dark;--bg:#0f1220;--card:#181c2e;--tx:#eef0f7;--mut:#9aa3b8;--pr:#3b6cf6;--ok:#22c55e;--warn:#f59e0b;--err:#ef4444;--bd:#272c44;--in:#10142540}
@media (prefers-color-scheme: light){:root{--bg:#f4f6fb;--card:#fff;--tx:#1e2433;--mut:#6b7385;--bd:#e4e8f1;--in:#f7f9fd}}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(1000px 500px at 10% -10%,rgba(59,108,246,.18),transparent),var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:var(--tx)}
.w{max-width:460px;width:100%}
.marka{text-align:center;font-weight:800;font-size:20px;letter-spacing:.2px;margin-bottom:16px}
.marka span{color:var(--pr)}
.c{background:var(--card);border:1px solid var(--bd);border-radius:20px;padding:36px 32px;text-align:center}
.ik{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;margin:0 auto 16px;font-size:26px;font-weight:700}
.ik.ok{background:rgba(34,197,94,.15);color:var(--ok)}
.ik.bilgi{background:rgba(59,108,246,.15);color:var(--pr)}
.ik.uyari{background:rgba(245,158,11,.15);color:var(--warn)}
h1{font-size:21px;margin:0 0 10px;line-height:1.3}
p{color:var(--mut);line-height:1.6;margin:0 0 22px;font-size:15px}
form{text-align:left}
label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
input[type=email]{width:100%;font:inherit;padding:12px 14px;border-radius:12px;border:1px solid var(--bd);background:var(--in);color:var(--tx);outline:none}
input[type=email]:focus{border-color:var(--pr);box-shadow:0 0 0 3px rgba(59,108,246,.2)}
.hp{position:absolute;left:-9999px;height:0;overflow:hidden}
button,a.b{font:inherit;font-weight:600;padding:12px 22px;border-radius:12px;border:0;background:var(--pr);color:#fff;cursor:pointer;text-decoration:none;display:inline-block}
form button{width:100%;margin-top:14px}
.hata{background:rgba(239,68,68,.12);color:var(--err);border-radius:10px;padding:10px 12px;font-size:14px;margin-bottom:14px}
.not{font-size:12.5px;color:var(--mut);margin-top:14px;line-height:1.5;text-align:center}
a.l{color:var(--mut);font-size:14px}
.alt{margin-top:18px;text-align:center}
.alt a{color:var(--mut);font-size:13px}
</style>
</head>
<body>
<div class="w">
<div class="marka">Pro<span>lyn</span></div>
<main class="c">
  <?php if ($basari): ?><div class="ik ok" aria-hidden="true">✓</div>
  <?php elseif ($durum === 'hata'): ?><div class="ik uyari" aria-hidden="true">!</div>
  <?php else: ?><div class="ik bilgi" aria-hidden="true"><?= $durum === 'ornek' ? 'i' : '✉' ?></div><?php endif; ?>

  <h1><?= $e($metin[0]) ?></h1>
  <p><?= $e($metin[1]) ?></p>

  <?php if ($durum === 'onay'): ?>
    <form method="post" action="ret">
      <input type="hidden" name="t" value="<?= $e($token) ?>">
      <button type="submit">Evet, listeden çıkar</button>
    </form>

  <?php elseif ($durum === 'gecersiz'): ?>
    <form method="post" action="ret" autocomplete="on" novalidate>
      <?php if ($formHata !== ''): ?><div class="hata" role="alert"><?= $e($formHata) ?></div><?php endif; ?>
      <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['ret_csrf']) ?>">
      <div class="hp" aria-hidden="true"><label>Web sitesi<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <label for="eposta">E-posta adresiniz</label>
      <input type="email" id="eposta" name="eposta" required maxlength="191" placeholder="ornek@firma.com" autocomplete="email" inputmode="email">
      <button type="submit">İletişimi durdur</button>
      <div class="not">Talebiniz kayıt altına alınır. Başka bir sorunuz varsa <a class="l" href="/contact">bize yazabilirsiniz</a>.</div>
    </form>

  <?php else: ?>
    <a class="l" href="/">prolyn.net</a>
  <?php endif; ?>
</main>
<?php if ($durum === 'gecersiz'): ?><div class="alt"><a href="/">← prolyn.net</a></div><?php endif; ?>
</div>
</body>
</html>
