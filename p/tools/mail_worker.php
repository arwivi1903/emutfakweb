<?php
/**
 * Prolyn - E-posta Kuyruğu İşleyici (CLI + cron)
 * ----------------------------------------------
 * mail_kuyrugu tablosundaki 'pending' kayıtları gönderir.
 * Cron örneği (her dakika):
 *   * * * * * php /var/www/prolynweb/p/tools/mail_worker.php >> /var/log/prolyn_mail.log 2>&1
 *
 * Idempotent ve güvenli: gönderilen kayıt 'sent', hata alan 'failed' işaretlenir;
 * belirli deneme sayısından sonra tekrar denenmez.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../functions/provision.php'; // provision_admin_pdo + env
require_once __DIR__ . '/../functions/mailer.php';
require_once __DIR__ . '/../functions/whatsapp.php';

$maxAttempts = 5;
$batch = 50;

try {
    // Uygulama (okuma/yazma) hesabı yeterli; kuyruk prolyn_master'da
    $host = prolynweb_env('DB_HOST', '127.0.0.1');
    $port = prolynweb_env('DB_PORT', '3306');
    $name = prolynweb_env('DB_NAME', 'prolyn_master');
    $user = prolynweb_env('DB_USER', '');
    $pass = prolynweb_env('DB_PASS', '');
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB bağlantısı kurulamadı: " . $e->getMessage() . "\n");
    exit(1);
}

// Tablo yoksa yapacak bir şey yok
try {
    $exists = $pdo->query("SHOW TABLES LIKE 'mail_kuyrugu'")->fetch();
    if (!$exists) {
        echo "mail_kuyrugu tablosu yok. Çıkılıyor.\n";
        exit(0);
    }
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$rows = $pdo->prepare("SELECT * FROM mail_kuyrugu WHERE status = 'pending' AND attempts < ? ORDER BY queue_id ASC LIMIT {$batch}");
$rows->execute([$maxAttempts]);
$items = $rows->fetchAll(PDO::FETCH_ASSOC);

echo "İşlenecek kayıt: " . count($items) . "\n";
$sent = 0; $failed = 0;

foreach ($items as $row) {
    $id = (int)$row['queue_id'];
    $payload = json_decode((string)($row['payload'] ?? ''), true) ?: [];
    $extraHeaders = is_array($payload['headers'] ?? null) ? $payload['headers'] : [];
    $isHtml = !empty($payload['html']);
    $to = $row['recipient'] ?? '';
    $subject = $row['subject'] ?? '(konu yok)';
    $message = $row['message'] ?? '';

    $channel = (string)($row['channel'] ?? 'email');

    // WhatsApp bildirimi (ör. iletişim formu): Meta Cloud API ile gönderilir
    $kopyaNot = '';
    if ($channel === 'whatsapp') {
        [$ok, $hata] = prolyn_whatsapp_gonder($to, $message);
    } else {
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $pdo->prepare("UPDATE mail_kuyrugu SET status='failed', attempts=attempts+1, last_error='Geçersiz alıcı adresi', processed_at=NOW() WHERE queue_id=?")->execute([$id]);
            $failed++;
            continue;
        }
        // Gönderen kimliği: payload'da açıkça verilmişse o, yoksa kaynağa göre (lead → destek, yanıt → info, diğer → noreply)
        $kimlik = in_array($payload['kimlik'] ?? '', ['destek', 'bilgi', 'noreply'], true)
            ? $payload['kimlik'] : prolyn_mail_kimlik_kaynaktan((string)($row['source'] ?? ''));
        $ok = prolyn_send_mail($to, $subject, $message, $isHtml, $extraHeaders, $kimlik);
        $hata = $ok ? '' : prolyn_mail_son_hata();
        // Gönderim başarılı ama "Gönderilenler" kopyası alınamadıysa not düş (durum yine 'sent')
        $kopyaNot = $ok ? prolyn_mail_kopya_hata() : '';
    }

    if ($ok) {
        $pdo->prepare("UPDATE mail_kuyrugu SET status='sent', attempts=attempts+1, last_error=?, processed_at=NOW() WHERE queue_id=?")
            ->execute([$kopyaNot !== '' ? mb_substr('Gönderildi; Gönderilenler kopyası alınamadı: ' . $kopyaNot, 0, 250) : null, $id]);
        $sent++;
    } else {
        // Deneme sayısı dolduysa failed, yoksa pending kalsın (tekrar denenir)
        $newStatus = ((int)$row['attempts'] + 1 >= $maxAttempts) ? 'failed' : 'pending';
        $pdo->prepare("UPDATE mail_kuyrugu SET status=?, attempts=attempts+1, last_error=?, processed_at=NOW() WHERE queue_id=?")
            ->execute([$newStatus, mb_substr($hata !== '' ? $hata : 'Gönderim başarısız (PHP error log)', 0, 250), $id]);
        $failed++;
    }
}

echo "Gönderildi: {$sent}, Başarısız: {$failed}\n";
