<?php
/**
 * Prolyn Admin Panel - Potansiyel Müşteriler (Lead) listesi
 *
 * Tasarım amacı: "bugün ne yapmalıyım?" sorusuna tek bakışta cevap vermek.
 *   1) Üstte bugünün işleri (takibi gelenler, mesaj atılmaya hazırlar, e-posta limiti)
 *   2) Satış hattı (yeni → müşteri) — tıklayınca liste o aşamaya süzülür
 *   3) Tek arama kutusu + açılır gelişmiş filtre + aktif filtre çipleri
 *   4) Sade tablo: firma / öncelik / durum / takip / hızlı işlem
 *   5) Toplu işlemler yalnızca satır seçilince görünür
 * Veri/yetki mantığı (POST işlemleri, sorgular, CSRF) önceki sürümle aynıdır.
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
lead_sema_kur($db);

$flash = null;

// ---- POST işlemleri (mantık değişmedi) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'set_status') {
        $id = (int)($_POST['lead_id'] ?? 0);
        $durum = (string)($_POST['durum'] ?? '');
        if ($id > 0 && isset(LEAD_DURUMLAR[$durum])) {
            $db->Update("UPDATE potansiyel_musteriler SET durum = ?, ret_tarihi = IF(? = 'ret', NOW(), ret_tarihi) WHERE lead_id = ?", [$durum, $durum, $id]);
            lead_aktivite($db, $id, 'durum', 'Durum: ' . LEAD_DURUMLAR[$durum][0]);
            $flash = ['success', 'Durum güncellendi.'];
        }
    }

    if ($action === 'bulk_status') {
        $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
        $durum = (string)($_POST['bulk_durum'] ?? '');
        if ($ids && isset(LEAD_DURUMLAR[$durum])) {
            foreach ($ids as $id) {
                $db->Update("UPDATE potansiyel_musteriler SET durum = ?, ret_tarihi = IF(? = 'ret', NOW(), ret_tarihi) WHERE lead_id = ?", [$durum, $durum, $id]);
                lead_aktivite($db, $id, 'durum', 'Toplu durum: ' . LEAD_DURUMLAR[$durum][0]);
            }
            $flash = ['success', count($ids) . ' kaydın durumu güncellendi.'];
        }
    }

    if ($action === 'bulk_mail') {
        $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
        $sablon = $db->getRowAssoc("SELECT * FROM lead_sablonlari WHERE kod = ? AND kanal = 'eposta' AND aktif = 1", [(string)($_POST['sablon'] ?? '')]);
        if (!$ids) {
            $flash = ['warning', 'Önce listeden firma seçin.'];
        } elseif (!$sablon) {
            $flash = ['warning', 'Geçerli bir e-posta şablonu seçin.'];
        } else {
            $k = lead_mail_kurallari();
            $kalan = max(0, $k['gunluk_limit'] - lead_bugun_gonderilen_mail($db));
            $eklendi = 0; $atlandi = [];
            foreach ($ids as $id) {
                $lead = $db->getRowAssoc("SELECT * FROM potansiyel_musteriler WHERE lead_id = ?", [$id]);
                if (!$lead) { continue; }
                if ($kalan <= 0) { $atlandi[] = $lead['firma_adi'] . ' (günlük limit)'; continue; }
                [$ok, $neden] = lead_gonderilebilir_mi($lead, 'eposta');
                if (!$ok) { $atlandi[] = $lead['firma_adi'] . ' (' . $neden . ')'; continue; }
                if (lead_mail_kuyruga_ekle($db, $lead, $sablon, lead_gonderen_adi())) { $eklendi++; $kalan--; }
            }
            $msg = $eklendi . ' e-posta kuyruğa eklendi; dakikada bir gönderilir.';
            if ($atlandi) { $msg .= ' Atlanan ' . count($atlandi) . ': ' . implode(', ', array_slice($atlandi, 0, 8)) . (count($atlandi) > 8 ? '…' : ''); }
            $flash = [$eklendi ? 'success' : 'warning', $msg];
        }
    }
}

// ---- Yardımcılar (yalnızca bu sayfa) ----
$e = static function ($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// ---- Filtreler ----
$f = [
    'q'       => trim((string)($_GET['q'] ?? '')),
    'durum'   => (string)($_GET['durum'] ?? ''),
    'oncelik' => (string)($_GET['oncelik'] ?? ''),
    'il'      => (string)($_GET['il'] ?? ''),
    'segment' => (string)($_GET['segment'] ?? ''),
    'kanal'   => (string)($_GET['kanal'] ?? ''),
    'takip'   => (string)($_GET['takip'] ?? ''),
    'hazir'   => (string)($_GET['hazir'] ?? ''),   // 1: kapanmış (deneme/müşteri/olumsuz/ret) kayıtları gizle
    'sirala'  => (string)($_GET['sirala'] ?? ''),
];
$durdurSql = "'" . implode("','", LEAD_DURDUR) . "'";   // sabit listeden üretilir, kullanıcı girdisi değildir
$where = ['1=1']; $p = [];
if ($f['q'] !== '') { $where[] = '(firma_adi LIKE ? OR telefon LIKE ? OR eposta LIKE ? OR ilce LIKE ?)'; $like = '%' . $f['q'] . '%'; array_push($p, $like, $like, $like, $like); }
if (isset(LEAD_DURUMLAR[$f['durum']])) { $where[] = 'durum = ?'; $p[] = $f['durum']; }
if (in_array($f['oncelik'], ['A', 'B', 'C'], true)) { $where[] = 'oncelik = ?'; $p[] = $f['oncelik']; }
if ($f['il'] !== '') { $where[] = 'il = ?'; $p[] = $f['il']; }
if ($f['segment'] !== '') { $where[] = 'segment = ?'; $p[] = $f['segment']; }
if ($f['kanal'] === 'wa') { $where[] = 'whatsapp = 1'; }
if ($f['kanal'] === 'mail') { $where[] = "eposta IS NOT NULL AND eposta <> ''"; }
if ($f['takip'] === 'bugun') { $where[] = "sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE() AND durum NOT IN ($durdurSql)"; }
if ($f['hazir'] === '1') { $where[] = "durum NOT IN ($durdurSql)"; }
$whereSql = implode(' AND ', $where);

// Sıralama: beyaz liste (kullanıcı girdisi SQL'e girmez)
$siralamalar = [
    ''        => ['Önerilen (takibi gelen önce, puan)', "(sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE()) DESC, puan DESC, lead_id ASC"],
    'puan'    => ['Puan (yüksekten düşüğe)', 'puan DESC, lead_id ASC'],
    'takip'   => ['Takip tarihi (yakın önce)', '(sonraki_takip IS NULL) ASC, sonraki_takip ASC, puan DESC'],
    'iletisim'=> ['Son iletişim (yakın önce)', '(son_iletisim IS NULL) ASC, son_iletisim DESC'],
    'yeni'    => ['Eklenme (yeni önce)', 'lead_id DESC'],
];
if (!isset($siralamalar[$f['sirala']])) { $f['sirala'] = ''; }

$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 50;
$toplamFiltre = (int)$db->getColumn("SELECT COUNT(*) FROM potansiyel_musteriler WHERE $whereSql", $p);
$sayfaSayisi = max(1, (int)ceil($toplamFiltre / $limit));
$sayfa = min($sayfa, $sayfaSayisi);
$offset = ($sayfa - 1) * $limit;
$leads = $db->getRowsAssoc("SELECT * FROM potansiyel_musteriler WHERE $whereSql
    ORDER BY " . $siralamalar[$f['sirala']][1] . " LIMIT $limit OFFSET $offset", $p);

// ---- Özet verileri ----
$ozet = $db->getRowAssoc("SELECT COUNT(*) toplam,
    SUM(whatsapp = 1 AND durum NOT IN ($durdurSql)) wa_hazir,
    SUM(eposta IS NOT NULL AND eposta <> '' AND durum NOT IN ($durdurSql)) mail_hazir,
    SUM(sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE() AND durum NOT IN ($durdurSql)) bugun,
    SUM(durum IN ('deneme','musteri')) donusum
    FROM potansiyel_musteriler") ?: [];
$durumSayilari = [];
foreach ($db->getRowsAssoc("SELECT durum, COUNT(*) n FROM potansiyel_musteriler GROUP BY durum") ?: [] as $r) { $durumSayilari[$r['durum']] = (int)$r['n']; }
$toplamTum = (int)($ozet['toplam'] ?? 0);
$donusumOrani = $toplamTum > 0 ? round(((int)($ozet['donusum'] ?? 0)) / $toplamTum * 100, 1) : 0;

$iller = $db->getRowsAssoc("SELECT il, COUNT(*) n FROM potansiyel_musteriler WHERE il <> '' GROUP BY il ORDER BY n DESC");
$segmentler = $db->getRowsAssoc("SELECT segment, COUNT(*) n FROM potansiyel_musteriler WHERE segment <> '' GROUP BY segment ORDER BY n DESC");
$waSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'whatsapp' AND aktif = 1 ORDER BY sira");
$mailSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'eposta' AND aktif = 1 ORDER BY sira");
$mk = lead_mail_kurallari();
$bugunMail = lead_bugun_gonderilen_mail($db);
$mailKalan = max(0, $mk['gunluk_limit'] - $bugunMail);
$mailYuzde = $mk['gunluk_limit'] > 0 ? min(100, (int)round($bugunMail / $mk['gunluk_limit'] * 100)) : 0;
$csrf = generateCSRFToken();

/** Mevcut filtreleri koruyup bazılarını değiştirerek sorgu dizesi üretir. */
$qs = function (array $degis) use ($f): string {
    return http_build_query(array_filter(array_merge($f, $degis), static function ($v) { return $v !== '' && $v !== null; }));
};

// Gelişmiş filtre açık mı? (bu alanlardan biri doluysa kutu açık gelir)
$gelismisAcik = ($f['oncelik'] !== '' || $f['il'] !== '' || $f['segment'] !== '' || $f['kanal'] !== '' || $f['sirala'] !== '');

// Aktif filtre çipleri
$cipler = [];
if ($f['q'] !== '')       { $cipler[] = ['Arama: ' . $f['q'], $qs(['q' => '', 'sayfa' => ''])]; }
if ($f['durum'] !== '' && isset(LEAD_DURUMLAR[$f['durum']])) { $cipler[] = ['Durum: ' . LEAD_DURUMLAR[$f['durum']][0], $qs(['durum' => '', 'sayfa' => ''])]; }
if ($f['takip'] === 'bugun') { $cipler[] = ['Takibi gelenler', $qs(['takip' => '', 'sayfa' => ''])]; }
if ($f['hazir'] === '1')  { $cipler[] = ['Kapanmışlar gizli', $qs(['hazir' => '', 'sayfa' => ''])]; }
if ($f['oncelik'] !== '') { $cipler[] = ['Öncelik: ' . $f['oncelik'], $qs(['oncelik' => '', 'sayfa' => ''])]; }
if ($f['il'] !== '')      { $cipler[] = ['İl: ' . $f['il'], $qs(['il' => '', 'sayfa' => ''])]; }
if ($f['segment'] !== '') { $cipler[] = ['Segment: ' . $f['segment'], $qs(['segment' => '', 'sayfa' => ''])]; }
if ($f['kanal'] !== '')   { $cipler[] = ['Kanal: ' . ($f['kanal'] === 'wa' ? 'WhatsApp' : 'E-posta'), $qs(['kanal' => '', 'sayfa' => ''])]; }

// Satış hattı aşamaları (soldan sağa); kapalı durumlar ayrı gösterilir
$hatAsamalari = ['yeni', 'iletisime_gecildi', 'ilgileniyor', 'demo', 'deneme', 'musteri'];
$kapaliDurumlar = ['olumsuz', 'ret'];

require_once '../config/sidebar.php';
?>
<style>
  /* Lead listesi — Metronic değişkenleriyle yazıldı; açık/koyu temada uyumlu */
  .lp-band { display:flex; align-items:center; gap:.9rem; padding:1rem 1.15rem; border:1px solid var(--bs-border-color, #f1f1f4); border-radius:.75rem; background:var(--bs-body-bg, #fff); text-decoration:none; height:100%; transition:border-color .15s, box-shadow .15s; }
  a.lp-band:hover { border-color:var(--bs-primary); box-shadow:0 2px 10px rgba(0,0,0,.05); }
  .lp-band .lp-ico { width:2.6rem; height:2.6rem; border-radius:.65rem; display:flex; align-items:center; justify-content:center; flex:0 0 auto; }
  .lp-band .lp-num { font-size:1.65rem; font-weight:700; line-height:1; }
  .lp-band.lp-urgent { border-color:var(--bs-warning); }

  .lp-hat { display:grid; grid-template-columns:repeat(6, minmax(0,1fr)); gap:.6rem; }
  .lp-stage { position:relative; display:block; padding:.8rem .9rem .75rem; border:1px solid var(--bs-border-color, #f1f1f4); border-top:3px solid var(--lp-c, var(--bs-gray-400)); border-radius:.6rem; background:var(--bs-body-bg, #fff); text-decoration:none; }
  .lp-stage:hover { box-shadow:0 2px 10px rgba(0,0,0,.06); }
  .lp-stage.is-active { background:var(--bs-primary-light, rgba(0,120,255,.07)); border-color:var(--bs-primary); border-top-color:var(--lp-c, var(--bs-primary)); }
  .lp-stage .lp-n { font-size:1.5rem; font-weight:700; line-height:1.1; color:var(--bs-gray-900); }
  .lp-stage .lp-l { font-size:.8rem; font-weight:600; color:var(--bs-gray-600); }
  .lp-kapali { display:flex; gap:.5rem; flex-wrap:wrap; align-items:center; margin-top:.6rem; font-size:.8rem; }
  .lp-kapali a { text-decoration:none; padding:.2rem .65rem; border-radius:2rem; border:1px solid var(--bs-border-color, #f1f1f4); color:var(--bs-gray-700); }
  .lp-kapali a.is-active { border-color:var(--bs-primary); color:var(--bs-primary); font-weight:600; }
  @media (max-width: 991.98px) { .lp-hat { grid-template-columns:repeat(3, minmax(0,1fr)); } }
  @media (max-width: 575.98px) { .lp-hat { grid-template-columns:repeat(2, minmax(0,1fr)); } }

  .lp-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .35rem .2rem .7rem; border-radius:2rem; background:var(--bs-gray-100); font-size:.8rem; font-weight:600; color:var(--bs-gray-800); }
  .lp-chip a { width:1.15rem; height:1.15rem; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; background:var(--bs-gray-300); color:var(--bs-gray-800); text-decoration:none; line-height:1; font-size:.8rem; }
  .lp-chip a:hover { background:var(--bs-danger); color:#fff; }

  .lp-tablo td { vertical-align:middle; }
  .lp-tablo tr.lp-gecikti > td:first-child { box-shadow:inset 3px 0 0 var(--bs-danger); }
  .lp-tablo tr.lp-bugun > td:first-child { box-shadow:inset 3px 0 0 var(--bs-warning); }
  .lp-tablo tr.lp-kapali-satir { opacity:.65; }
  .lp-firma { max-width:340px; }
  .lp-durum { min-width:10.5rem; border-left-width:4px; }
  .lp-hizli { display:flex; gap:.35rem; align-items:center; justify-content:flex-end; flex-wrap:nowrap; }

  .lp-bulkbar { position:sticky; bottom:0; z-index:5; display:none; gap:.6rem; align-items:center; flex-wrap:wrap; padding:.75rem 1.25rem; background:var(--bs-gray-900); color:#fff; border-radius:.6rem; margin:1rem 1.25rem 1.25rem; box-shadow:0 6px 24px rgba(0,0,0,.25); }
  .lp-bulkbar.is-open { display:flex; }
  .lp-bulkbar .form-select { max-width:15rem; }
  .lp-bulkbar-uyari { display:none; width:100%; font-size:.85rem; color:#ffd166; }
</style>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
        <div class="page-title d-flex flex-column justify-content-center me-3">
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Potansiyel Müşteriler</h1>
          <span class="text-muted fs-7 fw-semibold mt-1">Mutfak · dolap · banyo kampanyası satış hattı · <?= number_format($toplamTum, 0, ',', '.') ?> firma</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <a href="crm/lead_templates.php" class="btn btn-sm btn-light"><i class="ki-duotone ki-message-text-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Şablonlar</a>
          <a href="crm/lead_import.php" class="btn btn-sm btn-light-primary"><i class="ki-duotone ki-file-up fs-3"><span class="path1"></span><span class="path2"></span></i> İçe Aktar</a>
          <a href="crm/lead_detail.php?yeni=1" class="btn btn-sm btn-primary"><i class="ki-duotone ki-plus fs-3"></i> Yeni Lead</a>
        </div>
      </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div id="kt_app_content_container" class="app-container container-fluid">

        <?php if ($flash): ?>
        <div class="alert alert-<?= $e($flash[0]) ?> d-flex align-items-center p-4 mb-6">
          <div class="fw-semibold"><?= $e($flash[1]) ?></div>
        </div>
        <?php endif; ?>

        <!-- 1) BUGÜN: yapılacak işler -->
        <div class="row g-4 mb-6">
          <div class="col-12 col-md-6 col-xl-3">
            <a href="crm/leads.php?<?= $e($qs(['takip' => 'bugun', 'durum' => '', 'sayfa' => ''])) ?>" class="lp-band <?= (int)($ozet['bugun'] ?? 0) > 0 ? 'lp-urgent' : '' ?>">
              <span class="lp-ico bg-light-warning"><i class="ki-duotone ki-calendar-tick fs-1 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
              <span>
                <span class="lp-num text-gray-900 d-block"><?= number_format((int)($ozet['bugun'] ?? 0), 0, ',', '.') ?></span>
                <span class="text-muted fs-7 fw-semibold">Takibi gelen firma</span>
              </span>
            </a>
          </div>
          <div class="col-12 col-md-6 col-xl-3">
            <a href="crm/leads.php?<?= $e($qs(['kanal' => 'wa', 'hazir' => '1', 'takip' => '', 'sayfa' => ''])) ?>" class="lp-band">
              <span class="lp-ico bg-light-success"><i class="ki-duotone ki-whatsapp fs-1 text-success"><span class="path1"></span><span class="path2"></span></i></span>
              <span>
                <span class="lp-num text-gray-900 d-block"><?= number_format((int)($ozet['wa_hazir'] ?? 0), 0, ',', '.') ?></span>
                <span class="text-muted fs-7 fw-semibold">WhatsApp'a hazır</span>
              </span>
            </a>
          </div>
          <div class="col-12 col-md-6 col-xl-3">
            <a href="crm/leads.php?<?= $e($qs(['kanal' => 'mail', 'hazir' => '1', 'takip' => '', 'sayfa' => ''])) ?>" class="lp-band">
              <span class="lp-ico bg-light-info"><i class="ki-duotone ki-sms fs-1 text-info"><span class="path1"></span><span class="path2"></span></i></span>
              <span>
                <span class="lp-num text-gray-900 d-block"><?= number_format((int)($ozet['mail_hazir'] ?? 0), 0, ',', '.') ?></span>
                <span class="text-muted fs-7 fw-semibold">E-postaya hazır</span>
              </span>
            </a>
          </div>
          <div class="col-12 col-md-6 col-xl-3">
            <div class="lp-band flex-column align-items-stretch justify-content-center gap-2">
              <div class="d-flex justify-content-between align-items-baseline">
                <span class="text-muted fs-7 fw-semibold">Bugünkü e-posta limiti</span>
                <span class="fw-bold text-gray-900"><?= $bugunMail ?> / <?= (int)$mk['gunluk_limit'] ?></span>
              </div>
              <div class="progress h-6px"><div class="progress-bar <?= $mailKalan === 0 ? 'bg-danger' : 'bg-info' ?>" style="width: <?= $mailYuzde ?>%"></div></div>
              <span class="text-muted fs-8">Kalan <?= $mailKalan ?> · dönüşüm oranı %<?= str_replace('.', ',', (string)$donusumOrani) ?></span>
            </div>
          </div>
        </div>

        <!-- 2) SATIŞ HATTI -->
        <div class="mb-6">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold text-gray-800 fs-6">Satış hattı</span>
            <?php if ($f['durum'] !== ''): ?><a href="crm/leads.php?<?= $e($qs(['durum' => '', 'sayfa' => ''])) ?>" class="fs-8 text-muted">Aşama süzmesini kaldır</a><?php endif; ?>
          </div>
          <div class="lp-hat">
            <?php foreach ($hatAsamalari as $kod):
                [$ad, $renk] = LEAD_DURUMLAR[$kod];
                $aktif = $f['durum'] === $kod;
            ?>
            <a href="crm/leads.php?<?= $e($qs(['durum' => $aktif ? '' : $kod, 'takip' => '', 'hazir' => '', 'sayfa' => ''])) ?>"
               class="lp-stage <?= $aktif ? 'is-active' : '' ?>" style="--lp-c: var(--bs-<?= $e($renk) ?>)">
              <span class="lp-n d-block"><?= number_format($durumSayilari[$kod] ?? 0, 0, ',', '.') ?></span>
              <span class="lp-l"><?= $e($ad) ?></span>
            </a>
            <?php endforeach; ?>
          </div>
          <div class="lp-kapali">
            <span class="text-muted">Kapananlar:</span>
            <?php foreach ($kapaliDurumlar as $kod):
                $aktif = $f['durum'] === $kod; ?>
              <a href="crm/leads.php?<?= $e($qs(['durum' => $aktif ? '' : $kod, 'takip' => '', 'hazir' => '', 'sayfa' => ''])) ?>" class="<?= $aktif ? 'is-active' : '' ?>">
                <?= $e(LEAD_DURUMLAR[$kod][0]) ?> · <?= number_format($durumSayilari[$kod] ?? 0, 0, ',', '.') ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 3) ARAMA + FİLTRE -->
        <div class="card mb-4">
          <div class="card-body py-4">
            <form method="get" action="crm/leads.php">
              <?php foreach (['durum', 'takip', 'hazir'] as $gizli): if ($f[$gizli] !== ''): ?>
                <input type="hidden" name="<?= $gizli ?>" value="<?= $e($f[$gizli]) ?>">
              <?php endif; endforeach; ?>
              <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="flex-grow-1" style="min-width:240px">
                  <input type="search" name="q" value="<?= $e($f['q']) ?>" class="form-control form-control-solid" placeholder="Firma, telefon, e-posta veya ilçe ara…" aria-label="Ara">
                </div>
                <button class="btn btn-primary">Ara</button>
                <button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#lpGelismis" aria-expanded="<?= $gelismisAcik ? 'true' : 'false' ?>">
                  <i class="ki-duotone ki-filter fs-3"><span class="path1"></span><span class="path2"></span></i> Filtreler
                </button>
                <?php if ($cipler): ?><a href="crm/leads.php" class="btn btn-light-danger">Temizle</a><?php endif; ?>
              </div>

              <div class="collapse <?= $gelismisAcik ? 'show' : '' ?>" id="lpGelismis">
                <div class="row g-3 pt-4">
                  <div class="col-6 col-md-2"><label class="form-label fs-7">Öncelik</label>
                    <select name="oncelik" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                      <?php foreach (['A', 'B', 'C'] as $o): ?><option <?= $f['oncelik'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?>
                    </select></div>
                  <div class="col-6 col-md-3"><label class="form-label fs-7">İl</label>
                    <select name="il" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                      <?php foreach ($iller as $i): ?><option value="<?= $e($i['il']) ?>" <?= $f['il'] === $i['il'] ? 'selected' : '' ?>><?= $e($i['il']) ?> (<?= (int)$i['n'] ?>)</option><?php endforeach; ?>
                    </select></div>
                  <div class="col-6 col-md-2"><label class="form-label fs-7">Segment</label>
                    <select name="segment" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                      <?php foreach ($segmentler as $s): ?><option value="<?= $e($s['segment']) ?>" <?= $f['segment'] === $s['segment'] ? 'selected' : '' ?>><?= $e($s['segment']) ?></option><?php endforeach; ?>
                    </select></div>
                  <div class="col-6 col-md-2"><label class="form-label fs-7">Kanal</label>
                    <select name="kanal" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                      <option value="wa" <?= $f['kanal'] === 'wa' ? 'selected' : '' ?>>WhatsApp</option>
                      <option value="mail" <?= $f['kanal'] === 'mail' ? 'selected' : '' ?>>E-posta</option>
                    </select></div>
                  <div class="col-12 col-md-3"><label class="form-label fs-7">Sırala</label>
                    <select name="sirala" class="form-select form-select-sm form-select-solid">
                      <?php foreach ($siralamalar as $kod => $s): ?><option value="<?= $e($kod) ?>" <?= $f['sirala'] === $kod ? 'selected' : '' ?>><?= $e($s[0]) ?></option><?php endforeach; ?>
                    </select></div>
                  <div class="col-12"><button class="btn btn-sm btn-primary">Filtreyi uygula</button></div>
                </div>
              </div>
            </form>

            <?php if ($cipler): ?>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
              <span class="text-muted fs-8 fw-semibold">Aktif filtreler:</span>
              <?php foreach ($cipler as $c): ?>
                <span class="lp-chip"><?= $e($c[0]) ?> <a href="crm/leads.php<?= $c[1] !== '' ? '?' . $e($c[1]) : '' ?>" title="Bu filtreyi kaldır" aria-label="Filtreyi kaldır">×</a></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 4) LİSTE -->
        <form method="post" action="crm/leads.php?<?= $e($qs(['sayfa' => $sayfa])) ?>" id="leadBulkForm">
          <?= csrfTokenField() ?>
          <div class="card">
            <div class="card-header border-0 pt-5 pb-0 flex-wrap gap-2">
              <div class="card-title flex-column align-items-start">
                <span class="fw-bold fs-4"><?= number_format($toplamFiltre, 0, ',', '.') ?> kayıt</span>
                <?php if ($toplamFiltre > 0): ?>
                <span class="text-muted fs-8"><?= number_format($offset + 1, 0, ',', '.') ?>–<?= number_format($offset + count($leads), 0, ',', '.') ?> arası gösteriliyor · sıralama: <?= $e($siralamalar[$f['sirala']][0]) ?></span>
                <?php endif; ?>
              </div>
              <div class="card-toolbar text-muted fs-8">Satırı seçince toplu işlem çubuğu altta açılır</div>
            </div>
            <div class="card-body pt-2 px-0 pb-0">
              <div class="table-responsive">
                <table class="table lp-tablo align-middle table-row-dashed fs-6 gy-4 mb-0">
                  <thead>
                    <tr class="text-start text-muted fw-bold fs-8 text-uppercase gs-0">
                      <th class="w-25px ps-5"><input class="form-check-input" type="checkbox" id="leadTumu" aria-label="Sayfadakilerin tümünü seç"></th>
                      <th class="min-w-250px">Firma</th>
                      <th class="min-w-80px">Öncelik</th>
                      <th class="min-w-175px">Durum</th>
                      <th class="min-w-150px">Takip / son temas</th>
                      <th class="text-end pe-5 min-w-200px">Hızlı işlem</th>
                    </tr>
                  </thead>
                  <tbody class="text-gray-700 fw-semibold">
                  <?php if (!$leads): ?>
                    <tr><td colspan="6" class="text-center text-muted py-12">
                      <?php if ($toplamTum === 0): ?>
                        Henüz kayıt yok. <a href="crm/lead_import.php" class="fw-bold">CSV içe aktararak</a> veya <a href="crm/lead_detail.php?yeni=1" class="fw-bold">elle ekleyerek</a> başlayın.
                      <?php else: ?>
                        Bu filtreye uyan kayıt yok. <a href="crm/leads.php" class="fw-bold">Filtreleri temizle</a>
                      <?php endif; ?>
                    </td></tr>
                  <?php endif; ?>
                  <?php foreach ($leads as $l):
                      $kapali = in_array($l['durum'], LEAD_DURDUR, true);
                      [$takipYazi, $takipSinif, $takipTarih] = lead_takip_etiket($l['sonraki_takip'] ?: null, $kapali);
                      $gecikti = !$kapali && !empty($l['sonraki_takip']) && $l['sonraki_takip'] < date('Y-m-d');
                      $bugunSatir = !$kapali && !empty($l['sonraki_takip']) && $l['sonraki_takip'] === date('Y-m-d');
                      [$waOk] = lead_gonderilebilir_mi($l, 'whatsapp');
                      [$mailOk, $mailNeden] = lead_gonderilebilir_mi($l, 'eposta');
                      $oncelikRenk = ['A' => 'success', 'B' => 'primary', 'C' => 'secondary'][$l['oncelik']] ?? 'secondary';
                      $durumRenk = LEAD_DURUMLAR[$l['durum']][1] ?? 'secondary';
                      $konum = trim(trim((string)$l['ilce']) . ' / ' . trim((string)$l['il']), ' /');
                      $satirSinif = ($gecikti ? 'lp-gecikti ' : '') . ($bugunSatir ? 'lp-bugun ' : '') . ($kapali ? 'lp-kapali-satir' : '');
                  ?>
                    <tr class="<?= trim($satirSinif) ?>">
                      <td class="ps-5"><input class="form-check-input lead-sec" type="checkbox" name="ids[]" value="<?= (int)$l['lead_id'] ?>" aria-label="<?= $e($l['firma_adi']) ?> seç"></td>
                      <td class="lp-firma">
                        <a href="crm/lead_detail.php?id=<?= (int)$l['lead_id'] ?>" class="text-gray-900 fw-bold text-hover-primary d-block"><?= $e($l['firma_adi']) ?></a>
                        <div class="text-muted fs-8 fw-semibold">
                          <?= $e($konum) ?><?php if (!empty($l['telefon'])): ?><?= $konum !== '' ? ' · ' : '' ?><a href="tel:<?= $e($l['telefon']) ?>" class="text-muted text-hover-primary"><?= $e($l['telefon']) ?></a><?php endif; ?>
                        </div>
                        <div class="mt-1">
                          <?php if ($l['segment']): ?><span class="badge badge-light fs-9 me-1"><?= $e($l['segment']) ?></span><?php endif; ?>
                          <?php if ((int)$l['imalat_montaj']): ?><span class="badge badge-light-primary fs-9 me-1">imalat/montaj</span><?php endif; ?>
                          <?php if ((int)$l['buyuk_marka']): ?><span class="badge badge-light-warning fs-9">büyük marka</span><?php endif; ?>
                        </div>
                      </td>
                      <td>
                        <span class="badge badge-<?= $oncelikRenk ?> fs-7"><?= $e($l['oncelik']) ?> · <?= (int)$l['puan'] ?></span>
                        <?php if ($l['google_puan'] !== null): ?><div class="text-muted fs-8 mt-1">★ <?= $e((string)$l['google_puan']) ?></div><?php endif; ?>
                      </td>
                      <td>
                        <select class="form-select form-select-sm form-select-solid lp-durum lead-durum" data-id="<?= (int)$l['lead_id'] ?>"
                                style="border-left-color: var(--bs-<?= $e($durumRenk) ?>)" aria-label="Durum">
                          <?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>" <?= $l['durum'] === $kod ? 'selected' : '' ?>><?= $e($d[0]) ?></option><?php endforeach; ?>
                        </select>
                      </td>
                      <td>
                        <div class="<?= $takipSinif ?>"><?= $e($takipYazi) ?><?php if ($takipTarih !== ''): ?> <span class="text-muted fw-normal fs-9">(<?= $e($takipTarih) ?>)</span><?php endif; ?></div>
                        <div class="text-muted fs-8 fw-normal mt-1">
                          <?= $l['son_iletisim'] ? 'Son: ' . date('d.m', strtotime($l['son_iletisim'])) . ' · ' : 'Hiç ulaşılmadı · ' ?>WA <?= (int)$l['wa_sayisi'] ?> · Mail <?= (int)$l['mail_sayisi'] ?>
                        </div>
                      </td>
                      <td class="pe-5">
                        <div class="lp-hizli">
                        <?php if ($waOk): ?>
                          <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-light-success lp-wa" data-bs-toggle="dropdown" title="WhatsApp mesajı gönder" aria-label="WhatsApp">
                              <i class="ki-duotone ki-whatsapp fs-2"><span class="path1"></span><span class="path2"></span></i></button>
                            <ul class="dropdown-menu">
                              <?php foreach ($waSablonlar as $s): ?>
                              <li><a class="dropdown-item" target="_blank" rel="noopener" href="crm/lead_wa.php?id=<?= (int)$l['lead_id'] ?>&s=<?= urlencode($s['kod']) ?>&k=<?= urlencode($csrf) ?>"><?= $e($s['ad']) ?></a></li>
                              <?php endforeach; ?>
                            </ul>
                          </div>
                        <?php endif; ?>
                        <?php if (!empty($l['eposta'])): ?>
                          <span class="btn btn-sm btn-icon <?= $mailOk ? 'btn-light-info' : 'btn-light' ?>" title="<?= $e($l['eposta'] . ($mailOk ? ' — göndermeye uygun' : ' — ' . $mailNeden)) ?>"><i class="ki-duotone ki-sms fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <?php endif; ?>
                        <?php if (!empty($l['maps_url'])): ?>
                          <a class="btn btn-sm btn-icon btn-light" target="_blank" rel="noopener" href="<?= $e($l['maps_url']) ?>" title="Haritada aç" aria-label="Haritada aç"><i class="ki-duotone ki-geolocation fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                        <?php endif; ?>
                          <a href="crm/lead_detail.php?id=<?= (int)$l['lead_id'] ?>" class="btn btn-sm btn-light-primary ms-1">Detay</a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <?php if ($sayfaSayisi > 1):
                  // Pencereli sayfalama: 1 … 4 5 [6] 7 8 … 40
                  $bas = max(1, $sayfa - 2); $son = min($sayfaSayisi, $sayfa + 2);
              ?>
              <ul class="pagination justify-content-center my-6 flex-wrap">
                <li class="page-item <?= $sayfa <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="crm/leads.php?<?= $e($qs(['sayfa' => max(1, $sayfa - 1)])) ?>">‹ Önceki</a></li>
                <?php if ($bas > 1): ?>
                  <li class="page-item"><a class="page-link" href="crm/leads.php?<?= $e($qs(['sayfa' => 1])) ?>">1</a></li>
                  <?php if ($bas > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $bas; $i <= $son; $i++): ?>
                  <li class="page-item <?= $i === $sayfa ? 'active' : '' ?>"><a class="page-link" href="crm/leads.php?<?= $e($qs(['sayfa' => $i])) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($son < $sayfaSayisi): ?>
                  <?php if ($son < $sayfaSayisi - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                  <li class="page-item"><a class="page-link" href="crm/leads.php?<?= $e($qs(['sayfa' => $sayfaSayisi])) ?>"><?= $sayfaSayisi ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $sayfa >= $sayfaSayisi ? 'disabled' : '' ?>"><a class="page-link" href="crm/leads.php?<?= $e($qs(['sayfa' => min($sayfaSayisi, $sayfa + 1)])) ?>">Sonraki ›</a></li>
              </ul>
              <?php else: ?>
              <div class="pb-4"></div>
              <?php endif; ?>
            </div>

            <!-- 5) TOPLU İŞLEM ÇUBUĞU: yalnızca satır seçilince açılır -->
            <div class="lp-bulkbar" id="leadBulkBar" role="region" aria-label="Toplu işlemler">
              <strong><span id="leadSecSayi">0</span> firma seçili</strong>
              <span class="vr bg-white opacity-25 d-none d-md-block"></span>
              <select name="sablon" class="form-select form-select-sm form-select-solid" aria-label="E-posta şablonu">
                <?php foreach ($mailSablonlar as $s): ?><option value="<?= $e($s['kod']) ?>"><?= $e($s['ad']) ?></option><?php endforeach; ?>
              </select>
              <button type="submit" name="action" value="bulk_mail" class="btn btn-sm btn-info js-bulk" data-soru="Seçili firmalara e-posta kuyruğa eklensin mi? Kurallara uymayanlar atlanır (bugün kalan hak: <?= (int)$mailKalan ?>).">E-posta kuyruğuna ekle</button>
              <span class="vr bg-white opacity-25 d-none d-md-block"></span>
              <select name="bulk_durum" class="form-select form-select-sm form-select-solid" aria-label="Yeni durum">
                <?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>"><?= $e($d[0]) ?></option><?php endforeach; ?>
              </select>
              <button type="submit" name="action" value="bulk_status" class="btn btn-sm btn-light js-bulk" data-soru="Seçili kayıtların durumu değiştirilsin mi?">Durumu değiştir</button>
              <button type="button" class="btn btn-sm btn-link text-white ms-auto" id="leadSecTemizle">Seçimi kaldır</button>
            </div>
          </div>
        </form>

        <form method="post" action="crm/leads.php?<?= $e($qs(['sayfa' => $sayfa])) ?>" id="leadDurumForm" class="d-none">
          <?= csrfTokenField() ?>
          <input type="hidden" name="action" value="set_status">
          <input type="hidden" name="lead_id" value="">
          <input type="hidden" name="durum" value="">
        </form>

      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
<script>
(function () {
  var tumu = document.getElementById('leadTumu');
  var bar = document.getElementById('leadBulkBar');
  var sayi = document.getElementById('leadSecSayi');
  var kutular = function () { return document.querySelectorAll('.lead-sec'); };

  // Seçim sayısına göre toplu işlem çubuğunu aç/kapat
  function guncelle() {
    var n = document.querySelectorAll('.lead-sec:checked').length;
    sayi.textContent = n;
    bar.classList.toggle('is-open', n > 0);
    if (tumu) { tumu.checked = n > 0 && n === kutular().length; tumu.indeterminate = n > 0 && n < kutular().length; }
  }
  if (tumu) tumu.addEventListener('change', function () {
    kutular().forEach(function (c) { c.checked = tumu.checked; });
    guncelle();
  });
  kutular().forEach(function (c) { c.addEventListener('change', guncelle); });
  document.getElementById('leadSecTemizle').addEventListener('click', function () {
    kutular().forEach(function (c) { c.checked = false; });
    guncelle();
  });

  // Toplu işlem onayı (modal değil, tarayıcı onayı)
  document.querySelectorAll('.js-bulk').forEach(function (b) {
    b.addEventListener('click', function (ev) {
      if (!document.querySelector('.lead-sec:checked') || !confirm(b.dataset.soru)) { ev.preventDefault(); }
    });
  });

  // Satır içi durum değişikliği
  document.querySelectorAll('.lead-durum').forEach(function (s) {
    s.addEventListener('change', function () {
      var f = document.getElementById('leadDurumForm');
      f.lead_id.value = s.dataset.id; f.durum.value = s.value; f.submit();
    });
  });

  // WhatsApp menüsü tablo kaydırma alanında kırpılmasın: konumlandırmayı "fixed" yap
  if (window.bootstrap && bootstrap.Dropdown) {
    document.querySelectorAll('.lp-wa').forEach(function (b) {
      new bootstrap.Dropdown(b, { popperConfig: function (d) { d.strategy = 'fixed'; return d; } });
    });
  }
  guncelle();
})();
</script>
