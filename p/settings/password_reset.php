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
        'label' => 'Yönetici',
        'table' => 'yoneticiler',
        'id_col' => 'admin_id',
        'name_col' => 'full_name',
        'email_col' => 'email',
        'search_sql' => 'SELECT admin_id, username, email, full_name, role, status, updated_at FROM yoneticiler WHERE email = ? OR username = ? LIMIT 1',
    ],
    'customer' => [
        'label' => 'Kurum Kullanıcısı',
        'table' => 'users',
        'id_col' => 'UserID',
        'name_col' => 'UserName',
        'email_col' => 'UserMail',
        'search_sql' => 'SELECT UserID, FirmaID, UserName, UserMail, UserAuthority, UserStatus, BranchID, UserPicture, SessionMail, IpAdress FROM users WHERE UserMail = ? LIMIT 1',
    ],
];

$message = null;
$messageType = 'info';
$selectedType = 'admin';
$selectedIdentifier = '';
$selectedKurumKodu = '';
$targetUser = null;
$selectedTenant = null;
$requestMessage = null;
$requestMessageType = 'info';
$resetRequests = [];
$resetStats = [
    'total' => 0,
    'matched' => 0,
    'pending' => 0,
    'completed' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_action'])) {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $newStatus = trim((string)($_POST['new_status'] ?? ''));
    $allowedStatuses = ['pending', 'matched', 'in_review', 'completed', 'rejected', 'email_not_found', 'kurum_not_found'];

    if ($requestId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
        $requestMessage = 'Talep güncelleme bilgileri geçersiz.';
        $requestMessageType = 'danger';
    } else {
        try {
            $mailQueueStatus = ($newStatus === 'completed') ? 'processed' : 'pending';
            $updated = $db->Update(
                'UPDATE sifre_sifirlama_talepleri SET request_status = ?, mail_queue_status = ? WHERE request_id = ? LIMIT 1',
                [$newStatus, $mailQueueStatus, $requestId]
            );

            if ($updated > 0) {
                $requestMessage = 'Talep durumu güncellendi.';
                $requestMessageType = 'success';
            } else {
                $requestMessage = 'Talep güncellenemedi veya kayıt bulunamadı.';
                $requestMessageType = 'warning';
            }
        } catch (Exception $e) {
            $requestMessage = 'Talep güncelleme sırasında hata oluştu: ' . $e->getMessage();
            $requestMessageType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['request_action'])) {
    $selectedType = $_POST['account_type'] ?? 'admin';
    if (!isset($accountTypes[$selectedType])) {
        $selectedType = 'admin';
    }

    $selectedIdentifier = trim($_POST['identifier'] ?? '');
    $selectedKurumKodu = strtolower(trim($_POST['kurum_kodu'] ?? ''));
    $newPassword = (string)($_POST['new_password'] ?? '');
    $newPasswordConfirm = (string)($_POST['new_password_confirm'] ?? '');

    if ($selectedType === 'customer' && $selectedKurumKodu === '') {
        $message = 'Kurum kullanıcısı için kurum kodu veya alias zorunludur.';
        $messageType = 'danger';
    } elseif ($selectedIdentifier === '') {
        $message = 'Lütfen kullanıcı e-posta adresi veya yönetici adı girin.';
        $messageType = 'danger';
    } elseif ($newPassword === '' || $newPasswordConfirm === '') {
        $message = 'Yeni şifre ve tekrar alanları zorunludur.';
        $messageType = 'danger';
    } elseif (strlen($newPassword) < 8) {
        $message = 'Yeni şifre en az 8 karakter olmalıdır.';
        $messageType = 'danger';
    } elseif ($newPassword !== $newPasswordConfirm) {
        $message = 'Şifreler birbiriyle uyuşmuyor.';
        $messageType = 'danger';
    } else {
        $config = $accountTypes[$selectedType];
        try {
            if ($selectedType === 'admin') {
                $targetUser = $db->getRow($config['search_sql'], [$selectedIdentifier, $selectedIdentifier]);
            } else {
                $selectedTenant = $db->getRow(
                    "SELECT customer_id, company_name, company_email, database_name, status
                     FROM kurumlar
                     WHERE customer_code = ? OR tenant_alias = ? OR CONCAT('k', customer_id) = ?
                     ORDER BY (customer_code = ?) DESC, (tenant_alias = ?) DESC
                     LIMIT 1",
                    [$selectedKurumKodu, $selectedKurumKodu, $selectedKurumKodu, $selectedKurumKodu, $selectedKurumKodu]
                );

                if ($selectedTenant && !empty($selectedTenant->database_name)) {
                    $tenantDb = new Database($selectedTenant->database_name);
                    $targetUser = $tenantDb->getRow($config['search_sql'], [$selectedIdentifier]);
                }
            }

            if (!$targetUser) {
                $message = 'Kullanıcı bulunamadı. E-posta adresini veya yönetici adını kontrol edin.';
                $messageType = 'danger';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($selectedType === 'admin') {
                    $updated = $db->Update(
                        "UPDATE {$config['table']} SET password_hash = ?, updated_at = NOW() WHERE {$config['id_col']} = ?",
                        [$passwordHash, $targetUser->{$config['id_col']}]
                    );
                } else {
                    $tenantDb = new Database($selectedTenant->database_name);
                    $updated = $tenantDb->Update(
                        "UPDATE {$config['table']} SET UserPass = ?, SessionMail = NULL, IpAdress = NULL WHERE {$config['id_col']} = ?",
                        [$passwordHash, $targetUser->{$config['id_col']}]
                    );
                }

                if ($updated > 0) {
                    $resetToken = bin2hex(random_bytes(16));
                    $tokenValue = $selectedType === 'customer' && $selectedTenant
                        ? $selectedTenant->database_name . '::' . $resetToken
                        : 'master::' . $resetToken;

                    $db->Insert(
                        'INSERT INTO sifre_sifirlama (email, token, user_type, expires_at, used, used_at, created_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), 1, NOW(), NOW())',
                        [$targetUser->{$config['email_col']}, $tokenValue, $selectedType]
                    );

                    xlog(
                        'PASSWORD_RESET',
                        [
                            'account_type' => $selectedType,
                            'identifier' => $selectedIdentifier,
                            'kurum_kodu' => $selectedKurumKodu,
                            'target_email' => $targetUser->{$config['email_col']},
                            'target_id' => $targetUser->{$config['id_col']},
                            'tenant_db' => $selectedTenant->database_name ?? null,
                            'reset_by' => $currentAdmin['admin_id'] ?? null,
                            'reset_by_email' => $currentAdmin['email'] ?? null,
                        ],
                        $targetUser->{$config['id_col']},
                        $currentAdmin['admin_id'] ?? null,
                        $db,
                        null,
                        null,
                        'password_reset'
                    );

                    $message = $config['label'] . ' için şifre başarıyla güncellendi.';
                    $messageType = 'success';
                } else {
                    $message = 'Şifre güncellenemedi. Kayıt üzerinde değişiklik yapılmamış olabilir.';
                    $messageType = 'warning';
                }
            }
        } catch (Exception $e) {
            $message = 'İşlem sırasında hata oluştu: ' . $e->getMessage();
            $messageType = 'danger';
            error_log('[PASSWORD_RESET] ' . $e->getMessage());
        }
    }
}

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
        if ($status === 'matched') {
            $resetStats['matched']++;
        }
        if ($status === 'pending' || $status === 'in_review') {
            $resetStats['pending']++;
        }
        if ($status === 'completed') {
            $resetStats['completed']++;
        }
    }
}

$targetSummary = null;
if ($targetUser) {
    $targetSummary = [
        'type' => $selectedType,
        'label' => $accountTypes[$selectedType]['label'],
        'id' => $targetUser->{$accountTypes[$selectedType]['id_col']},
        'email' => $targetUser->{$accountTypes[$selectedType]['email_col']},
        'name' => $targetUser->{$accountTypes[$selectedType]['name_col']} ?? '',
        'status' => $selectedType === 'admin' ? ($targetUser->status ?? 'unknown') : (($targetUser->UserStatus ?? 0) ? 'active' : 'passive'),
        'role' => $selectedType === 'admin' ? ($targetUser->role ?? '-') : ($targetUser->UserAuthority ?? '-'),
        'updated_at' => $targetUser->updated_at ?? null,
        'tenant' => $selectedTenant->company_name ?? null,
        'tenant_db' => $selectedTenant->database_name ?? null,
    ];
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
                                                $statusClass = 'badge-light-secondary';
                                                if ($status === 'matched') $statusClass = 'badge-light-success';
                                                if ($status === 'pending' || $status === 'in_review') $statusClass = 'badge-light-warning';
                                                if ($status === 'completed') $statusClass = 'badge-light-primary';
                                                if ($status === 'email_not_found' || $status === 'kurum_not_found' || $status === 'rejected') $statusClass = 'badge-light-danger';
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
                                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><span class="badge badge-light-info"><?= htmlspecialchars((string)($req['mail_queue_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                                <td><?= htmlspecialchars((string)($req['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-end">
                                                    <form method="POST" class="d-inline-flex gap-2 align-items-center">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="request_action" value="1">
                                                        <input type="hidden" name="request_id" value="<?= (int)($req['request_id'] ?? 0) ?>">
                                                        <select name="new_status" class="form-select form-select-sm form-select-solid" style="min-width: 145px;">
                                                            <?php
                                                                $statusOptions = ['pending', 'in_review', 'matched', 'completed', 'rejected', 'email_not_found', 'kurum_not_found'];
                                                                foreach ($statusOptions as $opt):
                                                            ?>
                                                                <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= strtoupper($opt) ?></option>
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
                                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="En az 8 karakter">
                                                <button class="btn btn-light" type="button" id="generate_password">Üret</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark">Yeni Şifre Tekrar</label>
                                            <input type="password" name="new_password_confirm" id="new_password_confirm" class="form-control form-control-solid" placeholder="Şifreyi tekrar girin">
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

    function buildPassword(length = 12) {
        const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*';
        let password = '';
        for (let index = 0; index < length; index++) {
            const randomIndex = Math.floor(Math.random() * characters.length);
            password += characters.charAt(randomIndex);
        }
        return password;
    }

    if (generateButton) {
        generateButton.addEventListener('click', function () {
            const password = buildPassword();
            passwordInput.value = password;
            passwordConfirmInput.value = password;
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