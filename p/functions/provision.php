<?php
/**
 * Prolyn - Ortak Tenant Provisioning
 * -----------------------------------
 * Yeni müşteri (tenant) veritabanını GÜVENLİ şekilde kurar:
 *   - Merkezi MySQL'de prolyn_kN veritabanını oluşturur
 *   - Şablon (prolyn_default) şemasını içeri aktarır
 *   - Kiracıya ÖZEL, sadece kendi DB'sinde yetkili bir MySQL kullanıcısı yaratır
 *     (root paylaşımı SON BULUR)
 *   - firma / şube / kasa / admin kullanıcısını tohumlar
 *   - Herhangi bir adımda hata olursa oluşturduğu DB + kullanıcıyı GERİ ALIR
 *
 * Hem register.php (deneme kaydı) hem de p/crm/tenant_form.php (manuel ekleme)
 * bu fonksiyonu kullanır; böylece iki farklı provisioning kod yolu (drift) biter.
 *
 * Bağlantı bilgileri .env'den okunur (bkz. prolynweb/env.php).
 */

require_once __DIR__ . '/../../env.php';

if (!function_exists('provision_admin_pdo')) {

    /**
     * CREATE DATABASE / USER / GRANT yetkisine sahip yönetici bağlantısı.
     */
    function provision_admin_pdo(): PDO
    {
        $host = prolynweb_env('DB_HOST', '127.0.0.1');
        $port = prolynweb_env('DB_PORT', '3306');
        $user = prolynweb_env('DB_ADMIN_USER', prolynweb_env('DB_USER', ''));
        $pass = prolynweb_env('DB_ADMIN_PASS', prolynweb_env('DB_PASS', ''));

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    }

    /**
     * Kriptografik olarak güçlü, MySQL şifresi için güvenli rastgele parola.
     */
    function provision_random_password(int $len = 28): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#%^*_-';
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }

    /**
     * Şablon şemasını yeni DB'ye aktarır.
     * Öncelik: mysqldump | mysql pipe (env'deki ikili yolları veya PATH).
     * Yedek: prolyn_default.sql dosyası (varsa).
     * Şifreler komut satırında GÖRÜNMEZ; MYSQL_PWD ortam değişkeniyle geçer.
     *
     * @throws Exception
     */
    function provision_import_template(string $newDbName): void
    {
        $host       = prolynweb_env('DB_HOST', '127.0.0.1');
        $port       = (string)prolynweb_env('DB_PORT', '3306');
        $user       = prolynweb_env('DB_ADMIN_USER', prolynweb_env('DB_USER', ''));
        $pass       = prolynweb_env('DB_ADMIN_PASS', prolynweb_env('DB_PASS', ''));
        $templateDb = prolynweb_env('TENANT_TEMPLATE_DB', 'prolyn_default');

        $mysqlBin     = trim((string)prolynweb_env('MYSQL_BIN', ''));
        $mysqldumpBin = trim((string)prolynweb_env('MYSQLDUMP_BIN', ''));
        if ($mysqlBin === '')     { $mysqlBin = 'mysql'; }
        if ($mysqldumpBin === '') { $mysqldumpBin = 'mysqldump'; }

        // Şifreyi komut satırından uzak tut
        putenv('MYSQL_PWD=' . $pass);

        $common = sprintf('-h%s -P%s -u%s', escapeshellarg($host), escapeshellarg($port), escapeshellarg($user));

        // 1) Canlı şablon DB'sinden kopyala (mysqldump | mysql)
        $dumpCmd = sprintf(
            '%s %s --no-tablespaces --routines --triggers --single-transaction %s 2>/tmp/prolyn_dump_err.log | %s %s %s 2>/tmp/prolyn_imp_err.log',
            escapeshellarg($mysqldumpBin), $common, escapeshellarg($templateDb),
            escapeshellarg($mysqlBin), $common, escapeshellarg($newDbName)
        );
        $ret = 1;
        $out = [];
        @exec($dumpCmd, $out, $ret);
        if ($ret === 0) {
            putenv('MYSQL_PWD');
            return;
        }

        // 2) Yedek: prolyn_default.sql dosyasından yükle
        $sqlFile = __DIR__ . '/../database/prolyn_default.sql';
        if (is_readable($sqlFile)) {
            $impCmd = sprintf('%s %s %s < %s 2>/tmp/prolyn_imp_err.log',
                escapeshellarg($mysqlBin), $common, escapeshellarg($newDbName), escapeshellarg($sqlFile));
            $ret2 = 1;
            @exec($impCmd, $o2, $ret2);
            if ($ret2 === 0) {
                putenv('MYSQL_PWD');
                return;
            }
        }

        putenv('MYSQL_PWD');
        $err = @file_get_contents('/tmp/prolyn_imp_err.log');
        throw new Exception('Şablon veritabanı aktarılamadı.' . ($err ? ' Detay loglandı.' : ''));
    }

    /**
     * Kiracıya özel MySQL kullanıcısı oluşturur ve KISITLI yetki verir:
     *   - ALL PRIVILEGES  ON `prolyn_kN`.*
     *   - SELECT/INSERT/UPDATE/DELETE ON `prolyn_master`.`oturumlar`
     *     (uygulama oturum takibi için gerekli tek çapraz-DB erişimi)
     *
     * @return array [username, password]
     * @throws Exception
     */
    function provision_tenant_user(PDO $adminPdo, string $newDbName): array
    {
        $allowedHost = prolynweb_env('TENANT_DB_ALLOWED_HOST', '%');
        $username    = $newDbName; // örn. prolyn_k27 (max 32 char MySQL sınırı içinde)
        if (strlen($username) > 32) {
            $username = substr($username, 0, 32);
        }
        $password = provision_random_password();

        // Aynı isimde kullanıcı kaldıysa temizle (idempotent)
        $adminPdo->exec(sprintf("DROP USER IF EXISTS '%s'@'%s'",
            str_replace("'", "", $username), str_replace("'", "", $allowedHost)));

        $adminPdo->exec(sprintf("CREATE USER '%s'@'%s' IDENTIFIED BY %s",
            str_replace("'", "", $username),
            str_replace("'", "", $allowedHost),
            $adminPdo->quote($password)));

        $adminPdo->exec(sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%s'",
            str_replace('`', '', $newDbName),
            str_replace("'", "", $username),
            str_replace("'", "", $allowedHost)));

        // Oturum takibi için master.oturumlar üzerinde kısıtlı yetki
        try {
            $adminPdo->exec(sprintf(
                "GRANT SELECT, INSERT, UPDATE, DELETE ON `prolyn_master`.`oturumlar` TO '%s'@'%s'",
                str_replace("'", "", $username),
                str_replace("'", "", $allowedHost)));
        } catch (Exception $e) {
            // oturumlar tablosu yoksa yoksay; login yine de çalışır (try/catch içinde)
            error_log('[provision] oturumlar grant atlandı: ' . $e->getMessage());
        }

        $adminPdo->exec('FLUSH PRIVILEGES');

        return [$username, $password];
    }

    /**
     * Tüm provisioning akışı. Başarısızlıkta oluşturulanları geri alır.
     *
     * @param array $data Beklenen anahtarlar:
     *   customer_id (int, DB adı bundan türetilir: prolyn_k{customer_id}),
     *   company_name, official_name, tax_office, tax_number,
     *   city_id, district_id, billing_address, company_phone,
     *   admin_name, admin_email, admin_password (boşsa rastgele üretilir)
     * @return array [
     *   'success'=>bool, 'database_name'=>string, 'database_user'=>string,
     *   'database_password'=>string, 'firma_id'=>int, 'admin_password'=>string,
     *   'error'=>string|null ]
     */
    function provision_tenant(array $data): array
    {
        $result = [
            'success' => false, 'database_name' => '', 'database_user' => '',
            'database_password' => '', 'firma_id' => 0, 'admin_password' => '', 'error' => null,
        ];

        $customerId = (int)($data['customer_id'] ?? 0);
        if ($customerId <= 0) {
            $result['error'] = 'Geçersiz customer_id.';
            return $result;
        }
        $newDbName = 'prolyn_k' . $customerId;
        $result['database_name'] = $newDbName;

        $allowedHost = prolynweb_env('TENANT_DB_ALLOWED_HOST', '%');
        $dbCreated = false;
        $userCreated = false;
        $tenantUser = '';

        try {
            $adminPdo = provision_admin_pdo();

            // 1) Veritabanını oluştur (customer_id benzersiz olduğu için yarış yok)
            $adminPdo->exec("CREATE DATABASE `{$newDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $dbCreated = true;

            // 2) Şablonu aktar
            provision_import_template($newDbName);

            // 3) Kiracıya özel MySQL kullanıcısı + yetkiler
            list($tenantUser, $tenantPass) = provision_tenant_user($adminPdo, $newDbName);
            $userCreated = true;
            $result['database_user'] = $tenantUser;
            $result['database_password'] = $tenantPass;

            // 4) Tenant DB'ye bağlan (yönetici bağlantısı) ve tohumla
            $host = prolynweb_env('DB_HOST', '127.0.0.1');
            $port = prolynweb_env('DB_PORT', '3306');
            $adminUser = prolynweb_env('DB_ADMIN_USER', prolynweb_env('DB_USER', ''));
            $adminPass = prolynweb_env('DB_ADMIN_PASS', prolynweb_env('DB_PASS', ''));
            $tenantPdo = new PDO("mysql:host={$host};port={$port};dbname={$newDbName};charset=utf8mb4",
                $adminUser, $adminPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // permissions kolonu (şablon eskiyse)
            try { $tenantPdo->exec("ALTER TABLE usergrup ADD COLUMN permissions JSON DEFAULT NULL"); } catch (Exception $e) {}
            $tenantPdo->prepare("UPDATE usergrup SET permissions = '{\"all\": true}' WHERE UserGrupID = 1")->execute();

            $companyName = (string)($data['company_name'] ?? '');
            $officialName = (string)($data['official_name'] ?? '');
            $taxOffice = (string)($data['tax_office'] ?? '');
            $taxNumber = (string)($data['tax_number'] ?? '');
            $cityId = (int)($data['city_id'] ?? 0);
            $districtId = (int)($data['district_id'] ?? 0);
            $address = (string)($data['billing_address'] ?? '');
            $phone = (string)($data['company_phone'] ?? '');
            $adminName = (string)($data['admin_name'] ?? $companyName);
            $adminEmail = (string)($data['admin_email'] ?? '');
            $adminPass = (string)($data['admin_password'] ?? '');
            if ($adminPass === '') {
                $adminPass = provision_random_password(12);
            }
            $result['admin_password'] = $adminPass;

            // firmalar
            $stmt = $tenantPdo->prepare("INSERT INTO firmalar (CustomerID, FirmaAdi, FirmaDetay, FirmaVergi, FirmaVNO, FirmaCity, FirmaTown, FirmaAdres) VALUES (0, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$companyName, $officialName, $taxOffice, $taxNumber, $cityId, $districtId, $address]);
            $firmaId = (int)$tenantPdo->lastInsertId();
            if ($firmaId <= 0) { $firmaId = 1; }
            $result['firma_id'] = $firmaId;

            // subeler
            $branchName = $companyName !== '' ? $companyName : 'Merkez Şube';
            $stmt = $tenantPdo->prepare("INSERT INTO subeler (FirmaID, CustomerID, BranchName, BranchResmi, BranchArea, BranchDesc, BranchPhone, BranchAuthorized, BranchStatus, BranchMail, BranchAdres, BranchShort) VALUES (?, 0, ?, ?, ?, ?, ?, 1, 1, ?, ?, 'MRK')");
            $stmt->execute([$firmaId, $branchName, $branchName, $cityId, $branchName, $phone, $adminEmail, $address]);
            $branchId = (int)$tenantPdo->lastInsertId();

            // nakitkasa
            $tenantPdo->prepare("INSERT INTO nakitkasa (FirmaID, NakitKasaTutar) VALUES (?, 0)")->execute([$firmaId]);

            // admin kullanıcı (bcrypt)
            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $tenantPdo->prepare("INSERT INTO users (FirmaID, UserName, UserMail, UserPhone, UserPass, UserAuthority, UserGroupID, BranchID, UserStatus) VALUES (?, ?, ?, ?, ?, 1, 1, ?, 1)");
            $stmt->execute([$firmaId, $adminName, $adminEmail, $phone, $hash, $branchId]);

            $result['success'] = true;
            return $result;

        } catch (Throwable $e) {
            error_log('[provision] Başarısız (' . $newDbName . '): ' . $e->getMessage());
            // GERİ ALMA: kullanıcı ve DB'yi temizle
            try {
                $adminPdo = isset($adminPdo) ? $adminPdo : provision_admin_pdo();
                if ($userCreated && $tenantUser !== '') {
                    $adminPdo->exec(sprintf("DROP USER IF EXISTS '%s'@'%s'",
                        str_replace("'", "", $tenantUser), str_replace("'", "", $allowedHost)));
                }
                if ($dbCreated) {
                    $adminPdo->exec("DROP DATABASE IF EXISTS `{$newDbName}`");
                }
                $adminPdo->exec('FLUSH PRIVILEGES');
            } catch (Throwable $cleanupErr) {
                error_log('[provision] Temizlik hatası (' . $newDbName . '): ' . $cleanupErr->getMessage());
            }
            $result['error'] = $e->getMessage();
            return $result;
        }
    }
}
