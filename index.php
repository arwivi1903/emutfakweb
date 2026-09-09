<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn ERP - Yeni Çağın İş Yönetimi'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($appSettings['seo_description'] ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? ''); ?>">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
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
                                        <div class="mock-line w-3-4"></div>
                                        <div class="mock-line w-full"></div>
                                        <div class="mock-line w-1-2"></div>
                                        <div class="mock-line w-full"></div>
                                        <div class="mock-line w-3-4"></div>
                                    </div>
                                    <div class="content-mock">
                                        <div class="metrics-mock">
                                            <div class="metric-card-mock">
                                                <div class="mock-line w-1-2"></div>
                                                <div class="mock-line w-3-4"></div>
                                            </div>
                                            <div class="metric-card-mock">
                                                <div class="mock-line w-1-2"></div>
                                                <div class="mock-line w-3-4"></div>
                                            </div>
                                            <div class="metric-card-mock">
                                                <div class="mock-line w-1-2"></div>
                                                <div class="mock-line w-3-4"></div>
                                            </div>
                                        </div>
                                        <div class="chart-mock">
                                            <div class="chart-bar" style="height: 40%;"></div>
                                            <div class="chart-bar" style="height: 70%;"></div>
                                            <div class="chart-bar" style="height: 55%;"></div>
                                            <div class="chart-bar" style="height: 90%;"></div>
                                            <div class="chart-bar" style="height: 65%;"></div>
                                            <div class="chart-bar" style="height: 80%;"></div>
                                        </div>
                                    </div>
                                </div>
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
                        <div class="stat-number text-white">9+</div>
                        <div class="stat-label text-gray-500 fw-semibold">Gelişmiş Modül</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-1">
                        <div class="stat-number text-white">99.9%</div>
                        <div class="text-gray-500 fw-bold fs-5">Uptime Garantisi</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-2">
                        <div class="stat-number text-white">1sn</div>
                        <div class="text-gray-500 fw-bold fs-5">Altı Yanıt Süresi</div>
                    </div>
                    <div class="col-6 col-md-3 scroll-reveal delay-3">
                        <div class="stat-number text-white">7/24</div>
                        <div class="text-gray-500 fw-bold fs-5">Teknik Destek</div>
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
                    <div class="col-md-4 scroll-reveal">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-primary">
                                <i class="ki-duotone ki-rocket fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Işık Hızında Performans</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                PgBouncer ve Redis destekli altyapımızla binlerce veriyi milisaniyeler içinde işleyin. Connection kopmalarına son.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 scroll-reveal delay-1">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-success">
                                <i class="ki-duotone ki-shield-tick fs-3x text-success"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Tam İzolasyon (%100)</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                Her müşteriye (tenant) özel ayrı veritabanı (Multi-DB) mimarisi ile verileriniz diğer firmalardan tamamen izole ve güvende.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 scroll-reveal delay-2">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-info">
                                <i class="ki-duotone ki-graph-3 fs-3x text-info"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Gelişmiş Analitik</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                Tüm hareketlerinizi anlık takip edin. Log ve denetim mekanizmaları sayesinde kim, ne zaman, ne yaptı her zaman bilin.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 scroll-reveal">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-warning">
                                <i class="ki-duotone ki-abstract-26 fs-3x text-warning"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Kolay Entegrasyon</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                E-Fatura, banka sistemleri ve 3. parti yazılımlarınızla sorunsuz entegre çalışacak modern API altyapısı.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 scroll-reveal delay-1">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-danger">
                                <i class="ki-duotone ki-devices fs-3x text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Mobil Uyumluluk</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                Tüm cihazlardan kusursuz erişim. İşinizi ofise bağlı kalmadan cep telefonunuzdan veya tabletinizden yönetin.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 scroll-reveal delay-2">
                        <div class="glass-card h-100 p-8">
                            <div class="feature-icon-box text-primary">
                                <i class="ki-duotone ki-cloud fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <h4 class="text-white fs-3 fw-bold mb-4">Otomatik Yedekleme</h4>
                            <p class="text-gray-400 fs-6 lh-lg mb-0">
                                Verileriniz her gün otomatik olarak güvenli sunucularda yedeklenir. Veri kaybı endişesi yaşamadan işinize odaklanın.
                            </p>
                        </div>
                    </div>
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
                            <div class="glass-card mb-4 p-2">
                                <h2 class="accordion-header" id="faqHead1">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody1">
                                        30 günlük deneme sürümünde kısıtlama var mı?
                                    </button>
                                </h2>
                                <div id="faqBody1" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        Hayır. Deneme sürecinde de kendi izole veritabanınızla, seçtiğiniz pakete uygun modüllerle çalışırsınız. Kredi kartı istemiyoruz; süre sonunda dilerseniz sözleşmeli müşteriye geçersiniz, dilerseniz vazgeçersiniz.
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card mb-4 p-2">
                                <h2 class="accordion-header" id="faqHead2">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody2">
                                        Verilerimiz başka firmalarla aynı veritabanında mı tutuluyor?
                                    </button>
                                </h2>
                                <div id="faqBody2" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        Hayır. Prolyn'de her kurum için ayrı, tamamen izole bir veritabanı (Multi-DB mimari) oluşturulur. Verileriniz hiçbir şekilde başka bir müşteriyle aynı tabloyu paylaşmaz.
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card mb-4 p-2">
                                <h2 class="accordion-header" id="faqHead3">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody3">
                                        Paketler arasında sonradan geçiş yapabilir miyim?
                                    </button>
                                </h2>
                                <div id="faqBody3" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        Evet. Sözleşmeli müşterilerimiz panelden paket yükseltme talebinde bulunabilir; kalan süreye göre fiyat farkı otomatik hesaplanır, ek bir kurulum sürecine gerek kalmaz.
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card mb-4 p-2">
                                <h2 class="accordion-header" id="faqHead4">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody4">
                                        Mevcut sistemimizdeki verileri Prolyn'e aktarabilir miyiz?
                                    </button>
                                </h2>
                                <div id="faqBody4" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        Kayıt sonrası kurulum ekibimiz sizinle iletişime geçerek mevcut müşteri, cari ve stok verilerinizin aktarım sürecini birlikte planlar.
                                    </div>
                                </div>
                            </div>

                            <div class="glass-card p-2">
                                <h2 class="accordion-header" id="faqHead5">
                                    <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faqBody5">
                                        Destek talebi açtığımda ne kadar sürede dönüş alırım?
                                    </button>
                                </h2>
                                <div id="faqBody5" class="accordion-collapse collapse" data-bs-parent="#prolynFaqAccordion">
                                    <div class="accordion-body text-gray-400 fs-6 lh-lg">
                                        Paketinize göre değişen öncelik seviyelerimiz var; panel üzerinden açtığınız destek talepleri ekibimiz tarafından takip edilip yanıtlanır.
                                    </div>
                                </div>
                            </div>
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
    <div class="offcanvas offcanvas-end bg-dark" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
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
            <div class="d-grid mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5">30 Gün Ücretsiz Dene</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->
    <script src="assets/js/site.js"></script>
    <script src="assets/js/boot-check.js"></script>

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
