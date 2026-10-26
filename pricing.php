<?php require_once 'db.php'; ?>
<?php
/**
 * Fiyatlandırma paket_ozellikleri tablosundan okunur (TEK KAYNAK).
 * Önceden burada sabit bir dizi vardı ve panelde girilen gerçek
 * fiyat/limitle çelişiyordu.
 */
const VARSAYILAN_YILLIK_INDIRIM = 10; // paket_ozellikleri.yillik_indirim kolonu yoksa

$packages = [];
try {
    $satirlar = $pdo->query("SELECT * FROM paket_ozellikleri ORDER BY price ASC")->fetchAll();
    foreach ($satirlar as $r) {
        $packages[] = [
            'code'           => $r['plan_name'],
            'name'           => trim((string)($r['plan_display_name'] ?? '')) !== ''
                                ? $r['plan_display_name'] : $r['plan_name'],
            'description'    => (string)($r['plan_description'] ?? ''),
            'modules'        => json_decode((string)($r['allowed_modules'] ?? '[]'), true) ?: [],
            'storage_gb'     => (int)($r['max_storage_gb'] ?? 0),
            'support'        => (string)($r['support_level'] ?? 'email'),
            'support_hours'  => (int)($r['support_response_hours'] ?? 0),
            'price_monthly'  => (float)($r['price'] ?? 0),
            'max_users'      => (int)($r['max_total_users'] ?? 0),
            'max_branches'   => (int)($r['max_branches'] ?? 0),
            // Kolon henüz yoksa (Panel > Paketler bir kez açılınca eklenir)
            // varsayılan %20 yıllık indirim gösterilir.
            'yearly_discount'=> array_key_exists('yillik_indirim', $r)
                                ? (int)$r['yillik_indirim'] : VARSAYILAN_YILLIK_INDIRIM,
        ];
    }
} catch (Throwable $e) {
    error_log('[prolynweb] paket_ozellikleri okunamadi: ' . $e->getMessage());
}

// Veritabanı okunamazsa sayfa boş kalmasın — son çare yedek liste
if (empty($packages)) {
    $packages = [
        ['code'=>'basic','name'=>'Başlangıç','description'=>'','price_monthly'=>1190.00,'max_users'=>5,'max_branches'=>1,'yearly_discount'=>VARSAYILAN_YILLIK_INDIRIM],
        ['code'=>'pro','name'=>'Pro','description'=>'','price_monthly'=>2290.00,'max_users'=>15,'max_branches'=>3,'yearly_discount'=>VARSAYILAN_YILLIK_INDIRIM],
        ['code'=>'enterprise','name'=>'Kurumsal','description'=>'','price_monthly'=>4490.00,'max_users'=>0,'max_branches'=>0,'yearly_discount'=>VARSAYILAN_YILLIK_INDIRIM],
    ];
}

$varsayilanAciklama = [
    'basic'      => 'Küçük işletmeler için müşteri, randevu, cari takip ve sözleşme yönetimi.',
    'pro'        => 'Büyüyen işletmeler için gelişmiş özellikler ve çoklu şube desteği.',
    'enterprise' => 'Büyük ölçekli firmalar için tam donanımlı, sınırsız çözüm.',
];
foreach ($packages as $i => $pk) {
    if ($pk['description'] === '') {
        $packages[$i]['description'] = $varsayilanAciklama[$pk['code']] ?? '';
    }
}

// Sayfadaki geçiş düğmesi için en yüksek indirim oranı
$enYuksekIndirim = 0;
foreach ($packages as $pk) {
    if ($pk['yearly_discount'] > $enYuksekIndirim) { $enYuksekIndirim = $pk['yearly_discount']; }
}
?>

<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - Fiyatlandırma</title>
    <meta name="description" content="<?php echo htmlspecialchars('Prolyn ERP fiyatlandırma planları: Basic, Pro ve Enterprise paketlerini karşılaştırın, işletmenize uygun bulut ERP paketini seçin.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($appSettings['seo_keywords'] ?? 'erp, bulut erp, crm, ön muhasebe, ik'); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($appSettings['seo_title'] ?? 'Prolyn - Yeni Nesil Bulut ERP Sistemi'); ?> - Fiyatlandırma">
    <meta property="og:description" content="<?php echo htmlspecialchars('Prolyn ERP fiyatlandırma planları: Basic, Pro ve Enterprise paketlerini karşılaştırın, işletmenize uygun bulut ERP paketini seçin.'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars(prolynweb_current_url()); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=6" rel="stylesheet" type="text/css" />
<style>
/* ── Aylık / Yıllık periyot seçimi ─────────────────────────────── */
.py-periyot-sar{display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:44px;}
.py-periyot{position:relative;display:inline-flex;padding:6px;border-radius:999px;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
  backdrop-filter:blur(8px);}
.py-periyot-kaydirak{position:absolute;top:6px;left:6px;height:calc(100% - 12px);
  border-radius:999px;background:var(--bs-primary,#009ef7);
  box-shadow:0 6px 20px rgba(0,158,247,.45);
  transition:left .28s cubic-bezier(.4,0,.2,1),width .28s cubic-bezier(.4,0,.2,1);z-index:0;}
.py-periyot-btn{position:relative;z-index:1;border:0;background:transparent;cursor:pointer;
  padding:12px 30px;border-radius:999px;font-weight:700;font-size:15px;color:#9aa4b2;
  display:inline-flex;align-items:center;gap:9px;transition:color .2s;white-space:nowrap;}
.py-periyot-btn:hover{color:#cdd5df;}
.py-periyot-btn.aktif{color:#fff;}
.py-rozet{background:rgba(80,205,137,.18);color:#50cd89;border:1px solid rgba(80,205,137,.35);
  padding:3px 9px;border-radius:999px;font-size:11.5px;font-weight:700;letter-spacing:.2px;}
.py-periyot-btn.aktif .py-rozet{background:rgba(255,255,255,.2);color:#fff;border-color:rgba(255,255,255,.35);}
.py-periyot-not{font-size:14px;color:#9aa4b2;text-align:center;}

/* ── Kart içi fiyat ────────────────────────────────────────────── */
.py-fiyat{font-size:3rem;line-height:1.05;transition:opacity .18s;}
.py-fiyat.py-gecis{opacity:.25;}
.py-fiyat-kutu .py-not{margin-top:10px;font-size:13.5px;min-height:44px;}
.py-yillik-not{display:none;}
.py-rozet-yesil{background:rgba(80,205,137,.15);color:#50cd89;border:1px solid rgba(80,205,137,.3);
  padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;}

@media (max-width:575.98px){
  .py-periyot-btn{padding:11px 20px;font-size:14px;}
  .py-fiyat{font-size:2.4rem;}
}
</style>
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
        <div class="hero-section text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">
                        Sade ve Şeffaf <span class="text-gradient-primary">Fiyatlandırma</span>
                    </h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 600px;">
                        İşletmenizin ölçeğine en uygun planı seçin. Gizli ücret yok, sürpriz maliyetler yok.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Pricing-->
        <div class="py-10 mb-20">
            <div class="container-xxl">

                <!-- Aylık / Yıllık periyot seçimi -->
                <div class="py-periyot-sar">
                    <div class="py-periyot" id="pyPeriyot">
                        <span class="py-periyot-kaydirak" id="pyKaydirak"></span>
                        <button type="button" class="py-periyot-btn aktif" data-periyot="aylik">Aylık</button>
                        <button type="button" class="py-periyot-btn" data-periyot="yillik">
                            Yıllık
                            <?php if ($enYuksekIndirim > 0): ?>
                            <span class="py-rozet">%<?php echo (int)$enYuksekIndirim; ?> indirim</span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <?php if ($enYuksekIndirim > 0): ?>
                    <div class="py-periyot-not">
                        Yıllık peşin ödemede <strong class="text-success">%<?php echo (int)$enYuksekIndirim; ?></strong> avantaj
                        &mdash; yılda yaklaşık <strong class="text-success"><?php echo rtrim(rtrim(number_format(12 * $enYuksekIndirim / 100, 1, ',', '.'), '0'), ','); ?> ay</strong> bedava.
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row g-10 justify-content-center">
                    <?php if(empty($packages)): ?>
                        <div class="col-12 text-center text-gray-500">Paket verileri bulunmuyor.</div>
                    <?php else: ?>
                        <?php foreach($packages as $index => $pkg): 
                            $planCode = $pkg['code'];
                            $isPopular = ($planCode === 'pro' || strpos(strtolower($pkg['name']), 'pro') !== false);
                            $delay = ($index + 1) * 100;
                        ?>
                        <!--begin::Col-->
                        <div class="col-lg-4 animate-fade-in-up delay-<?php echo $delay; ?>">
                            <div class="glass-card pricing-card h-100 <?php echo $isPopular ? 'popular border-primary border-opacity-50' : ''; ?>">
                                <?php if($isPopular): ?>
                                <div class="position-absolute top-0 end-0 translate-middle-y me-5" style="margin-top: 15px;">
                                    <span class="badge badge-light-primary px-3 py-2 fw-bold text-uppercase rounded-pill border border-primary border-opacity-25">En Popüler</span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="card-header pt-10 border-0">
                                    <div class="card-title d-flex flex-column w-100">
                                        <h2 class="fw-bolder mb-3 text-white fs-2"><?php echo htmlspecialchars($pkg['name']); ?></h2>
                                        <div class="fs-6 text-gray-400 lh-lg" style="min-height: 48px;"><?php echo htmlspecialchars($pkg['description'] ?? ''); ?></div>
                                    </div>
                                </div>
                                
                                <div class="card-body pt-5 pb-10">
                                    <?php
                                        $indirim      = (int)$pkg['yearly_discount'];
                                        $aylikFiyat   = (float)$pkg['price_monthly'];
                                        $yillikAylik  = $aylikFiyat * (1 - $indirim / 100);   // yıllıkta aya düşen
                                        $yillikToplam = $yillikAylik * 12;
                                        $yillikKazanc = ($aylikFiyat * 12) - $yillikToplam;
                                    ?>
                                    <div class="py-fiyat-kutu mb-10">
                                        <div class="d-flex align-items-end">
                                            <span class="fs-3 fw-bold text-gray-500 me-1 mb-2">₺</span>
                                            <span class="py-fiyat fw-bolder text-white me-2"
                                                  data-aylik="<?php echo number_format($aylikFiyat, 2, ',', '.'); ?>"
                                                  data-yillik="<?php echo number_format($yillikAylik, 2, ',', '.'); ?>"><?php echo number_format($aylikFiyat, 2, ',', '.'); ?></span>
                                            <span class="fs-6 fw-bold text-gray-500 mb-2">/ ay</span>
                                        </div>

                                        <!-- Aylık seçiliyken -->
                                        <div class="py-not py-aylik-not">
                                            <span class="text-gray-500">Aylık ödeme &middot; taahhüt yok</span>
                                        </div>

                                        <!-- Yıllık seçiliyken -->
                                        <div class="py-not py-yillik-not">
                                            <?php if ($indirim > 0): ?>
                                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                <span class="text-gray-500 text-decoration-line-through"><?php echo number_format($aylikFiyat, 2, ',', '.'); ?> ₺</span>
                                                <span class="py-rozet-yesil">%<?php echo $indirim; ?> indirim</span>
                                            </div>
                                            <span class="text-gray-400">
                                                Yıllık <strong class="text-white"><?php echo number_format($yillikToplam, 2, ',', '.'); ?> ₺</strong> peşin
                                                &middot; <span class="text-success">yılda <?php echo number_format($yillikKazanc, 2, ',', '.'); ?> ₺ tasarruf</span>
                                            </span>
                                            <?php else: ?>
                                            <span class="text-gray-400">Yıllık <strong class="text-white"><?php echo number_format($aylikFiyat * 12, 2, ',', '.'); ?> ₺</strong> peşin</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <ul class="check-list list-unstyled d-flex flex-column mb-10">
                                        <li>
                                            <i class="ki-duotone ki-check-circle"></i>
                                            <span><strong class="text-white"><?php echo $pkg['max_users'] > 0 ? $pkg['max_users'] : 'Sınırsız'; ?></strong> Kullanıcı Kapasitesi</span>
                                        </li>
                                        <li>
                                            <i class="ki-duotone ki-check-circle"></i>
                                            <span><strong class="text-white"><?php echo $pkg['max_branches'] > 0 ? $pkg['max_branches'] : 'Sınırsız'; ?></strong> Şube Desteği</span>
                                        </li>
                                        <?php
                                            $modAd = ['core'=>'Kullanıcı Yönetimi','customer'=>'Müşteri','commercial'=>'Ticari Müşteri','appointment'=>'Randevu','current_account'=>'Cari Takip','inventory'=>'Ürün Yönetimi','accounting'=>'Ön Muhasebe','invoice'=>'Fatura','contract'=>'Sözleşme','reports'=>'Raporlar','planning'=>'Planlama & İmalat','bank'=>'Banka','statistics'=>'İstatistik'];
                                            $modListe = [];
                                            foreach (($pkg['modules'] ?? []) as $mk) { if (isset($modAd[$mk]) && $mk !== 'core') { $modListe[] = $modAd[$mk]; } }
                                            $destekAd = ['email'=>'E-posta desteği','priority'=>'Öncelikli destek','dedicated'=>'Özel destek'];
                                        ?>
                                        <?php if ($modListe): ?>
                                        <li>
                                            <i class="ki-duotone ki-check-circle"></i>
                                            <span><strong class="text-white"><?php echo count($modListe); ?></strong> Modül<br><small class="text-gray-500"><?php echo htmlspecialchars(implode(' · ', $modListe)); ?></small></span>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (!empty($pkg['storage_gb']) && $pkg['storage_gb'] < 900): ?>
                                        <li>
                                            <i class="ki-duotone ki-check-circle"></i>
                                            <span><strong class="text-white"><?php echo (int)$pkg['storage_gb']; ?> GB</strong> Dosya Depolama</span>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <i class="ki-duotone ki-check-circle"></i>
                                            <span><?php echo htmlspecialchars($destekAd[$pkg['support'] ?? 'email'] ?? 'Teknik destek'); ?><?php echo !empty($pkg['support_hours']) ? ' <small class="text-gray-500">(' . (int)$pkg['support_hours'] . ' saat içinde yanıt)</small>' : ''; ?></span>
                                        </li>
                                    </ul>
                                    
                                    <a href="register.php?plan=<?php echo urlencode($planCode); ?>" data-py-kayit="<?php echo urlencode($planCode); ?>" class="btn w-100 py-4 fs-5 <?php echo $isPopular ? 'btn-primary-glow' : 'btn-light-primary'; ?>">
                                        Hemen Başla
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!--end::Pricing-->

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
                            <a href="blog.php" class="footer-link fw-semibold">Blog</a>
                            <a href="video.php" class="footer-link fw-semibold">Videolar</a>
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
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="blog.php"><i class="bi bi-file-text me-2 text-primary"></i>Blog</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="video.php"><i class="bi bi-play-circle me-2 text-primary"></i>Videolar</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="pricing.php"><i class="bi bi-tag me-2 text-primary"></i>Fiyatlandırma</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white px-0" href="contact.php"><i class="bi bi-envelope me-2 text-primary"></i>İletişim</a></li>
            </ul>
            <div class="d-grid gap-3 mt-8">
                <a href="register.php" class="btn btn-primary-glow py-3 fw-bold fs-5 shadow-sm">30 Gün Ücretsiz Dene</a>
                <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold py-3 fs-5">Giriş Yap</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<?php require __DIR__ . '/partials/fab.php'; ?>
<script>
/* Aylık / Yıllık periyot seçimi.
   Kaydırak seçili düğmenin üzerine oturur; fiyatlar kısa bir sönümlemeyle
   değişir; kayıt bağlantısına ?periyot=yillik eklenir. */
(function () {
    var kutu = document.getElementById('pyPeriyot');
    if (!kutu) { return; }

    var kaydirak = document.getElementById('pyKaydirak');
    var butonlar = kutu.querySelectorAll('.py-periyot-btn');

    function kaydiragiKonumla() {
        var aktif = kutu.querySelector('.py-periyot-btn.aktif');
        if (!aktif || !kaydirak) { return; }
        kaydirak.style.width = aktif.offsetWidth + 'px';
        kaydirak.style.left  = aktif.offsetLeft + 'px';
    }

    function periyotSec(periyot) {
        var yillikMi = (periyot === 'yillik');

        butonlar.forEach(function (b) {
            b.classList.toggle('aktif', b.dataset.periyot === periyot);
        });
        kaydiragiKonumla();

        // Fiyatları kısa bir sönümlemeyle değiştir
        var fiyatlar = document.querySelectorAll('.py-fiyat');
        fiyatlar.forEach(function (el) { el.classList.add('py-gecis'); });
        setTimeout(function () {
            fiyatlar.forEach(function (el) {
                el.textContent = yillikMi ? el.dataset.yillik : el.dataset.aylik;
                el.classList.remove('py-gecis');
            });
        }, 130);

        document.querySelectorAll('.py-yillik-not').forEach(function (el) {
            el.style.display = yillikMi ? 'block' : 'none';
        });
        document.querySelectorAll('.py-aylik-not').forEach(function (el) {
            el.style.display = yillikMi ? 'none' : 'block';
        });
        document.querySelectorAll('[data-py-kayit]').forEach(function (a) {
            a.href = 'register.php?plan=' + a.dataset.pyKayit + (yillikMi ? '&periyot=yillik' : '');
        });

        try {
            var u = new URL(window.location.href);
            if (yillikMi) { u.searchParams.set('periyot', 'yillik'); }
            else { u.searchParams.delete('periyot'); }
            history.replaceState(null, '', u.toString());
        } catch (e) { /* eski tarayıcı — adres güncellenmese de olur */ }
    }

    butonlar.forEach(function (b) {
        b.addEventListener('click', function () { periyotSec(b.dataset.periyot); });
    });

    window.addEventListener('resize', kaydiragiKonumla);
    window.addEventListener('load', kaydiragiKonumla);
    kaydiragiKonumla();

    if (new URLSearchParams(window.location.search).get('periyot') === 'yillik') {
        periyotSec('yillik');
    }
})();
</script>
</body>
</html>
