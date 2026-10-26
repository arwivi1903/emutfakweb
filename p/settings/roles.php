<?php
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_roles');
require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();

// POST İşlemleri (Ekleme/Düzenleme/Silme)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $logAdminId = $_SESSION['admin_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($action == 'save_role') {
        $role_id = intval($_POST['role_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (!empty($name)) {
            try {
                $db->beginTransaction();
                
                if ($role_id > 0) {
                    $oldData = $db->getRow("SELECT * FROM roller WHERE id=?", [$role_id]);
                    $oldPerms = $db->getRows("SELECT p.name FROM rol_izinleri r JOIN izinler p ON r.permission_id = p.id WHERE r.role_id=?", [$role_id]);
                    $oldData->permissions = array_map(function($p) { return $p->name; }, $oldPerms);

                    $sql = "UPDATE roller SET name=?, description=? WHERE id=?";
                    $db->Update($sql, [$name, $description, $role_id]);
                    
                    // İzinleri Kaydet
                    $db->Delete("DELETE FROM rol_izinleri WHERE role_id=?", [$role_id]);
                    if (!empty($permissions)) {
                        foreach ($permissions as $perm_id) {
                            $db->Insert("INSERT INTO rol_izinleri (role_id, permission_id) VALUES (?, ?)", [$role_id, $perm_id]);
                        }
                    }

                    $newData = [
                        'name' => $name,
                        'description' => $description
                    ];
                    if (!empty($permissions)) {
                        $newPerms = $db->getRows("SELECT name FROM izinler WHERE id IN (" . implode(',', array_map('intval', $permissions)) . ")");
                        $newData['permissions'] = array_map(function($p) { return $p->name; }, $newPerms);
                    } else {
                        $newData['permissions'] = [];
                    }

                    xlog('UPDATE', 'Rol güncellendi', $role_id, $logAdminId, $db, $oldData, $newData, 'roller');
                    $success_msg = "Rol ve yetkileri başarıyla güncellendi.";
                } else {
                    $sql = "INSERT INTO roller (name, description, created_at) VALUES (?, ?, NOW())";
                    $role_id = $db->Insert($sql, [$name, $description]);
                    
                    // İzinleri Kaydet
                    $db->Delete("DELETE FROM rol_izinleri WHERE role_id=?", [$role_id]);
                    if (!empty($permissions)) {
                        foreach ($permissions as $perm_id) {
                            $db->Insert("INSERT INTO rol_izinleri (role_id, permission_id) VALUES (?, ?)", [$role_id, $perm_id]);
                        }
                    }

                    $newData = [
                        'name' => $name,
                        'description' => $description
                    ];
                    if (!empty($permissions)) {
                        $newPerms = $db->getRows("SELECT name FROM izinler WHERE id IN (" . implode(',', array_map('intval', $permissions)) . ")");
                        $newData['permissions'] = array_map(function($p) { return $p->name; }, $newPerms);
                    } else {
                        $newData['permissions'] = [];
                    }

                    xlog('INSERT', 'Yeni rol eklendi', $role_id, $logAdminId, $db, null, $newData, 'roller');
                    $success_msg = "Rol ve yetkileri başarıyla eklendi.";
                }
                
                $db->commit();
            } catch (Exception $e) {
                $db->rollback();
                $error_msg = "İşlem sırasında hata: " . $e->getMessage();
            }
        } else {
            $error_msg = "Rol Adı alanı zorunludur.";
        }
    }
}

// Tüm Rolleri Çek
$roles = $db->getRows("SELECT * FROM roller ORDER BY name ASC");

// İzinleri Çek
$izinler = $db->getRows("SELECT * FROM izinler ORDER BY id ASC");

// Rollere sahip admin sayılarını çek
$role_counts_data = $db->getRows("SELECT role_id, COUNT(admin_id) as c FROM yonetici_rolleri GROUP BY role_id");
$role_counts = [];
foreach($role_counts_data as $rc) {
    $role_counts[$rc->role_id] = $rc->c;
}

// Rollere sahip adminleri detaylı çek
$role_admins_data = $db->getRows("
    SELECT yr.role_id, a.full_name, a.username 
    FROM yonetici_rolleri yr
    JOIN yoneticiler a ON yr.admin_id = a.admin_id
");
$role_admins = [];
foreach($role_admins_data as $ra) {
    if(!isset($role_admins[$ra->role_id])) $role_admins[$ra->role_id] = [];
    $role_admins[$ra->role_id][] = $ra;
}

// Rol izinlerini çek
$rol_izinleri_data = $db->getRows("SELECT role_id, permission_id FROM rol_izinleri");
$rol_izinleri = [];
foreach($rol_izinleri_data as $ri) {
    $rol_izinleri[$ri->role_id][] = $ri->permission_id;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Roller ve Yetkiler</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Roller</li>
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
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">RBAC Ek Görevler / Roller</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Sisteme tanımlı yetki grupları</span>
                            </h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_role" onclick="clearRoleForm()">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Rol Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-200px">Rol Adı</th>
                                        <th class="min-w-250px">Açıklama</th>
                                        <th class="min-w-100px text-center">Atanan Yönetici</th>
                                        <th class="min-w-100px text-center">Yetkiler</th>
                                        <th class="min-w-150px">Oluşturulma</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <td>
                                            <span class="text-gray-800 fw-bold"><?= htmlspecialchars($role->name) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($role->description) ?></td>
                                        <td class="text-center">
                                            <a href="javascript:;" class="badge badge-light-primary fw-bold text-hover-white cursor-pointer btn-show-admins" 
                                               data-role-name="<?= htmlspecialchars($role->name, ENT_QUOTES) ?>" 
                                               data-admins="<?= htmlspecialchars(json_encode($role_admins[$role->id] ?? []), ENT_QUOTES) ?>">
                                                <?= count($role_admins[$role->id] ?? []) ?> Kişi
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <div class="badge badge-light-success fw-bold"><?= count($rol_izinleri[$role->id] ?? []) ?> Yetki</div>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($role->created_at)) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" 
                                                onclick="editRole(<?= (int) $role->id ?>, <?= htmlspecialchars(json_encode((string) $role->name), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode((string) $role->description), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode(json_encode($rol_izinleri[$role->id] ?? [])), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($roles)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-10">Henüz bir rol tanımlanmamış.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Yeni / Düzenle Rol -->
<div class="modal fade" id="kt_modal_add_role" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form class="form" action="settings/roles.php" method="post" id="kt_modal_add_role_form">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_role">
                <input type="hidden" name="role_id" id="role_id" value="0">
                <div class="modal-header" id="kt_modal_add_role_header">
                    <h2 class="fw-bold" id="modal_role_title">Yeni Rol Ekle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Rol Adı</label>
                        <input type="text" class="form-control form-control-solid" placeholder="Rol adını girin" name="name" id="name" required />
                    </div>
                    
                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Açıklama</label>
                        <textarea class="form-control form-control-solid" name="description" id="description" rows="3" placeholder="Rolün genel yetkilerini özetleyin"></textarea>
                    </div>
                    
                    <div class="fv-row mb-0">
                        <label class="fs-6 fw-semibold mb-4">Rol İzinleri</label>
                        <div class="d-flex flex-column" style="max-height: 250px; overflow-y: auto; padding: 10px; border: 1px solid var(--bs-gray-300); border-radius: 6px;">
                            <?php foreach($izinler as $izin): ?>
                            <label class="form-check form-check-custom form-check-solid mb-3">
                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="<?= $izin->id ?>" id="perm_<?= $izin->id ?>" />
                                <span class="form-check-label text-gray-700 fw-semibold">
                                    <?= htmlspecialchars($izin->name) ?> <span class="text-muted ms-2 fs-7 fw-normal">- <?= htmlspecialchars($izin->description) ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_add_role_cancel" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" id="kt_modal_add_role_submit" class="btn btn-primary">
                        <span class="indicator-label">Kaydet</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Roldeki Yöneticileri Göster -->
<div class="modal fade" id="kt_modal_show_admins" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_show_admins_title">Yöneticiler</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-5" id="modal_show_admins_body">
                <!-- Listeleme buraya gelecek -->
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../config/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-show-admins').forEach(btn => {
        btn.addEventListener('click', function() {
            let roleName = this.getAttribute('data-role-name');
            let adminsJson = this.getAttribute('data-admins');
            
            let admins = [];
            try {
                admins = JSON.parse(adminsJson);
            } catch(e) {}
            
            let listHtml = '';
            
            if(admins.length === 0) {
                listHtml = '<div class="text-muted text-center py-5">Bu role atanmış kimse yok.</div>';
            } else {
                listHtml = '<ul class="list-group list-group-flush">';
                admins.forEach(a => {
                    listHtml += '<li class="list-group-item d-flex align-items-center py-3"><i class="ki-duotone ki-user fs-2x me-4 text-primary"><span class="path1"></span><span class="path2"></span></i> <div class="d-flex flex-column"><span class="fw-bold text-gray-900 fs-5">' + a.full_name + '</span><span class="fs-7 text-muted">' + a.username + '</span></div></li>';
                });
                listHtml += '</ul>';
            }
            
            document.getElementById('modal_show_admins_title').innerText = roleName + " (" + admins.length + " Kişi)";
            document.getElementById('modal_show_admins_body').innerHTML = listHtml;
            
            var modal = new bootstrap.Modal(document.getElementById('kt_modal_show_admins'));
            modal.show();
        });
    });
});

function clearRoleForm() {
    document.getElementById('role_id').value = 0;
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('modal_role_title').innerText = "Yeni Rol Ekle";
    
    // Clear checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
}

function editRole(id, name, description, permissions_json) {
    document.getElementById('role_id').value = id;
    document.getElementById('name').value = name;
    document.getElementById('description').value = description;
    document.getElementById('modal_role_title').innerText = "Rolü Düzenle";
    
    // Clear checkboxes first
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    
    // Check the ones role has
    try {
        let perms = JSON.parse(permissions_json);
        perms.forEach(perm_id => {
            let cb = document.getElementById('perm_' + perm_id);
            if(cb) cb.checked = true;
        });
    } catch(e) {
        console.error("Yetki parse hatası", e);
    }
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_add_role'));
    modal.show();
}
</script>
