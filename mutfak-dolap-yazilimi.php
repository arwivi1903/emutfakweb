<?php
/**
 * Sektör tanıtım sayfası: mutfak, dolap ve banyo firmaları
 * Kampanya bağlantıları ?ref=L{lead_id} taşır; kayıt olunca lead eşleşir.
 */
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
$leadRef = '';
if (isset($_GET['ref']) && preg_match('/^L\d{1,9}$/', (string)$_GET['ref'])) {
    $leadRef = (string)$_GET['ref'];
    $_SESSION['lead_ref'] = $leadRef;
    setcookie('prolyn_lead', $leadRef, ['expires' => time() + 60 * 60 * 24 * 45, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
} elseif (!empty($_SESSION['lead_ref'])) {
    $leadRef = (string)$_SESSION['lead_ref'];
}
$kayitUrl = 'register.php' . ($leadRef !== '' ? '?ref=' . rawurlencode($leadRef) : '');

$waNo = preg_replace('/\D/', '', (string)($appSettings['contact_whatsapp'] ?? ''));
$telNo = preg_replace('/[^0-9+]/', '', (string)($appSettings['contact_phone'] ?? ''));
if ($waNo === '' && str_starts_with($telNo, '+905')) { $waNo = preg_replace('/\D/', '', $telNo); }
if ($waNo !== '' && strlen($waNo) === 11 && $waNo[0] === '0') { $waNo = '9' . $waNo; }
$fabWhatsappText = 'Merhaba, mutfak/dolap/banyo firmamız için Prolyn demosu istiyoruz.' . ($leadRef !== '' ? ' (Kod: ' . $leadRef . ')' : '');
$waDemoUrl = $waNo !== '' ? 'https://wa.me/' . $waNo . '?text=' . rawurlencode($fabWhatsappText) : 'contact.php';

$sss = [
    ['Prolyn mutfak ve dolap firmaları için uygun mu?', 'Evet. Prolyn; ölçü ve montaj randevuları, montaj ekiplerinin planı, eksik malzeme takibi, sözleşme, taksit, çek ve cari hesap gibi mutfak, dolap ve banyo firmalarının günlük işlerini tek panelde toplar.'],
    ['Ekiplerim telefondan kullanabilir mi?', 'Prolyn web tabanlıdır; bilgisayar, tablet ve telefon tarayıcısından kurulum gerektirmeden açılır. Her personel kendi kullanıcısıyla, yalnızca yetkili olduğu ekranları görür.'],
    ['Kurulum ve verilerin aktarılması ne kadar sürer?', 'Kampanya süresince kurulumu ve mevcut müşteri ile cari listenizin aktarımını ücretsiz biz yapıyoruz. Elinizdeki Excel listesini göndermeniz yeterli; çoğu firma aynı gün kullanmaya başlıyor.'],
    ['Stok takibi var mı?', 'Ürün kartları ve montajda eksik çıkan malzemenin takibi bugün kullanılabilir. Ayrıntılı depo stok modülü geliştirme aşamasında ve yakında açılacak.'],
    ['Verilerim güvende mi?', 'Her firma kendi ayrı veritabanında çalışır; verileriniz başka bir firmayla aynı tabloyu paylaşmaz. Bağlantılar şifrelidir ve kullanıcı yetkilerini siz belirlersiniz.'],
    ['30 günlük deneme bitince ne olur?', 'Deneme süresince kredi kartı istemiyoruz. Süre sonunda dilerseniz size uygun paketle devam edersiniz, dilerseniz hesabınız kapanır; ek bir ücret çıkmaz.'],
];
$base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
$canonical = $base . '/mutfak-dolap-yazilimi';
$sayfaBaslik = 'Mutfak, Dolap ve Banyo Firmaları için İş Takip Programı | Prolyn';
$sayfaAciklama = 'Ölçü ve montaj randevuları, montaj ekipleri, eksik malzeme, sözleşme, taksit, çek ve cari takibi tek ekranda. 30 gün ücretsiz deneme, ücretsiz kurulum ve veri aktarımı.';
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sayfaBaslik) ?></title>
    <meta name="description" content="<?= htmlspecialchars($sayfaAciklama) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= htmlspecialchars($appSettings['site_name'] ?? 'Prolyn ERP') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($sayfaBaslik) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($sayfaAciklama) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'WebPage', 'name' => $sayfaBaslik, 'url' => $canonical, 'description' => $sayfaAciklama, 'inLanguage' => 'tr-TR'],
            ['@type' => 'SoftwareApplication', 'name' => 'Prolyn', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web',
             'audience' => ['@type' => 'BusinessAudience', 'audienceType' => 'Mutfak dolabı, banyo mobilyası ve dolap imalat/montaj firmaları'],
             'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'TRY', 'description' => '30 gün ücretsiz deneme, ücretsiz kurulum ve veri aktarımı']],
            ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $sss)],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <link rel="shortcut icon" href="p/dist/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=7" rel="stylesheet" type="text/css" />
    <link href="assets/css/sektor.css?v=1" rel="stylesheet" type="text/css" />
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
                            <a href="<?= htmlspecialchars($kayitUrl) ?>" class="btn btn-primary-glow px-6 py-3 fw-bold">30 Gün Ücretsiz Dene</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!--end::Header-->

        
        <!--begin::Hero-->
        <div class="hero-section sk-hero">
            <div class="hero-glow-1"></div>
            <div class="container-xxl position-relative z-index-2">
                <div class="row align-items-center g-10">
                    <div class="col-lg-6 animate-fade-in-up">
                        <span class="sk-badge">Mutfak · Dolap · Banyo firmaları için</span>
                        <h1 class="text-white fw-bolder fs-2hx fs-lg-3x lh-sm mt-6 mb-6">
                            Ölçüden montaja, sözleşmeden tahsilata <span class="text-gradient-primary">her iş tek ekranda</span>
                        </h1>
                        <p class="fs-4 text-gray-400 mb-8">
                            Randevular defterde, eksik parçalar WhatsApp gruplarında, taksitler Excel'de kalmasın. Prolyn, mutfak ve dolap firmalarının günlük işini tek panelde toplar.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mb-6">
                            <a href="<?= htmlspecialchars($kayitUrl) ?>" class="btn btn-primary-glow btn-lg px-8 py-4 fw-bold">30 Gün Ücretsiz Dene</a>
                            <a href="<?= htmlspecialchars($waDemoUrl) ?>" <?= $waNo !== '' ? 'target="_blank" rel="noopener"' : '' ?> class="btn btn-lg sk-btn-wa px-8 py-4 fw-bold"><i class="bi bi-whatsapp me-2"></i>WhatsApp'tan Demo İste</a>
                        </div>
                        <ul class="sk-checks">
                            <li>Kurulum ve müşteri listenizin aktarımı ücretsiz</li>
                            <li>Kredi kartı yok, taahhüt yok</li>
                            <li>Telefon, tablet ve bilgisayardan çalışır</li>
                        </ul>
                    </div>
                    <div class="col-lg-6 animate-fade-in-up delay-100">
                        <div class="sk-board glass-card" aria-label="Örnek ekran: günlük montaj planı">
                            <div class="sk-board-head">
                                <span class="sk-dot"></span><span class="sk-dot"></span><span class="sk-dot"></span>
                                <span class="ms-3 text-gray-400 fs-7">Bugünkü randevular · örnek</span>
                            </div>
                            <div class="sk-row"><span class="sk-time">09:00</span><div><b>Ölçü</b> · Kartepe, A. Yılmaz<small>Ekip 1 · mutfak + banyo dolabı</small></div><span class="sk-tag sk-blue">Ölçü</span></div>
                            <div class="sk-row"><span class="sk-time">10:30</span><div><b>Montaj</b> · İzmit, Ş. Demir<small>Ekip 2 · üst dolap kapağı eksik</small></div><span class="sk-tag sk-red">1 eksik</span></div>
                            <div class="sk-row"><span class="sk-time">13:00</span><div><b>Tezgah</b> · Gebze, M. Kaya<small>Ekip 1 · granit tezgah montajı</small></div><span class="sk-tag sk-amber">Yolda</span></div>
                            <div class="sk-row"><span class="sk-time">16:00</span><div><b>Teslim</b> · Başiskele, E. Arslan<small>Kalan bakiye tahsil edilecek</small></div><span class="sk-tag sk-green">Tahsilat</span></div>
                            <div class="sk-foot"><span>Açık eksik malzeme: <b>3</b></span><span>Bugün tahsil edilecek: <b>4 iş</b></span></div>
                        </div>
                        <div class="text-gray-600 fs-8 mt-2 text-center">Görsel temsilidir; veriler örnektir.</div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Hero-->

        <!--begin::Pain-->
        <div class="py-15">
            <div class="container-xxl">
                <div class="text-center mb-12 scroll-reveal">
                    <h2 class="fs-2hx fw-bolder text-white mb-4">Tanıdık geliyor mu?</h2>
                    <p class="fs-5 text-gray-400 mx-auto" style="max-width:640px">Mutfak ve dolap firmalarıyla konuştuğumuzda en sık duyduğumuz dört sorun:</p>
                </div>
                <div class="row g-6">
                    <?php foreach ([
                        ['bi-calendar-x', 'Randevular dağınık', 'Ölçü ve montaj tarihleri defterde, telefonda ve WhatsApp gruplarında. Kim, nereye, ne zaman gidecek her sabah yeniden konuşuluyor.'],
                        ['bi-box-seam', 'Eksik parça unutuluyor', 'Montajda eksik çıkan kapak, menteşe ya da tezgah parçası kayda geçmiyor; müşteri tekrar arayana kadar kimse takip etmiyor.'],
                        ['bi-cash-coin', 'Kapora ve taksit takibi zor', 'Kapora, ara ödeme, çek ve senet ayrı ayrı yerlerde. Hangi müşterinin ne kadar borcu kaldığı anında bilinmiyor.'],
                        ['bi-people', 'Ekip ve şube kontrolü yok', 'Satış, imalat ve montaj aynı bilgiyi farklı yerden görüyor. Kim neyi değiştirdi belli değil.'],
                    ] as $p): ?>
                    <div class="col-md-6 col-lg-3 scroll-reveal">
                        <div class="glass-card p-8 h-100">
                            <div class="feature-icon-box text-danger mb-5"><i class="bi <?= $p[0] ?> fs-2x"></i></div>
                            <h3 class="text-white fs-4 fw-bold mb-3"><?= $p[1] ?></h3>
                            <p class="text-gray-400 fs-6 mb-0"><?= $p[2] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!--end::Pain-->

        <!--begin::Flow-->
        <div class="py-15 sk-flow-wrap">
            <div class="container-xxl">
                <div class="text-center mb-12 scroll-reveal">
                    <h2 class="fs-2hx fw-bolder text-white mb-4">Bir işin baştan sona yolculuğu</h2>
                    <p class="fs-5 text-gray-400">Her aşama aynı müşteri kaydında birikir; kimse bilgiyi yeniden yazmaz.</p>
                </div>
                <ol class="sk-flow">
                    <?php foreach ([
                        ['Müşteri ve sözleşme', 'Müşteri kartı, teklif ve sözleşme; kapora ve taksit planı.'],
                        ['Ölçü randevusu', 'Ekip ve saat atanır; durum rengiyle takvimde görünür.'],
                        ['İmalat', 'İmalathane panosu hangi işin hangi aşamada olduğunu gösterir.'],
                        ['Montaj', 'Ekiplerin günlük planı; eksik malzeme ve sorunlar kayda geçer.'],
                        ['Tahsilat ve cari', 'Nakit, kart, havale, çek; cari bakiye ve kasa anında güncellenir.'],
                    ] as $i => $a): ?>
                    <li class="scroll-reveal"><span class="sk-num"><?= $i + 1 ?></span><h3><?= $a[0] ?></h3><p><?= $a[1] ?></p></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <!--end::Flow-->

        <!--begin::Features-->
        <div class="py-15">
            <div class="container-xxl">
                <div class="text-center mb-12 scroll-reveal">
                    <h2 class="fs-2hx fw-bolder text-white mb-4">Sektörünüz için hazır ekranlar</h2>
                </div>
                <div class="row g-6">
                    <?php foreach ([
                        ['bi-calendar-week', 'primary', 'Randevu ve montaj takvimi', 'Ölçü, montaj, servis ve teslim randevuları; ekip ve şube bazında, durum renkleriyle.'],
                        ['bi-exclamation-diamond', 'danger', 'Eksik malzeme ve sorunlu iş', 'Montajda çıkan eksikler randevuya bağlanır; eksik özeti yazdırılır, sorunlar kapanana kadar izlenir.'],
                        ['bi-file-earmark-text', 'info', 'Sözleşme ve taksit', 'Sözleşme, kapora, ara ödeme ve taksit planı; vadesi gelenler tek listede.'],
                        ['bi-wallet2', 'success', 'Cari, kasa, banka ve çek', 'Müşteri cari bakiyesi, kasa ve banka hareketleri, çek ve senet takibi; birden fazla çeki birleştirme.'],
                        ['bi-person-badge', 'warning', 'Personel ve giderler', 'Personel kartları, prim ve gider kayıtları; giderler kasaya otomatik işlenir.'],
                        ['bi-shield-lock', 'primary', 'Şube ve kullanıcı yetkisi', 'Satış, muhasebe, imalat ve montaj ekipleri yalnızca kendi ekranlarını görür; her işlem kayıt altında.'],
                    ] as $m): ?>
                    <div class="col-md-6 col-lg-4 scroll-reveal">
                        <div class="glass-card p-8 h-100 feature-card">
                            <div class="feature-icon-box text-<?= $m[1] ?> mb-5"><i class="bi <?= $m[0] ?> fs-2x"></i></div>
                            <h3 class="text-white fs-4 fw-bold mb-3"><?= $m[2] ?></h3>
                            <p class="text-gray-400 fs-6 mb-0"><?= $m[3] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!--end::Features-->

        <!--begin::Offer-->
        <div class="py-15">
            <div class="container-xxl">
                <div class="sk-offer glass-card scroll-reveal">
                    <div class="row align-items-center g-8">
                        <div class="col-lg-7">
                            <span class="sk-badge sk-badge-green">Kampanya</span>
                            <h2 class="text-white fw-bolder fs-2x mt-5 mb-4">30 gün ücretsiz + ücretsiz kurulum</h2>
                            <p class="text-gray-400 fs-5 mb-0">Kurulumu biz yapıyoruz, mevcut müşteri ve cari listenizi Excel'den aktarıyoruz, ekibinize 30 dakikalık uzaktan eğitim veriyoruz. Kredi kartı istemiyoruz.</p>
                        </div>
                        <div class="col-lg-5">
                            <ol class="sk-steps">
                                <li><b>15 dakikalık demo</b><span>Görüntülü ya da telefonla, kendi işinize göre.</span></li>
                                <li><b>Kurulum ve aktarım</b><span>Excel listenizi gönderin, gerisini biz yapalım.</span></li>
                                <li><b>30 gün kullanın</b><span>Beğenirseniz paket seçin; beğenmezseniz hesap kapanır.</span></li>
                            </ol>
                            <div class="d-flex flex-wrap gap-3 mt-6">
                                <a href="<?= htmlspecialchars($kayitUrl) ?>" class="btn btn-primary-glow px-7 py-3 fw-bold">Hemen başla</a>
                                <a href="<?= htmlspecialchars($waDemoUrl) ?>" <?= $waNo !== '' ? 'target="_blank" rel="noopener"' : '' ?> class="btn sk-btn-wa px-7 py-3 fw-bold"><i class="bi bi-whatsapp me-2"></i>Demo iste</a>
                                                        </div>
                            <a href="assets/docs/Prolyn-Mutfak-Dolap-Banyo.pdf" target="_blank" rel="noopener" class="d-inline-block mt-4 text-gray-400 text-hover-primary fs-6"><i class="bi bi-file-earmark-pdf me-1"></i>Broşürü indir (PDF, 2 sayfa)</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Offer-->

        <!--begin::FAQ-->
        <div class="py-15">
            <div class="container-xxl">
                <div class="text-center mb-12"><h2 class="fs-2hx fw-bolder text-white">Sık sorulanlar</h2></div>
                <div class="row justify-content-center"><div class="col-lg-9">
                    <div class="accordion" id="skFaq">
                    <?php foreach ($sss as $i => $q): ?>
                        <div class="glass-card <?= $i < count($sss) - 1 ? 'mb-4 ' : '' ?>p-2">
                            <h3 class="accordion-header fs-5" id="skq<?= $i ?>">
                                <button class="accordion-button collapsed bg-transparent text-white fw-bold fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#ska<?= $i ?>"><?= htmlspecialchars($q[0]) ?></button>
                            </h3>
                            <div id="ska<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#skFaq">
                                <div class="accordion-body text-gray-400 fs-6 lh-lg"><?= htmlspecialchars($q[1]) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <p class="text-center text-gray-500 mt-8 mb-0">Diğer sektörlere uygun özellikler için <a href="moduller.php">Modüller</a>, paketler için <a href="pricing.php">Fiyatlandırma</a> sayfasına bakın.</p>
                </div></div>
            </div>
        </div>
        <!--end::FAQ-->

        <!--begin::CTA-->
        <div class="cta-section py-20">
            <div class="cta-pattern"></div>
            <div class="container-xxl position-relative z-index-2 text-center">
                <h2 class="text-white fw-bolder fs-2hx mb-5">İlk montaj planınızı bu hafta Prolyn'de yapın</h2>
                <p class="text-gray-400 fs-4 mb-10 mx-auto" style="max-width:640px">Kurulum ücretsiz, aktarım bizden. 30 gün sonunda devam edip etmemek size kalmış.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="<?= htmlspecialchars($kayitUrl) ?>" class="btn btn-primary-glow btn-lg px-10 py-4 fs-4">30 Gün Ücretsiz Dene</a>
                    <a href="<?= htmlspecialchars($waDemoUrl) ?>" <?= $waNo !== '' ? 'target="_blank" rel="noopener"' : '' ?> class="btn btn-lg sk-btn-wa px-10 py-4 fs-4"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
                </div>
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
                <a href="<?= htmlspecialchars($kayitUrl) ?>" class="btn btn-primary-glow py-3 fw-bold fs-5 shadow-sm">30 Gün Ücretsiz Dene</a>
                <a href="<?php echo htmlspecialchars($appPanelUrl); ?>" class="btn btn-outline btn-outline-dashed btn-outline-default btn-active-light-primary text-white fw-bold py-3 fs-5">Giriş Yap</a>
            </div>
        </div>
    </div>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

    <script src="assets/js/site.js?v=6"></script>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
