<?php
/**
 * Prolyn Admin Panel - Tenant User Edit
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once '../config/sidebar.php';

$tenant_id = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($tenant_id === 0) {
    echo "<script>window.location.href='crm/tenants.php';</script>";
    exit;
}

$tenant_info = $db->getRowAssoc("SELECT * FROM kurumlar WHERE customer_id = ?", [$tenant_id]);
if (!$tenant_info || empty($tenant_info['database_name'])) {
    echo "<script>window.location.href='crm/tenants.php';</script>";
    exit;
}

$tenant_db = null;
try {
    $tenant_db = new Database($tenant_info['database_name']);
} catch (Exception $e) {
    echo "<script>alert('Müşteri veritabanına bağlanılamadı.'); window.location.href='crm/tenant_detail.php?id={$tenant_id}';</script>";
    exit;
}

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_user') {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $role       = isset($_POST['role']) ? (int)$_POST['role'] : 3;
    $status     = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    if ($user_id > 0) {
        if (!empty($_POST['password'])) {
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $tenant_db->Update("UPDATE users SET UserName=?, UserMail=?, UserAuthority=?, UserStatus=?, UserPass=? WHERE UserID=?", 
                [$full_name, $email, $role, $status, $password_hash, $user_id]);
        } else {
            $tenant_db->Update("UPDATE users SET UserName=?, UserMail=?, UserAuthority=?, UserStatus=? WHERE UserID=?", 
                [$full_name, $email, $role, $status, $user_id]);
        }
    } else {
        $password = !empty($_POST['password']) ? $_POST['password'] : '123456';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $tenant_db->Insert("INSERT INTO users (FirmaID, UserName, UserMail, UserPass, UserAuthority, UserStatus, BranchID) VALUES (?, ?, ?, ?, ?, ?, ?)", 
            [1, $full_name, $email, $password_hash, $role, $status, 1]);
    }
    
    echo "<script>window.location.href='crm/tenant_detail.php?id={$tenant_id}&tab=users&success=user_saved';</script>";
    exit;
}

// Fetch user data if edit
$user = null;
if ($user_id > 0) {
    $user = $tenant_db->getRowAssoc("SELECT * FROM users WHERE UserID = ?", [$user_id]);
    if (!$user) {
        echo "<script>window.location.href='crm/tenant_detail.php?id={$tenant_id}&tab=users';</script>";
        exit;
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        <?php echo $user_id > 0 ? 'Kullanıcı Düzenle: ' . htmlspecialchars($user['UserName']) : 'Yeni Kullanıcı Ekle'; ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="crm/tenants.php" class="text-muted text-hover-primary">Müşteriler</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="crm/tenant_detail.php?id=<?php echo $tenant_id; ?>" class="text-muted text-hover-primary"><?php echo htmlspecialchars($tenant_info['company_name']); ?></a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Kullanıcı İşlemi</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="crm/tenant_detail.php?id=<?php echo $tenant_id; ?>&tab=users" class="btn btn-sm btn-light">Geri Dön</a>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Kullanıcı Bilgileri</h3>
                        </div>
                    </div>
                    
                    <div class="card-body border-top p-9">
                        <form action="crm/tenant_user_edit.php?tenant_id=<?php echo $tenant_id; ?>&user_id=<?php echo $user_id; ?>" method="POST" id="form_user">
                            <?= csrfTokenField() ?>
                            <input type="hidden" name="action" value="save_user">
                            
                            <div class="row g-9 mb-7">
                                <div class="col-md-6 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Ad Soyad</label>
                                    <input type="text" class="form-control form-control-solid" name="full_name" value="<?php echo htmlspecialchars($user['UserName'] ?? ''); ?>" required />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">E-posta</label>
                                    <input type="email" class="form-control form-control-solid" name="email" value="<?php echo htmlspecialchars($user['UserMail'] ?? ''); ?>" required />
                                </div>
                            </div>

                            <div class="row g-9 mb-7">
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Yetki Seviyesi</label>
                                    <select class="form-select form-select-solid" name="role">
                                        <option value="1" <?php echo (($user['UserAuthority'] ?? 3) == 1) ? 'selected' : ''; ?>>Sistem Yöneticisi (1)</option>
                                        <option value="2" <?php echo (($user['UserAuthority'] ?? 3) == 2) ? 'selected' : ''; ?>>Yönetici (2)</option>
                                        <option value="3" <?php echo (($user['UserAuthority'] ?? 3) == 3) ? 'selected' : ''; ?>>Standart Kullanıcı (3)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Durum</label>
                                    <select class="form-select form-select-solid" name="status">
                                        <option value="1" <?php echo (($user['UserStatus'] ?? 1) == 1) ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="0" <?php echo (($user['UserStatus'] ?? 1) == 0) ? 'selected' : ''; ?>>Pasif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="fv-row mb-7">
                                <label class="fs-6 fw-semibold mb-2">Şifre</label>
                                <input type="password" class="form-control form-control-solid" name="password" placeholder="Yeni eklenirken boş bırakılırsa '123456' olur. Düzenlerken boş bırakırsanız şifre değişmez." />
                            </div>

                            <div class="d-flex justify-content-end pt-7">
                                <a href="crm/tenant_detail.php?id=<?php echo $tenant_id; ?>&tab=users" class="btn btn-light me-3">İptal</a>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>
