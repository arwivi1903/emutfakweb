<?php

class Auth 
{
    private $db;
    private $loginCandidates = [
        [
            'table' => 'admins',
            'id_col' => 'admin_id',
            'pass_col' => 'password_hash',
            'email_cols' => ['email', 'username'],
            'name_col' => 'full_name',
            'status_col' => 'status'
        ],
        [
            'table' => 'admins',
            'id_col' => 'admin_id',
            'pass_col' => 'password_hash',
            'email_cols' => ['email', 'username'],
            'name_col' => 'full_name',
            'status_col' => 'status'
        ],
        [
            'table' => 'users',
            'id_col' => 'admin_id',
            'pass_col' => 'password_hash',
            'email_cols' => ['email', 'username', 'user_name'],
            'name_col' => 'name',
            'status_col' => 'status'
        ]
    ];

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Giriş işlemi
     * @param string $identifier E-posta veya Kullanıcı Adı
     * @param string $password Şifre
     * @return array ['success' => bool, 'message' => string, 'redirect_params' => string]
     */
    public function login(string $identifier, string $password): array
    {
        $identifier = trim($identifier);
        
        if ($identifier === '' || $password === '') {
            xlogSimple('LOGIN', 'Boş email veya şifre girişi');
            return ['success' => false, 'message' => 'Boş alan bırakmayınız', 'redirect_params' => 'islem=bos'];
        }

        $admin = null;
        $activeSource = null;

        $admin = null;
        $activeSource = null;

        // Kullanıcıyı bul
        foreach ($this->loginCandidates as $candidate) {
            $table = $candidate['table'];
            foreach ($candidate['email_cols'] as $emailCol) {
                try {
                    $sql = "SELECT * FROM {$table} WHERE {$emailCol} = ? LIMIT 1";
                    $row = $this->db->getRow($sql, [$identifier]);
                    
                    if ($row) {
                       $admin = $this->normalizeUser($row, $candidate);
                       $activeSource = $candidate;
                       error_log("[LOGIN] Kullanıcı bulundu: {$identifier} - Tablo: {$table}");
                       break 2;
                    }
                } catch (Exception $e) {
                    xlogSimple('ERROR', "Login sorgusu hatası: {$table}.{$emailCol}");
                    continue;
                }
            }
        }

        if (!$admin || !$activeSource) {
            $this->logLoginFailure('user_not_found', $identifier);
            return ['success' => false, 'message' => 'Kullanıcı bulunamadı', 'redirect_params' => 'islem=hata'];
        }

        // Durum kontrolü
        if (!in_array($admin->status, ['active', '1', 'true', 'enabled'], true)) {
            $this->logLoginFailure('account_inactive', $identifier, $admin->status, $admin->admin_id);
            return ['success' => false, 'message' => 'Hesap pasif', 'redirect_params' => 'islem=pasif'];
        }

        // Şifre Doğrulama
        // auth.php içindeki verify_admin_password fonksiyonu
        [$passwordOk, $hashType] = verify_admin_password($password, $admin->password);

        if (!$passwordOk) {
            $this->logLoginFailure('invalid_password', $identifier, $hashType, $admin->admin_id);
            return ['success' => false, 'message' => 'Hatalı şifre', 'redirect_params' => 'islem=hata'];
        }

        // Gerekirse şifreyi yeniden hashle (güvenlik güncellemesi)
        if (in_array($hashType, ['md5', 'plain']) || needsPasswordRehash($admin->password)) {
            $this->updatePasswordHash($activeSource, $admin->admin_id, $password, $hashType);
        }

        // Oturum verilerini ayarla
        $this->setSession($admin, $activeSource);

        // Başarılı girişi logla
        $this->logLoginSuccess($admin, $activeSource);

        return ['success' => true, 'message' => 'Giriş Başarılı', 'redirect_params' => ''];
    }

    private function normalizeUser($row, $candidate) {
        $admin = new stdClass();
        $admin->admin_id = (int)($row->{$candidate['id_col']} ?? 0);
        $admin->email = (string)($row->email ?? $row->username ?? ''); // Best guess for email display
        $admin->password = (string)($row->{$candidate['pass_col']} ?? '');
        $admin->full_name = (string)($row->{$candidate['name_col']} ?? '');
        $admin->admin_pic = (string)($row->admin_pic ?? '');
        $admin->role = (string)($row->role ?? '');
        $admin->status = $row->{$candidate['status_col']} ?? 'active';
        $admin->status = is_string($admin->status) ? strtolower($admin->status) : (string)$admin->status;
        
        // Permissions
        $admin->can_manage_customers = (int)($row->can_manage_customers ?? 0);
        $admin->can_manage_subscriptions = (int)($row->can_manage_subscriptions ?? 0);
        $admin->can_manage_payments = (int)($row->can_manage_payments ?? 0);
        $admin->can_view_analytics = (int)($row->can_view_analytics ?? 0);

        $admin->last_login = $row->last_login ?? null;
        $admin->two_factor_enabled = (int)($row->two_factor_enabled ?? 0);
        $admin->two_factor_secret = $row->two_factor_secret ?? null;
        
        return $admin;
    }

    private function setSession($admin, $activeSource) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin->admin_id;
        $_SESSION['email'] = $admin->email;
        $_SESSION['full_name'] = $admin->full_name;
        $_SESSION['admin_pic'] = $admin->admin_pic;
        $_SESSION['role'] = $admin->role;
        $_SESSION['status'] = $admin->status;
        
        $_SESSION['can_manage_customers'] = $admin->can_manage_customers;
        $_SESSION['can_manage_subscriptions'] = $admin->can_manage_subscriptions;
        $_SESSION['can_manage_payments'] = $admin->can_manage_payments;
        $_SESSION['can_view_analytics'] = $admin->can_view_analytics;
        
        $_SESSION['last_login'] = $admin->last_login;
        $_SESSION['two_factor_enabled'] = $admin->two_factor_enabled;
        $_SESSION['two_factor_secret'] = $admin->two_factor_secret;
        
        $_SESSION['admin_table'] = $activeSource['table'];
        $_SESSION['admin_id_col'] = $activeSource['id_col'];
        $_SESSION['admin_pass_col'] = $activeSource['pass_col'];
    }

    private function updatePasswordHash($activeSource, $adminId, $password, $oldHashType) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE {$activeSource['table']} 
                      SET {$activeSource['pass_col']} = ?, updated_at = NOW() 
                      WHERE {$activeSource['id_col']} = ?";
        $this->db->Update($updateSql, [$newHash, $adminId]);
        xlogSimple('PASSWORD_UPDATE', "Kullanıcı şifre güvenliği güncelleme - {$oldHashType} -> bcrypt", null, null, $this->db);
    }

    private function logLoginFailure($reason, $identifier, $extra = null, $adminId = null) {
        xlog(
            'GIRIS_BASARISIZ', 
            [
                'reason' => $reason,
                'identifier' => $identifier,
                'extra' => $extra,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ],
            null,
            $adminId,
            $this->db
        );
    }

    private function logLoginSuccess($admin, $activeSource) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Son giriş bilgisini güncelle
        try {
            $this->db->Update("UPDATE {$activeSource['table']} 
                         SET last_login = NOW(), last_ip = ?, updated_at = NOW() 
                         WHERE {$activeSource['id_col']} = ?", 
                        [$ip, $admin->admin_id]);
            xlogSimple('UPDATE_LOGIN_INFO', "Son giriş bilgisi güncellendi (IP: {$ip})");
        } catch (Exception $e) {
             // Fallback: last_ip sütunu yoksa
             try {
                $this->db->Update("UPDATE {$activeSource['table']} 
                             SET last_login = NOW(), updated_at = NOW() 
                             WHERE {$activeSource['id_col']} = ?", 
                            [$admin->admin_id]);
             } catch (Exception $e2) {}
        }

        xlogSimple('GIRIS_BASARILI', "Admin giriş başarılı - {$admin->email}");
        
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
            $this->db
        );
    }
}
