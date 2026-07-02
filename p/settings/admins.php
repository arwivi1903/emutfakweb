<?php
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_admins');
require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();

// POST İşlemleri (Ekleme/Düzenleme/Silme)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Loglama fonksiyonu hazırlığı
    $logAdminId = $_SESSION['admin_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($action == 'save_admin') {
        $admin_id = intval($_POST['admin_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'admin';
        $status = $_POST['status'] ?? 'active';
        $password = trim($_POST['password'] ?? '');
        
        // Checkboxes for extra roles
        $extra_roles = $_POST['extra_roles'] ?? []; // Array of role_ids

        if ($username && $email) {
            try {
                if ($admin_id > 0) {
                    // GÜNCELLEME
                    $oldData = $db->getRow("SELECT username, email, full_name, role, status FROM yoneticiler WHERE admin_id=?", [$admin_id]);
                    $oldRoles = $db->getRows("SELECT r.name FROM yonetici_rolleri yr JOIN roller r ON yr.role_id = r.id WHERE yr.admin_id=?", [$admin_id]);
                    $oldData->extra_roles = array_map(function($r) { return $r->name; }, $oldRoles);

                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "UPDATE yoneticiler SET username=?, email=?, full_name=?, role=?, status=?, password_hash=? WHERE admin_id=?";
                        $db->Update($sql, [$username, $email, $full_name, $role, $status, $password_hash, $admin_id]);
                    } else {
                        $sql = "UPDATE yoneticiler SET username=?, email=?, full_name=?, role=?, status=? WHERE admin_id=?";
                        $db->Update($sql, [$username, $email, $full_name, $role, $status, $admin_id]);
                    }
                    
                    // Ek Görevleri (yonetici_rolleri) güncelle
                    $db->Delete("DELETE FROM yonetici_rolleri WHERE admin_id = ?", [$admin_id]);
                    foreach ($extra_roles as $r_id) {
                        $db->Insert("INSERT INTO yonetici_rolleri (admin_id, role_id) VALUES (?, ?)", [$admin_id, intval($r_id)]);
                    }

                    $newData = [
                        'username' => $username,
                        'email' => $email,
                        'full_name' => $full_name,
                        'role' => $role,
                        'status' => $status
                    ];
                    if (!empty($password)) {
                        $newData['password'] = '*** CHANGED ***';
                    }
                    if (!empty($extra_roles)) {
                        $newRoles = $db->getRows("SELECT name FROM roller WHERE id IN (" . implode(',', array_map('intval', $extra_roles)) . ")");
                        $newData['extra_roles'] = array_map(function($r) { return $r->name; }, $newRoles);
                    } else {
                        $newData['extra_roles'] = [];
                    }

                    xlog('UPDATE', 'Yönetici bilgileri güncellendi', $admin_id, $logAdminId, $db, $oldData, $newData, 'yoneticiler');

                    $success_msg = "Yönetici başarıyla güncellendi.";
                } else {
                    // YENİ EKLEME
                    $password_hash = password_hash(!empty($password) ? $password : '123456', PASSWORD_DEFAULT);
                    $sql = "INSERT INTO yoneticiler (username, email, password_hash, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $new_admin_id = $db->Insert($sql, [$username, $email, $password_hash, $full_name, $role, $status]);
                    
                    if ($new_admin_id) {
                        // Ek Görevler
                        foreach ($extra_roles as $r_id) {
                            $db->Insert("INSERT INTO yonetici_rolleri (admin_id, role_id) VALUES (?, ?)", [$new_admin_id, intval($r_id)]);
                        }
                        
                        $newData = [
                            'username' => $username,
                            'email' => $email,
                            'full_name' => $full_name,
                            'role' => $role,
                            'status' => $status
                        ];
                        if (!empty($extra_roles)) {
                            $newRoles = $db->getRows("SELECT name FROM roller WHERE id IN (" . implode(',', array_map('intval', $extra_roles)) . ")");
                            $newData['extra_roles'] = array_map(function($r) { return $r->name; }, $newRoles);
                        } else {
                            $newData['extra_roles'] = [];
                        }

                        xlog('INSERT', 'Yeni yönetici eklendi', $new_admin_id, $logAdminId, $db, null, $newData, 'yoneticiler');
                        $success_msg = "Yönetici başarıyla eklendi.";
                    }
                }
            } catch (Exception $e) {
                $error_msg = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
                xlog('ERROR', 'Yönetici işlemi hatası: ' . $e->getMessage(), $admin_id > 0 ? $admin_id : null, $logAdminId, $db, null, null, 'yoneticiler');
            }
        } else {
            $error_msg = "Kullanıcı adı ve E-posta zorunludur.";
        }
    }
}

// Tüm Yöneticileri Çek
$admins = $db->getRows("SELECT * FROM yoneticiler ORDER BY created_at DESC");

// Tüm Rolleri Çek (Ek Görevler)
$all_roles = $db->getRows("SELECT * FROM roller ORDER BY name ASC");

// Yöneticilerin Ek Görevlerini (Role_ids) gruplayarak çekelim
$admin_roles_data = $db->getRows("
    SELECT yr.admin_id, r.name, r.id 
    FROM yonetici_rolleri yr
    JOIN roller r ON yr.role_id = r.id
");
$admin_extra_roles = [];
foreach ($admin_roles_data as $row) {
    if(!isset($admin_extra_roles[$row->admin_id])) $admin_extra_roles[$row->admin_id] = [];
    $admin_extra_roles[$row->admin_id][] = $row;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Yöneticiler</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Yöneticiler</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <?php if(isset($success_msg)): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span><?= $success_msg ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Hata</h4>
                        <span><?= $error_msg ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-admin-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Yönetici Ara..." />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_admin" onclick="clearAdminForm()">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Yönetici
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_admins_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-125px">Ad Soyad</th>
                                        <th class="min-w-125px">Ana Rol</th>
                                        <th class="min-w-150px">Ek Görevler</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="min-w-125px">Son Giriş</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                        <?= mb_substr($admin->full_name, 0, 1) ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold mb-1"><?= htmlspecialchars($admin->full_name) ?></span>
                                                    <span class="text-muted fs-7"><?= htmlspecialchars($admin->email) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge badge-light-primary fw-bold text-uppercase"><?= htmlspecialchars($admin->role) ?></div>
                                        </td>
                                        <td>
                                            <?php 
                                            if(isset($admin_extra_roles[$admin->admin_id])): 
                                                foreach($admin_extra_roles[$admin->admin_id] as $er):
                                            ?>
                                                <span class="badge badge-light-info me-1 mb-1"><?= htmlspecialchars($er->name) ?></span>
                                            <?php 
                                                endforeach;
                                            else: ?>
                                                <span class="text-muted fs-8">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($admin->status == 'active'): ?>
                                                <div class="badge badge-light-success">Aktif</div>
                                            <?php else: ?>
                                                <div class="badge badge-light-danger">Pasif</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $admin->last_login ? date('d.m.Y H:i', strtotime($admin->last_login)) : '<span class="text-muted">Giriş Yapılmadı</span>' ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" 
                                                onclick="editAdmin(
                                                    <?= $admin->admin_id ?>, 
                                                    '<?= htmlspecialchars($admin->username, ENT_QUOTES) ?>', 
                                                    '<?= htmlspecialchars($admin->email, ENT_QUOTES) ?>', 
                                                    '<?= htmlspecialchars($admin->full_name, ENT_QUOTES) ?>', 
                                                    '<?= $admin->role ?>', 
                                                    '<?= $admin->status ?>',
                                                    [<?= isset($admin_extra_roles[$admin->admin_id]) ? implode(',', array_column($admin_extra_roles[$admin->admin_id], 'id')) : '' ?>]
                                                )">
                                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Yeni / Düzenle -->
<div class="modal fade" id="kt_modal_add_admin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form class="form" action="settings/admins.php" method="post" id="kt_modal_add_admin_form">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_admin">
                <input type="hidden" name="admin_id" id="admin_id" value="0">
                <div class="modal-header" id="kt_modal_add_admin_header">
                    <h2 class="fw-bold" id="modal_title">Yönetici Ekle/Düzenle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7" id="kt_modal_add_admin_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_admin_header" data-kt-scroll-wrappers="#kt_modal_add_admin_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Ad Soyad</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Tam adını girin" name="full_name" id="full_name" required />
                        </div>
                        
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Kullanıcı Adı</label>
                                <input type="text" class="form-control form-control-solid" placeholder="Kullanıcı adı" name="username" id="username" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">E-posta Adresi</label>
                                <input type="email" class="form-control form-control-solid" placeholder="ornek@domain.com" name="email" id="email" required />
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Şifre</label>
                            <input type="password" class="form-control form-control-solid" placeholder="Şifre" name="password" id="password" />
                            <div class="text-muted fs-7 mt-2">Düzenleme yaparken şifreyi değiştirmek istemiyorsanız boş bırakın. Yeni eklemede boş bırakılırsa '123456' atanır.</div>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Ana Rol</label>
                                <select name="role" id="role" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                    <option value="admin">Yönetici (Admin)</option>
                                    <option value="superadmin">Süper Admin</option>
                                    <option value="support">Destek</option>
                                    <option value="financial">Finans/Muhasebe</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Durum</label>
                                <select name="status" id="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                    <option value="active">Aktif</option>
                                    <option value="passive">Pasif</option>
                                </select>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Ek Görevler (Çoklu Seçim)</label>
                            <select name="extra_roles[]" id="extra_roles" class="form-select form-select-solid" data-control="select2" data-placeholder="Ek görevleri seçin" multiple="multiple">
                                <?php foreach($all_roles as $r): ?>
                                    <option value="<?= $r->id ?>"><?= htmlspecialchars($r->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="text-muted fs-7 mt-2">Bu yöneticiye atanacak ekstra RBAC yetki grupları.</div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_add_admin_cancel" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" id="kt_modal_add_admin_submit" class="btn btn-primary">
                        <span class="indicator-label">Kaydet</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../config/footer.php'; ?>

<script>
function clearAdminForm() {
    document.getElementById('admin_id').value = 0;
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('password').value = '';
    
    $('#role').val('admin').trigger('change');
    $('#status').val('active').trigger('change');
    $('#extra_roles').val(null).trigger('change');
    
    document.getElementById('modal_title').innerText = "Yeni Yönetici Ekle";
}

function editAdmin(id, username, email, full_name, role, status, extraRoles) {
    document.getElementById('admin_id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('email').value = email;
    document.getElementById('full_name').value = full_name;
    document.getElementById('password').value = '';
    
    $('#role').val(role).trigger('change');
    $('#status').val(status).trigger('change');
    
    if(extraRoles) {
        $('#extra_roles').val(extraRoles).trigger('change');
    } else {
        $('#extra_roles').val(null).trigger('change');
    }

    document.getElementById('modal_title').innerText = "Yönetici Düzenle";
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_add_admin'));
    modal.show();
}

// Simple search for table
document.querySelector('[data-kt-admin-table-filter="search"]').addEventListener('keyup', function (e) {
    var searchString = e.target.value.toLowerCase();
    var tableRows = document.querySelectorAll('#kt_admins_table tbody tr');
    tableRows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (text.includes(searchString)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
