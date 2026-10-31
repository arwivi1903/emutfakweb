<?php
/**
 * Prolyn Admin Panel - SEO ve Temel Web Ayarları
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Form gönderildiyse güncelle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_seo') {
    
    $keys = [
        'seo_title', 'seo_description', 'seo_keywords', 
        'website_footer_text', 'website_hero_badge', 
        'website_hero_title', 'website_hero_subtitle',
        'contact_email', 'contact_phone', 'contact_whatsapp', 'contact_address',
        'social_instagram', 'social_youtube', 'social_twitter', 'social_linkedin'
    ];
    // --- Google ve diğer araç ayarları: biçim doğrulaması (Sıfır Güven) ---
    // Genel kodlar (sayfaya basılabilir): category='seo', is_public=1
    // PageSpeed API anahtarı gizlidir: category='integration', is_public=0, sayfaya geri basılmaz.
    $arac_kurallari = [
        'tool_ga_id'       => ['/^G-[A-Z0-9]{6,12}$/',                         'Google Analytics ID (G-XXXXXXXXXX biçiminde)'],
        'tool_gtm_id'      => ['/^GTM-[A-Z0-9]{4,10}$/',                       'Google Tag Manager ID (GTM-XXXXXXX biçiminde)'],
        'tool_gads_id'     => ['/^AW-\d{6,12}(\/[A-Za-z0-9_\-]{8,40})?$/',     'Google Ads ID (AW-123456789 veya AW-123456789/etiket)'],
        'tool_gsc_verify'  => ['/^[A-Za-z0-9_\-]{20,100}$/',                   'Search Console doğrulama kodu'],
        'tool_bing_verify' => ['/^[A-Za-z0-9_\-]{16,64}$/',                    'Bing Webmaster doğrulama kodu'],
        'tool_yandex_verify' => ['/^[A-Za-z0-9]{8,40}$/',                       'Yandex doğrulama kodu'],
        'tool_clarity_id'  => ['/^[a-z0-9]{8,12}$/',                            'Microsoft Clarity ID (küçük harf/rakam, ör. k1x2y3z4w5)'],
    ];
    $arac_hatalar = [];
    foreach ($arac_kurallari as $ak => [$re, $ad]) {
        if (!isset($_POST[$ak])) { continue; }
        $v = trim((string)$_POST[$ak]);
        // Doğrulama alanlarına tüm <meta ...> etiketi yapıştırılırsa content="..." değerini al.
        if (in_array($ak, ['tool_gsc_verify', 'tool_bing_verify', 'tool_yandex_verify'], true)
            && preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $v, $mm)) {
            $v = trim($mm[1]);
        }
        if ($v !== '' && !preg_match($re, $v)) {
            $arac_hatalar[] = $ad . ' geçersiz görünüyor.';
            continue; // Geçersiz değer kaydedilmez, mevcut değer korunur.
        }
        $db->Insert(
            "INSERT INTO sistem_ayarlari (setting_key, setting_value, category, is_public, updated_at)
             VALUES (?, ?, 'seo', 1, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), category = 'seo', is_public = 1, updated_at = NOW()",
            [$ak, $v]
        );
    }
    // PageSpeed API anahtarı: boş bırakılırsa mevcut korunur; "sil" işaretliyse temizlenir.
    $psKey = trim((string)($_POST['tool_pagespeed_key'] ?? ''));
    if (isset($_POST['tool_pagespeed_sil'])) {
        $psKey = ''; $psYaz = true;
    } elseif ($psKey !== '') {
        if (!preg_match('/^AIza[0-9A-Za-z_\-]{35}$/', $psKey)) {
            $arac_hatalar[] = 'Google PageSpeed API Key geçersiz görünüyor (AIza ile başlayan 39 karakter).';
            $psYaz = false;
        } else { $psYaz = true; }
    } else { $psYaz = false; }
    if ($psYaz) {
        $db->Insert(
            "INSERT INTO sistem_ayarlari (setting_key, setting_value, category, is_public, updated_at)
             VALUES ('tool_pagespeed_key', ?, 'integration', 0, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), category = 'integration', is_public = 0, updated_at = NOW()",
            [$psKey]
        );
    }
    $contactKeys = ['contact_email', 'contact_phone', 'contact_whatsapp', 'contact_address'];
    $socialKeys  = ['social_instagram', 'social_youtube', 'social_twitter', 'social_linkedin'];

    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            $val = trim($_POST[$k]);
            $cat = in_array($k, $contactKeys, true) ? 'contact' : (in_array($k, $socialKeys, true) ? 'social' : 'seo');
            // Sosyal medya bağlantısı: boş (gizle) ya da yalnızca http(s) adresi olabilir (javascript: vb. reddedilir).
            if (in_array($k, $socialKeys, true) && $val !== '' && !preg_match('#^https?://#i', $val)) { continue; }
            if (in_array($k, $socialKeys, true) && $val !== '' && !filter_var($val, FILTER_VALIDATE_URL)) { continue; }
            if ($k === 'contact_email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) { continue; }
            /* Satır hiç yoksa UPDATE sessizce 0 satır etkiler ve hiçbir şey
               kaydedilmez (sistem_ayarlari boş kurulduğunda tam olarak bu
               oluyordu). INSERT ... ON DUPLICATE KEY UPDATE ile satır yoksa
               oluşturuluyor, varsa güncelleniyor. */
            $db->Insert(
                "INSERT INTO sistem_ayarlari (setting_key, setting_value, category, is_public, updated_at)
                 VALUES (?, ?, ?, 1, NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                [$k, $val, $cat]
            );
        }
    }
    
    // Önbellekteki (5 dk) site ayarlarını sil ki yeni kodlar vitrinde hemen görünsün (tüm kayıtlardan sonra).
    foreach (glob(__DIR__ . '/../../cache/ayarlar*.cache.php') ?: [] as $cf) { @unlink($cf); }
    if (!$arac_hatalar) {
        echo "<script>window.location.href='website/seo.php?success=1';</script>";
        exit;
    }
}

// Verileri çek
$settings = $db->getRowsAssoc("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE setting_key IN ('seo_title', 'seo_description', 'seo_keywords', 'website_footer_text', 'website_hero_badge', 'website_hero_title', 'website_hero_subtitle', 'contact_email', 'contact_phone', 'contact_whatsapp', 'contact_address', 'social_instagram', 'social_youtube', 'social_twitter', 'social_linkedin', 'tool_ga_id', 'tool_gtm_id', 'tool_gads_id', 'tool_gsc_verify', 'tool_bing_verify', 'tool_yandex_verify', 'tool_clarity_id')");
$seo = [];
if($settings) {
    foreach($settings as $s) {
        $seo[$s['setting_key']] = $s['setting_value'];
    }
}
// PageSpeed anahtarı gizli: değeri sayfaya basılmaz, yalnızca kayıtlı olup olmadığı ve son 4 hanesi gösterilir.
$psRow = $db->getRowsAssoc("SELECT setting_value FROM sistem_ayarlari WHERE setting_key = 'tool_pagespeed_key' LIMIT 1");
$psVal = trim((string)(is_array($psRow) ? ($psRow[0]['setting_value'] ?? '') : ''));
$psVar = $psVal !== '';
$psMaske = $psVar ? '••••••••' . substr($psVal, -4) : '';
unset($psVal);
// Hatalı gönderimde kullanıcının yazdıklarını koru
if (!empty($arac_hatalar)) {
    foreach (array_keys($arac_kurallari) as $ak) { if (isset($_POST[$ak])) { $seo[$ak] = trim((string)$_POST[$ak]); } }
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

                <?php if (!empty($arac_hatalar)): ?>
                <div class="alert alert-danger p-5 mb-10">
                    <h4 class="mb-2 text-danger">Bazı alanlar kaydedilmedi</h4>
                    <ul class="mb-0">
                        <?php foreach ($arac_hatalar as $h): ?><li><?= htmlspecialchars($h) ?></li><?php endforeach; ?>
                    </ul>
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

                            <div class="card mb-5 mb-xl-8">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Google ve Diğer Araçlar</span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">Analiz, reklam ve arama motoru doğrulama kodları. Boş bırakılan alan kullanılmaz.</span>
                                    </h3>
                                </div>
                                <div class="card-body py-3">
                                    <div class="mb-8">
                                        <label class="form-label">Google Analytics ID</label>
                                        <input type="text" name="tool_ga_id" class="form-control form-control-solid" placeholder="G-XXXXXXXXXX" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_ga_id'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Google Tag Manager ID</label>
                                        <input type="text" name="tool_gtm_id" class="form-control form-control-solid" placeholder="GTM-XXXXXXX" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_gtm_id'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Google Ads ID (Dönüşüm)</label>
                                        <input type="text" name="tool_gads_id" class="form-control form-control-solid" placeholder="AW-123456789" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_gads_id'] ?? '') ?>" />
                                        <div class="form-text">Dönüşüm etiketi varsa AW-123456789/etiket biçiminde yazabilirsiniz.</div>
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Search Console Doğrulama</label>
                                        <input type="text" name="tool_gsc_verify" class="form-control form-control-solid" placeholder="Doğrulama kodu (content değeri)" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_gsc_verify'] ?? '') ?>" />
                                        <div class="form-text">Meta etiketinin tamamını yapıştırabilirsiniz; yalnızca content değeri alınır.</div>
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Bing Webmaster Doğrulama</label>
                                        <input type="text" name="tool_bing_verify" class="form-control form-control-solid" placeholder="Doğrulama kodu" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_bing_verify'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Yandex Doğrulama</label>
                                        <input type="text" name="tool_yandex_verify" class="form-control form-control-solid" placeholder="Doğrulama kodu" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_yandex_verify'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Microsoft Clarity ID</label>
                                        <input type="text" name="tool_clarity_id" class="form-control form-control-solid" placeholder="k1x2y3z4w5" autocomplete="off" spellcheck="false" value="<?= htmlspecialchars($seo['tool_clarity_id'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-8">
                                        <label class="form-label">Google PageSpeed API Key</label>
                                        <input type="password" name="tool_pagespeed_key" class="form-control form-control-solid" placeholder="<?= $psVar ? htmlspecialchars($psMaske) : 'AIza...' ?>" autocomplete="new-password" spellcheck="false" />
                                        <div class="form-text">Gizlidir; sayfaya geri yazılmaz ve sitede yayınlanmaz. Boş bırakırsanız mevcut anahtar korunur.<?= $psVar ? ' <span class="text-success">Kayıtlı.</span>' : '' ?></div>
                                        <?php if ($psVar): ?>
                                        <label class="form-check form-check-custom form-check-solid mt-3">
                                            <input class="form-check-input" type="checkbox" name="tool_pagespeed_sil" value="1" />
                                            <span class="form-check-label text-gray-700">Kayıtlı anahtarı sil</span>
                                        </label>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notice d-flex bg-light-warning rounded p-4 mb-5">
                                        <span class="fs-7 text-gray-700">Doğrulama kodları (Search Console, Bing, Yandex) çerez kullanmadığı için sitede hemen yayınlanır. Analytics, Tag Manager, Ads ve Clarity betikleri çerez/takip içerdiğinden, çerez bandı açık rıza akışına çevrilene kadar sitede <strong>çalıştırılmaz</strong>; yalnızca kodlar saklanır.</span>
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

                            <div class="card mb-5 mb-xl-8">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">İletişim Bilgileri</span>
                                        <span class="text-muted fw-semibold fs-7">İletişim sayfasında gösterilir. Telefon ve adres boşsa o kart gizlenir.</span>
                                    </h3>
                                </div>
                                <div class="card-body py-3">
                                    <div class="mb-10">
                                        <label class="form-label">E-posta</label>
                                        <input type="email" name="contact_email" class="form-control form-control-solid" placeholder="info@prolyn.net" value="<?= htmlspecialchars($seo['contact_email'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">Telefon</label>
                                        <input type="text" name="contact_phone" class="form-control form-control-solid" placeholder="+90 850 000 00 00" value="<?= htmlspecialchars($seo['contact_phone'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">WhatsApp numarası</label>
                                        <input type="text" name="contact_whatsapp" class="form-control form-control-solid" placeholder="+90 5xx xxx xx xx" value="<?= htmlspecialchars($seo['contact_whatsapp'] ?? '') ?>" />
                                        <div class="form-text">Sitedeki WhatsApp butonu ve sektör sayfasındaki "WhatsApp'tan demo iste" bu numaraya gider. Boşsa telefon cep numarasıysa o kullanılır.</div>
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">Adres</label>
                                        <textarea name="contact_address" class="form-control form-control-solid" rows="3"><?= htmlspecialchars($seo['contact_address'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-5 mb-xl-8">
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Sosyal Medya</span>
                                        <span class="text-muted mt-1 fw-semibold fs-7">Sitenin alt bilgisindeki "Bizi Takip Edin" simgeleri. Boş bırakılan simge gösterilmez.</span>
                                    </h3>
                                </div>
                                <div class="card-body py-3">
                                    <div class="mb-10">
                                        <label class="form-label">Instagram</label>
                                        <input type="url" name="social_instagram" class="form-control form-control-solid" placeholder="https://www.instagram.com/hesabiniz/" value="<?= htmlspecialchars($seo['social_instagram'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">YouTube</label>
                                        <input type="url" name="social_youtube" class="form-control form-control-solid" placeholder="https://www.youtube.com/@kanaliniz" value="<?= htmlspecialchars($seo['social_youtube'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">X (Twitter)</label>
                                        <input type="url" name="social_twitter" class="form-control form-control-solid" placeholder="https://x.com/hesabiniz" value="<?= htmlspecialchars($seo['social_twitter'] ?? '') ?>" />
                                    </div>
                                    <div class="mb-10">
                                        <label class="form-label">LinkedIn</label>
                                        <input type="url" name="social_linkedin" class="form-control form-control-solid" placeholder="https://www.linkedin.com/company/sirketiniz" value="<?= htmlspecialchars($seo['social_linkedin'] ?? '') ?>" />
                                    </div>
                                    <div class="form-text mb-5">Adresler https:// ile başlamalıdır; geçersiz adres kaydedilmez.</div>
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
