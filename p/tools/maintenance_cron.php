<?php
/**
 * Prolyn - Bakım Görevi (CLI + cron)
 * ----------------------------------
 * 1. Süresi dolmuş oturumları temizler (prolyn_master.oturumlar)
 * 2. Deneme (trial) süresi dolmuş ve sözleşmeye geçmemiş kiracıları raporlar
 *    (isteğe bağlı olarak status='suspended' yapabilir - aşağıda AUTO_SUSPEND)
 *
 * Cron örneği (her 15 dakikada bir):
 *   Dakika alanına "0,15,30,45" yazın:
 *   0,15,30,45 * * * * php /var/www/prolynweb/p/tools/maintenance_cron.php >> /var/log/prolyn_maint.log 2>&1
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../functions/provision.php'; // provision_admin_pdo + env

$AUTO_SUSPEND = false; // true yaparsanız trial biten kiracılar otomatik askıya alınır

try {
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

// 1) Süresi dolmuş oturumları temizle
try {
    if ($pdo->query("SHOW TABLES LIKE 'oturumlar'")->fetch()) {
        $n = $pdo->exec("DELETE FROM oturumlar WHERE expires_at IS NOT NULL AND expires_at < NOW()");
        echo "Temizlenen oturum: " . (int)$n . "\n";
    }
} catch (Throwable $e) {
    error_log('[maintenance] oturum temizleme: ' . $e->getMessage());
}

// 2) Trial süresi dolmuş kiracılar
try {
    $rows = $pdo->query("
        SELECT customer_id, company_name, trial_end_date
        FROM kurumlar
        WHERE status = 'active'
          AND trial_end_date IS NOT NULL
          AND trial_end_date < CURDATE()
          AND (sozlesme_bitis_tarihi IS NULL OR sozlesme_bitis_tarihi = '' )
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Trial süresi dolan kiracı: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo " - #{$r['customer_id']} {$r['company_name']} (bitiş: {$r['trial_end_date']})\n";
        if ($AUTO_SUSPEND) {
            $pdo->prepare("UPDATE kurumlar SET status='suspended' WHERE customer_id=?")->execute([$r['customer_id']]);
        }
    }
} catch (Throwable $e) {
    error_log('[maintenance] trial kontrol: ' . $e->getMessage());
}

echo "Bakım tamamlandı.\n";
