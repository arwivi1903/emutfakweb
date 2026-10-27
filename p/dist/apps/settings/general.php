<?php
require_once __DIR__ . '/../../../../env.php';
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();

// Tablo yeni kurulumda (üretim) yoksa oluştur; ayarlar kaydedilemiyordu.
try {
    $db->Insert("CREATE TABLE IF NOT EXISTS system_settings (
        setting_id INT(11) NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (setting_id),
        UNIQUE KEY uq_setting_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    error_log('[ayarlar] system_settings oluşturulamadı: ' . $e->getMessage());
}

// Varsayılanlar .env değerlerinden gelir (şifre HARİÇ; şifreyi panelden gir).
$envMailFrom = (string)prolynweb_env('MAIL_FROM', 'destek@prolyn.net');
$requiredMailSettings = [
    'smtp_host' => (string)prolynweb_env('MAIL_HOST', ''),
    'smtp_port' => (string)prolynweb_env('MAIL_PORT', '587'),
    'smtp_secure' => (string)prolynweb_env('MAIL_SECURE', 'tls'),
    'smtp_user' => (string)prolynweb_env('MAIL_USER', ''),
    'smtp_pass' => '',
    'from_email' => $envMailFrom,
    'from_name' => (string)prolynweb_env('MAIL_FROM_NAME', 'Prolyn'),
    'support_email' => 'destek@prolyn.net',
    'notify_email' => 'destek@prolyn.net',
    'whatsapp_enabled' => '0',
    'whatsapp_notify_number' => '',
    'whatsapp_api_url' => '',
];

foreach ($requiredMailSettings as $settingKey => $defaultValue) {
    try {
        $exists = (int)$db->getColumn("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?", [$settingKey]);
        if ($exists === 0) {
            $db->Insert("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)", [$settingKey, $defaultValue]);
        }
    } catch (Exception $e) {
        // Sessiz gec
    }
}

// Ayarları güncelle
/* ESKİ HATA: yalnızca UPDATE yapılıyordu. system_settings'e sadece mail anahtarları
   tohumlandığı için site_name / currency / trial_period_days satırları hiç yoktu:
   alan boş geliyor, UPDATE de hiçbir satıra denk gelmeyip sessizce atlanıyordu.
   Artık izinli anahtarlar UPSERT ediliyor. Ana site site adını sistem_ayarlari'ndan
   okuduğu için site_name orayla da eşitleniyor. */
$kayit_ok = false; $kayit_hata = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_settings') {
    $izinli = array_merge(array_keys($requiredMailSettings), ['site_name', 'currency', 'trial_period_days']);
    try {
        foreach ($izinli as $key) {
            if (!array_key_exists($key, $_POST)) continue;
            $value = trim((string)$_POST[$key]);
            $db->Update("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)", [$key, $value]);
        }
        if (isset($_POST['site_name']) && trim((string)$_POST['site_name']) !== '') {
            $db->Update("INSERT INTO sistem_ayarlari (setting_key, setting_value, setting_type, category, description, is_public, updated_at)
                         VALUES ('site_name', ?, 'string', 'general', 'Footer ve copyright metninde kullanılan site adı', 1, NOW())
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                [trim((string)$_POST['site_name'])]);
        }
        $kayit_ok = true;
    } catch (Throwable $e) {
        $kayit_hata = 'Ayarlar kaydedilemedi: ' . $e->getMessage();
    }
}

$settings = $db->allAssoc("SELECT * FROM system_settings");
$config = [];
foreach ($settings as $s) {
    if (isset($s['setting_key'])) {
        $config[$s['setting_key']] = $s['setting_value'];
    }
}
if (empty($config['site_name'])) {
    try {
        $config['site_name'] = (string)$db->getColumn("SELECT setting_value FROM sistem_ayarlari WHERE setting_key = 'site_name'");
    } catch (Throwable $e) { $config['site_name'] = ''; }
}
if (!isset($config['currency']) || $config['currency'] === '') $config['currency'] = 'TRY';
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
                        <?php if ($kayit_ok): ?><div class="alert alert-success m-9 mb-0">Ayarlar kaydedildi.</div><?php endif; ?>
                        <?php if ($kayit_hata !== ''): ?><div class="alert alert-danger m-9 mb-0"><?= htmlspecialchars($kayit_hata) ?></div><?php endif; ?>
                        <form id="kt_account_profile_details_form" class="form" method="POST">
                            <?= csrfTokenField() ?>
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

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">SMTP Port</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="number" name="smtp_port" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['smtp_port'] ?? '587') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">SMTP Güvenlik</label>
                                    <div class="col-lg-8 fv-row">
                                        <select name="smtp_secure" class="form-select form-select-solid form-select-lg">
                                            <option value="tls" <?= (($config['smtp_secure'] ?? 'tls') === 'tls') ? 'selected' : '' ?>>TLS</option>
                                            <option value="ssl" <?= (($config['smtp_secure'] ?? '') === 'ssl') ? 'selected' : '' ?>>SSL</option>
                                            <option value="none" <?= (($config['smtp_secure'] ?? '') === 'none') ? 'selected' : '' ?>>Yok</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">SMTP Kullanıcı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="smtp_user" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['smtp_user'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">SMTP Şifre</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="smtp_pass" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['smtp_pass'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Gönderen E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="from_email" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['from_email'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Gönderen Ad</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="from_name" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['from_name'] ?? 'Prolyn') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Destek E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="support_email" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['support_email'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Bildirim E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="notify_email" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['notify_email'] ?? '') ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">WhatsApp Bildirim Aktif</label>
                                    <div class="col-lg-8 fv-row">
                                        <select name="whatsapp_enabled" class="form-select form-select-solid form-select-lg">
                                            <option value="0" <?= (($config['whatsapp_enabled'] ?? '0') === '0') ? 'selected' : '' ?>>Pasif</option>
                                            <option value="1" <?= (($config['whatsapp_enabled'] ?? '0') === '1') ? 'selected' : '' ?>>Aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">WhatsApp Bildirim No</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="whatsapp_notify_number" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['whatsapp_notify_number'] ?? '') ?>" placeholder="905xxxxxxxxx" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">WhatsApp API URL</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="whatsapp_api_url" class="form-control form-control-lg form-control-solid" value="<?= htmlspecialchars($config['whatsapp_api_url'] ?? '') ?>" placeholder="https://api.example.com/whatsapp/send" />
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
