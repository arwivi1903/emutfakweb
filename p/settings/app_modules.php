<?php
/**
 * Prolyn Admin Panel - Uygulama Modülleri Yönetimi
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_module') {
        $module_key = trim($_POST['module_key']);
        $module_name = trim($_POST['module_name']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $db->Insert("INSERT INTO app_modules (module_key, module_name, status, created_at) VALUES (?, ?, ?, NOW())", 
            [$module_key, $module_name, $status]);
            
        echo "<script>window.location.href='app_modules.php?success=1';</script>";
        exit;
    }
    elseif ($_POST['action'] === 'edit_module') {
        $id = (int)$_POST['id'];
        $module_key = trim($_POST['module_key']);
        $module_name = trim($_POST['module_name']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        $db->Update("UPDATE app_modules SET module_key = ?, module_name = ?, status = ? WHERE id = ?", 
            [$module_key, $module_name, $status, $id]);
            
        echo "<script>window.location.href='app_modules.php?success=2';</script>";
        exit;
    }
    elseif ($_POST['action'] === 'delete_module') {
        $id = (int)$_POST['id'];
        $db->Delete("DELETE FROM app_modules WHERE id = ?", [$id]);
        
        echo "<script>window.location.href='app_modules.php?success=3';</script>";
        exit;
    }
}

// Kayıtları Çek
$modules = $db->getRowsAssoc("SELECT * FROM app_modules ORDER BY id ASC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Uygulama Modülleri (Sayfa Yetkileri)</h1>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İşlem başarıyla tamamlandı.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Modül / Sayfa Listesi</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add">
                                <i class="ki-duotone ki-plus fs-2"></i> Yeni Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-100px">ID</th>
                                        <th class="min-w-150px">Modül Key (Anahtar)</th>
                                        <th class="min-w-200px">Modül Adı (Açıklama)</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php if ($modules): ?>
                                        <?php foreach ($modules as $f): ?>
                                        <tr>
                                            <td><?= $f['id'] ?></td>
                                            <td>
                                                <span class="badge badge-light-primary fs-7"><?= htmlspecialchars($f['module_key']) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6"><?= htmlspecialchars($f['module_name']) ?></span>
                                            </td>
                                            <td>
                                                <?php if($f['status']): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_<?= $f['id'] ?>">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-light-danger btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_<?= $f['id'] ?>">
                                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="kt_modal_edit_<?= $f['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered mw-650px">
                                                <div class="modal-content">
                                                    <form class="form" action="" method="post">
                                                        <input type="hidden" name="action" value="edit_module">
                                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                        
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Modül Düzenle</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10 px-lg-17">
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Modül Key (Kısa İsim - İngilizce/Boşluksuz)</label>
                                                                <input type="text" class="form-control form-control-solid" name="module_key" value="<?= htmlspecialchars($f['module_key']) ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Modül Adı (Kullanıcıların Göreceği İsim)</label>
                                                                <input type="text" class="form-control form-control-solid" name="module_name" value="<?= htmlspecialchars($f['module_name']) ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="form-check form-switch form-check-custom form-check-solid">
                                                                    <input class="form-check-input" type="checkbox" name="status" <?= $f['status'] ? 'checked' : '' ?> />
                                                                    <span class="form-check-label fw-semibold text-muted">Aktif (Kullanıcılara Seçenek Olarak Sunulsun)</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer flex-center">
                                                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <span class="indicator-label">Kaydet</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="kt_modal_delete_<?= $f['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered mw-400px">
                                                <div class="modal-content">
                                                    <form class="form" action="" method="post">
                                                        <input type="hidden" name="action" value="delete_module">
                                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Modül Sil</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10">
                                                            <p><strong><?= htmlspecialchars($f['module_name']) ?></strong> adlı modülü silmek istediğinize emin misiniz? (Bu işlem tenantlardaki eski yetkileri bozabilir!)</p>
                                                        </div>
                                                        <div class="modal-footer flex-center">
                                                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-danger">Evet, Sil</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Henüz kayıt eklenmemiş.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Modal -->
                <div class="modal fade" id="kt_modal_add" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered mw-650px">
                        <div class="modal-content">
                            <form class="form" action="" method="post">
                                <input type="hidden" name="action" value="add_module">
                                <div class="modal-header">
                                    <h2 class="fw-bold">Yeni Modül/Sayfa Ekle</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-lg-17">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Modül Key (Kısa İsim - İngilizce/Boşluksuz)</label>
                                        <input type="text" class="form-control form-control-solid" name="module_key" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Modül Adı (Kullanıcıların Göreceği İsim)</label>
                                        <input type="text" class="form-control form-control-solid" name="module_name" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="status" checked />
                                            <span class="form-check-label fw-semibold text-muted">Aktif (Kullanıcılara Seçenek Olarak Sunulsun)</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer flex-center">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="indicator-label">Kaydet</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once '../config/footer.php'; ?>
</div>
