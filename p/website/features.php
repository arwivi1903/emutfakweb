<?php
/**
 * Prolyn Admin Panel - Ana Sayfa Özellikleri Yönetimi
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_feature') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);
        $order_no = (int)$_POST['order_no'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        $db->Insert("INSERT INTO website_features (title, description, icon, order_no, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())", 
            [$title, $description, $icon, $order_no, $status]);
            
        echo "<script>window.location.href='features.php?success=1';</script>";
        exit;
    }
    elseif ($_POST['action'] === 'edit_feature') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);
        $order_no = (int)$_POST['order_no'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        $db->Update("UPDATE website_features SET title = ?, description = ?, icon = ?, order_no = ?, status = ? WHERE id = ?", 
            [$title, $description, $icon, $order_no, $status, $id]);
            
        echo "<script>window.location.href='features.php?success=2';</script>";
        exit;
    }
    elseif ($_POST['action'] === 'delete_feature') {
        $id = (int)$_POST['id'];
        $db->Delete("DELETE FROM website_features WHERE id = ?", [$id]);
        
        echo "<script>window.location.href='features.php?success=3';</script>";
        exit;
    }
}

// Kayıtları Çek
$features = $db->getRowsAssoc("SELECT * FROM website_features ORDER BY order_no ASC, id DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Ana Sayfa Özellikleri</h1>
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
                            <span class="card-label fw-bold fs-3 mb-1">Özellik Listesi</span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add">
                                <i class="ki-duotone ki-plus fs-2"></i> Yeni Özellik Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">Sıra</th>
                                        <th class="min-w-50px">İkon</th>
                                        <th class="min-w-200px">Başlık / Açıklama</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php if ($features): ?>
                                        <?php foreach ($features as $f): ?>
                                        <tr>
                                            <td><?= $f['order_no'] ?></td>
                                            <td><i class="fs-2 <?= htmlspecialchars($f['icon']) ?> text-primary"></i></td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6"><?= htmlspecialchars($f['title']) ?></span>
                                                <span class="text-muted fw-semibold d-block fs-7"><?= htmlspecialchars($f['description']) ?></span>
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
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="edit_feature">
                                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                        
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Özellik Düzenle</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10 px-lg-17">
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                                                <input type="text" class="form-control form-control-solid" name="title" value="<?= htmlspecialchars($f['title']) ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Açıklama</label>
                                                                <textarea class="form-control form-control-solid" name="description" rows="3" required><?= htmlspecialchars($f['description']) ?></textarea>
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">İkon Sınıfı (Örn: bi bi-box)</label>
                                                                <input type="text" class="form-control form-control-solid" name="icon" value="<?= htmlspecialchars($f['icon']) ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Sıra No</label>
                                                                <input type="number" class="form-control form-control-solid" name="order_no" value="<?= $f['order_no'] ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="form-check form-switch form-check-custom form-check-solid">
                                                                    <input class="form-check-input" type="checkbox" name="status" <?= $f['status'] ? 'checked' : '' ?> />
                                                                    <span class="form-check-label fw-semibold text-muted">Aktif (Sitede Görünsün)</span>
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
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="delete_feature">
                                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Özellik Sil</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10">
                                                            <p><strong><?= htmlspecialchars($f['title']) ?></strong> başlıklı özelliği silmek istediğinize emin misiniz?</p>
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
                                <?= csrfTokenField() ?>
                                <input type="hidden" name="action" value="add_feature">
                                <div class="modal-header">
                                    <h2 class="fw-bold">Yeni Özellik Ekle</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-lg-17">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                        <input type="text" class="form-control form-control-solid" name="title" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Açıklama</label>
                                        <textarea class="form-control form-control-solid" name="description" rows="3" required></textarea>
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">İkon Sınıfı (Örn: bi bi-box)</label>
                                        <input type="text" class="form-control form-control-solid" name="icon" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Sıra No</label>
                                        <input type="number" class="form-control form-control-solid" name="order_no" value="0" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="status" checked />
                                            <span class="form-check-label fw-semibold text-muted">Aktif (Sitede Görünsün)</span>
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
