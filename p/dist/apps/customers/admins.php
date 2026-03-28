<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();

// Yeni admin ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_admin') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if ($username && $email && $password) {
        // Şifre hashleme (Örnek: password_hash kullanılması önerilir, fakat mevcut sistemde MD5/SHA vb olabilir, standardı takip ediyoruz)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (username, email, password_hash, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())";
        $db->Update($sql, [$username, $email, $password_hash, $full_name, $role]);
    }
}

$admins = $db->getRows("SELECT * FROM admins ORDER BY created_at DESC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Yöneticiler</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Sistem yöneticileri listesi</span>
                            </h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_admin">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Yönetici Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Yönetici</th>
                                    <th class="min-w-125px">Email</th>
                                    <th class="min-w-125px">Rol</th>
                                    <th class="min-w-125px">Durum</th>
                                    <th class="min-w-125px">Son Giriş</th>
                                    <th class="text-end min-w-70px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-50px me-5">
                                                <?php if($admin->admin_pic): ?>
                                                    <img src="../../../<?= htmlspecialchars($admin->admin_pic) ?>" alt="" />
                                                <?php else: ?>
                                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                        <?= mb_substr($admin->full_name, 0, 1) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 text-hover-primary mb-1"><?= htmlspecialchars($admin->full_name) ?></span>
                                                <span><?= htmlspecialchars($admin->username) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><a href="#" class="text-gray-600 text-hover-primary mb-1"><?= htmlspecialchars($admin->email) ?></a></td>
                                    <td>
                                        <div class="badge badge-light fw-bold"><?= htmlspecialchars($admin->role) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($admin->status == 'active'): ?>
                                            <div class="badge badge-light-success">Aktif</div>
                                        <?php else: ?>
                                            <div class="badge badge-light-danger">Pasif</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $admin->last_login ? date('d.m.Y H:i', strtotime($admin->last_login)) : '-' ?></td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary">Düzenle</a>
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
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>

<!-- Add Admin Modal Scaffold -->
<div class="modal fade" id="kt_modal_add_admin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_user_header">
                <h2 class="fw-bold">Yönetici Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_user_form" class="form" action="#" method="POST">
                    <input type="hidden" name="action" value="add_admin">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                        <!-- Username -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Kullanıcı Adı</label>
                            <input type="text" class="form-control form-control-solid" placeholder="" name="username" />
                        </div>
                        <!-- Full Name -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Ad Soyad</label>
                            <input type="text" class="form-control form-control-solid" placeholder="" name="full_name" />
                        </div>
                        <!-- Email -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Email</label>
                            <input type="email" class="form-control form-control-solid" placeholder="" name="email" />
                        </div>
                        <!-- Password -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Şifre</label>
                            <input type="password" class="form-control form-control-solid" placeholder="" name="password" />
                        </div>
                        <!-- Role -->
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Rol</label>
                            <select class="form-select form-select-solid fw-bold" name="role">
                                <option value="admin">Admin</option>
                                <option value="superadmin">Süper Admin</option>
                                <option value="support">Destek</option>
                                <option value="financial">Finans</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                            <span class="indicator-label">Kaydet</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
