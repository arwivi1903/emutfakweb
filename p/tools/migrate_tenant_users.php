<?php
/**
 * Prolyn - Mevcut kiracılar için izolasyon migrasyonu (SADECE CLI)
 * ----------------------------------------------------------------
 * Ne yapar:
 *   1. kurumlar tablosuna database_user kolonunu ekler (yoksa)
 *   2. Her mevcut kiracı için (prolyn_kN) sadece kendi DB'sinde yetkili
 *      bir MySQL kullanıcısı oluşturur/günceller (root paylaşımını bitirir)
 *   3. Kiracının parolasını rastgele güçlü bir değerle değiştirir
 *   4. kurumlar.database_user / database_password alanlarını günceller
 *
 * Idempotent: tekrar çalıştırmak güvenlidir.
 *
 * Çalıştırma (üretim MySQL sunucusuna erişimi olan makinede):
 *   php p/tools/migrate_tenant_users.php
 *
 * ÖNCE STAGING'DE ÇALIŞTIRIP TENANT GİRİŞİNİ TEST EDİN.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../functions/provision.php';

$allowedHost = prolynweb_env('TENANT_DB_ALLOWED_HOST', '%');

echo "== Prolyn tenant izolasyon migrasyonu ==\n";

try {
    $admin = provision_admin_pdo();
    $admin->exec('USE `' . str_replace('`', '', prolynweb_env('DB_NAME', 'prolyn_master')) . '`');
} catch (Throwable $e) {
    fwrite(STDERR, "Yönetici bağlantısı kurulamadı: " . $e->getMessage() . "\n");
    exit(1);
}

// 1) database_user kolonu
try {
    $col = $admin->query("SHOW COLUMNS FROM kurumlar LIKE 'database_user'")->fetch();
    if (!$col) {
        $admin->exec("ALTER TABLE kurumlar ADD COLUMN database_user VARCHAR(64) NULL AFTER database_name");
        echo "[+] kurumlar.database_user kolonu eklendi.\n";
    } else {
        echo "[=] kurumlar.database_user kolonu zaten var.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Kolon eklenemedi: " . $e->getMessage() . "\n");
    exit(1);
}

// 2) Kiracıları getir
$rows = $admin->query("SELECT customer_id, database_name FROM kurumlar WHERE database_name IS NOT NULL AND database_name <> ''")->fetchAll(PDO::FETCH_ASSOC);
echo "[i] " . count($rows) . " kiracı bulundu.\n";

$ok = 0; $fail = 0;
foreach ($rows as $r) {
    $dbName = $r['database_name'];
    $cid = (int)$r['customer_id'];
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $dbName)) {
        echo "[!] Atlandı (geçersiz DB adı): {$dbName}\n";
        $fail++;
        continue;
    }
    // DB gerçekten var mı?
    $exists = $admin->query("SHOW DATABASES LIKE " . $admin->quote($dbName))->fetch();
    if (!$exists) {
        echo "[!] Atlandı (DB yok): {$dbName}\n";
        $fail++;
        continue;
    }

    try {
        list($user, $pass) = provision_tenant_user($admin, $dbName);
        $admin->prepare("UPDATE kurumlar SET database_user = ?, database_password = ? WHERE customer_id = ?")
              ->execute([$user, $pass, $cid]);
        echo "[+] {$dbName}: kullanıcı '{$user}' oluşturuldu/güncellendi, parola döndürüldü.\n";
        $ok++;
    } catch (Throwable $e) {
        echo "[x] {$dbName}: HATA - " . $e->getMessage() . "\n";
        $fail++;
    }
}

echo "== Bitti. Başarılı: {$ok}, Başarısız: {$fail} ==\n";
echo "Not: Tenant panelinde bir hesapla giriş yaparak doğrulayın.\n";
