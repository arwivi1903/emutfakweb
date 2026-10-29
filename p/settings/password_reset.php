<?php
// DİKKAT: allClass.php/combine.php'yi burada tekrar (ve header.php'den ÖNCE)
// require etmeyin — combine.php'nin otomatik CSRF kontrolü, header.php'nin
// güvenli çerez parametreleriyle oturumu başlatmasından önce session_start()
// tetikleyip admin oturum çerezinin Secure/HttpOnly bayraklarını atlatabilir.
// header.php zaten doğru sırayla ikisini de require ediyor.
require_once __DIR__ . '/../config/header.php';

Auth::requireModule('manage_admins');

require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();
$currentAdmin = current_admin(false) ?: [];

$accountTypes = [
    'admin' => [
        'label'     => 'Yönetici',
        'table'     => 'yoneticiler',
        'id_col'    => 'admin_id',
        'name_col'  => 'full_name',
        'email_col' => 'email',
        'search_sql' => 'SELECT admin_id, username, email, full_name, role, status, updated_at FROM yoneticiler WHERE email = ? OR username = ? LIMIT 1',
    ],
    'customer' => [
        'label'     => 'Kurum Kullanıcısı',
        'table'     => 'users',
        'id_col'    => 'UserID',
        'name_col'  => 'UserName',
        'email_col' => 'UserMail',
        // Gereksiz kolonlar (resim, IP vb.) çekilmez; yalnızca özet ekranı için gerekenler.
        'search_sql' => 'SELECT UserID, FirmaID, UserName, UserMail, UserAuthority, UserStatus FROM users WHERE UserMail = ? LIMIT 1',
    ],
];

// Talep durumları: değer => [Türkçe etiket, rozet sınıfı]. Tek kaynak; hem doğrulama hem görünüm bunu kullanır.
$requestStatuses = [
    'pending'         => ['Bekliyor',          'badge-light-warning'],
    'in_review'       => ['İncelemede',        'badge-light-warning'],
    'matched'         => ['Eşleşti',           'badge-light-success'],
    'completed'       => ['Tamamlandı',        'badge-light-primary'],
    'rejected'        => ['Reddedildi',        'badge-light-danger'],
    'email_not_found' => ['E-posta bulunamadı', 'badge-light-danger'],
    'kurum_not_found' => ['Kurum bulunamadı',  'badge-light-danger'],
];

/* ---------------------------------------------------------------
 * Yardımcı fonksiyonlar
 * ------------------------------------------------------------- */

// Sonuç mesajlarını bir sonraki sayfa yüklemesine taşır (POST-Redirect-GET).
// Böylece sayfa yenilendiğinde şifre sıfırlama işlemi tekrar gönderilmez.
if (!function_exists('pwr_flash_set')) {
    function pwr_flash_set(array $data): void { $_SESSION['pwr_flash'] = $data; }
    function pwr_flash_pull(): array {
        $data = $_SESSION['pwr_flash'] ?? [];
        unset($_SESSION['pwr_flash']);
        return is_array($data) ? $data : [];
    }
    function pwr_redirect(): void {
        $url = strtok((string)($_SERVER['REQUEST_URI'] ?? 'password_reset.php'), '?');
        if (!headers_sent()) {
            header('Location: ' . $url, true, 303);
        } else {
            // header.php çıktıyı başlatmış olabilir; projedeki mevcut desenle yönlendir.
            echo '<script>window.location.replace(' . json_encode($url) . ');</script>';
        }
        exit;
    }

    /** Parola politikası. Hata varsa mesaj, yoksa null döner. */
    function pwr_validate_password(string $password, string $confirm, string $identifier): ?string {
        if ($password === '' || $confirm === '') {
            return 'Yeni şifre ve tekrar alanları zorunludur.';
        }
        // bcrypt 72 baytın ötesini sessizce keser; bunu açıkça reddediyoruz.
        if (strlen($password) < 8 || strlen($password) > 72) {
            return 'Yeni şifre 8 ile 72 karakter arasında olmalıdır.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Yeni şifre en az bir harf ve bir rakam içermelidir.';
        }
        if (!hash_equals($password, $confirm)) {
            return 'Şifreler birbiriyle uyuşmuyor.';
        }
        if ($identifier !== '' && strcasecmp($password, $identifier) === 0) {
            return 'Şifre, kullanıcı adı veya e-posta ile aynı olamaz.';
        }
        return null;
    }

    /**
     * Hedef hesabı bulur.
     * Dönüş: [$kullanici|null, $kurum|null, $hataMesaji|null]
     */
    function pwr_find_account(Database $db, array $config, string $type, string $identifier, string $kurumKodu): array {
        if ($type === 'admin') {
            $user = $db->getRow($config['search_sql'], [$identifier, $identifier]);
            return $user ? [$user, null, null] : [null, null, 'Yönetici bulunamadı. E-posta veya yönetici adını kontrol edin.'];
        }

        $tenant = $db->getRow(
            "SELECT customer_id, company_name, company_email, database_name, status
             FROM kurumlar
             WHERE customer_code = ? OR tenant_alias = ? OR CONCAT('k', customer_id) = ?
             ORDER BY (customer_code = ?) DESC, (tenant_alias = ?) DESC
             LIMIT 1",
            [$kurumKodu, $kurumKodu, $kurumKodu, $kurumKodu, $kurumKodu]
        );
        if (!$tenant) {
            return [null, null, 'Bu kurum kodu / alias ile kayıtlı bir kurum bulunamadı.'];
        }
        // Sıfır güven: veritabanı adı kimlik değeri olarak kullanılmadan önce biçimi doğrulanır.
        if (empty($tenant->database_name) || !preg_match('/^[A-Za-z0-9_]+$/', (string)$tenant->database_name)) {
            return [null, $tenant, 'Kurumun veritabanı henüz kurulmamış veya geçersiz.'];
        }

        $tenantDb = new Database($tenant->database_name);
        $user = $tenantDb->getRow($config['search_sql'], [$identifier]);
        return $user ? [$user, $tenant, null] : [null, $tenant, 'Bu kurumda bu e-posta ile kullanıcı bulunamadı.'];
    }
}

/* ---------------------------------------------------------------
 * Durum değişkenleri
 * ------------------------------------------------------------- */
$currentAdminId = $currentAdmin['admin_id'] ?? null;

$flash = pwr_flash_pull();
$message            = $flash['message'] ?? null;
$messageType        = $flash['message_type'] ?? 'info';
$requestMessage     = $flash['request_message'] ?? null;
$requestMessageType = $flash['request_message_type'] ?? 'info';
$targetSummary      = $flash['summary'] ?? null;

$selectedType = 'admin';
$selectedIdentifier = '';
$selectedKurumKodu = '';
$resetRequests = [];
$resetStats = ['total' => 0, 'matched' => 0, 'pending' => 0, 'completed' => 0];

/* ---------------------------------------------------------------
 * 1) Talep durumu güncelleme
 * ------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_action'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $newStatus = trim((string)($_POST['new_status'] ?? ''));

    if ($requestId <= 0 || !isset($requestStatuses[$newStatus])) {
        $requestMessage = 'Talep güncelleme bilgileri geçersiz.';
        $requestMessageType = 'danger';
    } else {
        try {
            $mailQueueStatus = ($newStatus === 'completed') ? 'processed' : 'pending';
            $db->Update(
                'UPDATE sifre_sifirlama_talepleri SET request_status = ?, mail_queue_status = ? WHERE request_id = ? LIMIT 1',
                [$newStatus, $mailQueueStatus, $requestId]
            );
            // Database::Update hatayı yutar; SQL hatasını ayrıca kontrol et.
            // (Değer aynıysa rowCount 0 döner, bu hata sayılmaz.)
            if ($db->getLastError()) {
                throw new RuntimeException($db->getLastError());
            }
            xlog('PASSWORD_RESET_REQUEST_STATUS', ['request_id' => $requestId, 'new_status' => $newStatus],
                 $requestId, $currentAdminId, $db, null, $newStatus, 'password_reset_request');
            pwr_flash_set(['request_message' => 'Talep durumu güncellendi.', 'request_message_type' => 'success']);
            pwr_redirect();
        } catch (Throwable $e) {
            error_log('[PASSWORD_RESET] talep güncelleme: ' . $e->getMessage());
            $requestMessage = 'Talep güncellenirken bir hata oluştu. Ayrıntı sunucu kaydındadır.';
            $requestMessageType = 'danger';
        }
    }
}

/* ---------------------------------------------------------------
 * 2) Manuel şifre sıfırlama
 * ------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['request_action'])) {
    $selectedType = (string)($_POST['account_type'] ?? 'admin');
    if (!isset($accountTypes[$selectedType])) {
        $selectedType = 'admin';
    }
    $config             = $accountTypes[$selectedType];
    $selectedIdentifier = trim((string)($_POST['identifier'] ?? ''));
    $selectedKurumKodu  = strtolower(trim((string)($_POST['kurum_kodu'] ?? '')));
    $newPassword        = (string)($_POST['new_password'] ?? '');
    $newPasswordConfirm = (string)($_POST['new_password_confirm'] ?? '');

    // Doğrulama hataları: sayfa aynı istekte, girilen değerler korunarak gösterilir (şifre alanları hariç).
    if ($selectedType === 'customer' && $selectedKurumKodu === '') {
        $message = 'Kurum kullanıcısı için kurum kodu veya alias zorunludur.';
        $messageType = 'danger';
    } elseif ($selectedIdentifier === '') {
        $message = 'Lütfen kullanıcı e-posta adresi veya yönetici adı girin.';
        $messageType = 'danger';
    } elseif (($passwordError = pwr_validate_password($newPassword, $newPasswordConfirm, $selectedIdentifier)) !== null) {
        $message = $passwordError;
        $messageType = 'danger';
    } else {
        try {
            [$targetUser, $selectedTenant, $findError] = pwr_find_account($db, $config, $selectedType, $selectedIdentifier, $selectedKurumKodu);

            if (!$targetUser) {
                $message = $findError ?: 'Kullanıcı bulunamadı.';
                $messageType = 'danger';
            } elseif ($selectedType === 'admin'
                && ($targetUser->role ?? '') === 'superadmin'
                && ($currentAdmin['role'] ?? '') !== 'superadmin') {
                // Yetki yükseltme koruması: "manage_admins" yetkisi olan biri süper yöneticinin şifresini değiştiremez.
                xlog('PASSWORD_RESET_DENIED', ['target_id' => $targetUser->{$config['id_col']}, 'reason' => 'superadmin_protected'],
                     $targetUser->{$config['id_col']}, $currentAdminId, $db, null, null, 'password_reset');
                $message = 'Süper yönetici hesabının şifresini yalnızca bir süper yönetici sıfırlayabilir.';
                $messageType = 'danger';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $targetId     = $targetUser->{$config['id_col']};
                $targetEmail  = $targetUser->{$config['email_col']};

                if ($selectedType === 'admin') {
                    $targetDb = $db;
                    $updateSql = "UPDATE {$config['table']} SET password_hash = ?, updated_at = NOW() WHERE {$config['id_col']} = ?";
                } else {
                    $targetDb = new Database($selectedTenant->database_name);
                    // Oturum bağlama bilgileri temizlenir: eski oturumlar bu şifreyle devam edemez.
                    $updateSql = "UPDATE {$config['table']} SET UserPass = ?, SessionMail = NULL, IpAdress = NULL WHERE {$config['id_col']} = ?";
                }

                $updated = $targetDb->Update($updateSql, [$passwordHash, $targetId]);

                if ($targetDb->getLastError()) {
                    throw new RuntimeException($targetDb->getLastError());
                }

                if ($updated > 0) {
                    $tokenValue = ($selectedType === 'customer' ? $selectedTenant->database_name : 'master') . '::' . bin2hex(random_bytes(16));

                    // Denetim izi: kullanılmış (used=1) kayıt olarak yazılır; geçerli bir sıfırlama bağlantısı üretmez.
                    $db->Insert(
                        'INSERT INTO sifre_sifirlama (email, token, user_type, expires_at, used, used_at, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 1, NOW(), NOW())',
                        [$targetEmail, $tokenValue, $selectedType]
                    );
                    if ($db->getLastError()) {
                        error_log('[PASSWORD_RESET] sifre_sifirlama kaydı yazılamadı: ' . $db->getLastError());
                    }

                    // Şifrenin kendisi hiçbir yere (log dahil) yazılmaz.
                    xlog('PASSWORD_RESET', [
                        'account_type'   => $selectedType,
                        'identifier'     => $selectedIdentifier,
                        'kurum_kodu'     => $selectedKurumKodu,
                        'target_email'   => $targetEmail,
                        'target_id'      => $targetId,
                        'tenant_db'      => $selectedTenant->database_name ?? null,
                        'reset_by'       => $currentAdminId,
                        'reset_by_email' => $currentAdmin['email'] ?? null,
                    ], $targetId, $currentAdminId, $db, null, null, 'password_reset');

                    pwr_flash_set([
                        'message'      => $config['label'] . ' için şifre başarıyla güncellendi.',
                        'message_type' => 'success',
                        'summary'      => [
                            'label'      => $config['label'],
                            'id'         => $targetId,
                            'email'      => $targetEmail,
                            'name'       => $targetUser->{$config['name_col']} ?? '',
                            'status'     => $selectedType === 'admin' ? ($targetUser->status ?? 'unknown') : (!empty($targetUser->UserStatus) ? 'active' : 'passive'),
                            'role'       => $selectedType === 'admin' ? ($targetUser->role ?? '-') : ($targetUser->UserAuthority ?? '-'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'tenant'     => $selectedTenant->company_name ?? null,
                            'tenant_db'  => $selectedTenant->database_name ?? null,
                        ],
                    ]);
                    pwr_redirect();
                }

                $message = 'Şifre güncellenemedi. Kayıt üzerinde değişiklik yapılmamış olabilir.';
                $messageType = 'warning';
            }
        } catch (Throwable $e) {
            // Kullanıcıya sade mesaj, ayrıntı loga.
            error_log('[PASSWORD_RESET] ' . $e->getMessage());
            $message = 'İşlem sırasında bir hata oluştu. Ayrıntı sunucu kaydındadır.';
            $messageType = 'danger';
        }
    }
}

/* ---------------------------------------------------------------
 * 3) Talep listesi ve istatistikler
 * ------------------------------------------------------------- */
if (method_exists($db, 'tableExists') && $db->tableExists('sifre_sifirlama_talepleri')) {
    $resetRequests = $db->getRowsAssoc(
        "SELECT t.request_id, t.kurum_kodu, t.request_email, t.customer_id, t.tenant_db, t.user_id,
                t.request_status, t.mail_queue_status, t.request_ip, t.created_at,
                k.company_name, k.customer_code, k.tenant_alias
         FROM sifre_sifirlama_talepleri t
         LEFT JOIN kurumlar k ON k.customer_id = t.customer_id
         ORDER BY t.request_id DESC
         LIMIT 100"
    );

    foreach ($resetRequests as $req) {
        $status = (string)($req['request_status'] ?? 'pending');
        $resetStats['total']++;
        if ($status === 'matched')   { $resetStats['matched']++; }
        if ($status === 'pending' || $status === 'in_review') { $resetStats['pending']++; }
        if ($status === 'completed') { $resetStats['completed']++; }
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Şifre Sıfırlama</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Şifre Sıfırlama</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <?php if ($message): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?> d-flex align-items-center p-5 mb-8">
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">Bilgi</h4>
                            <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($requestMessage): ?>
                    <div class="alert alert-<?= htmlspecialchars($requestMessageType, ENT_QUOTES, 'UTF-8') ?> d-flex align-items-center p-5 mb-8">
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-<?= htmlspecialchars($requestMessageType, ENT_QUOTES, 'UTF-8') ?>">Talep Durumu</h4>
                            <span><?= htmlspecialchars($requestMessage, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card mb-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark">Şifre Sıfırlama Talepleri</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Login ekranından gelen kurum kodu/alias talepleri</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row g-4 mb-6">
                            <div class="col-md-3">
                                <div class="bg-light rounded p-4 h-100">
                                    <div class="fs-7 text-muted">Toplam Talep</div>
                                    <div class="fs-2 fw-bold text-gray-900"><?= (int)$resetStats['total'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light-success rounded p-4 h-100">
                                    <div class="fs-7 text-muted">Eşleşen</div>
                                    <div class="fs-2 fw-bold text-success"><?= (int)$resetStats['matched'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light-warning rounded p-4 h-100">
                                    <div class="fs-7 text-muted">Bekleyen / İncelemede</div>
                                    <div class="fs-2 fw-bold text-warning"><?= (int)$resetStats['pending'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-light-primary rounded p-4 h-100">
                                    <div class="fs-7 text-muted">Tamamlanan</div>
                                    <div class="fs-2 fw-bold text-primary"><?= (int)$resetStats['completed'] ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-7 gy-4">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                        <th>ID</th>
                                        <th>Kurum</th>
                                        <th>Email</th>
                                        <th>Durum</th>
                                        <th>Kuyruk</th>
                                        <th>Tarih</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-700">
                                    <?php if (empty($resetRequests)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-8">Henüz talep kaydı bulunmuyor.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($resetRequests as $req): ?>
                                            <?php
                                                $status = (string)($req['request_status'] ?? 'pending');
                                                [$statusLabel, $statusClass] = $requestStatuses[$status] ?? [$status, 'badge-light-secondary'];
                                            ?>
                                            <tr>
                                                <td>#<?= (int)($req['request_id'] ?? 0) ?></td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars((string)($req['company_name'] ?: '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="text-muted">Kod/Alias: <?= htmlspecialchars((string)($req['kurum_kodu'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars((string)($req['request_email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="text-muted fs-8">IP: <?= htmlspecialchars((string)($req['request_ip'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                                                </td>
                                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><span class="badge badge-light-info"><?= htmlspecialchars((string)($req['mail_queue_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><?= htmlspecialchars((string)($req['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-end">
                                                    <form method="POST" class="d-inline-flex gap-2 align-items-center">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="request_action" value="1">
                                                        <input type="hidden" name="request_id" value="<?= (int)($req['request_id'] ?? 0) ?>">
                                                        <select name="new_status" class="form-select form-select-sm form-select-solid" style="min-width: 145px;">
                                                            <?php foreach ($requestStatuses as $opt => [$optLabel]): ?>
                                                                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $opt ? 'selected' : '' ?>><?= htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8') ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-light-primary">Kaydet</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row g-5 g-xl-8">
                    <div class="col-xl-8">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Şifreyi Güncelle</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Hesabı bulun ve yeni şifreyi doğrudan atayın.</span>
                                </h3>
                            </div>
                            <div class="card-body pt-4">
                                <form method="POST" class="form">
                                    <?= csrfTokenField() ?>
                                    <input type="hidden" name="form_mode" value="manual_reset">
                                    <div class="row g-5">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark">Hesap Türü</label>
                                            <select name="account_type" class="form-select form-select-solid">
                                                <option value="admin" <?= $selectedType === 'admin' ? 'selected' : '' ?>>Yönetici</option>
                                                <option value="customer" <?= $selectedType === 'customer' ? 'selected' : '' ?>>Kurum Kullanıcısı</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold text-dark">E-posta / Kullanıcı Adı</label>
                                            <input type="text" name="identifier" value="<?= htmlspecialchars($selectedIdentifier, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-solid" placeholder="admin@prolyn.net veya kullanıcı adı">
                                        </div>
                                        <div class="col-md-12 customer-field" style="display:none;">
                                            <label class="form-label fw-bold text-dark">Kurum Kodu / Alias</label>
                                            <input type="text" name="kurum_kodu" value="<?= htmlspecialchars($selectedKurumKodu, ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-solid" placeholder="k1 veya alias kodu">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Yeni Şifre</label>
                                            <div class="input-group input-group-solid">
                                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="En az 8 karakter, harf ve rakam" autocomplete="new-password" maxlength="72" required>
                                                <button class="btn btn-light" type="button" id="generate_password">Üret</button>
                                                <button class="btn btn-light" type="button" id="toggle_password" aria-pressed="false" aria-label="Şifreleri göster veya gizle">Göster</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Yeni Şifre Tekrar</label>
                                            <input type="password" name="new_password_confirm" id="new_password_confirm" class="form-control form-control-solid" placeholder="Şifreyi tekrar girin" autocomplete="new-password" maxlength="72" required>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 mt-8">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ki-duotone ki-shield-tick fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                            Şifreyi Sıfırla
                                        </button>
                                        <button type="reset" class="btn btn-light">Temizle</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-5 mb-xl-8 bg-light-primary border-0">
                            <div class="card-body p-8">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-primary">
                                            <i class="ki-duotone ki-key fs-2 text-white"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="text-gray-900 fw-bold mb-1">İşlem Notu</h3>
                                        <div class="text-muted fw-semibold">Güncelleme anında kayıt altına alınır.</div>
                                    </div>
                                </div>
                                <div class="fs-6 text-gray-700 fw-semibold">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ki-duotone ki-check-circle fs-3 text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                                        Şifreler bcrypt ile hashlenir.
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ki-duotone ki-check-circle fs-3 text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                                        Sıfırlama kaydı sifre_sifirlama tablosuna yazılır.
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-check-circle fs-3 text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                                        İşlem, mevcut yönetici log altyapısına da düşer.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($targetSummary): ?>
                            <div class="card card-xl-stretch">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark">Son Bulunan Kayıt</span>
                                        <span class="text-muted mt-1 fw-semibold fs-7"><?= htmlspecialchars($targetSummary['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </h3>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="d-flex flex-column gap-3 text-gray-700 fw-semibold fs-6">
                                        <div><span class="text-gray-500">ID:</span> <?= htmlspecialchars((string)$targetSummary['id'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div><span class="text-gray-500">Ad / Ünvan:</span> <?= htmlspecialchars((string)$targetSummary['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div><span class="text-gray-500">E-posta:</span> <?= htmlspecialchars((string)$targetSummary['email'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div><span class="text-gray-500">Rol:</span> <?= htmlspecialchars((string)$targetSummary['role'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div><span class="text-gray-500">Durum:</span> <?= htmlspecialchars((string)$targetSummary['status'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($targetSummary['tenant'])): ?>
                                            <div><span class="text-gray-500">Kurum:</span> <?= htmlspecialchars((string)$targetSummary['tenant'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($targetSummary['tenant_db'])): ?>
                                            <div><span class="text-gray-500">DB:</span> <?= htmlspecialchars((string)$targetSummary['tenant_db'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                        <?php if ($targetSummary['updated_at']): ?>
                                            <div><span class="text-gray-500">Son Güncelleme:</span> <?= htmlspecialchars((string)$targetSummary['updated_at'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const generateButton = document.getElementById('generate_password');
    const passwordInput = document.getElementById('new_password');
    const passwordConfirmInput = document.getElementById('new_password_confirm');

    // Kriptografik olarak güvenli rastgele parola (Math.random tahmin edilebilir, kullanılmaz).
    function buildPassword(length = 8) {
        // Yalnızca harf ve rakam (özel işaret yok); karışan karakterler (0/O, 1/l/I) hariç.
        const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        const limit = 256 - (256 % characters.length); // modulo yanlılığını önlemek için
        let password = '';
        while (password.length < length) {
            const bytes = new Uint8Array(length);
            window.crypto.getRandomValues(bytes);
            for (const b of bytes) {
                if (b < limit && password.length < length) {
                    password += characters.charAt(b % characters.length);
                }
            }
        }
        return password;
    }

    // Şifreleri göster / gizle (iki alan birlikte değişir)
    const toggleButton = document.getElementById('toggle_password');
    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            passwordConfirmInput.type = show ? 'text' : 'password';
            toggleButton.textContent = show ? 'Gizle' : 'Göster';
            toggleButton.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    }

    if (generateButton) {
        generateButton.addEventListener('click', function () {
            let password = buildPassword();
            // 8 karakter; en az bir büyük harf, bir küçük harf ve bir rakam içerir.
            while (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password)) { password = buildPassword(); }
            passwordInput.value = password;
            passwordConfirmInput.value = password;
            // Üretilen şifre kopyalanabilsin diye iki alan da görünür yapılır.
            passwordInput.type = 'text';
            passwordConfirmInput.type = 'text';
            if (toggleButton) { toggleButton.textContent = 'Gizle'; toggleButton.setAttribute('aria-pressed', 'true'); }
            passwordInput.focus();
        });
    }

    const accountTypeSelect = document.querySelector('select[name="account_type"]');
    const customerFields = document.querySelectorAll('.customer-field');

    function toggleCustomerFields() {
        const isCustomer = accountTypeSelect && accountTypeSelect.value === 'customer';
        customerFields.forEach(function (field) {
            field.style.display = isCustomer ? '' : 'none';
        });
    }

    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', toggleCustomerFields);
        toggleCustomerFields();
    }
});
</script>

<?php require_once __DIR__ . '/../config/footer.php'; ?>