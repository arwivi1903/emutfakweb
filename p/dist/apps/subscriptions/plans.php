<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update' && isset($_POST['feature_id'])) {
        $id = $_POST['feature_id'];
        
        
        // Veritabanı güncelleme
        $db->Update("UPDATE package_features SET max_total_users = ?, max_storage_gb = ? WHERE feature_id = ?", 
            [$_POST['max_total_users'], $_POST['max_storage_gb'], $id]);
    }
}

$plans = $db->getRows("SELECT * FROM package_features ORDER BY feature_id ASC");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="row g-5 g-xl-10">
                    <?php foreach ($plans as $plan): ?>
                    <div class="col-xl-4">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-7">
                                <div class="card-title">
                                    <h2 class="text-uppercase"><?= htmlspecialchars($plan->plan_name) ?></h2>
                                </div>
                                <div class="card-toolbar">
                                    <span class="badge badge-light-primary fs-7 fw-bold"><?= $plan->plan_display_name ?? 'Standart' ?></span>
                                </div>
                            </div>
                            <div class="card-body pt-1">
                                <div class="fw-bold text-gray-600 mb-5">Plan Özellikleri ve Limitleri</div>
                                
                                <div class="d-flex flex-column text-gray-600 gap-2">
                                    <div class="d-flex align-items-center py-2">
                                        <i class="ki-duotone ki-check-circle fs-2 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-800 fw-bold me-2">Admin Kullanıcı:</span>
                                        <span><?= $plan->max_admin_users ?></span>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <i class="ki-duotone ki-check-circle fs-2 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-800 fw-bold me-2">Toplam Kullanıcı:</span>
                                        <span><?= $plan->max_total_users ?></span>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <i class="ki-duotone ki-check-circle fs-2 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-800 fw-bold me-2">Depolama:</span>
                                        <span><?= $plan->max_storage_gb ?> GB</span>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <i class="ki-duotone ki-check-circle fs-2 <?= $plan->api_access ? 'text-success' : 'text-danger' ?> me-2"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-800 fw-bold me-2">API Erişimi:</span>
                                        <span><?= $plan->api_access ? 'Var' : 'Yok' ?></span>
                                    </div>
                                    <div class="d-flex align-items-center py-2">
                                        <i class="ki-duotone ki-check-circle fs-2 <?= $plan->white_label ? 'text-success' : 'text-danger' ?> me-2"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-800 fw-bold me-2">White Label:</span>
                                        <span><?= $plan->white_label ? 'Var' : 'Yok' ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer pt-0">
                                <button type="button" class="btn btn-light-primary w-100" data-bs-toggle="modal" data-bs-target="#kt_modal_update_plan_<?= $plan->feature_id ?>">
                                    Düzenle
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="kt_modal_update_plan_<?= $plan->feature_id ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered mw-650px">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="fw-bold">Plan Düzenle: <?= htmlspecialchars($plan->plan_name) ?></h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                </div>
                                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                                    <form id="kt_modal_update_plan_form_<?= $plan->feature_id ?>" class="form" action="#" method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="feature_id" value="<?= $plan->feature_id ?>">
                                        
                                        <div class="fv-row mb-7">
                                            <label class="required fs-6 fw-semibold mb-2">Maksimum Admin</label>
                                            <input type="number" class="form-control form-control-solid" name="max_admin_users" value="<?= $plan->max_admin_users ?>" />
                                        </div>
                                        <div class="fv-row mb-7">
                                            <label class="required fs-6 fw-semibold mb-2">Maksimum Kullanıcı</label>
                                            <input type="number" class="form-control form-control-solid" name="max_total_users" value="<?= $plan->max_total_users ?>" />
                                        </div>
                                        <div class="fv-row mb-7">
                                            <label class="required fs-6 fw-semibold mb-2">Depolama (GB)</label>
                                            <input type="number" class="form-control form-control-solid" name="max_storage_gb" value="<?= $plan->max_storage_gb ?>" />
                                        </div>
                                        
                                        <div class="text-center pt-15">
                                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                            <button type="submit" class="btn btn-primary">
                                                <span class="indicator-label">Kaydet</span>
                                                <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
