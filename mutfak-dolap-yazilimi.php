<?php
/**
 * Sektör tanıtım sayfası: mutfak, dolap ve banyo firmaları
 * Kampanya bağlantıları ?ref=L{lead_id} taşır; kayıt olunca lead eşleşir.
 */
require_once 'db.php';
// Paket ve modül verisi site genelinde TEK KAYNAKTAN gelir (fiyat sayfası, ana sayfa, bu sayfa aynı rakamı gösterir)
require_once __DIR__ . '/partials/paket_veri.php';
require_once __DIR__ . '/partials/modul_icerik.php';
$packages = prolynweb_paketler($pdo);
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

// Kayıt bağlantısı: seçilen paket + yıllık periyot + kampanya kodu (lead eşleşmesi korunur)
$planUrl = function (string $kod) use ($leadRef): string {
    return 'register.php?plan=' . rawurlencode($kod) . '&periyot=yillik' . ($leadRef !== '' ? '&ref=' . rawurlencode($leadRef) : '');
};

// Mutfak/dolap firmasının günlük işi için gereken çekirdek modüller; bunların tamamını içeren en ucuz paket "önerilen" olur
$sektorAnahtarlar = ['appointment', 'contract', 'current_account', 'accounting'];
$onerilenKod = '';
foreach ($packages as $pk) {
    if (prolynweb_pakette_var($pk, $sektorAnahtarlar) === 'tam') { $onerilenKod = $pk['code']; break; }
}
if ($onerilenKod === '' && !empty($packages)) { $onerilenKod = $packages[min(1, count($packages) - 1)]['code']; }
// Sektör için hangi modüller pakette yok? (kartta dürüstçe gösterilir)
$sektorModulAdi = ['accounting' => 'Kasa, çek ve personel gideri', 'planning' => 'Planlama ve imalat', 'bank' => 'Banka', 'contract' => 'Sözleşme', 'current_account' => 'Cari takip', 'appointment' => 'Randevu'];
$enUcuzAylik = !empty($packages) ? (float)$packages[0]['price_monthly'] : 0;
$destekAd = ['email' => 'E-posta desteği', 'priority' => 'Öncelikli destek', 'dedicated' => 'Özel destek'];

$sss = [
    ['Prolyn mutfak ve dolap firmaları için uygun mu?', 'Evet. Prolyn; ölçü ve montaj randevuları, montaj ekiplerinin planı, eksik malzeme takibi, sözleşme, taksit, çek ve cari hesap gibi mutfak, dolap ve banyo firmalarının günlük işlerini tek panelde toplar.'],
    ['Ekiplerim telefondan kullanabilir mi?', 'Prolyn web tabanlıdır; bilgisayar, tablet ve telefon tarayıcısından kurulum gerektirmeden açılır. Her personel kendi kullanıcısıyla, yalnızca yetkili olduğu ekranları görür.'],
    ['Kurulum ve verilerin aktarılması ne kadar sürer?', 'Kampanya süresince kurulumu ve mevcut müşteri ile cari listenizin aktarımını ücretsiz biz yapıyoruz. Elinizdeki Excel listesini göndermeniz yeterli; çoğu firma aynı gün kullanmaya başlıyor.'],
    ['Stok takibi var mı?', 'Ürün kartları ve montajda eksik çıkan malzemenin takibi bugün kullanılabilir. Ayrıntılı depo stok modülü geliştirme aşamasında ve yakında açılacak.'],
    ['Verilerim güvende mi?', 'Her firma kendi ayrı veritabanında çalışır; verileriniz başka bir firmayla aynı tabloyu paylaşmaz. Bağlantılar şifrelidir ve kullanıcı yetkilerini siz belirlersiniz.'],
    ['30 günlük deneme bitince ne olur?', 'Deneme süresince kredi kartı istemiyoruz. Süre sonunda dilerseniz size uygun paketle devam edersiniz, dilerseniz hesabınız kapanır; ek bir ücret çıkmaz.'],
    ['Hangi paketi almalıyım?', 'Ölçü ve montaj randevusu, sözleşme, taksit, kasa ve çek takibini birlikte kullanacak çoğu mutfak ve dolap firması için önerdiğimiz paket işaretlidir. Yalnızca randevu, sözleşme ve cari takibi isteyen küçük ekipler için ilk paket yeterlidir. Planlama ve imalat panolarını da kullanacaksanız en üst paket gerekir. Yanlış seçerseniz ek kurulum gerekmeden sonradan yükseltebilirsiniz.'],
    ['Fiyatlara KDV dahil mi, yıllık ödemede indirim var mı?', 'Fiyatlara %20 KDV dahildir. Yıllık peşin ödemede paketin aylık fiyatına göre indirim uygulanır; tutarlar yukarıdaki paket kartlarında yıllık ve aylık karşılığıyla birlikte görünür.'],
];
$base = rtrim((string)prolynweb_env('BASE_URL', 'https://prolyn.net'), '/');
$canonical = $base . '/mutfak-dolap-yazilimi';
$sayfaBaslik = 'Mutfak, Dolap ve Banyo Firmaları için İş Takip Programı | Prolyn';
$sayfaAciklama = 'Ölçü ve montaj randevuları, montaj ekipleri, eksik malzeme, sözleşme, taksit, çek ve cari takibi tek ekranda. 30 gün ücretsiz deneme, ücretsiz kurulum ve veri aktarımı.';
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/partials/tema_head.php'; ?>
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
             // Teklif: paket fiyatlarından (aylık, KDV dahil) en düşük/en yüksek; deneme bilgisi açıklamada
             'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'TRY',
                 'lowPrice' => (string)(!empty($packages) ? min(array_column($packages, 'price_monthly')) : 0),
                 'highPrice' => (string)(!empty($packages) ? max(array_column($packages, 'price_monthly')) : 0),
                 'offerCount' => count($packages),
                 'description' => 'Aylık fiyat aralığı. 30 gün ücretsiz deneme, ücretsiz kurulum ve veri aktarımı']],
            ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $sss)],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=12" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=2" rel="stylesheet" type="text/css" />
    <link href="assets/css/modul.css?v=1" rel="stylesheet" type="text/css" />
    <link href="assets/css/sektor.css?v=1" rel="stylesheet" type="text/css" />
    <style>
/* v2: fiyat satırı ve paket kartı notu (paketler bölümü modul.css'teki .pk-* sınıflarını kullanır) */
.sk-fiyatsatir{margin:14px 0 0;color:#aab3c8;font-size:14px}
.sk-fiyatsatir b{color:#fff}
.sk-fiyatsatir a{text-decoration:underline;color:#9db8ff}
html[data-tema="light"] .sk-fiyatsatir{color:var(--pw-muted)}
html[data-tema="light"] .sk-fiyatsatir b{color:var(--pw-ink)}
html[data-tema="light"] .sk-fiyatsatir a{color:var(--pw-link)}
.pk-not.sk-yok{margin:-.6rem 0 1.2rem;color:var(--pw-warn)}

/* modul.css'e dokunmadan: tablo hücrelerindeki gizli metinler sayfayı yatay taşırmasın */
.pk-tablo-sar{position:relative}
    
/* ---- "Tanıdık geliyor mu?" hareketli çizimler: önce dağınık, sonra düzenli (9 sn döngü) ---- */
.sk-anim{position:relative;aspect-ratio:240/146;padding-top:26px;border-radius:14px;margin-bottom:20px;overflow:hidden;background:var(--pw-surface-2,#f8fafd);border:1px solid var(--pw-line,#dce3ef)}
.sk-anim svg{display:block;width:100%;height:100%}
html[data-tema="dark"] .sk-anim{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08)}
.sk-anim{--ok:#12a37f;--bad:#d94a6a;--amb:#d98a0b;--blu:#2b7fe0;--pur:#7b5be0;--ink:#7a869d}
.sk-anim .sf{fill:var(--pw-surface,#fff);stroke:var(--pw-line,#dce3ef);stroke-width:1.2}
html[data-tema="dark"] .sk-anim .sf{fill:#182137;stroke:#2c3858}
.sk-anim .gr{fill:var(--pw-line,#dce3ef)}
html[data-tema="dark"] .sk-anim .gr{fill:#2c3858}
.sk-anim text{font:700 7.5px Inter,system-ui,sans-serif;fill:var(--pw-ink-2,#2a3856)}
html[data-tema="dark"] .sk-anim text{fill:#cfd8ec}
.sk-anim :is(.it,.fly,.bar,.fade-in,.fade-out,.draw,.pop,.fillok,.dotbad,.dotok){transform-box:fill-box;transform-origin:center;animation-duration:9s;animation-iteration-count:infinite;animation-timing-function:ease-in-out;animation-fill-mode:both;animation-play-state:paused}
.sk-anim.on :is(.it,.fly,.bar,.fade-in,.fade-out,.draw,.pop,.fillok,.dotbad,.dotok){animation-play-state:running}
/* dağınık -> düzenli; her öğe kendi --x0/--y0/--r0 (önce) ve --x1/--y1 (sonra) değerlerini taşır */
.sk-anim .it{transform:translate(var(--x1),var(--y1));animation-name:sk-move;animation-delay:calc(var(--d,0) * .09s)}
@keyframes sk-move{0%,26%{transform:translate(var(--x0),var(--y0)) rotate(var(--r0))}46%,88%{transform:translate(var(--x1),var(--y1)) rotate(0deg)}100%{transform:translate(var(--x0),var(--y0)) rotate(var(--r0))}}
.sk-anim .pop{animation-name:sk-pop}
@keyframes sk-pop{0%,44%{opacity:0;transform:scale(.3)}54%{opacity:1;transform:scale(1.18)}60%,90%{opacity:1;transform:scale(1)}100%{opacity:0;transform:scale(.3)}}
.sk-anim .fade-in{animation-name:sk-in}
@keyframes sk-in{0%,40%{opacity:0}54%,90%{opacity:1}100%{opacity:0}}
.sk-anim .fade-out{animation-name:sk-out}
@keyframes sk-out{0%,24%{opacity:1}40%,92%{opacity:0}100%{opacity:1}}
.sk-anim .fly{animation-name:sk-fly}
@keyframes sk-fly{0%,24%{transform:translate(80px,-56px) rotate(24deg);opacity:0}34%{opacity:1}52%,88%{transform:translate(0,0) rotate(0deg);opacity:1}97%{opacity:1}100%{transform:translate(80px,-56px) rotate(24deg);opacity:0}}
.sk-anim .fillok{animation-name:sk-fillok}
@keyframes sk-fillok{0%,50%{fill:rgba(217,74,106,.2);stroke:#d94a6a}58%,92%{fill:rgba(18,163,127,.22);stroke:#12a37f}100%{fill:rgba(217,74,106,.2);stroke:#d94a6a}}
.sk-anim .dotbad{animation-name:sk-out}
.sk-anim .dotok{animation-name:sk-in}
.sk-anim .draw{stroke-dasharray:1;animation-name:sk-draw}
@keyframes sk-draw{0%,38%{stroke-dashoffset:1;opacity:1}56%,90%{stroke-dashoffset:0;opacity:1}98%{stroke-dashoffset:0;opacity:0}100%{stroke-dashoffset:1;opacity:0}}
.sk-anim .bar{transform-origin:left center;animation-name:sk-bar}
@keyframes sk-bar{0%,44%{transform:scaleX(0)}62%,90%{transform:scaleX(1)}100%{transform:scaleX(0)}}
.sk-anim .q{animation:sk-q 9s ease-in-out infinite both paused;transform-box:fill-box;transform-origin:center}
.sk-anim.on .q{animation-play-state:running}
@keyframes sk-q{0%,8%{opacity:.35;transform:scale(1)}14%{opacity:1;transform:scale(1.25)}20%,26%{opacity:.35;transform:scale(1)}40%,92%{opacity:0}100%{opacity:.35}}
.sk-lbl{position:absolute;top:7px;right:8px;height:18px;min-width:50px;font:800 9.5px/18px Inter,system-ui,sans-serif;letter-spacing:.05em;text-transform:uppercase}
.sk-lbl i{position:absolute;right:0;font-style:normal;padding:0 8px;border-radius:99px;white-space:nowrap;animation-duration:9s;animation-iteration-count:infinite;animation-fill-mode:both;animation-play-state:paused}
.sk-anim.on .sk-lbl i{animation-play-state:running}
.sk-lbl .o{color:#b02a37;background:rgba(217,74,106,.14);animation-name:sk-out}
.sk-lbl .s{color:#0c7a5e;background:rgba(18,163,127,.16);animation-name:sk-in}
@media (prefers-reduced-motion: reduce){
  .sk-anim *{animation:none!important}
  .sk-anim .fly{transform:none;opacity:1}.sk-anim .fillok{fill:rgba(18,163,127,.22);stroke:#12a37f}
  .sk-anim .fade-out,.sk-anim .dotbad,.sk-anim .q,.sk-lbl .o{opacity:0}.sk-anim .fade-in,.sk-anim .dotok,.sk-anim .pop{opacity:1}
  .sk-anim .draw{stroke-dashoffset:0}.sk-anim .bar{transform:none}
}
    
/* ---- "Bir işin yolculuğu": adımlar sırayla yanar, iş bir sonraki adıma akar (12 sn döngü) ---- */
.sk-flow{--fl-bd:var(--pw-line,#dce3ef);--fl-cn:rgba(102,126,234,.28);--fl-ok:#12a37f;--fl-pri:var(--pw-primary,#667eea)}
html[data-tema="dark"] .sk-flow{--fl-bd:rgba(255,255,255,.07)}
.sk-flow li{padding-right:62px}
.sk-fic{position:absolute;top:16px;right:14px;width:36px;height:36px;color:var(--fl-pri);opacity:.5;overflow:visible}
.sk-fic .mv{transform-box:fill-box;transform-origin:center}
.sk-flow :is(li,.sk-num,.sk-fic,.mv),.sk-flow li::after{animation-duration:12s;animation-iteration-count:infinite;animation-timing-function:ease-in-out;animation-fill-mode:both}
.sk-flow:not(.on) :is(li,.sk-num,.sk-fic,.mv),.sk-flow:not(.on) li::after{animation-play-state:paused}
@keyframes sk-fl1{0%,3%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}4%{border-color:var(--fl-pri);box-shadow:0 8px 22px rgba(47,91,234,.2);transform:translateY(-5px)}13%,93%{border-color:rgba(18,163,127,.55);box-shadow:none;transform:translateY(0)}100%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}}
@keyframes sk-nm1{0%,3%{background-color:#667eea;transform:scale(1)}4%{background-color:var(--fl-pri);transform:scale(1.22)}13%,93%{background-color:var(--fl-ok);transform:scale(1)}100%{background-color:#667eea;transform:scale(1)}}
@keyframes sk-ic1{0%,3%{opacity:.5;color:var(--fl-pri)}4%{opacity:1;color:var(--fl-pri)}13%,93%{opacity:1;color:var(--fl-ok)}100%{opacity:.5;color:var(--fl-pri)}}
@keyframes sk-cn1{0%,7%{background-color:var(--fl-cn)}8%{background-color:var(--fl-pri)}17%,93%{background-color:var(--fl-ok)}100%{background-color:var(--fl-cn)}}
.sk-flow li:nth-child(1){animation-name:sk-fl1}.sk-flow li:nth-child(1) .sk-num{animation-name:sk-nm1}.sk-flow li:nth-child(1) .sk-fic{animation-name:sk-ic1}.sk-flow li:nth-child(1)::after{animation-name:sk-cn1}
@keyframes sk-fl2{0%,19%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}20%{border-color:var(--fl-pri);box-shadow:0 8px 22px rgba(47,91,234,.2);transform:translateY(-5px)}29%,93%{border-color:rgba(18,163,127,.55);box-shadow:none;transform:translateY(0)}100%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}}
@keyframes sk-nm2{0%,19%{background-color:#667eea;transform:scale(1)}20%{background-color:var(--fl-pri);transform:scale(1.22)}29%,93%{background-color:var(--fl-ok);transform:scale(1)}100%{background-color:#667eea;transform:scale(1)}}
@keyframes sk-ic2{0%,19%{opacity:.5;color:var(--fl-pri)}20%{opacity:1;color:var(--fl-pri)}29%,93%{opacity:1;color:var(--fl-ok)}100%{opacity:.5;color:var(--fl-pri)}}
@keyframes sk-cn2{0%,23%{background-color:var(--fl-cn)}24%{background-color:var(--fl-pri)}33%,93%{background-color:var(--fl-ok)}100%{background-color:var(--fl-cn)}}
.sk-flow li:nth-child(2){animation-name:sk-fl2}.sk-flow li:nth-child(2) .sk-num{animation-name:sk-nm2}.sk-flow li:nth-child(2) .sk-fic{animation-name:sk-ic2}.sk-flow li:nth-child(2)::after{animation-name:sk-cn2}
@keyframes sk-fl3{0%,35%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}36%{border-color:var(--fl-pri);box-shadow:0 8px 22px rgba(47,91,234,.2);transform:translateY(-5px)}45%,93%{border-color:rgba(18,163,127,.55);box-shadow:none;transform:translateY(0)}100%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}}
@keyframes sk-nm3{0%,35%{background-color:#667eea;transform:scale(1)}36%{background-color:var(--fl-pri);transform:scale(1.22)}45%,93%{background-color:var(--fl-ok);transform:scale(1)}100%{background-color:#667eea;transform:scale(1)}}
@keyframes sk-ic3{0%,35%{opacity:.5;color:var(--fl-pri)}36%{opacity:1;color:var(--fl-pri)}45%,93%{opacity:1;color:var(--fl-ok)}100%{opacity:.5;color:var(--fl-pri)}}
@keyframes sk-cn3{0%,39%{background-color:var(--fl-cn)}40%{background-color:var(--fl-pri)}49%,93%{background-color:var(--fl-ok)}100%{background-color:var(--fl-cn)}}
.sk-flow li:nth-child(3){animation-name:sk-fl3}.sk-flow li:nth-child(3) .sk-num{animation-name:sk-nm3}.sk-flow li:nth-child(3) .sk-fic{animation-name:sk-ic3}.sk-flow li:nth-child(3)::after{animation-name:sk-cn3}
@keyframes sk-fl4{0%,51%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}52%{border-color:var(--fl-pri);box-shadow:0 8px 22px rgba(47,91,234,.2);transform:translateY(-5px)}61%,93%{border-color:rgba(18,163,127,.55);box-shadow:none;transform:translateY(0)}100%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}}
@keyframes sk-nm4{0%,51%{background-color:#667eea;transform:scale(1)}52%{background-color:var(--fl-pri);transform:scale(1.22)}61%,93%{background-color:var(--fl-ok);transform:scale(1)}100%{background-color:#667eea;transform:scale(1)}}
@keyframes sk-ic4{0%,51%{opacity:.5;color:var(--fl-pri)}52%{opacity:1;color:var(--fl-pri)}61%,93%{opacity:1;color:var(--fl-ok)}100%{opacity:.5;color:var(--fl-pri)}}
@keyframes sk-cn4{0%,55%{background-color:var(--fl-cn)}56%{background-color:var(--fl-pri)}65%,93%{background-color:var(--fl-ok)}100%{background-color:var(--fl-cn)}}
.sk-flow li:nth-child(4){animation-name:sk-fl4}.sk-flow li:nth-child(4) .sk-num{animation-name:sk-nm4}.sk-flow li:nth-child(4) .sk-fic{animation-name:sk-ic4}.sk-flow li:nth-child(4)::after{animation-name:sk-cn4}
@keyframes sk-fl5{0%,67%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}68%{border-color:var(--fl-pri);box-shadow:0 8px 22px rgba(47,91,234,.2);transform:translateY(-5px)}77%,93%{border-color:rgba(18,163,127,.55);box-shadow:none;transform:translateY(0)}100%{border-color:var(--fl-bd);box-shadow:none;transform:translateY(0)}}
@keyframes sk-nm5{0%,67%{background-color:#667eea;transform:scale(1)}68%{background-color:var(--fl-pri);transform:scale(1.22)}77%,93%{background-color:var(--fl-ok);transform:scale(1)}100%{background-color:#667eea;transform:scale(1)}}
@keyframes sk-ic5{0%,67%{opacity:.5;color:var(--fl-pri)}68%{opacity:1;color:var(--fl-pri)}77%,93%{opacity:1;color:var(--fl-ok)}100%{opacity:.5;color:var(--fl-pri)}}
@keyframes sk-cn5{0%,71%{background-color:var(--fl-cn)}72%{background-color:var(--fl-pri)}81%,93%{background-color:var(--fl-ok)}100%{background-color:var(--fl-cn)}}
.sk-flow li:nth-child(5){animation-name:sk-fl5}.sk-flow li:nth-child(5) .sk-num{animation-name:sk-nm5}.sk-flow li:nth-child(5) .sk-fic{animation-name:sk-ic5}.sk-flow li:nth-child(5)::after{animation-name:sk-cn5}
.sk-flow li:nth-child(1) .mv{stroke-dasharray:1;animation-name:sk-mv1}
@keyframes sk-mv1{0%,3%{stroke-dashoffset:1}10%{stroke-dashoffset:0}13%,93%{stroke-dashoffset:0}100%{stroke-dashoffset:1}}
.sk-flow li:nth-child(2) .mv{animation-name:sk-mv2}
@keyframes sk-mv2{0%,19%{transform:scale(0)}26%{transform:scale(1.35)}29%,93%{transform:scale(1)}100%{transform:scale(0)}}
.sk-flow li:nth-child(3) .mv{animation-name:sk-mv3}
@keyframes sk-mv3{0%,35%{transform:rotate(0deg)}42%{transform:rotate(90deg)}45%,93%{transform:rotate(90deg)}100%{transform:rotate(0deg)}}
.sk-flow li:nth-child(4) .mv{animation-name:sk-mv4}
@keyframes sk-mv4{0%,51%{transform:translateX(-14px);opacity:.2}58%{transform:translateX(0);opacity:1}61%,93%{transform:translateX(0);opacity:1}100%{transform:translateX(-14px);opacity:.2}}
.sk-flow li:nth-child(5) .mv{animation-name:sk-mv5}
@keyframes sk-mv5{0%,67%{transform:rotateY(0deg)}74%{transform:rotateY(360deg)}77%,93%{transform:rotateY(360deg)}100%{transform:rotateY(0deg)}}
@media (prefers-reduced-motion: reduce){.sk-flow :is(li,.sk-num,.sk-fic,.mv),.sk-flow li::after{animation:none!important}.sk-fic{opacity:1}.sk-flow .mv{stroke-dashoffset:0}}
@media (max-width:991px){.sk-flow li{padding-right:62px}}
    </style>
</head>
<body id="kt_body" class="app-blank">
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
<?php $navActive = ''; require __DIR__ . '/partials/site_header.php'; ?>

        
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
                        <?php if ($enUcuzAylik > 0): ?>
                        <p class="sk-fiyatsatir">Paketler aylık <b>₺<?= prolynweb_tl($enUcuzAylik) ?></b>'den başlar (KDV dahil). <a href="#paketler">Paketleri karşılaştır</a></p>
                        <?php endif; ?>
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
                    <?php
                    // Başlığa göre hareketli çizimler: önce dağınık, sonra Prolyn düzeni
                    $sorunCizim = [
                        <<<'SVG'
<div class="sk-anim" data-sk><span class="sk-lbl" aria-hidden="true"><i class="o">Önce</i><i class="s">Sonra</i></span><svg viewBox="0 0 240 120" aria-hidden="true" focusable="false"><g class="it" style="--x0:34px;--y0:60px;--r0:-8deg;--x1:12px;--y1:10px;--d:0"><rect class="sf" x="0" y="0" width="186" height="20" rx="6"/><rect x="0" y="0" width="5" height="20" rx="2.5" fill="#2b7fe0"/><rect x="12" y="5" width="26" height="10" rx="3" fill="#2b7fe0" opacity=".22"/><rect class="gr" x="46" y="6" width="70" height="3.2" rx="1.6"/><rect class="gr" x="46" y="12" width="44" height="3.2" rx="1.6"/></g><g class="it" style="--x0:2px;--y0:40px;--r0:6deg;--x1:12px;--y1:36px;--d:1"><rect class="sf" x="0" y="0" width="186" height="20" rx="6"/><rect x="0" y="0" width="5" height="20" rx="2.5" fill="#d98a0b"/><rect x="12" y="5" width="26" height="10" rx="3" fill="#d98a0b" opacity=".22"/><rect class="gr" x="46" y="6" width="64" height="3.2" rx="1.6"/><rect class="gr" x="46" y="12" width="52" height="3.2" rx="1.6"/></g><g class="it" style="--x0:30px;--y0:16px;--r0:9deg;--x1:12px;--y1:62px;--d:2"><rect class="sf" x="0" y="0" width="186" height="20" rx="6"/><rect x="0" y="0" width="5" height="20" rx="2.5" fill="#7b5be0"/><rect x="12" y="5" width="26" height="10" rx="3" fill="#7b5be0" opacity=".22"/><rect class="gr" x="46" y="6" width="58" height="3.2" rx="1.6"/><rect class="gr" x="46" y="12" width="60" height="3.2" rx="1.6"/></g><g class="it" style="--x0:18px;--y0:32px;--r0:-4deg;--x1:12px;--y1:88px;--d:3"><rect class="sf" x="0" y="0" width="186" height="20" rx="6"/><rect x="0" y="0" width="5" height="20" rx="2.5" fill="#12a37f"/><rect x="12" y="5" width="26" height="10" rx="3" fill="#12a37f" opacity=".22"/><rect class="gr" x="46" y="6" width="52" height="3.2" rx="1.6"/><rect class="gr" x="46" y="12" width="68" height="3.2" rx="1.6"/></g>
<g transform="translate(219 26)"><g class="pop"><circle r="11" fill="#12a37f"/><path d="M-5 0.5l3.6 3.6L5.5-3.4" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></g></g></svg></div>
SVG,
                        <<<'SVG'
<div class="sk-anim" data-sk><span class="sk-lbl" aria-hidden="true"><i class="o">Önce</i><i class="s">Sonra</i></span><svg viewBox="0 0 240 120" aria-hidden="true" focusable="false"><rect class="sf" x="14" y="14" width="30" height="40" rx="4"/><rect class="gr" x="36" y="31" width="3" height="8" rx="1.5"/><rect class="sf" x="50" y="14" width="30" height="40" rx="4"/><rect class="gr" x="72" y="31" width="3" height="8" rx="1.5"/><rect class="sf" x="86" y="14" width="30" height="40" rx="4"/><rect class="gr" x="108" y="31" width="3" height="8" rx="1.5"/><rect class="sf" x="14" y="60" width="30" height="40" rx="4"/><rect class="gr" x="36" y="77" width="3" height="8" rx="1.5"/><rect class="sf" x="50" y="60" width="30" height="40" rx="4"/><rect class="gr" x="72" y="77" width="3" height="8" rx="1.5"/>
<g class="fade-out"><rect x="86" y="60" width="30" height="40" rx="4" fill="rgba(217,74,106,.08)" stroke="#d94a6a" stroke-width="1.4" stroke-dasharray="4 3"/><text class="q" x="101" y="84" text-anchor="middle" style="font-size:15px;fill:#d94a6a">?</text></g>
<g class="fly"><rect class="fillok" x="86" y="60" width="30" height="40" rx="4" stroke-width="1.4"/><rect x="108" y="77" width="3" height="8" rx="1.5" fill="#12a37f"/></g>
<rect class="sf" x="134" y="16" width="94" height="22" rx="7"/><circle cx="146" cy="27" r="3.4" fill="#12a37f"/><text x="156" y="30">Gövde</text>
<rect class="sf" x="134" y="46" width="94" height="22" rx="7"/><circle cx="146" cy="57" r="3.4" fill="#12a37f"/><text x="156" y="60">Tezgah</text>
<rect class="sf" x="134" y="76" width="94" height="22" rx="7"/><text x="156" y="90">Kapak</text>
<circle class="dotbad" cx="146" cy="87" r="3.4" fill="#d94a6a"/>
<g class="dotok" transform="translate(146 87)"><circle r="5" fill="#12a37f"/><path d="M-2.4 .2l1.6 1.6L2.6-1.8" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></svg></div>
SVG,
                        <<<'SVG'
<div class="sk-anim" data-sk><span class="sk-lbl" aria-hidden="true"><i class="o">Önce</i><i class="s">Sonra</i></span><svg viewBox="0 0 240 120" aria-hidden="true" focusable="false"><g class="it" style="--x0:16px;--y0:56px;--r0:-16deg;--x1:10px;--y1:18px;--d:0"><rect class="sf" x="0" y="0" width="38" height="46" rx="6" style="stroke:#12a37f"/><circle cx="19" cy="15" r="8" fill="#12a37f" opacity=".95"/><text x="19" y="18" text-anchor="middle" style="fill:#fff;font-size:9px">₺</text><rect class="gr" x="7" y="31" width="24" height="3" rx="1.5"/><rect class="gr" x="7" y="37" width="15" height="3" rx="1.5"/></g><g class="it" style="--x0:80px;--y0:8px;--r0:12deg;--x1:56px;--y1:18px;--d:1"><rect class="sf" x="0" y="0" width="38" height="46" rx="6" style="stroke:#12a37f"/><circle cx="19" cy="15" r="8" fill="#12a37f" opacity=".95"/><text x="19" y="18" text-anchor="middle" style="fill:#fff;font-size:9px">₺</text><rect class="gr" x="7" y="31" width="24" height="3" rx="1.5"/><rect class="gr" x="7" y="37" width="15" height="3" rx="1.5"/></g><g class="it" style="--x0:124px;--y0:62px;--r0:-7deg;--x1:102px;--y1:18px;--d:2"><rect class="sf" x="0" y="0" width="38" height="46" rx="6"/><circle cx="19" cy="15" r="8" fill="#d98a0b" opacity=".85"/><text x="19" y="18" text-anchor="middle" style="fill:#fff;font-size:9px">₺</text><rect class="gr" x="7" y="31" width="24" height="3" rx="1.5"/><rect class="gr" x="7" y="37" width="15" height="3" rx="1.5"/></g><g class="it" style="--x0:176px;--y0:16px;--r0:15deg;--x1:148px;--y1:18px;--d:3"><rect class="sf" x="0" y="0" width="38" height="46" rx="6"/><circle cx="19" cy="15" r="8" fill="#d98a0b" opacity=".85"/><text x="19" y="18" text-anchor="middle" style="fill:#fff;font-size:9px">₺</text><rect class="gr" x="7" y="31" width="24" height="3" rx="1.5"/><rect class="gr" x="7" y="37" width="15" height="3" rx="1.5"/></g><g class="it" style="--x0:48px;--y0:30px;--r0:-5deg;--x1:194px;--y1:18px;--d:4"><rect class="sf" x="0" y="0" width="38" height="46" rx="6"/><circle cx="19" cy="15" r="8" fill="#d98a0b" opacity=".85"/><text x="19" y="18" text-anchor="middle" style="fill:#fff;font-size:9px">₺</text><rect class="gr" x="7" y="31" width="24" height="3" rx="1.5"/><rect class="gr" x="7" y="37" width="15" height="3" rx="1.5"/></g>
<g class="fade-in"><rect class="gr" x="10" y="86" width="218" height="8" rx="4"/></g>
<rect class="bar" x="10" y="86" width="92" height="8" rx="4" fill="#12a37f"/>
<g class="fade-in"><text x="10" y="108" style="font-size:7.5px">Kapora + 1 taksit alındı</text></g></svg></div>
SVG,
                        <<<'SVG'
<div class="sk-anim" data-sk><span class="sk-lbl" aria-hidden="true"><i class="o">Önce</i><i class="s">Sonra</i></span><svg viewBox="0 0 240 120" aria-hidden="true" focusable="false">
<g class="fade-out"><path d="M48 24 L168 24" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 24 L168 60" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 24 L168 96" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 60 L168 24" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 60 L168 60" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 60 L168 96" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 96 L168 24" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 96 L168 60" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/><path d="M48 96 L168 96" stroke="#d94a6a" stroke-width="1.3" fill="none" opacity=".5"/></g><path class="draw" pathLength="1" d="M48 24 L168 24" stroke="#2b7fe0" stroke-width="2" stroke-linecap="round" fill="none"/><path class="draw" pathLength="1" d="M48 60 L168 60" stroke="#12a37f" stroke-width="2" stroke-linecap="round" fill="none"/><path class="draw" pathLength="1" d="M48 96 L168 96" stroke="#e5683c" stroke-width="2" stroke-linecap="round" fill="none"/><g transform="translate(34 24)"><circle cy="-5" r="6" fill="#2b7fe0"/><path d="M-9 11a9 9 0 0118 0z" fill="#2b7fe0" opacity=".85"/></g><g transform="translate(34 60)"><circle cy="-5" r="6" fill="#12a37f"/><path d="M-9 11a9 9 0 0118 0z" fill="#12a37f" opacity=".85"/></g><g transform="translate(34 96)"><circle cy="-5" r="6" fill="#e5683c"/><path d="M-9 11a9 9 0 0118 0z" fill="#e5683c" opacity=".85"/></g><rect class="sf" x="168" y="13" width="62" height="22" rx="6"/><g class="fade-in"><text x="173" y="27">Satış</text><g transform="translate(224 24)"><rect x="-4" y="-1" width="8" height="6" rx="1.5" fill="#2b7fe0"/><path d="M-2.4-1v-2a2.4 2.4 0 014.8 0v2" fill="none" stroke="#2b7fe0" stroke-width="1.3"/></g></g><rect class="sf" x="168" y="49" width="62" height="22" rx="6"/><g class="fade-in"><text x="173" y="63">Muhasebe</text><g transform="translate(224 60)"><rect x="-4" y="-1" width="8" height="6" rx="1.5" fill="#12a37f"/><path d="M-2.4-1v-2a2.4 2.4 0 014.8 0v2" fill="none" stroke="#12a37f" stroke-width="1.3"/></g></g><rect class="sf" x="168" y="85" width="62" height="22" rx="6"/><g class="fade-in"><text x="173" y="99">Montaj</text><g transform="translate(224 96)"><rect x="-4" y="-1" width="8" height="6" rx="1.5" fill="#e5683c"/><path d="M-2.4-1v-2a2.4 2.4 0 014.8 0v2" fill="none" stroke="#e5683c" stroke-width="1.3"/></g></g></svg></div>
SVG,
                    ];
                    foreach ([
                        ['bi-calendar-x', 'Randevular dağınık', 'Ölçü ve montaj tarihleri defterde, telefonda ve WhatsApp gruplarında. Kim, nereye, ne zaman gidecek her sabah yeniden konuşuluyor.'],
                        ['bi-box-seam', 'Eksik parça unutuluyor', 'Montajda eksik çıkan kapak, menteşe ya da tezgah parçası kayda geçmiyor; müşteri tekrar arayana kadar kimse takip etmiyor.'],
                        ['bi-cash-coin', 'Kapora ve taksit takibi zor', 'Kapora, ara ödeme, çek ve senet ayrı ayrı yerlerde. Hangi müşterinin ne kadar borcu kaldığı anında bilinmiyor.'],
                        ['bi-people', 'Ekip ve şube kontrolü yok', 'Satış, imalat ve montaj aynı bilgiyi farklı yerden görüyor. Kim neyi değiştirdi belli değil.'],
                    ] as $pi => $p): ?>
                    <div class="col-md-6 col-lg-3 scroll-reveal">
                        <div class="glass-card p-8 h-100">
                            <?= $sorunCizim[$pi] ?>
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
                <ol class="sk-flow" data-sk>
                    <?php
                    // Adım simgeleri (süs amaçlı; ekran okuyuculardan gizli)
                    $akisIkon = [
                        <<<'SVG'
<svg class="sk-fic" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><rect x="7" y="4" width="22" height="28" rx="4" fill="none" stroke="currentColor" stroke-width="2"/><path class="mv" pathLength="1" d="M12 13h12M12 19h12M12 25h7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
SVG,
                        <<<'SVG'
<svg class="sk-fic" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><rect x="5" y="8" width="26" height="23" rx="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M5 15h26M12 4v6M24 4v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle class="mv" cx="18" cy="24" r="3.4" fill="currentColor"/></svg>
SVG,
                        <<<'SVG'
<svg class="sk-fic" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><g class="mv"><circle cx="18" cy="18" r="5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M18 4v5M18 27v5M4 18h5M27 18h5M8 8l3.5 3.5M24.5 24.5L28 28M28 8l-3.5 3.5M11.5 24.5L8 28" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></g></svg>
SVG,
                        <<<'SVG'
<svg class="sk-fic" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><path d="M4 30h28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><rect x="5" y="6" width="26" height="24" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M18 6v24" stroke="currentColor" stroke-width="1.6"/><rect class="mv" x="8" y="11" width="7" height="14" rx="1.5" fill="currentColor" opacity=".85"/></svg>
SVG,
                        <<<'SVG'
<svg class="sk-fic" viewBox="0 0 36 36" aria-hidden="true" focusable="false"><g class="mv"><circle cx="18" cy="18" r="13" fill="none" stroke="currentColor" stroke-width="2"/><text x="18" y="23.5" text-anchor="middle" style="font:800 16px Inter,system-ui,sans-serif;fill:currentColor">₺</text></g></svg>
SVG,
                    ];
                    foreach ([
                        ['Müşteri ve sözleşme', 'Müşteri kartı, teklif ve sözleşme; kapora ve taksit planı.'],
                        ['Ölçü randevusu', 'Ekip ve saat atanır; durum rengiyle takvimde görünür.'],
                        ['İmalat', 'İmalathane panosu hangi işin hangi aşamada olduğunu gösterir.'],
                        ['Montaj', 'Ekiplerin günlük planı; eksik malzeme ve sorunlar kayda geçer.'],
                        ['Tahsilat ve cari', 'Nakit, kart, havale, çek; cari bakiye ve kasa anında güncellenir.'],
                    ] as $i => $a): ?>
                    <li class="scroll-reveal"><?= $akisIkon[$i] ?><span class="sk-num"><?= $i + 1 ?></span><h3><?= $a[0] ?></h3><p><?= $a[1] ?></p></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <!--end::Flow-->

        <!--begin::Moduller-->
        <div class="py-15 pb-0">
            <div class="container-xxl">
                <div class="bolum-bas orta mb-0">
                    <h2 class="bolum-baslik">Sektörünüz için hazır ekranlar</h2>
                    <p class="bolum-giris">Aşağıdaki her bölüm, mutfak ve dolap firmasının günlük işinin bir parçasını karşılar. Hangi paketle açıldığı her bölümün altında yazar.</p>
                </div>
            </div>
        </div>
        <?php
        // İçerik ve çizimler ana sayfayla aynı kaynaktan gelir.
        // (partials/modul_bolumleri.php'ye dokunulmadığı için aynı işaretleme burada süzülerek yazılır.)
        // Ana sayfadaki tüm modüller (13) listelenir; sıra ve içerik modul_icerik.php'den gelir.
        $__moduller = prolynweb_modul_icerik();
        $__tik = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
        $__cizgi = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 12h12"/></svg>';
        ?>
        <div class="mod-nav-sar" aria-label="Modüller">
            <div class="container-xxl">
                <nav class="mod-nav" aria-label="Modüllere git">
                    <?php foreach ($__moduller as $m): ?>
                    <a href="#<?= $m['slug'] ?>" data-mod="<?= $m['slug'] ?>" style="--m:var(--m-<?= $m['renk'] ?>)"><i></i><?= htmlspecialchars($m['ad']) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
        <?php foreach ($__moduller as $m): ?>
        <section class="mod" id="<?= $m['slug'] ?>" style="--m:var(--m-<?= $m['renk'] ?>)" aria-labelledby="b-<?= $m['slug'] ?>">
            <div class="container-xxl">
                <div class="mod-kutu">
                    <figure class="ill mod-gorsel" aria-hidden="false">
                        <?php require __DIR__ . '/partials/ill/' . $m['slug'] . '.php'; ?>
                    </figure>
                    <div class="mod-metin">
                        <div class="mod-etiket"><i></i><?= htmlspecialchars($m['ad']) ?><?php if ($m['durum'] === 'kismen'): ?> <span class="mod-rozet">Bir kısmı yakında</span><?php endif; ?></div>
                        <h2 class="mod-baslik" id="b-<?= $m['slug'] ?>"><?= htmlspecialchars($m['baslik']) ?></h2>
                        <p class="mod-ozet"><?= htmlspecialchars($m['ozet']) ?></p>
                        <ul class="mod-maddeler">
                            <?php foreach ($m['maddeler'] as $md): ?>
                            <li><b><?= htmlspecialchars($md[0]) ?></b><span><?= htmlspecialchars($md[1]) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="mod-ornek"><b>Örneğin:</b> <?= htmlspecialchars($m['ornek']) ?></p>
                        <div class="mod-paket">
                            <span class="mod-paket-etiket">Hangi pakette?</span>
                            <?php foreach ($packages as $pk): $d = prolynweb_pakette_var($pk, $m['anahtarlar']); ?>
                            <span class="pk-d <?= $d ?>"><?= $d === 'yok' ? $__cizgi : $__tik ?><?= htmlspecialchars($pk['name']) ?><?= $d === 'kismen' ? ' (kısmen)' : ($d === 'yok' ? ' (yok)' : '') ?></span>
                            <?php endforeach; ?>
                            <a class="mod-link" href="moduller.php#<?= $m['slug'] ?>">Tüm özellikler</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endforeach; ?>
        <!--end::Moduller-->

        <!--begin::Paketler-->
        <div id="paketler" class="py-15 py-lg-20" style="background:var(--pw-bg-alt);scroll-margin-top:90px">
            <div class="container-xxl">
                <div class="bolum-bas orta">
                    <h2 class="bolum-baslik">Firmanız için hangi paket?</h2>
                    <p class="bolum-giris">Hepsinde aynı uygulama, ayrı veritabanı ve rol bazlı yetki vardır. Paketler kullanıcı, şube ve açılan modüllerde ayrışır. Seçtiğiniz paketi 30 gün ücretsiz kullanırsınız; beğenmezseniz hesap kapanır.</p>
                </div>
                <div class="pk-sar">
                    <?php foreach ($packages as $pk):
                        $one = ($pk['code'] === $onerilenKod);
                        $modSayi = count(array_diff($pk['modules'], ['core']));
                        // Sektörün çekirdek modüllerinden bu pakette bulunmayanlar
                        $yok = [];
                        foreach (['accounting', 'planning'] as $k) {
                            if (prolynweb_pakette_var($pk, [$k]) === 'yok') { $yok[] = $sektorModulAdi[$k]; }
                        }
                    ?>
                    <div class="pk-kart<?= $one ? ' one' : '' ?>">
                        <?php if ($one): ?><span class="pk-onerilen">Sektörünüz için önerilen</span><?php endif; ?>
                        <h3 class="pk-ad"><?= htmlspecialchars($pk['name']) ?></h3>
                        <p class="pk-acik"><?= htmlspecialchars($pk['description']) ?></p>
                        <div class="pk-fiyat"><span>₺</span><b><?= prolynweb_tl($pk['yearly_total']) ?></b><span>/ yıl</span></div>
                        <p class="pk-not">Aylık ≈ ₺<?= prolynweb_tl($pk['monthly_approx']) ?><?php if ($pk['yearly_discount'] > 0): ?> · <s>₺<?= prolynweb_tl($pk['list_yearly']) ?></s> yerine %<?= (int)$pk['yearly_discount'] ?> yıllık indirimle<?php endif; ?></p>
                        <ul class="pk-ozet">
                            <li><b><?= $pk['max_users'] > 0 ? (int)$pk['max_users'] : 'Sınırsız' ?></b>&nbsp;kullanıcı</li>
                            <li><b><?= $pk['max_branches'] > 0 ? (int)$pk['max_branches'] : 'Sınırsız' ?></b>&nbsp;şube</li>
                            <li><b><?= $modSayi ?></b>&nbsp;modül</li>
                            <li><?= htmlspecialchars($destekAd[$pk['support'] ?? 'email'] ?? 'Teknik destek') ?></li>
                        </ul>
                        <?php if ($yok): ?><p class="pk-not sk-yok">Bu pakette yok: <?= htmlspecialchars(implode(', ', $yok)) ?></p><?php endif; ?>
                        <a href="<?= htmlspecialchars($planUrl($pk['code'])) ?>" class="btn <?= $one ? 'btn-primary-glow' : 'btn-light-primary' ?> w-100 py-3 fw-bold">30 gün ücretsiz dene</a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h3 class="bolum-baslik mt-15 mb-6" style="font-size:1.6rem">Hangi pakette neler var?</h3>
                <?php require __DIR__ . '/partials/paket_matris.php'; ?>
                <p class="pk-alt-not">Fiyatlara %20 KDV dahildir. "Kısmen", o bölümün bir kısmının pakette bulunduğunu gösterir. Her modülün ayrıntısı <a href="moduller.php" style="color:var(--pw-link);text-decoration:underline">Modül Detayları</a>, ödeme seçenekleri <a href="pricing.php" style="color:var(--pw-link);text-decoration:underline">Fiyatlandırma</a> sayfasındadır.</p>
            </div>
        </div>
        <!--end::Paketler-->

        <!--begin::Offer-->
        <div class="py-15">
            <div class="container-xxl">
                <div class="sk-offer glass-card scroll-reveal">
                    <div class="row align-items-center g-8">
                        <div class="col-lg-7">
                            <span class="sk-badge sk-badge-green">Kampanya</span>
                            <h2 class="text-white fw-bolder fs-2x mt-5 mb-4">Satın almadan önce 30 gün kendi işinizle deneyin</h2>
                            <p class="text-gray-400 fs-5 mb-0">Kurulumu biz yapıyoruz, mevcut müşteri ve cari listenizi Excel'den aktarıyoruz, ekibinize 30 dakikalık uzaktan eğitim veriyoruz. Kredi kartı istemiyoruz.</p>
                        </div>
                        <div class="col-lg-5">
                            <ol class="sk-steps">
                                <li><b>15 dakikalık demo</b><span>Görüntülü ya da telefonla, kendi işinize göre.</span></li>
                                <li><b>Kurulum ve aktarım</b><span>Excel listenizi gönderin, gerisini biz yapalım.</span></li>
                                <li><b>30 gün kullanın, sonra devam edin</b><span>Beğenirseniz deneme bitince paketinizle devam edersiniz; beğenmezseniz hesap kapanır, ücret çıkmaz.</span></li>
                            </ol>
                            <div class="d-flex flex-wrap gap-3 mt-6">
                                <a href="#paketler" class="btn btn-primary-glow px-7 py-3 fw-bold">Paketi seç ve başla</a>
                                <a href="<?= htmlspecialchars($waDemoUrl) ?>" <?= $waNo !== '' ? 'target="_blank" rel="noopener"' : '' ?> class="btn sk-btn-wa px-7 py-3 fw-bold"><i class="bi bi-whatsapp me-2"></i>Demo iste</a>
                                                        </div>
                            <a href="assets/docs/Prolyn-Mutfak-Dolap-Banyo.pdf" target="_blank" rel="noopener" class="d-inline-block mt-4 text-gray-400 text-hover-primary fs-6"><i class="bi bi-file-earmark-pdf me-1"></i>Broşürü indir (PDF, 3 sayfa: özellikler, paketler, nasıl başlanır)</a>
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
                <h2 class="text-white fw-bolder fs-2hx mb-5">Bu ay kapora, taksit ve montaj takibini tek yerden yapın</h2>
                <p class="text-gray-400 fs-4 mb-10 mx-auto" style="max-width:640px">Kurulum ücretsiz, veri aktarımı bizden. Paketinizi seçin, 30 gün deneyin; devam edip etmemek size kalmış.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="<?= htmlspecialchars($onerilenKod !== '' ? $planUrl($onerilenKod) : $kayitUrl) ?>" class="btn btn-primary-glow btn-lg px-10 py-4 fs-4">Önerilen paketle 30 gün ücretsiz dene</a>
                    <a href="<?= htmlspecialchars($waDemoUrl) ?>" <?= $waNo !== '' ? 'target="_blank" rel="noopener"' : '' ?> class="btn btn-lg sk-btn-wa px-10 py-4 fs-4"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
                </div>
            </div>
        </div>
        <!--end::CTA-->

<?php require __DIR__ . '/partials/site_footer.php'; ?>
    </div>
    <!--end::Root-->

<?php require __DIR__ . '/partials/site_mobile_menu.php'; ?>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="p/dist/assets/plugins/global/plugins.bundle.js"></script>
    <script src="p/dist/assets/js/scripts.bundle.js"></script>
    <!--end::Global Javascript Bundle-->

    <script src="assets/js/site.js?v=6"></script>
    <script>
    // Hareketli çizimler yalnızca ekrana girince oynar (pil ve işlemci tasarrufu)
    (function () {
        var k = document.querySelectorAll('[data-sk]');
        if (!('IntersectionObserver' in window)) { k.forEach(function (e) { e.classList.add('on'); }); return; }
        var io = new IntersectionObserver(function (l) { l.forEach(function (e) { e.target.classList.toggle('on', e.isIntersecting); }); }, { threshold: 0.2 });
        k.forEach(function (e) { io.observe(e); });
    })();
    </script>
<?php require __DIR__ . '/partials/cookie_banner.php'; ?>
<?php require __DIR__ . '/partials/fab.php'; ?>
</body>
</html>
