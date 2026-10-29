<?php
/**
 * Prolyn Admin Panel - Lead içe aktarma (CSV, XLS, XLSX)
 * Beklenen başlıklar: firma_adi; telefon; whatsapp; eposta; web; il; ilce; adres; segment;
 * imalat_montaj; buyuk_marka; google_puan; puan; oncelik; kaynak; place_id; maps_url
 * (Yalnızca firma_adi zorunlu. CSV'de ayraç ; veya , olabilir, UTF-8. Excel dosyalarında ilk uygun sayfa okunur.)
 * Google Maps Scraper başlıkları da tanınır: title, phone, website, email, rating, address.
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
require_once __DIR__ . '/../functions/tablo_okuyucu.php';
lead_sema_kur($db);

/** Telefonu E.164 (+90...) biçimine getirir; Excel'deki 905321112233 / 0532 111 22 33 gibi yazımları da çevirir. */
function lead_import_tel(string $v): string
{
    $t = preg_replace('/[^\d+]/', '', $v);
    if ($t === '' || $t[0] === '+') { return $t; }
    $d = preg_replace('/\D/', '', $t);
    if (strlen($d) === 12 && substr($d, 0, 2) === '90') { return '+' . $d; }
    if (strlen($d) === 11 && $d[0] === '0') { return '+9' . $d; }
    if (strlen($d) === 10) { return '+90' . $d; }
    return $t;
}

/** Scraper başlıklarını bizim sütun adlarımıza eşler (hedef sütun dosyada zaten varsa dokunmaz). */
const LEAD_IMPORT_ESLEME = ['title' => 'firma_adi', 'phone' => 'telefon', 'website' => 'web', 'email' => 'eposta', 'rating' => 'google_puan', 'address' => 'adres'];

$sonuc = null; $hata = null;
// Web modu: yeni kayıt EKLEMEZ; mevcut leadlerin boş web alanını CSV'deki adresle doldurur
$webModu = ($_POST['mod'] ?? '') === 'web_guncelle';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'import') {
    $f = $_FILES['csv'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        $hata = 'Dosya yüklenemedi.';
    } elseif ($f['size'] > 10 * 1024 * 1024) {
        $hata = 'Dosya 10 MB sınırını aşıyor.';
    } elseif (!preg_match('/\.(csv|txt|xls|xlsx)$/i', (string)$f['name'])) {
        $hata = 'Yalnızca .csv, .xls veya .xlsx dosyası yükleyin.';
    } else {
        // Dosya türü uzantıya değil içerik imzasına göre belirlenir (güvenlik)
        $tur = tablo_dosya_turu($f['tmp_name']);
        $excelUzanti = (bool)preg_match('/\.xlsx?$/i', (string)$f['name']);
        $tablo = null;
        try {
            if ($excelUzanti && $tur === 'csv') {
                throw new TabloHatasi('Bu dosya gerçek bir Excel dosyası değil (metin/HTML olabilir). Excel\'de açıp .xlsx veya CSV olarak yeniden kaydedin.');
            }
            if ($tur === 'csv') {
                $ham = (string)file_get_contents($f['tmp_name']);
                $ham = preg_replace('/^\xEF\xBB\xBF/', '', $ham);
                if (!mb_check_encoding($ham, 'UTF-8')) { $ham = mb_convert_encoding($ham, 'UTF-8', 'Windows-1254'); }
                $ilkSatir = strtok($ham, "\n");
                $ayrac = substr_count((string)$ilkSatir, ';') >= substr_count((string)$ilkSatir, ',') ? ';' : ',';
                $fh = fopen('php://temp', 'r+'); fwrite($fh, $ham); rewind($fh);
                $tablo = [];
                while (($csvSatir = fgetcsv($fh, 0, $ayrac, '"', '\\')) !== false) { $tablo[] = $csvSatir; }
                fclose($fh);
            } else {
                $tablo = tablo_oku($f['tmp_name'], $tur, ['firma_adi', 'title']);
            }
        } catch (TabloHatasi $ex) {
            $hata = $ex->getMessage();
        }

        // İlk dolu satır başlıktır
        $basliklar = [];
        if ($tablo !== null) {
            while ($tablo && !array_filter($tablo[0], static fn($h) => trim((string)$h) !== '')) { array_shift($tablo); }
            $basliklar = array_map(static fn($b) => strtolower(trim((string)$b)), (array)array_shift($tablo));
            foreach ($basliklar as $i => $b) {
                if (isset(LEAD_IMPORT_ESLEME[$b]) && !in_array(LEAD_IMPORT_ESLEME[$b], $basliklar, true)) { $basliklar[$i] = LEAD_IMPORT_ESLEME[$b]; }
            }
        }
        if ($hata !== null) {
            // dosya okunamadı: mesaj hazır
        } elseif (!in_array('firma_adi', $basliklar, true)) {
            $hata = 'Başlık satırında "firma_adi" (veya Google Maps Scraper çıktısındaki "title") sütunu bulunamadı.';
        } else {
            $eklenen = 0; $tekrar = 0; $bos = 0; $webGuncellenen = 0;
            $varsayilanKaynak = $tur === 'csv' ? 'csv' : 'excel';
            foreach ($tablo as $satir) {
                if (!array_filter($satir, static fn($h) => trim((string)$h) !== '')) { continue; }
                $r = [];
                foreach ($basliklar as $i => $b) { $r[$b] = trim((string)($satir[$i] ?? '')); }
                $firma = mb_substr($r['firma_adi'] ?? '', 0, 255);
                if ($firma === '') { $bos++; continue; }
                $tel = lead_import_tel($r['telefon'] ?? '');
                $eposta = strtolower($r['eposta'] ?? '');
                if ($eposta !== '' && !filter_var($eposta, FILTER_VALIDATE_EMAIL)) { $eposta = ''; }
                $placeId = $r['place_id'] ?? '';

                // Tekrar kontrolü: place_id, telefon veya e-posta
                $var = null;
                if ($placeId !== '') { $var = $db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE place_id = ?", [$placeId]); }
                if (!$var && $tel !== '') { $var = $db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE telefon = ?", [$tel]); }
                if (!$var && $eposta !== '') { $var = $db->getColumn("SELECT lead_id FROM potansiyel_musteriler WHERE eposta = ?", [$eposta]); }
                if ($var) {
                    $tekrar++;
                    // Mevcut kaydın web alanı boşsa ve CSV'de geçerli http(s) adresi varsa doldur
                    $webCsv = mb_substr($r['web'] ?? '', 0, 255);
                    if ($webModu && preg_match('~^https?://~i', $webCsv)) {
                        $db->Update("UPDATE potansiyel_musteriler SET web = ? WHERE lead_id = ? AND (web IS NULL OR web = '')", [$webCsv, (int)$var]);
                        $webGuncellenen++;
                    }
                    continue;
                }
                if ($webModu) { continue; }   // web modunda listede olmayan firma eklenmez

                // İl/ilçe sütunu yoksa adresin sonundaki "34290 Küçükçekmece/İstanbul" kalıbından çıkar
                if (($r['il'] ?? '') === '' && preg_match('~([^,/]+)/([^,/]+)\s*$~u', (string)($r['adres'] ?? ''), $m)) {
                    $r['ilce'] = $r['ilce'] ?? trim($m[1]); $r['il'] = trim($m[2]);
                }
                $oncelik = in_array($r['oncelik'] ?? '', ['A', 'B', 'C'], true) ? $r['oncelik'] : 'B';
                $gp = is_numeric(str_replace(',', '.', $r['google_puan'] ?? '')) ? (float)str_replace(',', '.', $r['google_puan']) : null;
                $ok = $db->Insert("INSERT INTO potansiyel_musteriler
                    (firma_adi, telefon, whatsapp, eposta, web, il, ilce, adres, segment, imalat_montaj, buyuk_marka, google_puan, puan, oncelik, kaynak, place_id, maps_url, ret_token)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
                    $firma, $tel ?: null, (str_starts_with($tel, '+905') || ($r['whatsapp'] ?? '') === '1') ? 1 : 0, $eposta ?: null,
                    mb_substr($r['web'] ?? '', 0, 255) ?: null, mb_substr($r['il'] ?? '', 0, 60) ?: null, mb_substr(trim(preg_replace('/^\d{5}\s*/', '', $r['ilce'] ?? '')), 0, 80) ?: null,
                    mb_substr($r['adres'] ?? '', 0, 500) ?: null, mb_substr($r['segment'] ?? '', 0, 40) ?: null,
                    (int)(($r['imalat_montaj'] ?? '') === '1'), (int)(($r['buyuk_marka'] ?? '') === '1'), $gp,
                    max(0, min(100, (int)($r['puan'] ?? 0))), $oncelik, mb_substr($r['kaynak'] ?? $varsayilanKaynak, 0, 40) ?: $varsayilanKaynak,
                    $placeId !== '' ? mb_substr($placeId, 0, 120) : null, mb_substr($r['maps_url'] ?? '', 0, 255) ?: null, lead_yeni_token(),
                ]);
                if ($ok !== false) { $eklenen++; }
            }
            $sonuc = compact('eklenen', 'tekrar', 'bos', 'webGuncellenen', 'webModu');
        }
    }
}
require_once '../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-xxl d-flex flex-stack">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Lead İçe Aktar</h1>
        <a href="crm/leads.php" class="btn btn-sm btn-light">← Listeye dön</a>
      </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <?php if ($hata): ?><div class="alert alert-danger p-5 mb-6"><?= htmlspecialchars($hata) ?></div><?php endif; ?>
        <?php if ($sonuc): ?>
          <div class="alert alert-success p-5 mb-6">
            <?php if (!empty($sonuc['webModu'])): ?>
              <div class="fw-bold fs-5 mb-1">Web adresi işlenen kayıt: <?= (int)$sonuc['webGuncellenen'] ?></div>
              <div>Listede bulunan <?= (int)$sonuc['tekrar'] ?> firma eşleşti (web adresi zaten dolu olanlara dokunulmadı). Listede olmayan firmalar eklenmedi.</div>
            <?php else: ?>
            <div class="fw-bold fs-5 mb-1"><?= (int)$sonuc['eklenen'] ?> firma eklendi.</div>
            <div><?= (int)$sonuc['tekrar'] ?> kayıt zaten listede olduğu için atlandı<?= $sonuc['bos'] ? ', ' . (int)$sonuc['bos'] . ' satırda firma adı yoktu' : '' ?>.</div>
            <?php endif; ?>
            <a href="crm/leads.php" class="btn btn-sm btn-success mt-3">Listeye git</a>
          </div>
        <?php endif; ?>
        <div class="card">
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" action="crm/lead_import.php">
              <?= csrfTokenField() ?><input type="hidden" name="action" value="import">
              <label class="form-label fw-bold">Dosya (CSV, XLS veya XLSX)</label>
              <input type="file" name="csv" accept=".csv,.txt,.xls,.xlsx" class="form-control form-control-solid mb-4" required>
              <label class="form-check form-check-custom form-check-solid mb-4">
                <input class="form-check-input" type="checkbox" name="mod" value="web_guncelle">
                <span class="form-check-label">Yalnızca web adreslerini güncelle <span class="text-muted fs-8">(yeni firma eklemez; listede olan ve web alanı boş firmaların web adresini <code>place_id</code> / telefon eşleşmesiyle doldurur)</span></span>
              </label>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn btn-primary">İçe aktar</button>
                <a href="crm/lead_ornek.php" class="btn btn-light-primary">Örnek dosyayı indir</a>
              </div>
            </form>
            <div class="separator my-8"></div>
            <div class="text-gray-700 fs-6">
              <p class="fw-bold mb-2">Dosya biçimi</p>
              <p class="mb-2">Örnek dosyayı indirip Excel'de doldurun; <strong>.xlsx</strong>, <strong>.xls</strong> veya <strong>CSV</strong> olarak doğrudan yükleyebilirsiniz (CSV'ye çevirmeniz gerekmez). Birden fazla sayfa varsa başlığında <code>firma_adi</code> (ya da <code>title</code>) bulunan ilk sayfa okunur. En çok 20.000 satır, 10 MB. Örnekteki 3 satır uydurmadır; silerek kendi verinizi yazın.</p>
              <p class="mb-2">Google Maps Scraper çıktısı da olduğu gibi yüklenebilir: <code>title, phone, website, email, rating, address</code> sütunları otomatik tanınır; <code>905321112233</code> gibi telefonlar <code>+90…</code> biçimine çevrilir, il/ilçe adresten çıkarılır.</p>
              <p class="mb-2">İlk satır başlık olmalı. Zorunlu sütun yalnızca <code>firma_adi</code>. Desteklenen diğer sütunlar:</p>
              <p><code>telefon; whatsapp; eposta; web; il; ilce; adres; segment; imalat_montaj; buyuk_marka; google_puan; puan; oncelik; kaynak; place_id; maps_url</code></p>
              <p class="mb-0">Aynı <code>place_id</code>, telefon veya e-postaya sahip kayıtlar tekrar eklenmez; dosyayı birden fazla kez yüklemek güvenlidir.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
