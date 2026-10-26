<?php
/**
 * Prolyn Admin Panel - Potansiyel Müşteriler (Lead) listesi
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
lead_sema_kur($db);

$flash = null;

// ---- POST işlemleri ----
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

// ---- Filtreler ----
$f = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'durum' => (string)($_GET['durum'] ?? ''),
    'oncelik' => (string)($_GET['oncelik'] ?? ''),
    'il' => (string)($_GET['il'] ?? ''),
    'segment' => (string)($_GET['segment'] ?? ''),
    'kanal' => (string)($_GET['kanal'] ?? ''),
    'takip' => (string)($_GET['takip'] ?? ''),
];
$where = ['1=1']; $p = [];
if ($f['q'] !== '') { $where[] = '(firma_adi LIKE ? OR telefon LIKE ? OR eposta LIKE ? OR ilce LIKE ?)'; $like = '%' . $f['q'] . '%'; array_push($p, $like, $like, $like, $like); }
if (isset(LEAD_DURUMLAR[$f['durum']])) { $where[] = 'durum = ?'; $p[] = $f['durum']; }
if (in_array($f['oncelik'], ['A', 'B', 'C'], true)) { $where[] = 'oncelik = ?'; $p[] = $f['oncelik']; }
if ($f['il'] !== '') { $where[] = 'il = ?'; $p[] = $f['il']; }
if ($f['segment'] !== '') { $where[] = 'segment = ?'; $p[] = $f['segment']; }
if ($f['kanal'] === 'wa') { $where[] = 'whatsapp = 1'; }
if ($f['kanal'] === 'mail') { $where[] = "eposta IS NOT NULL AND eposta <> ''"; }
if ($f['takip'] === 'bugun') { $where[] = "sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE() AND durum NOT IN ('deneme','musteri','olumsuz','ret')"; }
$whereSql = implode(' AND ', $where);

$sayfa = max(1, (int)($_GET['sayfa'] ?? 1));
$limit = 50;
$toplamFiltre = (int)$db->getColumn("SELECT COUNT(*) FROM potansiyel_musteriler WHERE $whereSql", $p);
$sayfaSayisi = max(1, (int)ceil($toplamFiltre / $limit));
$sayfa = min($sayfa, $sayfaSayisi);
$offset = ($sayfa - 1) * $limit;
$leads = $db->getRowsAssoc("SELECT * FROM potansiyel_musteriler WHERE $whereSql
    ORDER BY (sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE()) DESC, puan DESC, lead_id ASC LIMIT $limit OFFSET $offset", $p);

// ---- Özet ----
$ozet = $db->getRowAssoc("SELECT COUNT(*) toplam,
    SUM(whatsapp = 1 AND durum NOT IN ('deneme','musteri','olumsuz','ret')) wa_hazir,
    SUM(eposta IS NOT NULL AND eposta <> '' AND durum NOT IN ('deneme','musteri','olumsuz','ret')) mail_hazir,
    SUM(sonraki_takip IS NOT NULL AND sonraki_takip <= CURDATE() AND durum NOT IN ('deneme','musteri','olumsuz','ret')) bugun,
    SUM(durum IN ('ilgileniyor','demo')) sicak,
    SUM(durum IN ('deneme','musteri')) donusum,
    SUM(durum = 'ret') ret
    FROM potansiyel_musteriler") ?: [];
$iller = $db->getRowsAssoc("SELECT il, COUNT(*) n FROM potansiyel_musteriler WHERE il <> '' GROUP BY il ORDER BY n DESC");
$segmentler = $db->getRowsAssoc("SELECT segment, COUNT(*) n FROM potansiyel_musteriler WHERE segment <> '' GROUP BY segment ORDER BY n DESC");
$waSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'whatsapp' AND aktif = 1 ORDER BY sira");
$mailSablonlar = $db->getRowsAssoc("SELECT kod, ad FROM lead_sablonlari WHERE kanal = 'eposta' AND aktif = 1 ORDER BY sira");
$mk = lead_mail_kurallari();
$bugunMail = lead_bugun_gonderilen_mail($db);
$csrf = generateCSRFToken();
$qs = function (array $degis) use ($f) { return http_build_query(array_filter(array_merge($f, $degis), 'strlen')); };

require_once '../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
        <div class="page-title d-flex flex-column justify-content-center me-3">
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Potansiyel Müşteriler</h1>
          <span class="text-muted fs-7 fw-semibold mt-1">Mutfak · dolap · banyo kampanyası satış hattı</span>
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
        <div class="alert alert-<?= htmlspecialchars($flash[0]) ?> d-flex align-items-center p-5 mb-6">
          <div class="fw-semibold"><?= htmlspecialchars($flash[1]) ?></div>
        </div>
        <?php endif; ?>

        <?php
        $kartlar = [
            ['Toplam', (int)($ozet['toplam'] ?? 0), 'ki-people', 'primary', ''],
            ['WhatsApp\'a hazır', (int)($ozet['wa_hazir'] ?? 0), 'ki-whatsapp', 'success', $qs(['kanal' => 'wa', 'sayfa' => ''])],
            ['E-postaya hazır', (int)($ozet['mail_hazir'] ?? 0), 'ki-sms', 'info', $qs(['kanal' => 'mail', 'sayfa' => ''])],
            ['Bugün takip', (int)($ozet['bugun'] ?? 0), 'ki-calendar-tick', 'warning', $qs(['takip' => 'bugun', 'sayfa' => ''])],
            ['İlgilenen / demo', (int)($ozet['sicak'] ?? 0), 'ki-star', 'primary', ''],
            ['Deneme / müşteri', (int)($ozet['donusum'] ?? 0), 'ki-medal-star', 'success', ''],
        ];
        ?>
        <div class="row g-4 mb-6">
          <?php foreach ($kartlar as $k): ?>
          <div class="col-6 col-md-4 col-xl-2">
            <a <?= $k[4] !== '' ? 'href="crm/leads.php?' . htmlspecialchars($k[4]) . '"' : '' ?> class="card card-flush h-100 text-decoration-none">
              <div class="card-body d-flex flex-column py-5">
                <span class="text-muted fw-semibold fs-7 mb-2"><?= htmlspecialchars($k[0]) ?></span>
                <span class="fs-2hx fw-bold text-<?= $k[3] ?> lh-1"><?= number_format($k[1], 0, ',', '.') ?></span>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="card mb-6">
          <div class="card-body py-5">
            <form method="get" action="crm/leads.php" class="row g-3 align-items-end">
              <div class="col-12 col-md-3"><label class="form-label fs-7">Ara</label>
                <input type="text" name="q" value="<?= htmlspecialchars($f['q']) ?>" class="form-control form-control-sm form-control-solid" placeholder="Firma, telefon, e-posta, ilçe"></div>
              <div class="col-6 col-md-2"><label class="form-label fs-7">Durum</label>
                <select name="durum" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                  <?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>" <?= $f['durum'] === $kod ? 'selected' : '' ?>><?= htmlspecialchars($d[0]) ?></option><?php endforeach; ?>
                </select></div>
              <div class="col-6 col-md-1"><label class="form-label fs-7">Öncelik</label>
                <select name="oncelik" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                  <?php foreach (['A', 'B', 'C'] as $o): ?><option <?= $f['oncelik'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?>
                </select></div>
              <div class="col-6 col-md-2"><label class="form-label fs-7">İl</label>
                <select name="il" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                  <?php foreach ($iller as $i): ?><option value="<?= htmlspecialchars($i['il']) ?>" <?= $f['il'] === $i['il'] ? 'selected' : '' ?>><?= htmlspecialchars($i['il']) ?> (<?= (int)$i['n'] ?>)</option><?php endforeach; ?>
                </select></div>
              <div class="col-6 col-md-1"><label class="form-label fs-7">Segment</label>
                <select name="segment" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                  <?php foreach ($segmentler as $s): ?><option value="<?= htmlspecialchars($s['segment']) ?>" <?= $f['segment'] === $s['segment'] ? 'selected' : '' ?>><?= htmlspecialchars($s['segment']) ?></option><?php endforeach; ?>
                </select></div>
              <div class="col-6 col-md-1"><label class="form-label fs-7">Kanal</label>
                <select name="kanal" class="form-select form-select-sm form-select-solid"><option value="">Tümü</option>
                  <option value="wa" <?= $f['kanal'] === 'wa' ? 'selected' : '' ?>>WhatsApp</option>
                  <option value="mail" <?= $f['kanal'] === 'mail' ? 'selected' : '' ?>>E-posta</option>
                </select></div>
              <div class="col-12 col-md-2 d-flex gap-2">
                <?php if ($f['takip'] === 'bugun'): ?><input type="hidden" name="takip" value="bugun"><?php endif; ?>
                <button class="btn btn-sm btn-primary flex-grow-1">Filtrele</button>
                <a href="crm/leads.php" class="btn btn-sm btn-light">Temizle</a>
              </div>
            </form>
          </div>
        </div>

        <form method="post" action="crm/leads.php?<?= htmlspecialchars($qs(['sayfa' => $sayfa])) ?>" id="leadBulkForm">
          <?= csrfTokenField() ?>
          <div class="card">
            <div class="card-header border-0 pt-6 flex-wrap gap-3">
              <div class="card-title flex-column">
                <span class="fw-bold fs-4"><?= number_format($toplamFiltre, 0, ',', '.') ?> kayıt</span>
                <span class="text-muted fs-8">Bugün kampanya e-postası: <?= $bugunMail ?> / <?= $mk['gunluk_limit'] ?> · lead başına en fazla <?= $mk['max_mail'] ?>, arada en az <?= $mk['min_gun_arasi'] ?> gün</span>
              </div>
              <div class="card-toolbar d-flex gap-2 flex-wrap">
                <select name="sablon" class="form-select form-select-sm form-select-solid w-auto">
                  <?php foreach ($mailSablonlar as $s): ?><option value="<?= htmlspecialchars($s['kod']) ?>"><?= htmlspecialchars($s['ad']) ?></option><?php endforeach; ?>
                </select>
                <button type="submit" name="action" value="bulk_mail" class="btn btn-sm btn-light-info" onclick="return leadSecimVar('Seçili firmalara e-posta kuyruğa eklensin mi? Kurallara uymayanlar atlanır.')">Seçilenlere e-posta</button>
                <select name="bulk_durum" class="form-select form-select-sm form-select-solid w-auto">
                  <?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>"><?= htmlspecialchars($d[0]) ?></option><?php endforeach; ?>
                </select>
                <button type="submit" name="action" value="bulk_status" class="btn btn-sm btn-light" onclick="return leadSecimVar('Seçili kayıtların durumu değiştirilsin mi?')">Durumu değiştir</button>
              </div>
            </div>
            <div class="card-body pt-0">
              <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4">
                  <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                      <th class="w-25px"><input class="form-check-input" type="checkbox" id="leadTumu"></th>
                      <th class="min-w-250px">Firma</th>
                      <th class="min-w-80px">Puan</th>
                      <th class="min-w-200px">İletişim</th>
                      <th class="min-w-175px">Durum</th>
                      <th class="min-w-100px">Takip</th>
                      <th class="text-end"></th>
                    </tr>
                  </thead>
                  <tbody class="text-gray-700 fw-semibold">
                  <?php if (!$leads): ?>
                    <tr><td colspan="7" class="text-center text-muted py-10">
                      Kayıt yok. <a href="crm/lead_import.php">CSV içe aktararak</a> başlayın.
                    </td></tr>
                  <?php endif; ?>
                  <?php foreach ($leads as $l):
                      $gecikti = !empty($l['sonraki_takip']) && $l['sonraki_takip'] <= date('Y-m-d') && !in_array($l['durum'], LEAD_DURDUR, true);
                      [$waOk] = lead_gonderilebilir_mi($l, 'whatsapp');
                      [$mailOk, $mailNeden] = lead_gonderilebilir_mi($l, 'eposta');
                      $oncelikRenk = ['A' => 'success', 'B' => 'primary', 'C' => 'secondary'][$l['oncelik']] ?? 'secondary';
                  ?>
                    <tr>
                      <td><input class="form-check-input lead-sec" type="checkbox" name="ids[]" value="<?= (int)$l['lead_id'] ?>"></td>
                      <td>
                        <a href="crm/lead_detail.php?id=<?= (int)$l['lead_id'] ?>" class="text-gray-900 fw-bold text-hover-primary d-block"><?= htmlspecialchars($l['firma_adi']) ?></a>
                        <span class="text-muted fs-8"><?= htmlspecialchars(trim($l['ilce'] . ' / ' . $l['il'], ' /')) ?></span>
                        <?php if ($l['segment']): ?><span class="badge badge-light fs-9 ms-1"><?= htmlspecialchars($l['segment']) ?></span><?php endif; ?>
                        <?php if ((int)$l['imalat_montaj']): ?><span class="badge badge-light-primary fs-9">imalat/montaj</span><?php endif; ?>
                        <?php if ((int)$l['buyuk_marka']): ?><span class="badge badge-light-warning fs-9">büyük marka</span><?php endif; ?>
                      </td>
                      <td><span class="badge badge-<?= $oncelikRenk ?> fs-7"><?= htmlspecialchars($l['oncelik']) ?> · <?= (int)$l['puan'] ?></span>
                        <?php if ($l['google_puan'] !== null): ?><div class="text-muted fs-8 mt-1">★ <?= htmlspecialchars((string)$l['google_puan']) ?></div><?php endif; ?></td>
                      <td>
                        <div class="d-flex gap-1 flex-wrap align-items-center">
                        <?php if ($waOk): ?>
                          <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-light-success" data-bs-toggle="dropdown" title="WhatsApp">
                              <i class="ki-duotone ki-whatsapp fs-2"><span class="path1"></span><span class="path2"></span></i></button>
                            <ul class="dropdown-menu">
                              <?php foreach ($waSablonlar as $s): ?>
                              <li><a class="dropdown-item" target="_blank" rel="noopener" href="crm/lead_wa.php?id=<?= (int)$l['lead_id'] ?>&s=<?= urlencode($s['kod']) ?>&k=<?= urlencode($csrf) ?>"><?= htmlspecialchars($s['ad']) ?></a></li>
                              <?php endforeach; ?>
                            </ul>
                          </div>
                        <?php endif; ?>
                        <?php if (!empty($l['eposta'])): ?>
                          <span class="btn btn-sm btn-icon <?= $mailOk ? 'btn-light-info' : 'btn-light' ?>" title="<?= htmlspecialchars($l['eposta'] . ($mailOk ? '' : ' — ' . $mailNeden)) ?>"><i class="ki-duotone ki-sms fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                        <?php endif; ?>
                        <?php if (!empty($l['telefon'])): ?>
                          <a class="btn btn-sm btn-light fs-8 px-3" href="tel:<?= htmlspecialchars($l['telefon']) ?>"><?= htmlspecialchars($l['telefon']) ?></a>
                        <?php endif; ?>
                        <?php if (!empty($l['maps_url'])): ?>
                          <a class="btn btn-sm btn-icon btn-light" target="_blank" rel="noopener" href="<?= htmlspecialchars($l['maps_url']) ?>" title="Haritada aç"><i class="ki-duotone ki-geolocation fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                        <?php endif; ?>
                        </div>
                        <div class="text-muted fs-8 mt-1">WA <?= (int)$l['wa_sayisi'] ?> · e-posta <?= (int)$l['mail_sayisi'] ?><?= $l['son_iletisim'] ? ' · son ' . date('d.m', strtotime($l['son_iletisim'])) : '' ?></div>
                      </td>
                      <td>
                        <select class="form-select form-select-sm form-select-solid lead-durum" data-id="<?= (int)$l['lead_id'] ?>">
                          <?php foreach (LEAD_DURUMLAR as $kod => $d): ?><option value="<?= $kod ?>" <?= $l['durum'] === $kod ? 'selected' : '' ?>><?= htmlspecialchars($d[0]) ?></option><?php endforeach; ?>
                        </select>
                      </td>
                      <td class="<?= $gecikti ? 'text-danger fw-bold' : 'text-muted' ?>"><?= $l['sonraki_takip'] ? date('d.m.Y', strtotime($l['sonraki_takip'])) : '—' ?></td>
                      <td class="text-end"><a href="crm/lead_detail.php?id=<?= (int)$l['lead_id'] ?>" class="btn btn-sm btn-light-primary">Detay</a></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <?php if ($sayfaSayisi > 1): ?>
              <ul class="pagination justify-content-center mt-6">
                <?php for ($i = 1; $i <= $sayfaSayisi; $i++): ?>
                  <li class="page-item <?= $i === $sayfa ? 'active' : '' ?>"><a class="page-link" href="crm/leads.php?<?= htmlspecialchars($qs(['sayfa' => $i])) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
              </ul>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <form method="post" action="crm/leads.php?<?= htmlspecialchars($qs(['sayfa' => $sayfa])) ?>" id="leadDurumForm" class="d-none">
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
  if (tumu) tumu.addEventListener('change', function () {
    document.querySelectorAll('.lead-sec').forEach(function (c) { c.checked = tumu.checked; });
  });
  document.querySelectorAll('.lead-durum').forEach(function (s) {
    s.addEventListener('change', function () {
      var f = document.getElementById('leadDurumForm');
      f.lead_id.value = s.dataset.id; f.durum.value = s.value; f.submit();
    });
  });
  window.leadSecimVar = function (soru) {
    if (!document.querySelector('.lead-sec:checked')) { alertBox('Önce listeden en az bir firma seçin.'); return false; }
    return confirm(soru);
  };
  function alertBox(m) { if (window.Swal) { Swal.fire({ icon: 'info', text: m }); } else { alert(m); } }
})();
</script>
