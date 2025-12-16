<?php

// Yönetici tablosu adı (gerekiyorsa değiştir)
if (!defined('admin_users')) {
    define('admin_users', 'admin_users');
}

// Güvenli oturum başlatma (cookie parametreleri ile)
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
    
    // Session activity timeout kontrolü (30 dakika inaktivite)
    $timeout = 30 * 60; // 30 dakika saniye cinsinden
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Timeout oluştu, session'ı temizle
            session_destroy();
            session_start();
            $_SESSION = [];
            return;
        }
    }
    $_SESSION['last_activity'] = time();
}

// Global db örneğini al; yoksa yeni aç
function get_db_instance(): Database
{
    global $db;
    if (isset($db) && $db instanceof Database) {
        return $db;
    }
    return new Database();
}

// Oturumdaki güncel admin bilgisini getir (istersen DB'den tazele)
function current_admin(bool $refreshFromDb = true): ?array
{
    start_secure_session();

    $adminId = $_SESSION['admin_id'] ?? null;
    $email   = $_SESSION['email'] ?? null;

    if (!$adminId || !$email) {
        // Session eksik - null dön
        return null;
    }

    $sessionAdmin = [
        'admin_id' => (int)$adminId,
        'email' => $email,
        'full_name' => $_SESSION['full_name'] ?? '',
        'role' => $_SESSION['role'] ?? null,
        'status' => $_SESSION['status'] ?? null,
        'permissions' => [
            'can_manage_customers' => (int)($_SESSION['can_manage_customers'] ?? 0),
            'can_manage_subscriptions' => (int)($_SESSION['can_manage_subscriptions'] ?? 0),
            'can_manage_payments' => (int)($_SESSION['can_manage_payments'] ?? 0),
            'can_view_analytics' => (int)($_SESSION['can_view_analytics'] ?? 0),
        ],
        'two_factor_enabled' => (int)($_SESSION['two_factor_enabled'] ?? 0),
    ];

    if (!$refreshFromDb) {
        return $sessionAdmin;
    }

    try {
        $db = get_db_instance();

        // Girişte belirlenen tablo/kolon varsa onları kullan; yoksa varsayılanı dene
        $table = $_SESSION['admin_table'] ?? admin_users;
        $idCol = $_SESSION['admin_id_col'] ?? 'admin_id';

        // SELECT * ile çek, status/kolon varyasyonlarını toleranslı işle
        $adminRow = $db->getRow("SELECT * FROM {$table} WHERE {$idCol} = ? LIMIT 1", [$sessionAdmin['admin_id']]);
        if (!$adminRow) {
            // Fallback: admin_users -> admins -> users
            foreach (['admin_users','admins','users'] as $fb) {
                $idC = ($fb === 'users') ? 'id' : 'admin_id';
                $adminRow = $db->getRow("SELECT * FROM {$fb} WHERE {$idC} = ? LIMIT 1", [$sessionAdmin['admin_id']]);
                if ($adminRow) { $table = $fb; $idCol = $idC; break; }
            }
        }

        if (!$adminRow) {
            // Admin bulunamadı - session'ı temizle
            return null;
        }

        // Kolon tespiti yardımcıları
        $findCol = function($row, array $cands) {
            foreach ($cands as $c) { if (property_exists($row, $c)) return $c; }
            return null;
        };

        $mailCol = $findCol($adminRow, ['email']);
        $nameCol = $findCol($adminRow, ['full_name','name','fullname','adsoyad','ad_soyad']);
        $roleCol = $findCol($adminRow, ['role','yetki','rol']);
        $statCol = $findCol($adminRow, ['status','durum','active']);
        $tfaCol  = $findCol($adminRow, ['two_factor_enabled','tfa_enabled']);

        $statusVal = $statCol ? $adminRow->{$statCol} : 'active';
        $st = is_string($statusVal) ? strtolower($statusVal) : (string)$statusVal;
        if (!in_array($st, ['active','enabled','true','1',1], true)) {
            // Admin deaktif - session'ı temizle
            return null;
        }

        // Session'ı güncelle
        $_SESSION['admin_table'] = $table;
        $_SESSION['admin_id_col'] = $idCol;

        // Session'ı DB'deki güncel değerlerle senkronize et
        $_SESSION['admin_id'] = (int)$adminRow->{$idCol};
        $_SESSION['email'] = $mailCol ? $adminRow->{$mailCol} : ($sessionAdmin['email'] ?? '');
        $_SESSION['full_name'] = $nameCol ? ($adminRow->{$nameCol} ?? '') : ($sessionAdmin['full_name'] ?? '');
        $_SESSION['role'] = $roleCol ? ($adminRow->{$roleCol} ?? null) : ($sessionAdmin['role'] ?? null);
        $_SESSION['status'] = $statusVal;
        $_SESSION['two_factor_enabled'] = $tfaCol ? (int)$adminRow->{$tfaCol} : (int)($sessionAdmin['two_factor_enabled'] ?? 0);

        return [
            'admin_id' => (int)$adminRow->{$idCol},
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'status' => $_SESSION['status'],
            'two_factor_enabled' => (int)$_SESSION['two_factor_enabled'],
            'last_login' => $adminRow->last_login ?? null,
            'permissions' => [
                'can_manage_customers' => (int)($_SESSION['can_manage_customers'] ?? 0),
                'can_manage_subscriptions' => (int)($_SESSION['can_manage_subscriptions'] ?? 0),
                'can_manage_payments' => (int)($_SESSION['can_manage_payments'] ?? 0),
                'can_view_analytics' => (int)($_SESSION['can_view_analytics'] ?? 0),
            ],
        ];
    } catch (Exception $e) {
        // DB hatası durumunda session'daki verileri kullan ama null dön
        // Böylece tekrar login sayfasına yönlendirilir
        error_log("current_admin() DB Hatası: " . $e->getMessage());
        return null;
    }
}

// Yetki kontrolü
function oturumkontrol(array $allowedRoles = [], array $requiredPermissions = []): void
{
    $admin = current_admin(true);

    if (!$admin) {
        // Debug bilgisi - log'a yaz
        $debugInfo = [
            'timestamp' => date('Y-m-d H:i:s'),
            'session_admin_id' => $_SESSION['admin_id'] ?? 'boş',
            'session_email' => $_SESSION['email'] ?? 'boş',
            'session_data_keys' => array_keys($_SESSION),
            'reason' => 'current_admin() null döndü'
        ];
        error_log("Yetki Hatası: " . json_encode($debugInfo));
        swallMsg("login", "yetki");
        exit;
    }

    if ($allowedRoles && (!isset($admin['role']) || !in_array($admin['role'], $allowedRoles, true))) {
        error_log("Rol Hatası: " . json_encode(['allowed' => $allowedRoles, 'current' => $admin['role'] ?? 'none']));
        swallMsg("login", "yetki");
        exit;
    }

    if ($requiredPermissions) {
        foreach ($requiredPermissions as $perm) {
            $flag = $admin['permissions'][$perm] ?? 0;
            if ((int)$flag !== 1) {
                error_log("İzin Hatası: " . json_encode(['permission' => $perm, 'has' => $flag]));
                swallMsg("login", "yetki");
                exit;
            }
        }
    }
}

// --- Parola doğrulama yardımcıları ---
// Depodaki hash türünü tespit ederek kontrol eder
function verify_admin_password(string $inputPassword, string $storedHash): array
{
    $storedHash = (string)$storedHash;
    $hashType = 'unknown';

    // bcrypt ($2y$) veya argon2 ailesi için native verify
    if (preg_match('/^\$2y\$/', $storedHash)) {
        $hashType = 'bcrypt';
        return [password_verify($inputPassword, $storedHash) === true, $hashType];
    }
    if (preg_match('/^\$argon2(id|i|d)\$/', $storedHash)) {
        $hashType = 'argon2';
        return [password_verify($inputPassword, $storedHash) === true, $hashType];
    }

    // 32 haneli md5 hex
    if (preg_match('/^[a-f0-9]{32}$/i', $storedHash)) {
        $hashType = 'md5';
        return [hash_equals(strtolower($storedHash), md5($inputPassword)), $hashType];
    }

    // Düz metin fallback (kaçınılmalı, geçmiş uyumluluk için)
    if ($storedHash !== '' && strpos($storedHash, '$') === false && !preg_match('/^[a-f0-9]{32}$/i', $storedHash)) {
        $hashType = 'plain';
        return [hash_equals($storedHash, $inputPassword), $hashType];
    }

    // Bilinmiyorsa password_verify ile bir şans dene
    $ok = password_verify($inputPassword, $storedHash);
    if ($ok) {
        $hashType = 'php';
    }
    return [$ok, $hashType];
}

function needsPasswordRehash(string $storedHash): bool
{
    // Native PHP hash’lerinde rehash ihtiyacını kontrol et
    if (preg_match('/^(\$2y\$|\$argon2(id|i|d)\$)/', $storedHash)) {
        return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
    }
    // md5 veya plain ise kesin rehash
    if (preg_match('/^[a-f0-9]{32}$/i', $storedHash)) {
        return true;
    }
    if ($storedHash !== '' && strpos($storedHash, '$') === false) {
        return true;
    }
    return false;
}
/**
 * Belirli bir yetkiye sahip olup olmadığını kontrol eder
 * @param string $permission İzin adı (örn: 'can_manage_customers')
 * @return bool
 */
function hasPermission(string $permission): bool
{
    $admin = current_admin(false); // Session'dan hızlı okuma
    if (!$admin) {
        return false;
    }
    
    return isset($admin['permissions'][$permission]) && (int)$admin['permissions'][$permission] === 1;
}

/**
 * Admin yetkisini kontrol eder (rol bazlı)
 * @param array $allowedRoles İzin verilen roller ['admin', 'superadmin']
 * @return bool
 */
function isAdmin(array $allowedRoles = ['admin', 'superadmin']): bool
{
    $admin = current_admin(false);
    if (!$admin || !isset($admin['role'])) {
        return false;
    }
    
    return in_array($admin['role'], $allowedRoles, true);
}

/**
 * Silme işlemi için yetki kontrolü (izin veya admin rolü)
 * @return bool
 */
function canDeleteCustomer(): bool
{
    return hasPermission('can_manage_customers') || isAdmin();
}