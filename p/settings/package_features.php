<?php
/**
 * Prolyn Admin Panel - Package Features Management
 * prolynweb/p/settings/package_features.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_plan') {
    $feature_id = (int)$_POST['feature_id'];
    
    $params = [
        $_POST['plan_display_name'],
        (int)$_POST['max_admin_users'],
        (int)$_POST['max_total_users'],
        (int)$_POST['max_storage_gb'],
        (int)$_POST['max_file_size_mb'],
        (int)$_POST['max_files_per_upload'],
        isset($_POST['api_access']) ? 1 : 0,
        (int)$_POST['api_requests_per_month'],
        isset($_POST['custom_reports']) ? 1 : 0,
        isset($_POST['advanced_analytics']) ? 1 : 0,
        isset($_POST['white_label']) ? 1 : 0,
        isset($_POST['email_campaigns']) ? 1 : 0,
        isset($_POST['bulk_operations']) ? 1 : 0,
        $_POST['support_level'],
        (int)$_POST['support_response_hours'],
        $feature_id
    ];
    
    $sql = "UPDATE paket_ozellikleri SET 
            plan_display_name = ?, max_admin_users = ?, max_total_users = ?, max_storage_gb = ?, max_file_size_mb = ?, 
            max_files_per_upload = ?, api_access = ?, api_requests_per_month = ?, custom_reports = ?, advanced_analytics = ?, 
            white_label = ?, email_campaigns = ?, bulk_operations = ?, support_level = ?, support_response_hours = ?
            WHERE feature_id = ?";
            
    $db->Update($sql, $params);
    echo "<script>window.location.href='settings/package_features.php?success=1';</script>";
    exit;
}

// Planları Çek
$plans = $db->getRowsAssoc("SELECT * FROM paket_ozellikleri ORDER BY feature_id ASC");

?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Paket Özellikleri ve Sınırları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Yönetim Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Paket Özellikleri</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>Paket limitleri başarıyla güncellendi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row g-5 g-xl-8">
                    <?php foreach($plans as $plan): ?>
                    <div class="col-xl-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1 text-uppercase text-primary"><?php echo htmlspecialchars($plan['plan_name']); ?></span>
                                    <span class="text-muted fw-semibold fs-7"><?php echo htmlspecialchars($plan['plan_display_name']); ?></span>
                                </h3>
                                <div class="card-toolbar">
                                    <button class="btn btn-sm btn-light-primary" onclick='editPlan(<?php echo json_encode($plan, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        Düzenle
                                    </button>
                                </div>
                            </div>
                            <div class="card-body pt-5">
                                <div class="d-flex flex-stack fs-6 fw-semibold mb-3">
                                    <span class="text-gray-400">Max Admin:</span>
                                    <span class="text-gray-800"><?php echo $plan['max_admin_users']; ?></span>
                                </div>
                                <div class="d-flex flex-stack fs-6 fw-semibold mb-3">
                                    <span class="text-gray-400">Max Toplam Kullanıcı:</span>
                                    <span class="text-gray-800"><?php echo $plan['max_total_users']; ?></span>
                                </div>
                                <div class="d-flex flex-stack fs-6 fw-semibold mb-3">
                                    <span class="text-gray-400">Depolama (GB):</span>
                                    <span class="text-gray-800"><?php echo $plan['max_storage_gb']; ?></span>
                                </div>
                                <div class="d-flex flex-stack fs-6 fw-semibold mb-3">
                                    <span class="text-gray-400">API İstek Limit (Aylık):</span>
                                    <span class="text-gray-800"><?php echo $plan['api_requests_per_month']; ?></span>
                                </div>
                                <div class="d-flex flex-stack fs-6 fw-semibold mb-3">
                                    <span class="text-gray-400">Destek Seviyesi:</span>
                                    <span class="text-gray-800 text-uppercase"><?php echo $plan['support_level']; ?> (<?php echo $plan['support_response_hours']; ?> Saat)</span>
                                </div>
                                <div class="separator my-5"></div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-duotone <?php echo $plan['api_access'] ? 'ki-check text-success' : 'ki-cross text-danger'; ?> fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <span class="text-gray-800 fw-semibold">API Erişimi</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-duotone <?php echo $plan['custom_reports'] ? 'ki-check text-success' : 'ki-cross text-danger'; ?> fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <span class="text-gray-800 fw-semibold">Özel Raporlar</span>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <i class="ki-duotone <?php echo $plan['white_label'] ? 'ki-check text-success' : 'ki-cross text-danger'; ?> fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <span class="text-gray-800 fw-semibold">White Label (Markasız)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Plan -->
<div class="modal fade" id="kt_modal_edit_plan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="edit_modal_title">Plan Düzenle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="settings/package_features.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="update_plan">
                <input type="hidden" name="feature_id" id="inp_feature_id" value="">
                
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="row mb-5">
                        <div class="col-md-6 mb-5">
                            <label class="required fs-6 fw-semibold mb-2">Görünür İsim (Display Name)</label>
                            <input type="text" class="form-control form-control-solid" name="plan_display_name" id="inp_plan_display_name" required />
                        </div>
                        <div class="col-md-6 mb-5">
                            <label class="required fs-6 fw-semibold mb-2">Destek Seviyesi</label>
                            <select class="form-select form-select-solid" name="support_level" id="inp_support_level" required>
                                <option value="email">Email</option>
                                <option value="priority">Priority</option>
                                <option value="dedicated">Dedicated</option>
                            </select>
                        </div>
                    </div>
                    
                    <h3 class="fs-5 fw-bold mb-5 mt-5">Kota Limitleri</h3>
                    <div class="row mb-5">
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Max Admin</label>
                            <input type="number" class="form-control form-control-solid" name="max_admin_users" id="inp_max_admin_users" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Max Toplam Kullanıcı</label>
                            <input type="number" class="form-control form-control-solid" name="max_total_users" id="inp_max_total_users" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Depolama (GB)</label>
                            <input type="number" class="form-control form-control-solid" name="max_storage_gb" id="inp_max_storage_gb" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Max Dosya Boyutu (MB)</label>
                            <input type="number" class="form-control form-control-solid" name="max_file_size_mb" id="inp_max_file_size_mb" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Max Toplu Dosya (Adet)</label>
                            <input type="number" class="form-control form-control-solid" name="max_files_per_upload" id="inp_max_files_per_upload" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Aylık API İstek Limiti</label>
                            <input type="number" class="form-control form-control-solid" name="api_requests_per_month" id="inp_api_requests_per_month" required />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Destek Yanıt (Saat)</label>
                            <input type="number" class="form-control form-control-solid" name="support_response_hours" id="inp_support_response_hours" required />
                        </div>
                    </div>
                    
                    <h3 class="fs-5 fw-bold mb-5 mt-5">Özellik Yetkileri (Booleans)</h3>
                    <div class="row mb-5">
                        <div class="col-md-4 mb-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="api_access" id="inp_api_access" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">API Erişimi</span>
                            </label>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="custom_reports" id="inp_custom_reports" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">Özel Raporlar</span>
                            </label>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="advanced_analytics" id="inp_advanced_analytics" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">Gelişmiş Analitik</span>
                            </label>
                        </div>
                        <div class="col-md-4 mb-3 mt-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="white_label" id="inp_white_label" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">White Label</span>
                            </label>
                        </div>
                        <div class="col-md-4 mb-3 mt-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="email_campaigns" id="inp_email_campaigns" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">E-posta Kampanyaları</span>
                            </label>
                        </div>
                        <div class="col-md-4 mb-3 mt-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="bulk_operations" id="inp_bulk_operations" value="1" />
                                <span class="form-check-label text-gray-700 fw-semibold">Toplu İşlemler</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPlan(plan) {
    document.getElementById('edit_modal_title').innerText = plan.plan_name.toUpperCase() + ' Planını Düzenle';
    document.getElementById('inp_feature_id').value = plan.feature_id;
    document.getElementById('inp_plan_display_name').value = plan.plan_display_name;
    document.getElementById('inp_support_level').value = plan.support_level;
    document.getElementById('inp_max_admin_users').value = plan.max_admin_users;
    document.getElementById('inp_max_total_users').value = plan.max_total_users;
    document.getElementById('inp_max_storage_gb').value = plan.max_storage_gb;
    document.getElementById('inp_max_file_size_mb').value = plan.max_file_size_mb;
    document.getElementById('inp_max_files_per_upload').value = plan.max_files_per_upload;
    document.getElementById('inp_api_requests_per_month').value = plan.api_requests_per_month;
    document.getElementById('inp_support_response_hours').value = plan.support_response_hours;
    
    document.getElementById('inp_api_access').checked = plan.api_access == 1;
    document.getElementById('inp_custom_reports').checked = plan.custom_reports == 1;
    document.getElementById('inp_advanced_analytics').checked = plan.advanced_analytics == 1;
    document.getElementById('inp_white_label').checked = plan.white_label == 1;
    document.getElementById('inp_email_campaigns').checked = plan.email_campaigns == 1;
    document.getElementById('inp_bulk_operations').checked = plan.bulk_operations == 1;
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_edit_plan'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
