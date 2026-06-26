<?php 
require_once 'db.php'; 

try {
    $stmtModules = $pdo->query("SELECT * FROM website_modules WHERE status = 1 ORDER BY order_no ASC");
    $websiteModules = $stmtModules->fetchAll();
} catch (Exception $e) {
    $websiteModules = [];
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - Modüller</title>
    <meta name="description" content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? 'Kurumsal Kaynak Planlama, CRM, İK ve Ön Muhasebe çözümleri tek bir platformda.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=5" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
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
                    <button class="btn btn-icon btn-active-color-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                        <i class="bi bi-list fs-1 text-white"></i>
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
                                <a class="nav-link text-white px-4" href="pricing.php">Fiyatlandırma</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white px-4" href="contact.php">İletişim</a>
                            </li>
                        </ul>
                        
                        <div class="d-flex align-items-center flex-shrink-0">
                            <a href="register.php" class="btn btn-primary-glow px-6 py-3 fw-bold">30 Gün Ücretsiz Dene</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Hero-->
        <div class="hero-section text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">
                        Prolyn <span class="text-gradient-primary">Modülleri</span>
                    </h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 600px;">
                        İşletmenizin tüm ihtiyaçlarını tek bir platformda birleştiren, esnek ve güçlü çözümler.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Modules-->
        <div class="py-10 mb-20">
            <div class="container-xxl">
                <div class="row g-10">
                    <?php if (!empty($websiteModules)): ?>
                        <?php foreach ($websiteModules as $module): ?>
                            <div class="col-lg-4 col-md-6 animate-fade-in-up">
                                <div class="glass-card p-10 h-100 feature-card">
                                    <div class="feature-icon-box text-primary mb-6">
                                        <i class="bi <?php echo htmlspecialchars($module['icon']); ?> fs-3x text-primary"></i>
                                    </div>
                                    <h3 class="text-white fs-2 fw-bold mb-4"><?php echo htmlspecialchars($module['title']); ?></h3>
                                    <p class="text-gray-400 fs-5 mb-0"><?php echo htmlspecialchars($module['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center text-gray-500 fs-4">
                            Sistemde henüz aktif modül bulunmamaktadır.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!--end::Modules-->

        <!--begin::Footer-->
        <div class="mb-0 py-10 border-top border-gray-800 mt-auto" style="background: rgba(19, 22, 31, 0.6); backdrop-filter: blur(10px);">
            <div class="container-xxl">
                <div class="row pt-9 pb-5">
                    <div class="col-lg-4 mb-10 mb-lg-0">
                        <h1 class="text-white fs-2x fw-bolder mb-5">Prolyn<span class="text-primary">.</span></h1>
                        <p class="text-gray-500 fs-6 fw-semibold lh-lg mb-5">
                            <?php echo htmlspecialchars($appSettings['website_footer_text'] ?? 'Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız. Hız, güvenlik ve esnekliği bir arada sunuyoruz.'); ?>
                        </p>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Ürün</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="index.php#ozellikler" class="footer-link fw-semibold">Özellikler</a>
                            <a href="index.php#nasil-calisir" class="footer-link fw-semibold">Nasıl Çalışır?</a>
                            <a href="moduller.php" class="footer-link fw-semibold">Modüller</a>
                            <a href="pricing.php" class="footer-link fw-semibold">Fiyatlandırma</a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6 mb-10 mb-lg-0">
                        <h4 class="text-white fw-bold mb-5">Destek</h4>
                        <div class="d-flex flex-column gap-3">
                            <a href="contact.php" class="footer-link fw-semibold">İletişim</a>
                            <a href="#" class="footer-link fw-semibold">SSS</a>
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
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="index.php#ozellikler"><i class="bi bi-star me-2 text-primary"></i>Özellikler</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="index.php#nasil-calisir"><i class="bi bi-info-circle me-2 text-primary"></i>Nasıl Çalışır?</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="moduller.php"><i class="bi bi-grid me-2 text-primary"></i>Modüller</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="pricing.php"><i class="bi bi-tag me-2 text-primary"></i>Fiyatlandırma</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="contact.php"><i class="bi bi-envelope me-2 text-primary"></i>İletişim</a></li>
            </ul>
            <div class="d-grid mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5 shadow-sm">30 Gün Ücretsiz Dene</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<!-- Floating Action Buttons -->
<div class="floating-action-buttons">
    <a href="tel:+905555555555" class="fab-btn fab-phone" title="Bizi Arayın">
        <i class="bi bi-telephone-fill"></i>
    </a>
    <a href="https://wa.me/905555555555" class="fab-btn fab-whatsapp" target="_blank" title="WhatsApp Destek">
        <i class="bi bi-whatsapp"></i>
    </a>
    <button class="fab-btn fab-scrolltop" onclick="window.scrollTo({top: 0, behavior: 'smooth'});" title="Yukarı Çık">
        <i class="bi bi-arrow-up-short"></i>
    </button>
</div>

</body>
</html>
