<?php
/**
 * Prolyn Admin Panel - Lead detayı / yeni lead
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
lead_sema_kur($db);

$id = (int)($_GET['id'] ?? 0);
$yeni = $id === 0;
$flash = null;

function lead_post_alanlari(): array
{
    $tel = preg_replace('/[^\d+]/', '', (string)($_POST['telefon'] ?? ''));
    if ($tel !== '' && $tel[0] !== '+') {
        $d = preg_replace('/\D/', '', $tel);
        if (strlen($d) === 11 && $d[0] === '0') { $tel = '+9' . $d; }
        elseif (strlen($d) === 10) { $tel = '+90' . $d; }
        elseif (strlen($d) === 12 && substr($d, 0, 2) === '90') { $tel = '+' . $d; }
    }
    $eposta = strtolower(trim((string)($_POST['eposta'] ?? '')));
    if ($eposta !== '' && !filter_var($eposta, FILTER_VALIDATE_EMAIL)) { $eposta = ''; }
    return [
        'firma_adi' => trim((string)($_POST['firma_adi'] ?? '')),
        'yetkili' => trim((string)($_POST['yetkili'] ?? '')) ?: null,
        'telefon' => $tel ?: null,
        'whatsapp' => (str_starts_with($tel, '+905')) ? 1 : 0,
        'eposta' => $eposta ?: null,
        'web' => trim((string)($_POST['web'] ?? '')) ?: null,
        'il' => trim((string)($_POST['il'] ?? '')) ?: null,
        'ilce' => trim((string)($_POST['ilce'] ?? '')) ?: null,
        'segment' => trim((string)($_POST['segment'] ?? '')) ?: null,
        'oncelik' => in_array($_POST['oncelik'] ?? '', ['A', 'B', 'C'], true) ? $_POST['oncelik'] : 'B',
        'sonraki_takip' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['sonraki_takip'] ?? '')) ? $_POST['sonraki_takip'] : null,
        'notlar' => trim((string)($_POST['notlar'] ?? '')) ?: null,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $a = lead_post_alanlari();
        $durum = isset(LEAD_DURUMLAR[$_POST['durum'] ?? '']) ? $_POST['durum'] : 'yeni';
        if ($a['firma_adi'] === '') {
            $flash = ['danger', 'Firma adı zorunlu.'];
        } elseif ($yeni) {
            $token = lead_yeni_token();
            $db->Insert("INSERT INTO potansiyel_musteriler (firma_adi, yetkili, telefon, whatsapp, eposta, web, il, ilce, segment, oncelik, puan, sonraki_takip, notlar, durum, kaynak, ret_token)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'manuel', ?)",
                [$a['firma_adi'], $a['yetkili'], $a['telefon'], $a['whatsapp'], $a['eposta'], $a['web'], $a['il'], $a['ilce'], $a['segment'], $a['oncelik'],
                 ['A' => 80, 'B' => 60, 'C' => 40][$a['oncelik']], $a['sonraki_takip'], $a['notlar'], $durum, $token]);
            $id = (int)$db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE ret_token = ?", [$token]);
            if ($id > 0) {
                lead_aktivite($db, $id, 'sistem', 'Lead elle eklendi.');
                echo "<script>window.location.href='crm/lead_detail.php?id={$id}&ok=1';</script>";
                exit;
            }
            $flash = ['danger', 'Kayıt eklenemedi.'];
        } else {
            $eski = $db->getRowAssoc("SELECT durum FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);
            $db->Update("UPDATE potansiyel_musteriler SET firma_adi=?, yetkili=?, telefon=?, whatsapp=?, eposta=?, web=?, il=?, ilce=?, segment=?, oncelik=?, sonraki_takip=?, notlar=?, durum=?,
                         ret_tarihi = IF(? = 'ret' AND ret_tarihi IS NULL, NOW(), ret_tarihi) WHERE lead_id=?",
                [$a['firma_adi'], $a['yetkili'], $a['telefon'], $a['whatsapp'], $a['eposta'], $a['web'], $a['il'], $a['ilce'], $a['segment'], $a['oncelik'], $a['sonraki_takip'], $a['notlar'], $durum, $durum, $id]);
            if ($eski && $eski['durum'] !== $durum) { lead_aktivite($db, $id, 'durum', 'Durum: ' . LEAD_DURUMLAR[$durum][0]); }
            $flash = ['success', 'Kaydedildi.'];
        }
    }

    // Satış hattı çubuğundan tek tıkla durum değiştirme (liste sayfasıyla aynı kural)
    if ($action === 'durum' && !$yeni) {
        $durum = (string)($_POST['durum'] ?? '');
        if (isset(LEAD_DURUMLAR[$durum])) {
            $eski = $db->getRowAssoc("SELECT durum FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);
            if ($eski && $eski['durum'] !== $durum) {
                $db->Update("UPDATE potansiyel_musteriler SET durum = ?, ret_tarihi = IF(? = 'ret' AND ret_tarihi IS NULL, NOW(), ret_tarihi) WHERE lead_id = ?", [$durum, $durum, $id]);
                lead_aktivite($db, $id, 'durum', 'Durum: ' . LEAD_DURUMLAR[$durum][0]);
                $flash = ['success', 'Durum güncellendi: ' . LEAD_DURUMLAR[$durum][0] . '.'];
            }
        }
    }

    if ($action === 'note' && !$yeni) {
        $tur = in_array($_POST['tur'] ?? '', ['not', 'arama', 'ziyaret', 'whatsapp', 'eposta'], true) ? $_POST['tur'] : 'not';
        $metin = trim((string)($_POST['aciklama'] ?? ''));
        if ($metin !== '') {
            lead_aktivite($db, $id, $tur, $metin);
            if (in_array($tur, ['arama', 'ziyaret', 'whatsapp', 'eposta'], true)) {
                $db->Update("UPDATE potansiyel_musteriler SET son_iletisim = NOW(), durum = IF(durum='yeni','iletisime_gecildi',durum) WHERE lead_id = ?", [$id]);
            }
            $takip = (string)($_POST['takip'] ?? '');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $takip)) {
                $db->Update("UPDATE potansiyel_musteriler SET sonraki_takip = ? WHERE lead_id = ?", [$takip, $id]);
            }
            $flash = ['success', 'Not eklendi.'];
        }
    }

    if ($action === 'mail' && !$yeni) {
        $lead = $db->getRowAssoc("SELECT * FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);
        $sablon = $db->getRowAssoc("SELECT * FROM lead_sablonlari WHERE kod = ? AND kanal = 'eposta' AND aktif = 1", [(string)($_POST['sablon'] ?? '')]);
        [$ok, $neden] = $lead ? lead_gonderilebilir_mi($lead, 'eposta') : [false, 'kayıt yok'];
        $k = lead_mail_kurallari();
        if (!$sablon) { $flash = ['warning', 'Şablon seçin.']; }
        elseif (!$ok) { $flash = ['warning', 'Gönderilemez: ' . $neden . '.']; }
        elseif (lead_bugun_gonderilen_mail($db) >= $k['gunluk_limit']) { $flash = ['warning', 'Günlük kampanya e-postası sınırına ulaşıldı (' . $k['gunluk_limit'] . ').']; }
        elseif (lead_mail_kuyruga_ekle($db, $lead, $sablon, lead_gonderen_adi())) { $flash = ['success', 'E-posta kuyruğa eklendi; bir dakika içinde gönderilir.']; }
        else { $flash = ['danger', 'E-posta kuyruğa eklenemedi.']; }
    }
}

$lead = $yeni ? [
    'lead_id' => 0, 'firma_adi' => '', 'yetkili' => '', 'telefon' => '', 'whatsapp' => 0, 'eposta' => '', 'web' => '', 'il' => '', 'ilce' => '', 'adres' => '',
    'segment' => 'mutfak', 'oncelik' => 'B', 'puan' => 0, 'durum' => 'yeni', 'sonraki_takip' => '', 'notlar' => '', 'ret_tarihi' => null, 'ret_token' => '',
    'mail_sayisi' => 0, 'wa_sayisi' => 0, 'son_mail' => null, 'google_puan' => null, 'maps_url' => '', 'customer_id' => null, 'kaynak' => 'manuel',
] : $db->getRowAssoc("SELECT * FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);

if (!$lead) {
    require_once '../config/sidebar.php';
    echo '<div class="app-main flex-column flex-row-fluid"><div class="app-container container-xxl py-10"><div class="alert alert-warning">Kayıt bulunamadı. <a href="crm/leads.php">Listeye dön</a></div></div></div>';
    require_once '../config/footer.php';
    exit;
}
if (isset($_GET['ok'])) { $flash = ['success', 'Lead eklendi.']; }

$aktiviteler = $yeni ? [] : $db->getRowsAssoc("SELECT a.*, y.full_name FROM lead_aktiviteler a LEFT JOIN yoneticiler y ON y.admin_id = a.admin_id WHERE a.lead_id = ? ORDER BY a.aktivite_id DESC LIMIT 100", [$id]);
$waSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'whatsapp' AND aktif = 1 ORDER BY sira");
$mailSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'eposta' AND aktif = 1 ORDER BY sira");
[$waOk, $waNeden] = lead_gonderilebilir_mi($lead, 'whatsapp');
[$mailOk, $mailNeden] = lead_gonderilebilir_mi($lead, 'eposta');
$csrf = generateCSRFToken();
$turIkon = ['whatsapp' => ['ki-whatsapp', 'success'], 'eposta' => ['ki-sms', 'info'], 'arama' => ['ki-phone', 'primary'], 'ziyaret' => ['ki-geolocation', 'warning'],
            'durum' => ['ki-arrows-loop', 'dark'], 'not' => ['ki-notepad', 'secondary'], 'sistem' => ['ki-setting-2', 'secondary']];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

require_once '../config/sidebar.php';
?>
<?php
// ---- Görünüm için hazırlık (yalnızca gösterim; veri mantığı yukarıda) ----
$kapaliDurum = in_array($lead['durum'], LEAD_DURDUR, true);
[$takipYazi, $takipSinif, $takipTarih] = lead_takip_etiket($yeni ? null : ($lead['sonraki_takip'] ?: null), $kapaliDurum);
$hatAsamalari = ['yeni', 'iletisime_gecildi', 'ilgileniyor', 'demo', 'deneme', 'musteri'];
$mevcutAsama = array_search($lead['durum'], $hatAsamalari, true);   // olumsuz/ret ise false
$konum = trim(trim((string)$lead['ilce']) . ' / ' . trim((string)$lead['il']), ' /');
$turAd = ['whatsapp' => 'WhatsApp', 'eposta' => 'E-posta', 'arama' => 'Arama', 'ziyaret' => 'Ziyaret', 'durum' => 'Durum', 'not' => 'Not', 'sistem' => 'Sistem'];
$oncelikRenk = ['A' => 'success', 'B' => 'primary', 'C' => 'secondary'][$lead['oncelik']] ?? 'secondary';
?>
<style>
  /* Lead detay — Metronic değişkenleriyle; açık/koyu temada uyumlu */
  .ld-ozet { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:.75rem; }
  .ld-ozet > div { border:1px solid var(--bs-border-color, #f1f1f4); border-radius:.65rem; padding:.8rem 1rem; background:var(--bs-body-bg, #fff); }
  .ld-ozet .ld-k { font-size:.75rem; font-weight:600; color:var(--bs-gray-600); text-transform:uppercase; letter-spacing:.02em; }
  .ld-ozet .ld-v { font-size:1.15rem; font-weight:700; margin-top:.2rem; }
  @media (max-width: 767.98px) { .ld-ozet { grid-template-columns:repeat(2, minmax(0,1fr)); } }

  .ld-hat { display:grid; grid-template-columns:repeat(6, minmax(0,1fr)); gap:.4rem; }
  .ld-adim { width:100%; border:1px solid var(--bs-border-color, #f1f1f4); border-top:3px solid var(--bs-gray-300); background:var(--bs-body-bg, #fff); border-radius:.5rem; padding:.55rem .4rem; font-size:.78rem; font-weight:600; color:var(--bs-gray-600); text-align:center; cursor:pointer; line-height:1.2; }
  .ld-adim:hover { border-color:var(--bs-primary); color:var(--bs-primary); }
  .ld-adim.is-done { border-top-color:var(--bs-success); color:var(--bs-gray-800); }
  .ld-adim.is-now { border-color:var(--bs-primary); border-top-color:var(--bs-primary); background:var(--bs-primary-light, rgba(0,120,255,.07)); color:var(--bs-primary); }
  @media (max-width: 767.98px) { .ld-hat { grid-template-columns:repeat(3, minmax(0,1fr)); } }

  .ld-tur { display:flex; flex-wrap:wrap; gap:.4rem; }
  .ld-tur input { position:absolute; opacity:0; pointer-events:none; }
  .ld-tur label { padding:.3rem .8rem; border:1px solid var(--bs-border-color, #f1f1f4); border-radius:2rem; font-size:.82rem; font-weight:600; cursor:pointer; color:var(--bs-gray-700); }
  .ld-tur input:checked + label { background:var(--bs-primary); border-color:var(--bs-primary); color:#fff; }
  .ld-tur input:focus-visible + label { outline:2px solid var(--bs-primary); outline-offset:2px; }

  .ld-zaman { position:relative; padding-left:0; }
  .ld-zaman::before { content:""; position:absolute; left:17px; top:6px; bottom:6px; width:2px; background:var(--bs-gray-200); }
  .ld-olay { position:relative; display:flex; gap:.9rem; padding-bottom:1.25rem; }
  .ld-olay:last-child { padding-bottom:0; }
  .ld-olay .symbol { position:relative; z-index:1; }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
        <div class="page-title d-flex flex-column me-3">
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0"><?= $yeni ? 'Yeni Lead' : $e($lead['firma_adi']) ?></h1>
          <?php if (!$yeni): ?>
          <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
            <?= lead_durum_rozet($lead['durum']) ?>
            <span class="badge badge-light-<?= $oncelikRenk ?>">Öncelik <?= $e($lead['oncelik']) ?> · <?= (int)$lead['puan'] ?></span>
            <?php if ($lead['google_puan'] !== null): ?><span class="badge badge-light">★ <?= $e((string)$lead['google_puan']) ?></span><?php endif; ?>
            <?php if ($konum !== ''): ?><span class="text-muted fs-7 fw-semibold"><?= $e($konum) ?><?= $lead['segment'] ? ' · ' . $e($lead['segment']) : '' ?></span><?php endif; ?>
            <?php if ($lead['customer_id']): ?><a class="badge badge-light-success" href="crm/tenant_detail.php?id=<?= (int)$lead['customer_id'] ?>">Kurum #<?= (int)$lead['customer_id'] ?></a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <?php if (!$yeni && !empty($lead['maps_url'])): ?><a href="<?= $e($lead['maps_url']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light">Haritada aç</a><?php endif; ?>
          <a href="crm/leads.php" class="btn btn-sm btn-light">← Listeye dön</a>
        </div>
      </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-fluid">
        <?php if ($flash): ?><div class="alert alert-<?= $e($flash[0]) ?> p-4 mb-6 fw-semibold"><?= $e($flash[1]) ?></div><?php endif; ?>

        <?php if (!$yeni): ?>
        <!-- ÖZET + SATIŞ HATTI -->
        <?php if (!empty($lead['ret_tarihi'])): ?>
          <div class="alert alert-danger py-3 mb-6">Bu firma <?= date('d.m.Y', strtotime($lead['ret_tarihi'])) ?> tarihinde iletişim istemediğini bildirdi. Kampanya mesajı gönderilmez.</div>
        <?php endif; ?>
        <div class="ld-ozet mb-6">
          <div><div class="ld-k">Sonraki takip</div><div class="ld-v <?= $takipSinif ?>"><?= $e($takipYazi) ?></div><?php if ($takipTarih !== ''): ?><div class="text-muted fs-8"><?= $e($takipTarih) ?></div><?php endif; ?></div>
          <div><div class="ld-k">Son iletişim</div><div class="ld-v text-gray-900"><?= !empty($lead['son_iletisim']) ? date('d.m.Y', strtotime($lead['son_iletisim'])) : 'Hiç ulaşılmadı' ?></div></div>
          <div><div class="ld-k">WhatsApp / e-posta</div><div class="ld-v text-gray-900"><?= (int)$lead['wa_sayisi'] ?> / <?= (int)$lead['mail_sayisi'] ?></div><div class="text-muted fs-8">e-posta en fazla <?= (int)lead_mail_kurallari()['max_mail'] ?></div></div>
          <div><div class="ld-k">Kaynak</div><div class="ld-v text-gray-900"><?= $e($lead['kaynak']) ?></div></div>
        </div>

        <form method="post" action="crm/lead_detail.php?id=<?= $id ?>" class="card mb-6">
          <?= csrfTokenField() ?><input type="hidden" name="action" value="durum">
          <div class="card-body py-5">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <span class="fw-bold text-gray-800">Satış hattı <span class="text-muted fw-normal fs-8">— aşamaya tıklayarak ilerletin</span></span>
              <span class="d-flex gap-2">
                <?php foreach (['olumsuz', 'ret'] as $kod): if ($lead['durum'] === $kod) { continue; } ?>
                  <button name="durum" value="<?= $kod ?>" class="btn btn-sm btn-light-danger js-kapat" data-soru="Durum '<?= $e(LEAD_DURUMLAR[$kod][0]) ?>' olarak işaretlensin mi? Bu firmaya kampanya mesajı gönderilmez."><?= $e(LEAD_DURUMLAR[$kod][0]) ?></button>
                <?php endforeach; ?>
              </span>
            </div>
            <div class="ld-hat">
              <?php foreach ($hatAsamalari as $i => $kod):
                  $sinif = ($mevcutAsama !== false && $i < $mevcutAsama) ? 'is-done' : (($mevcutAsama !== false && $i === $mevcutAsama) ? 'is-now' : ''); ?>
                <button name="durum" value="<?= $kod ?>" class="ld-adim <?= $sinif ?>" <?= $sinif === 'is-now' ? 'aria-current="step"' : '' ?>><?= $e(LEAD_DURUMLAR[$kod][0]) ?></button>
              <?php endforeach; ?>
            </div>
            <?php if ($mevcutAsama === false): ?><div class="text-danger fs-7 mt-3">Şu an kapalı: <?= $e(LEAD_DURUMLAR[$lead['durum']][0]) ?>. Aşamaya tıklayarak durumu değiştirebilirsiniz (iletişim reddi kalıcıdır, kampanya mesajı yine gitmez).</div><?php endif; ?>
          </div>
        </form>
        <?php endif; ?>

        <div class="row g-6">
          <?php if (!$yeni): ?>
          <!-- SOL: ULAŞ + GEÇMİŞ -->
          <div class="col-xl-7">
            <div class="card mb-6">
              <div class="card-header"><h3 class="card-title fw-bold">Ulaş</h3></div>
              <div class="card-body">
                <div class="row g-6">
                  <div class="col-md-6">
                    <div class="fw-bold mb-2">WhatsApp <span class="text-muted fw-normal fs-8">(<?= (int)$lead['wa_sayisi'] ?> kez)</span></div>
                    <?php if ($waOk): ?>
                      <div class="d-flex flex-column gap-2">
                        <?php foreach ($waSablonlar as $s): ?>
                          <a class="btn btn-sm btn-light-success text-start" target="_blank" rel="noopener" href="crm/lead_wa.php?id=<?= $id ?>&s=<?= urlencode($s['kod']) ?>&k=<?= urlencode($csrf) ?>"><?= $e($s['ad']) ?></a>
                        <?php endforeach; ?>
                      </div>
                      <div class="text-muted fs-8 mt-2">Mesaj kendi WhatsApp'ınızda açılır, gönderimi siz yaparsınız. Not düşmeyi unutmayın.</div>
                    <?php else: ?><div class="text-muted fs-7">Gönderilemez: <?= $e($waNeden) ?></div><?php endif; ?>
                  </div>
                  <div class="col-md-6">
                    <div class="fw-bold mb-2">E-posta <span class="text-muted fw-normal fs-8">(<?= (int)$lead['mail_sayisi'] ?> kez<?= $lead['son_mail'] ? ', son ' . date('d.m.Y', strtotime($lead['son_mail'])) : '' ?>)</span></div>
                    <?php if ($mailOk): ?>
                      <form method="post" action="crm/lead_detail.php?id=<?= $id ?>" class="d-flex flex-column gap-2">
                        <?= csrfTokenField() ?><input type="hidden" name="action" value="mail">
                        <select name="sablon" class="form-select form-select-sm form-select-solid" aria-label="E-posta şablonu">
                          <?php foreach ($mailSablonlar as $s): ?><option value="<?= $e($s['kod']) ?>"><?= $e($s['ad']) ?></option><?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-light-info">Kuyruğa ekle</button>
                      </form>
                      <div class="text-muted fs-8 mt-2"><?= $e($lead['eposta']) ?></div>
                    <?php else: ?><div class="text-muted fs-7">Gönderilemez: <?= $e($mailNeden) ?></div><?php endif; ?>
                  </div>
                </div>
                <?php if (!empty($lead['telefon']) || !empty($lead['web'])): ?>
                <div class="separator my-5"></div>
                <div class="d-flex flex-wrap gap-2">
                  <?php if (!empty($lead['telefon'])): ?><a href="tel:<?= $e($lead['telefon']) ?>" class="btn btn-sm btn-light">Ara: <?= $e($lead['telefon']) ?></a><?php endif; ?>
                  <?php if (!empty($lead['web']) && preg_match('~^https?://~i', (string)$lead['web'])): ?><a href="<?= $e($lead['web']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light">Web sitesi</a><?php endif; ?>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="card">
              <div class="card-header"><h3 class="card-title fw-bold">Görüşme notu ve geçmiş</h3></div>
              <div class="card-body">
                <form method="post" action="crm/lead_detail.php?id=<?= $id ?>" class="mb-8">
                  <?= csrfTokenField() ?><input type="hidden" name="action" value="note">
                  <div class="ld-tur mb-3" role="radiogroup" aria-label="Kayıt türü">
                    <?php foreach (['not' => 'Not', 'arama' => 'Arama', 'whatsapp' => 'WhatsApp yanıtı', 'eposta' => 'E-posta yanıtı', 'ziyaret' => 'Ziyaret'] as $kod => $ad): ?>
                      <span><input type="radio" name="tur" id="tur_<?= $kod ?>" value="<?= $kod ?>" <?= $kod === 'not' ? 'checked' : '' ?>><label for="tur_<?= $kod ?>"><?= $e($ad) ?></label></span>
                    <?php endforeach; ?>
                  </div>
                  <textarea name="aciklama" rows="3" class="form-control form-control-solid mb-3" placeholder="Ne konuşuldu, ne dedi, sıradaki adım ne?" required></textarea>
                  <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted fs-8 fw-semibold">Tekrar ara:</span>
                    <?php foreach ([1 => 'Yarın', 3 => '3 gün', 7 => '1 hafta', 14 => '2 hafta'] as $gun => $ad): ?>
                      <button type="button" class="btn btn-sm btn-light js-takip" data-gun="<?= $gun ?>"><?= $e($ad) ?></button>
                    <?php endforeach; ?>
                    <input type="date" name="takip" id="ldTakip" class="form-control form-control-sm form-control-solid w-150px" title="Sonraki takip (isteğe bağlı)" aria-label="Sonraki takip tarihi">
                    <button class="btn btn-sm btn-primary ms-auto">Kaydet</button>
                  </div>
                </form>

                <?php if (!$aktiviteler): ?><div class="text-muted">Henüz kayıt yok.</div><?php endif; ?>
                <div class="ld-zaman">
                <?php foreach ($aktiviteler as $a): [$ik, $rk] = $turIkon[$a['tur']] ?? ['ki-notepad', 'secondary']; ?>
                  <div class="ld-olay">
                    <span class="symbol symbol-35px flex-shrink-0"><span class="symbol-label bg-light-<?= $rk ?>"><i class="ki-duotone <?= $ik ?> fs-3 text-<?= $rk ?>"><span class="path1"></span><span class="path2"></span></i></span></span>
                    <div>
                      <div class="fs-8 fw-bold text-uppercase text-<?= $rk === 'secondary' ? 'muted' : $rk ?>"><?= $e($turAd[$a['tur']] ?? $a['tur']) ?></div>
                      <div class="fw-semibold text-gray-800" style="white-space:pre-line"><?= $e($a['aciklama']) ?></div>
                      <div class="text-muted fs-8"><?= date('d.m.Y H:i', strtotime($a['created_at'])) ?><?= $a['full_name'] ? ' · ' . $e($a['full_name']) : '' ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- SAĞ: FİRMA BİLGİLERİ (yeni kayıtta tek sütun) -->
          <div class="<?= $yeni ? 'col-xl-8' : 'col-xl-5' ?>">
            <form method="post" action="crm/lead_detail.php?<?= $yeni ? 'yeni=1' : 'id=' . $id ?>" class="card mb-6">
              <?= csrfTokenField() ?>
              <input type="hidden" name="action" value="save">
              <div class="card-header"><h3 class="card-title fw-bold">Firma bilgileri</h3></div>
              <div class="card-body row g-5">
                <div class="col-md-8"><label class="form-label required">Firma adı</label><input name="firma_adi" class="form-control form-control-solid" value="<?= $e($lead['firma_adi']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Yetkili</label><input name="yetkili" class="form-control form-control-solid" value="<?= $e($lead['yetkili']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Telefon</label><input name="telefon" class="form-control form-control-solid" value="<?= $e($lead['telefon']) ?>" placeholder="0532 000 00 00"></div>
                <div class="col-md-6"><label class="form-label">E-posta</label><input name="eposta" type="email" class="form-control form-control-solid" value="<?= $e($lead['eposta']) ?>"></div>
                <div class="col-12"><label class="form-label">Web / Instagram</label><input name="web" class="form-control form-control-solid" value="<?= $e($lead['web']) ?>"></div>
                <div class="col-md-6"><label class="form-label">İl</label><input name="il" class="form-control form-control-solid" value="<?= $e($lead['il']) ?>"></div>
                <div class="col-md-6"><label class="form-label">İlçe</label><input name="ilce" class="form-control form-control-solid" value="<?= $e($lead['ilce']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Segment</label>
                  <select name="segment" class="form-select form-select-solid">
                    <?php foreach (['mutfak', 'banyo', 'dolap', 'endüstriyel mutfak', 'diğer'] as $s): ?><option <?= $lead['segment'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                  </select></div>
                <div class="col-md-6"><label class="form-label">Öncelik</label>
                  <select name="oncelik" class="form-select form-select-solid"><?php foreach (['A', 'B', 'C'] as $o): ?><option <?= $lead['oncelik'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Durum</label>
                  <select name="durum" class="form-select form-select-solid"><?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>" <?= $lead['durum'] === $kod ? 'selected' : '' ?>><?= $e($d[0]) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Sonraki takip</label><input type="date" name="sonraki_takip" class="form-control form-control-solid" value="<?= $e($lead['sonraki_takip']) ?>"></div>
                <div class="col-12"><label class="form-label">Genel notlar</label><textarea name="notlar" rows="3" class="form-control form-control-solid"><?= $e($lead['notlar']) ?></textarea></div>
                <?php if (!$yeni && $lead['adres']): ?><div class="col-12 text-muted fs-7"><?= $e($lead['adres']) ?></div><?php endif; ?>
              </div>
              <div class="card-footer d-flex justify-content-end"><button class="btn btn-primary">Kaydet</button></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
<script>
(function () {
  // "Tekrar ara" kısayolları: bugünden itibaren N gün sonrasını tarih alanına yazar
  var alan = document.getElementById('ldTakip');
  document.querySelectorAll('.js-takip').forEach(function (b) {
    b.addEventListener('click', function () {
      var d = new Date(); d.setDate(d.getDate() + parseInt(b.dataset.gun, 10));
      var iso = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      if (alan) { alan.value = iso; }
    });
  });
  // Geri dönüşü zor durumlar (olumsuz / iletişim istemiyor) için onay
  document.querySelectorAll('.js-kapat').forEach(function (b) {
    b.addEventListener('click', function (ev) { if (!confirm(b.dataset.soru)) { ev.preventDefault(); } });
  });
})();
</script>
