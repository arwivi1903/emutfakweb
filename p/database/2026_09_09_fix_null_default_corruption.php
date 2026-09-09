<?php
// Önceki (hatalı) yorum ekleme script'inin bıraktığı DEFAULT 'NULL' (tırnaklı
// string) bozulmasını düzeltir -> gerçek DEFAULT NULL yapar. Mevcut satır
// verilerine dokunmaz (yalnızca kolon tanımı); comment aynen korunur.

require_once __DIR__ . '/../../env.php';

$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
    prolynweb_env('DB_USER', ''),
    prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$targets = [
    'blog_yazilari' => ['excerpt', 'cover_image'],
    'destek_cevaplari' => ['replied_by_tenant_name'],
    'destek_ekleri' => ['mime_tipi', 'yukleyen_ad'],
    'destek_talepleri' => ['category', 'tenant_db', 'created_by_name', 'created_by_email'],
    'kurumlar' => ['official_name', 'contact_person', 'tax_office', 'tax_number', 'billing_address', 'tenant_alias', 'musteri_paketi', 'database_name', 'database_user', 'database_password', 'logo_url', 'contact_name', 'contact_email', 'contact_phone'],
    'mail_kuyrugu' => ['last_error', 'config_source', 'config_snapshot', 'payload'],
    'oturumlar' => ['user_email', 'user_full_name', 'tenant_db', 'firma_adi'],
    'paket_ozellikleri' => ['allowed_modules'],
    'sifre_sifirlama_talepleri' => ['tenant_db', 'request_ip', 'user_agent'],
    'videolar' => ['thumbnail_url', 'author_name'],
];

$fixed = 0;
$errors = [];

foreach ($targets as $table => $columns) {
    foreach ($columns as $col) {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLLATION_NAME, COLUMN_COMMENT
                                FROM information_schema.COLUMNS
                                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $col]);
        $meta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$meta) {
            $errors[] = "$table.$col: bulunamadı";
            continue;
        }
        if ($meta['IS_NULLABLE'] !== 'YES') {
            $errors[] = "$table.$col: NOT NULL kolon, atlandı (beklenmiyordu)";
            continue;
        }

        $def = "`$col` " . $meta['COLUMN_TYPE'];
        if ($meta['COLLATION_NAME']) {
            $def .= ' COLLATE ' . $meta['COLLATION_NAME'];
        }
        $def .= ' DEFAULT NULL';
        $def .= ' COMMENT ' . $pdo->quote($meta['COLUMN_COMMENT']);

        $sql = "ALTER TABLE `$table` MODIFY COLUMN $def";
        try {
            $pdo->exec($sql);
            $fixed++;
        } catch (Throwable $e) {
            $errors[] = "$table.$col: " . $e->getMessage() . " | SQL: $sql";
        }
    }
}

echo "Düzeltilen kolon sayısı: $fixed\n";
if ($errors) {
    echo "Hatalar/Notlar:\n" . implode("\n", $errors) . "\n";
}
