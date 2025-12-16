<?php

require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
require_once __DIR__ . '/../functions/logtut.php';

start_secure_session();
$db = new Database();
$db->setDebugMode(true);
$loginUrl = '../login.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    xlogSimple('POST olmayan istek', 'POST olmayan isteği islem.php\'ye erişim denemesi');
    header("Location: {$loginUrl}?islem=bos", true, 302);
    exit;
}

// Form verilerini al
$identifier = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Boş alan kontrolü
if ($identifier === '' || $password === '') {
    xlogSimple('LOGIN', 'Boş email veya şifre girişi');
    header("Location: {$loginUrl}?islem=bos", true, 302);
    exit;
}

// Deneme yapılacak tablo ve sütun kombinasyonları
$tableCandidates = [
    [
        'table' => 'admin_users',
        'id_col' => 'admin_id',
        'pass_col' => 'password',
        'email_cols' => ['email', 'username'],
        'name_col' => 'full_name',
        'status_col' => 'status'
    ],
    [
        'table' => 'admins',
        'id_col' => 'admin_id',
        'pass_col' => 'password',
        'email_cols' => ['email', 'username'],
        'name_col' => 'full_name',
        'status_col' => 'status'
    ],
    [
        'table' => 'users',
        'id_col' => 'id',
        'pass_col' => 'password_hash',
        'email_cols' => ['email', 'username', 'user_name'],
        'name_col' => 'name',
        'status_col' => 'status'
    ]
];

$admin = null;
$activeSource = null;

// Her tablo kombinasyonunu dene
foreach ($tableCandidates as $candidate) {
    $table = $candidate['table'];
    
    // Her email/username sütununu dene
    foreach ($candidate['email_cols'] as $emailCol) {
        try {
            $sql = "SELECT * FROM {$table} WHERE {$emailCol} = ? LIMIT 1";
            $row = $db->getRow($sql, [$identifier]);
            
            if ($row) {
                // Kullanıcı bulundu, normalize et
                $admin = new stdClass();
                $admin->admin_id = (int)($row->{$candidate['id_col']} ?? 0);
                $admin->email = (string)($row->{$emailCol} ?? '');
                $admin->password = (string)($row->{$candidate['pass_col']} ?? '');
                $admin->full_name = (string)($row->{$candidate['name_col']} ?? '');
                $admin->admin_pic = (string)($row->admin_pic ?? '');
                $admin->role = (string)($row->role ?? '');
                $admin->status = $row->{$candidate['status_col']} ?? 'active';
                
                // İzinleri al (varsa)
                $admin->can_manage_customers = (int)($row->can_manage_customers ?? 0);
                $admin->can_manage_subscriptions = (int)($row->can_manage_subscriptions ?? 0);
                $admin->can_manage_payments = (int)($row->can_manage_payments ?? 0);
                $admin->can_view_analytics = (int)($row->can_view_analytics ?? 0);

                //last login (varsa)
                $admin->last_login = $row->last_login ?? null;
                
                // 2FA bilgileri (varsa)
                $admin->two_factor_enabled = (int)($row->two_factor_enabled ?? 0);
                $admin->two_factor_secret = $row->two_factor_secret ?? null;
                
                // Kaynak bilgisini sakla
                $activeSource = $candidate;
                
                error_log("[LOGIN] Kullanıcı bulundu: {$identifier} - Tablo: {$table}");
                break 2; // İki döngüden de çık
            }
        } catch (Exception $e) {
            xlogSimple('ERROR', "Login sorgusu hatası: {$table}.{$emailCol}");
            continue;
        }
    }
}

// Kullanıcı bulunamadı
if (!$admin || !$activeSource) {
    xlog(
        'GIRIS_BASARISIZ', 
        [
            'reason' => 'user_not_found',
            'identifier' => $identifier,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ],
        null,
        null,
        $db
    );
    header("Location: {$loginUrl}?islem=hata", true, 302);
    exit;
}

// Durum kontrolü
$status = is_string($admin->status) ? strtolower($admin->status) : (string)$admin->status;
if (!in_array($status, ['active', '1', 'true', 'enabled'], true)) {
    xlog(
        'GIRIS_BASARISIZ', 
        [
            'reason' => 'account_inactive',
            'identifier' => $identifier,
            'account_status' => $status,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ],
        null,
        $admin->admin_id,
        $db
    );
    header("Location: {$loginUrl}?islem=pasif", true, 302);
    exit;
}

// Şifre doğrulama
[$passwordOk, $hashType] = verify_admin_password($password, $admin->password);

if (!$passwordOk) {
    xlog(
        'GIRIS_BASARISIZ', 
        [
            'reason' => 'invalid_password',
            'identifier' => $identifier,
            'hash_type' => $hashType,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ],
        null,
        $admin->admin_id,
        $db
    );
    header("Location: {$loginUrl}?islem=hata", true, 302);
    exit;
}

// Eski hash varsa güncelle (md5 veya plain text)
if (in_array($hashType, ['md5', 'plain']) || needsPasswordRehash($admin->password)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $updateSql = "UPDATE {$activeSource['table']} 
                  SET {$activeSource['pass_col']} = ?, updated_at = NOW() 
                  WHERE {$activeSource['id_col']} = ?";
    $db->Update($updateSql, [$newHash, $admin->admin_id]);
    xlogSimple('PASSWORD_UPDATE', "Kullanıcı şifre güvenliği güncelleme - {$hashType} -> bcrypt", null, null, $db);
}

// Session'ı yenile
session_regenerate_id(true);

// Session verilerini set et
$_SESSION['admin_id'] = $admin->admin_id;
$_SESSION['email'] = $admin->email;
$_SESSION['full_name'] = $admin->full_name;
$_SESSION['admin_pic'] = $admin->admin_pic;
$_SESSION['role'] = $admin->role;
$_SESSION['status'] = $admin->status;

// İzinleri session'a ekle
$_SESSION['can_manage_customers'] = $admin->can_manage_customers;
$_SESSION['can_manage_subscriptions'] = $admin->can_manage_subscriptions;
$_SESSION['can_manage_payments'] = $admin->can_manage_payments;
$_SESSION['can_view_analytics'] = $admin->can_view_analytics;

// Last login bilgisi
$_SESSION['last_login'] = $admin->last_login;

// 2FA bilgileri
$_SESSION['two_factor_enabled'] = $admin->two_factor_enabled;
$_SESSION['two_factor_secret'] = $admin->two_factor_secret;

// Tablo bilgilerini session'a ekle (gelecekte kullanmak için)
$_SESSION['admin_table'] = $activeSource['table'];
$_SESSION['admin_id_col'] = $activeSource['id_col'];
$_SESSION['admin_pass_col'] = $activeSource['pass_col'];

// Session oluşturma başarılı logu
xlogSimple('GIRIS_BASARILI', "Admin giriş başarılı - {$admin->email}");

// Son giriş bilgisini güncelle
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Eğer last_ip kolonu varsa onu da güncelle
try {
    $db->Update("UPDATE {$activeSource['table']} 
                 SET last_login = NOW(), last_ip = ?, updated_at = NOW() 
                 WHERE {$activeSource['id_col']} = ?", 
                [$ip, $admin->admin_id]);
    
    // Son giriş bilgisi güncelleme başarılı logu
    xlogSimple('UPDATE_LOGIN_INFO', "Son giriş bilgisi güncellendi (IP dahil: {$ip})");

} catch (Exception $e) {
    // last_ip kolonu yoksa sadece last_login güncelle
    try {
        $db->Update("UPDATE {$activeSource['table']} 
                     SET last_login = NOW(), updated_at = NOW() 
                     WHERE {$activeSource['id_col']} = ?", 
                    [$admin->admin_id]);
        
        // Son giriş bilgisi güncelleme başarılı logu (IP sütunu yok)
        xlogSimple('UPDATE_LOGIN_INFO', "Son giriş bilgisi güncellendi (IP sütunu mevcut değil)");
    } catch (Exception $e2) {
        xlogSimple('ERROR', "Son giriş bilgisi güncellenirken hata: " . $e2->getMessage());
    }
}

// Başarılı giriş logu
xlog(
    'GIRIS_BASARILI', 
    [
        'email' => $admin->email, 
        'ip' => $ip, 
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'full_name' => $admin->full_name,
        'role' => $admin->role
    ],
    null,
    $admin->admin_id,
    $db
);

// Index.php'ye yönlendir
header('Location: ../', true, 302);
exit;

?>