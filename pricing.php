<?php define('PROLYNWEB_LAZY_DB', true); require_once 'db.php'; ?>
<?php
/**
 * Fiyatlandırma: paket verisi TEK KAYNAKTAN (paket_ozellikleri) partials/paket_veri.php ile okunur.
 * Önceden bu sayfada ayrı bir okuma + yedek liste vardı; ana sayfadaki paket bölümüyle
 * ayrışmasın diye ortak fonksiyona taşındı.
 */
require_once __DIR__ . '/partials/paket_veri.php';
require_once __DIR__ . '/partials/modul_icerik.php';
$packages = prolynweb_paketler($pdo);

// Sayfadaki geçiş düğmesi için en yüksek indirim oranı
$enYuksekIndirim = 0;
foreach ($packages as $pk) {
    if ($pk['yearly_discount'] > $enYuksekIndirim) { $enYuksekIndirim = $pk['yearly_discount']; }
}
?>

<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
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
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!-- New Custom Redesign CSS -->
    <link href="assets/css/style.css?v=12" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=2" rel="stylesheet" type="text/css" />
    <link href="assets/css/modul.css?v=1" rel="stylesheet" type="text/css" />
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
<?php $navActive = 'pricing'; require __DIR__ . '/partials/site_header.php'; ?>


        
        <!--begin::Hero-->
        <div class="hero-section hero-compact text-center">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="mb-5 animate-fade-in-up">
                    <h1 class="text-white fw-bolder fs-2hx fs-lg-3x mb-6">
                        Sade ve Şeffaf <span class="text-gradient-primary">Fiyatlandırma</span>
                    </h1>
                    <div class="fw-medium fs-4 text-gray-400 mb-10 mx-auto" style="max-width: 800px;">
                        İşletmenizin ölçeğine en uygun planı seçin. Gizli ücret yok, sürpriz maliyetler yok.
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Pricing-->
        <div class="py-10 mb-20">
            <div class="container-xxl">

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
                                        $yillikToplamTam = round($yillikToplam / 50) * 50;   // yıllık peşin: en yakın 50 ₺'ye yuvarlanır
                                        $aylikYaklasik   = round($yillikToplamTam / 12 / 10) * 10;  // aylık karşılığı: en yakın 10 ₺
                                    ?>
                                    <div class="py-fiyat-kutu mb-10">
                                        <div class="d-flex align-items-end flex-wrap">
                                            <span class="fs-3 fw-bold text-gray-500 me-1 mb-2">₺</span>
                                            <span class="py-fiyat fw-bolder text-white me-2"><?php echo number_format($yillikToplamTam, 0, ',', '.'); ?></span>
                                            <span class="fs-6 fw-bold text-gray-500 mb-2">/ YIL</span>
                                        </div>
                                        <div class="py-not">
                                            <span class="text-gray-300 fw-semibold">Aylık &asymp; ₺<?php echo number_format($aylikYaklasik, 0, ',', '.'); ?></span>
                                            <?php if ($indirim > 0): ?>
                                            <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                                                <span class="text-gray-500 text-decoration-line-through">₺<?php echo number_format($aylikFiyat * 12, 0, ',', '.'); ?></span>
                                                <span class="py-rozet-yesil">%<?php echo $indirim; ?> yıllık indirim</span>
                                            </div>
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
                                    
                                    <a href="register.php?plan=<?php echo urlencode($planCode); ?>&amp;periyot=yillik" class="btn w-100 py-4 fs-5 <?php echo $isPopular ? 'btn-primary-glow' : 'btn-light-primary'; ?>">
                                        Hemen Başla
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end::Col-->
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-15">
                    <h2 class="bolum-baslik" style="font-size:1.8rem">Hangi pakette neler var?</h2>
                    <p class="bolum-giris mb-8">Satın almadan önce neyi alacağınızı görün: kullanıcı ve şube limitleri, destek ve her modülün hangi pakette açıldığı.</p>
                    <?php require __DIR__ . '/partials/paket_matris.php'; ?>
                    <p class="pk-alt-not">"Kısmen", o bölümün bir kısmının pakette bulunduğunu gösterir. Her modülün ne yaptığını <a href="moduller.php" style="color:var(--pw-link);text-decoration:underline">Modül Detayları</a> sayfasında okuyabilirsiniz. 30 gün boyunca seçtiğiniz paketi ücretsiz deneyebilirsiniz.</p>
                </div>

                <p class="text-center text-gray-400 fs-6 mt-12 mb-0">
                    Belirtilen fiyatlara %20 KDV dahildir. Farklı bir ihtiyacınız varsa
                    <a href="contact.php" class="text-primary fw-semibold">bize ulaşın</a>.
                </p>
            </div>
        </div>
        <!--end::Pricing-->

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
