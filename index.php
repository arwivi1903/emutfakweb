<?php require_once 'db.php'; ?>
<?php
$websiteFeatures = [];
try {
    $stmt = $pdo->query("SELECT * FROM website_features WHERE status = 1 ORDER BY order_no ASC");
    $websiteFeatures = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[prolynweb] website_features okunamadi: ' . $e->getMessage());
}
$featureColors = ['primary', 'success', 'info', 'warning', 'danger'];
?>
<?php
$faqItems = [
    ['Prolyn nedir?', 'Prolyn, işletmeler için geliştirilmiş bulut tabanlı bir ERP ve ön muhasebe yazılımıdır. Müşteri (CRM), randevu, cari hesap, kasa ve banka, sözleşme, personel ve destek süreçlerini tek panelden yönetmenizi sağlar. Her kurum kendi izole veritabanında çalışır ve ilk 30 gün ücretsiz denenebilir.'],
    ['Prolyn hangi modülleri içerir?', 'Müşteri ve cari yönetimi, randevu ve servis takibi, kasa ve banka hareketleri, çek ve sözleşme yönetimi, personel giderleri, raporlama ve denetim kayıtları ile destek talebi modülü Prolyn ile birlikte gelir. Kullanabileceğiniz modüller seçtiğiniz pakete göre belirlenir; güncel liste Modüller sayfasında yer alır.'],
    ['30 günlük deneme sürümünde kısıtlama var mı?', 'Hayır. Deneme sürecinde de kendi izole veritabanınızla, seçtiğiniz pakete uygun modüllerle çalışırsınız. Kredi kartı istemiyoruz; süre sonunda dilerseniz sözleşmeli müşteriye geçersiniz, dilerseniz vazgeçersiniz.'],
    ['Verilerimiz başka firmalarla aynı veritabanında mı tutuluyor?', 'Hayır. Prolyn\'de her kurum için ayrı, tamamen izole bir veritabanı (Multi-DB mimari) oluşturulur. Verileriniz hiçbir şekilde başka bir müşteriyle aynı tabloyu paylaşmaz.'],
    ['Paketler arasında sonradan geçiş yapabilir miyim?', 'Evet. Paket yükseltme talebinizi bize iletmeniz yeterli; ek bir kurulum sürecine gerek kalmadan paketiniz güncellenir.'],
    ['Mevcut sistemimizdeki verileri Prolyn\'e aktarabilir miyiz?', 'Kayıt sonrası kurulum ekibimiz sizinle iletişime geçerek mevcut müşteri ve cari verilerinizin aktarım sürecini birlikte planlar.'],
    ['Destek talebi açtığımda ne kadar sürede dönüş alırım?', 'Paketinize göre değişen öncelik seviyelerimiz var; panel üzerinden açtığınız destek talepleri ekibimiz tarafından takip edilip yanıtlanır.'],
];
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP - Yeni Çağın İş Yönetimi'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Prolyn ERP: Kurumsal Kaynak Planlama, CRM, İK ve Ön Muhasebe çözümlerini tek bir bulut platformunda birleştiren yeni nesil iş yönetim sistemi.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP - Yeni Çağın İş Yönetimi'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Prolyn ERP: Kurumsal Kaynak Planlama, CRM, İK ve Ön Muhasebe çözümlerini tek bir bulut platformunda birleştiren yeni nesil iş yönetim sistemi.'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <?php
    $ldBase  = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
    $ldName  = $appSettings['site_name'] ?? 'Prolyn ERP';
    $ldMail  = trim((string)($appSettings['contact_email'] ?? '')) ?: 'info@prolyn.net';
    $ldPhone = trim((string)($appSettings['contact_phone'] ?? ''));
    $ldOrg = [
        '@type' => 'Organization', '@id' => $ldBase . '/#org', 'name' => $ldName, 'url' => $ldBase,
        'logo' => $ldBase . '/p/dist/assets/media/logos/favicon.ico',
        'email' => $ldMail,
        'sameAs' => array_values(array_filter([$appSettings['social_twitter'] ?? null, $appSettings['social_linkedin'] ?? null])),
    ];
    if ($ldPhone !== '') {
        $ldOrg['contactPoint'] = [['@type' => 'ContactPoint', 'contactType' => 'customer support', 'telephone' => $ldPhone, 'email' => $ldMail, 'availableLanguage' => 'Turkish']];
    }
    $ldFaq = [];
    foreach ($faqItems as $fq) {
        $ldFaq[] = ['@type' => 'Question', 'name' => $fq[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $fq[1]]];
    }
    $ldGraph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $ldOrg,
            ['@type' => 'WebSite', '@id' => $ldBase . '/#website', 'url' => $ldBase, 'name' => $ldName, 'inLanguage' => 'tr-TR', 'publisher' => ['@id' => $ldBase . '/#org']],
            ['@type' => 'SoftwareApplication', 'name' => $ldName, 'applicationCategory' => 'BusinessApplication',
             'operatingSystem' => 'Web', 'inLanguage' => 'tr-TR', 'url' => $ldBase,
             'description' => $appSettings['seo_description'] ?? 'Bulut tabanlı ERP, CRM ve ön muhasebe yazılımı.',
             'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'TRY', 'description' => '30 gün ücretsiz deneme'],
             'publisher' => ['@id' => $ldBase . '/#org']],
            ['@type' => 'FAQPage', 'mainEntity' => $ldFaq],
        ],
    ];
    ?>
    <script type="application/ld+json"><?php echo json_encode($ldGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?></script>
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=8" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <!--begin::Security Check Overlay-->
    <div id="prolyn-boot-check" class="boot-check">
        <div class="boot-check-box">
            <div class="boot-check-icon">🛡️</div>
            <h1 class="text-white fs-3 fw-bolder mb-3">Güvenlik Kontrolü</h1>
            <p id="boot-check-message" class="text-gray-400 fs-6 mb-0">Lütfen bekleyin, tarayıcınız doğrulanıyor...</p>
            <div class="boot-check-bar"><div class="boot-check-bar-fill"></div></div>
        </div>
    </div>
    <!--end::Security Check Overlay-->

    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Header-->
        <div class="landing-header glass-header" data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
            <div class="container-xxl">
                <nav class="navbar navbar-dark w-100 py-4 py-lg-6">
                    <!--begin::Logo-->
                    <a class="navbar-brand d-flex align-items-center" href="index.php">
                        <h1 class="text-white fs-1 fw-bolder m-0">Prolyn<span class="text-primary">.</span></h1>
                    </a>
                    <!--end::Logo-->
                    
                    <!-- Mobile Toggle -->
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Desktop Menu -->
                    <div class="d-none d-lg-flex align-items-center ms-auto">
                        <ul class="nav text-white fw-semibold fs-6 me-10">
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="index.php#ozellikler">Özellikler</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="index.php#nasil-calisir">Nasıl Çalışır?</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="moduller.php">Modüller</a>
                            </li>
                                                        <li class="nav-item">
                                <a class="nav-link text-white px-4" href="blog.php">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="video.php">Videolar</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="pricing.php">Fiyatlandırma</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="contact.php">İletişim</a>
                            </li>
                        </ul>
                        
                        <div class="d-flex align-items-center flex-shrink-0">
                            <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold px-6 py-3 me-4">Giriş Yap</a>
                            <a href="register.php" class="btn btn-primary-glow px-6 py-3 fw-bold">30 Gün Ücretsiz Dene</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--end::Header-->
        
        <!--begin::Hero-->
        <div class="hero-section">
            <div class="hero-glow-1"></div>
            <div class="hero-glow-2"></div>

            <div class="container-xxl position-relative z-index-2">
                <div class="row align-items-center">
                    <div class="col-lg-6 text-center text-lg-start">
                        <div class="animate-fade-in-up">
                            <span class="badge badge-light-primary fw-bolder px-5 py-2 fs-7 mb-7 rounded-pill border border-primary border-opacity-25">
                                ✨ Yeni Nesil ERP ile Tanışın
                            </span>

                            <h1 class="text-white fw-bolder fs-3x fs-lg-4x mb-6 lh-sm">
                                İşletmenizi <span class="text-gradient-primary">Geleceğe</span> Taşıyın
                            </h1>

                            <p class="fw-medium fs-4 text-gray-400 mb-10 mx-auto mx-lg-0" style="max-width: 600px; line-height: 1.6;">
                                Karmaşık süreçlere son verin. Bulut tabanlı, ışık hızında ve tamamen izole mimari ile iş akışlarınızı anında dijitalleştirin.
                            </p>

                            <div class="d-flex justify-content-center justify-content-lg-start gap-4 flex-wrap mb-10">
                                <a href="register.php" class="btn btn-primary-glow btn-lg px-8">Hemen Başla</a>
                                <a href="#nasil-calisir" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary btn-lg text-white">Nasıl Çalışır?</a>
                            </div>

                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-5 text-gray-400 fs-6 fw-semibold flex-wrap">
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-check-circle fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Kredi kartı gerekmez
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="ki-duotone ki-check-circle fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Anında Kurulum
                                </div>
                                <div class="d-flex align-items-center d-none d-sm-flex">
                                    <i class="ki-duotone ki-check-circle fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                    İzole Veritabanı
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-visual">
                            <div class="dashboard-mockup">
                                <div class="dashboard-header">
                                    <div class="mac-buttons">
                                        <span class="mac-btn mac-close"></span>
                                        <span class="mac-btn mac-min"></span>
                                        <span class="mac-btn mac-max"></span>
                                    </div>
                                </div>
                                <div class="dashboard-body">
                                    <div class="sidebar-mock">
                                        <div class="mock-nav active"><i class="bi bi-speedometer2"></i> Pano</div>
                                        <div class="mock-nav"><i class="bi bi-people"></i> Müşteriler</div>
                                        <div class="mock-nav"><i class="bi bi-calendar-check"></i> Randevular</div>
                                        <div class="mock-nav"><i class="bi bi-journal-text"></i> Cari</div>
                                        <div class="mock-nav"><i class="bi bi-wallet2"></i> Kasa</div>
                                        <div class="mock-nav"><i class="bi bi-file-earmark-text"></i> Sözleşme</div>
                                    </div>
                                    <div class="content-mock">
                                        <div class="metrics-mock">
                                            <div class="metric-card-mock">
                                                <span class="mock-metric-label">Aylık Tahsilat</span>
                                                <span class="mock-metric-value">₺184.500</span>
                                            </div>
                                            <div class="metric-card-mock">
                                                <span class="mock-metric-label">Açık Randevu</span>
                                                <span class="mock-metric-value">27</span>
                                            </div>
                                            <div class="metric-card-mock">
                                                <span class="mock-metric-label">Cari Bakiye</span>
                                                <span class="mock-metric-value">₺96.250</span>
                                            </div>
                                        </div>
                                        <div class="chart-mock">
                                            <div class="chart-col"><div class="chart-bar" style="height: 40%;"></div><span>Mar</span></div>
                                            <div class="chart-col"><div class="chart-bar" style="height: 70%;"></div><span>Nis</span></div>
                                            <div class="chart-col"><div class="chart-bar" style="height: 55%;"></div><span>May</span></div>
                                            <div class="chart-col"><div class="chart-bar" style="height: 90%;"></div><span>Haz</span></div>
                                            <div class="chart-col"><div class="chart-bar" style="height: 65%;"></div><span>Tem</span></div>
                                            <div class="chart-col"><div class="chart-bar" style="height: 80%;"></div><span>Ağu</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mock-caption">Örnek ekran · veriler temsilidir</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Stats Section-->
        <div class="py-10 bg-black">
            <div class="container-xxl">
                <div class="row g-8 text-center">
                    <div class="col-6 col-md-3 scroll-reveal">
                        <div class="stat-number text-white">12</div>
                        <div class="stat-label text-gray-500 fw-semibold">Entegre Modül</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-1">
                        <div class="stat-number text-white">30 Gün</div>
                        <div class="text-gray-500 fw-bold fs-5">Ücretsiz Deneme</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-2">
                        <div class="stat-number text-white">%100</div>
                        <div class="text-gray-500 fw-bold fs-5">İzole Veritabanı</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-3">
                        <div class="stat-number text-white">1 Panel</div>
                        <div class="text-gray-500 fw-bold fs-5">Tüm İşletme Süreçleri</div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stats Section-->

        <!--begin::Features-->
        <div id="ozellikler" class="py-15 py-lg-20">
            <div class="container-xxl">
                <div class="text-center mb-17">
                    <h3 class="fs-2hx fw-bolder text-white mb-5">Neden Prolyn?</h3>
                    <div class="fs-5 text-gray-400 fw-medium">Kurumsal seviyede güvenlik ve performansı bir araya getirdik.</div>
                </div>

                <div class="row g-10">
                    <?php if (!empty($websiteFeatures)): ?>
                        <?php foreach ($websiteFeatures as $i => $feature): ?>
                        <?php $color = $featureColors[$i % count($featureColors)]; ?>
                        <div class="col-md-4 scroll-reveal delay-<?php echo ($i % 3) + 1; ?>">
                            <div class="glass-card h-100 p-8">
                                <div class="feature-icon-box text-<?php echo $color; ?>">
                                    <i class="bi <?php echo htmlspecialchars($feature['icon']); ?> fs-3x text-<?php echo $color; ?>"></i>
                                </div>
                                <h4 class="text-white fs-3 fw-bold mb-4"><?php echo htmlspecialchars($feature['title']); ?></h4>
                                <p class="text-gray-400 fs-6 lh-lg mb-0">
                                    <?php echo htmlspecialchars($feature['description']); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!--end::Features-->

        <!--begin::How It Works-->
        <div id="nasil-calisir" class="py-15 py-lg-20 bg-black bg-opacity-50">
            <div class="container-xxl">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-10 mb-lg-0 scroll-reveal">
                        <h3 class="fs-2hx fw-bolder text-white mb-6">Sadece <span class="text-gradient-primary">3 Adımda</span> Başlayın</h3>
                        <p class="text-gray-400 fs-5 mb-10 lh-lg">
                            Prolyn'in tam otomatik tenant altyapısı sayesinde kayıt olduğunuz anda size özel veritabanınız ve ortamınız milisaniyeler içinde hazır olur. Herhangi bir kurulum beklemezsiniz.
                        </p>

                        <div class="d-flex flex-column gap-6">
                            <div class="d-flex">
                                <div class="step-number">1</div>
                                <div class="ms-4">
                                    <h4 class="text-white fs-4 fw-bold">Hesabınızı Oluşturun</h4>
                                    <p class="text-gray-500 mb-0">Temel bilgilerinizi girerek saniyeler içinde kayıt olun.</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="step-number">2</div>
                                <div class="ms-4">
                                    <h4 class="text-white fs-4 fw-bold">Altyapınız Hazırlanır</h4>
                                    <p class="text-gray-500 mb-0">Sistem size özel tamamen izole bir veritabanı kurar ve şablon verileri aktarır.</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="step-number">3</div>
                                <div class="ms-4">
                                    <h4 class="text-white fs-4 fw-bold">Hemen Yönetmeye Başlayın</h4>
                                    <p class="text-gray-500 mb-0">Özel kurum kodunuz ve şifrenizle giriş yapıp şirketinizi dijitalden yönetin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center scroll-reveal delay-2">
                        <img src="p/dist/assets/media/illustrations/sketchy-1/17-dark.png" class="mw-100" style="max-height: 400px; opacity: 0.8;" alt="" />
                    </div>
                </div>
            </div>
        </div>
        <!--end::How It Works-->

        <!--begin::FAQ-->
        <div id="sss" class="py-15 py-lg-20">
            <div class="container-xxl">
                <div class="text-center mb-17 scroll-reveal">
                    <h3 class="fs-2hx fw-bolder text-white mb-5">Sıkça Sorulan Sorular</h3>
                    <div class="fs-5 text-gray-400 fw-medium">Aklınıza takılan bir şey mi var? İşte en çok sorulanlar.</div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="accordion scroll-reveal" id="prolynFaqAccordion">
                            <?php foreach ($faqItems as $fi => $faq): $n = $fi + 1; ?>
                            <div class="glass-card <?= $fi < count($faqItems) - 1 ? 'mb-4 ' : '' ?>p-2">
                                <h3 class="accordion-header fs-5" id="faqHead<?= $n ?>">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody<?= $n ?>">
                                        <?= htmlspecialchars($faq[0]) ?>
                                    </button>
                                </h3>
                                <div id="faqBody<?= $n ?>" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        <?= htmlspecialchars($faq[1]) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::FAQ-->

        <!--begin::CTA-->
        <div class="cta-section py-20">
            <div class="cta-pattern"></div>
            <div class="container-xxl position-relative z-index-2 text-center scroll-reveal">
                <h2 class="text-white fw-bolder fs-2hx mb-5">Şirketinizi Dönüştürmeye Hazır Mısınız?</h2>
                <p class="text-gray-400 fs-4 mb-10 max-w-700 mx-auto">
                    Türkiye'nin en yenilikçi bulut ERP sistemine geçiş yapın, operasyonel yüklerinizi %70 oranında azaltın.
                </p>
                <a href="register.php" class="btn btn-primary-glow btn-lg px-10 py-4 fs-4">Ücretsiz Denemeye Başla</a>
            </div>
        </div>
        <!--end::CTA-->

        <!--begin::Footer-->
        <div class="mb-0 py-10 border-top border-gray-800 mt-auto" style="background: rgba(19, 22, 31, 0.6); backdrop-filter: blur(10px);">
            <div class="container-xxl">
                <div class="row pt-9 pb-5">
                    <div class="col-lg-4 mb-10 mb-lg-0">
                        <h1 class="text-white fs-2x fw-bolder mb-5">Prolyn<span class="text-primary">.</span></h1>
                        <p class="text-gray-500 fs-6 fw-semibold lh-lg mb-5">
                            Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.
                        </p>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Ürün</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="index.php#ozellikler" class="footer-link fw-semibold">Özellikler</a>
                            <a href="index.php#nasil-calisir" class="footer-link fw-semibold">Nasıl Çalışır?</a>
                            <a href="moduller.php" class="footer-link fw-semibold">Modüller</a>
                            <a href="blog.php" class="footer-link fw-semibold">Blog</a>
                            <a href="video.php" class="footer-link fw-semibold">Videolar</a>
                            <a href="pricing.php" class="footer-link fw-semibold">Fiyatlandırma</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Destek</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="contact.php" class="footer-link fw-semibold">İletişim</a>
                            <a href="index.php#sss" class="footer-link fw-semibold">SSS</a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h4 class="text-white fw-bold mb-5">Bizi Takip Edin</h4>
                        <div class="d-flex gap-4">
                            <?php if(!empty($appSettings['social_twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['social_twitter']); ?>" target="_blank" class="btn btn-icon btn-light-primary btn-sm rounded-circle">
                                    <i class="bi bi-twitter-x fs-4"></i>
                                </a>
                            <?php endif; ?>
                            <?php if(!empty($appSettings['social_linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['social_linkedin']); ?>" target="_blank" class="btn btn-icon btn-light-primary btn-sm rounded-circle">
                                    <i class="bi bi-linkedin fs-4"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-center border-top border-gray-800 pt-10">
                    <span class="text-gray-500 fw-semibold">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>. Tüm hakları saklıdır. - <a href="https://echoshift.net" target="_blank" class="text-gray-400 text-hover-primary">Created by EchoShift</a></span>
                </div>
            </div>
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Root-->

    <!-- Offcanvas Mobile Menu -->
    <div class="offcanvas offcanvas-end offcanvas-glass border-0" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom border-gray-800 p-6">
            <h5 class="offcanvas-title text-white fs-2 fw-bolder" id="mobileMenuLabel">Prolyn<span class="text-primary">.</span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body p-6">
            <ul class="nav flex-column fs-4 fw-semibold mb-8">
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="index.php#ozellikler">Özellikler</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="index.php#nasil-calisir">Nasıl Çalışır?</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="moduller.php">Modüller</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="blog.php">Blog</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="video.php">Videolar</a></li>
                <li class="nav-item mb-4"><a class="nav-link text-white px-0" href="pricing.php">Fiyatlandırma</a></li>
                <li class="nav-item"><a class="nav-link text-white px-0" href="contact.php">İletişim</a></li>
            </ul>
            <div class="d-grid gap-3 mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5">30 Gün Ücretsiz Dene</a>
                <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold py-3 fs-5">Giriş Yap</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <script src="assets/js/site.js?v=6"></script>
    <script src="assets/js/boot-check.js"></script>

<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
