<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();

// Ayarları güncelle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_settings') {
    foreach ($_POST as $key => $value) {
        if ($key == 'action') continue;
        // System settings güncelleme
        $db->Update("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
    }
}

$settings = $db->allAssoc("SELECT * FROM system_settings");
$config = [];
foreach ($settings as $s) {
    if (isset($s['setting_key'])) {
        $config[$s['setting_key']] = $s['setting_value'];
    }
}
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Sistem Ayarları</h3>
                        </div>
                    </div>
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <form id="kt_account_profile_details_form" class="form" method="POST">
                            <input type="hidden" name="action" value="save_settings">
                            <div class="card-body border-top p-9">
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Site Adı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="site_name" class="form-control form-control-lg form-control-solid" placeholder="Site ismi" value="<?= htmlspecialchars($config['site_name'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Para Birimi</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="currency" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['currency'] ?? 'TRY') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Deneme Süresi (Gün)</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="number" name="trial_period_days" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['trial_period_days'] ?? '30') ?>" />
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">SMTP Host</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="smtp_host" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['smtp_host'] ?? '') ?>" />
                                    </div>
                                </div>
                                
                            </div>
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
