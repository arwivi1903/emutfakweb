<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions/combine.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /prolynweb/p/login.php', true, 302);
    exit;
}

$db = new Database();
$accountType = $_POST['account_type'] ?? 'admin';
$identifier = trim($_POST['identifier'] ?? '');
$companyEmail = trim($_POST['company_email'] ?? '');

if (!in_array($accountType, ['admin', 'customer'], true) || $identifier === '') {
    header('Location: /prolynweb/p/login.php?islem=hata', true, 302);
    exit;
}

$created = false;
$requestEmail = '';
$tenantName = '';
$tenantDbName = '';

try {
    if ($accountType === 'admin') {
        $admin = $db->getRow('SELECT admin_id, email, full_name FROM yoneticiler WHERE email = ? OR username = ? LIMIT 1', [$identifier, $identifier]);
        if ($admin) {
            $requestEmail = $admin->email;
            $created = true;

            $resetToken = bin2hex(random_bytes(16));
            $db->Insert(
                'INSERT INTO sifre_sifirlama (email, token, user_type, expires_at, used, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 0, NOW())',
                [$requestEmail, 'master::' . $resetToken, 'admin']
            );

            $admins = $db->getRows('SELECT admin_id FROM yoneticiler WHERE status = "active"');
            foreach ($admins as $notifyAdmin) {
                $db->Insert(
                    'INSERT INTO bildirimler (admin_id, title, message, type, is_read, related_entity_type, action_url) VALUES (?, ?, ?, ?, 0, ?, ?)',
                    [
                        $notifyAdmin->admin_id,
                        'Yeni şifre talebi',
                        'Yönetici hesabı için şifre talebi oluşturuldu. E-posta: ' . $requestEmail,
                        'warning',
                        'password_reset',
                        'logs/password_resets.php'
                    ]
                );
            }

            $db->Insert(
                'INSERT INTO bildirim_kuyrugu (channel, recipient, subject, message, status, attempts, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())',
                ['email', $requestEmail, 'Şifre talebi alındı', 'Yönetici şifreniz için talep oluşturuldu.']
            );

            xlog('PASSWORD_REQUEST', ['type' => 'admin', 'email' => $requestEmail], $admin->admin_id, $admin->admin_id, $db, null, null, 'password_request');
        }
    } else {
        if ($companyEmail !== '') {
            $tenant = $db->getRow('SELECT customer_id, company_name, company_email, database_name FROM kurumlar WHERE company_email = ? LIMIT 1', [$companyEmail]);
            if ($tenant && !empty($tenant->database_name)) {
                $tenantName = $tenant->company_name;
                $tenantDbName = $tenant->database_name;
                $tenantDb = new Database($tenant->database_name);
                $user = $tenantDb->getRow('SELECT UserID, UserMail, UserName FROM users WHERE UserMail = ? LIMIT 1', [$identifier]);
                if ($user) {
                    $requestEmail = $user->UserMail;
                    $created = true;

                    $resetToken = bin2hex(random_bytes(16));
                    $db->Insert(
                        'INSERT INTO sifre_sifirlama (email, token, user_type, expires_at, used, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 0, NOW())',
                        [$requestEmail, $tenantDbName . '::' . $resetToken, 'customer']
                    );

                    $admins = $db->getRows('SELECT admin_id FROM yoneticiler WHERE status = "active"');
                    foreach ($admins as $notifyAdmin) {
                        $db->Insert(
                            'INSERT INTO bildirimler (admin_id, title, message, type, is_read, related_entity_type, action_url) VALUES (?, ?, ?, ?, 0, ?, ?)',
                            [
                                $notifyAdmin->admin_id,
                                'Yeni kurum şifre talebi',
                                'Kurum: ' . $tenantName . ' | Kullanıcı: ' . $requestEmail,
                                'warning',
                                'password_reset',
                                'settings/password_reset.php'
                            ]
                        );
                    }

                    $db->Insert(
                        'INSERT INTO bildirim_kuyrugu (channel, recipient, subject, message, status, attempts, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())',
                        ['email', $requestEmail, 'Şifre talebi alındı', 'Kurumunuz için şifre talebi oluşturuldu.']
                    );

                    xlog('PASSWORD_REQUEST', ['type' => 'customer', 'email' => $requestEmail, 'company_email' => $companyEmail, 'tenant_db' => $tenantDbName], $user->UserID, null, $db, null, null, 'password_request');
                }
            }
        }
    }
} catch (Exception $e) {
    error_log('[PASSWORD_REQUEST] ' . $e->getMessage());
}

header('Location: /prolynweb/p/login.php?islem=talep_ok', true, 302);
exit;