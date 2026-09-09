<?php
/**
 * Prolyn Admin Panel - SEO ve Temel Web Ayarları
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// Form gönderildiyse güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_seo') {
    
    $keys = [
        'seo_title', 'seo_description', 'seo_keywords', 
        'website_footer_text', 'website_hero_badge', 
        'website_hero_title', 'website_hero_subtitle'
    ];

    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            $val = trim($_POST[$k]);
            $db->Update("UPDATE sistem_ayarlari SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$val, $k]);
        }
    }
    
    echo "<script>window.location.href='seo.php?success=1';</script>";
    exit;
}

// Verileri çek
$settings = $db->getRowsAssoc("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE setting_key IN ('seo_title', 'seo_description', 'seo_keywords', 'website_footer_text', 'website_hero_badge', 'website_hero_title', 'website_hero_subtitle')");
$seo = [];
if($settings) {
    foreach($settings as $s) {
        $seo[$s['setting_key']] = $s['setting_value'];
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">SEO ve Temel Ayarlar</h1>
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
                        <span>Ayarlar başarıyla güncellendi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <form method="post" action="">
                    <?= csrfTokenField() ?>
                    <input type="hidden" name="action" value="update_seo">
                    
                    <div class="row g-5 g-xl-8">
                        <div class="col-xl-6">
                            <div class="card mb-5 mb-xl-8">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">SEO Ayarları</span>
                                    </h3>
                                </div>
                                <div class="card-body py-3">
                                    <div class="mb-10">
                                        <label class="form-label">SEO Title (Site Başlığı)</label>
                                        <input type="text" name="seo_title" class="form-control form-control-solid" value="<?= htmlspecialchars($seo['seo_title'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">SEO Description (Açıklama)</label>
                                        <textarea name="seo_description" class="form-control form-control-solid" rows="3"><?= htmlspecialchars($seo['seo_description'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">SEO Keywords (Anahtar Kelimeler)</label>
                                        <input type="text" name="seo_keywords" class="form-control form-control-solid" value="<?= htmlspecialchars($seo['seo_keywords'] ?? '') ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card mb-5 mb-xl-8">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Vitrin Ana Sayfa İçeriği</span>
                                    </h3>
                                </div>
                                <div class="card-body py-3">
                                    <div class="mb-10">
                                        <label class="form-label">Hero Badge (Üst Etiket)</label>
                                        <input type="text" name="website_hero_badge" class="form-control form-control-solid" value="<?= htmlspecialchars($seo['website_hero_badge'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">Hero Title (Ana Başlık - H1)</label>
                                        <input type="text" name="website_hero_title" class="form-control form-control-solid" value="<?= htmlspecialchars($seo['website_hero_title'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">Hero Subtitle (Alt Açıklama)</label>
                                        <textarea name="website_hero_subtitle" class="form-control form-control-solid" rows="3"><?= htmlspecialchars($seo['website_hero_subtitle'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">Footer Hakkımızda Metni</label>
                                        <textarea name="website_footer_text" class="form-control form-control-solid" rows="4"><?= htmlspecialchars($seo['website_footer_text'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Değişiklikleri Kaydet</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <?php require_once '../config/footer.php'; ?>
</div>
