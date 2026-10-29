<?php
/**
 * Prolyn Admin Panel - Sistem Dosyaları (kiracı bazlı depolama raporu)
 *
 * prolyn (app) uygulamasının yükleme kökünü (uploads/K<kurum_id>/… ve uploads/acik/K<kurum_id>/…)
 * tarar ve kurum başına boyut / dosya sayısı / alan dağılımını gösterir.
 *
 * Salt OKUNUR: silme/taşıma yoktur (eski sayfadaki CSRF'siz DB silmesi kaldırıldı).
 * Kullanıcıdan yol alınmaz; yalnızca .env'deki kök taranır.
 *
 * .env:  PROLYN_UPLOADS_PATH=/home/teknikse/public_html/app/uploads   (app'in PROLYN_UPLOAD_DIR / uploads klasörü)
 *        Boşsa ../prolyn/uploads (yerel XAMPP düzeni) denenir.
 */

require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_settings');
require_once __DIR__ . '/../config/sidebar.php';

const SD_MAX_DOSYA = 200000; // paylaşımlı hosting koruması: tarama üst sınırı

function sd_h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function sd_boyut($b): string
{
    if ($b >= 1073741824) { return number_format($b / 1073741824, 2, ',', '.') . ' GB'; }
    if ($b >= 1048576)    { return number_format($b / 1048576, 1, ',', '.') . ' MB'; }
    if ($b >= 1024)       { return number_format($b / 1024, 1, ',', '.') . ' KB'; }
    return (int) $b . ' B';
}

/** Paket görünen adını plan koduna çevirir: basic | pro | enterprise | null. */
function sd_plan_kodu(?string $ad): ?string
{
    $a = mb_strtolower(trim((string) $ad), 'UTF-8');
    $a = strtr($a, ['ı' => 'i', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c']);
    if ($a === '') { return null; }
    if (strpos($a, 'baslangic') !== false || $a === 'basic' || $a === 'starter') { return 'basic'; }
    if (strpos($a, 'kurumsal') !== false || strpos($a, 'enterprise') !== false)  { return 'enterprise'; }
    if (strpos($a, 'pro') !== false)                                              { return 'pro'; }
    return null;
}

/** Yükleme kökünü bulur (env → yerel varsayılan). */
function sd_kok(): ?string
{
    $env = function_exists('prolynweb_env') ? trim((string) prolynweb_env('PROLYN_UPLOADS_PATH', '')) : '';
    $adaylar = [];
    if ($env !== '') { $adaylar[] = $env; }
    $adaylar[] = realpath(__DIR__ . '/../../..') . '/prolyn/uploads';   // yerel: htdocs/prolyn/uploads
    foreach ($adaylar as $a) {
        $rp = realpath($a);
        if ($rp !== false && is_dir($rp)) { return $rp; }
    }
    return null;
}

/** Bir dizini özyinelemeli tarar: [boyut, adet, son değişiklik, .php sayısı]. Symlink izlenmez. */
function sd_tara(string $dir, int &$toplamAdet): array
{
    $r = ['size' => 0, 'count' => 0, 'mtime' => 0, 'php' => 0, 'kesik' => false];
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach ($it as $f) {
            if ($f->isLink() || !$f->isFile()) { continue; }
            if ($f->getFilename() === '.htaccess' || $f->getFilename() === '.DS_Store') { continue; }
            if (++$toplamAdet > SD_MAX_DOSYA) { $r['kesik'] = true; break; }
            $r['size'] += $f->getSize();
            $r['count']++;
            $r['mtime'] = max($r['mtime'], $f->getMTime());
            if (preg_match('/\.(php\d?|phtml|phar)$/i', $f->getFilename())) { $r['php']++; }
        }
    } catch (Throwable $e) {
        $r['kesik'] = true;
    }
    return $r;
}

$kok = sd_kok();
$kurumlar = [];   // kurum_id => ad
$kimlikKolonu = 'customer_id';
try {
    $kol = array_column($db->getRowsAssoc("SHOW COLUMNS FROM kurumlar") ?: [], 'Field');
    if (in_array('id', $kol, true)) { $kimlikKolonu = 'id'; } // app: kurumlar.id → K<id>
    foreach (($db->getRowsAssoc("SELECT $kimlikKolonu AS kid, company_name FROM kurumlar") ?: []) as $k) {
        $kurumlar[(int) $k['kid']] = (string) $k['company_name'];
    }
} catch (Throwable $e) { /* kurum adları alınamazsa yalnızca K<id> gösterilir */ }

// Paket başına depolama limiti (GB). null = sınırsız.
// Varsayılanlar: Başlangıç 5 GB, Pro 50 GB, Kurumsal sınırsız. paket_ozellikleri.max_storage_gb > 0 ise
// Başlangıç/Pro için o değer kullanılır (panelden değiştirilebilsin); Kurumsal her zaman sınırsız.
$limitler = ['basic' => 5, 'pro' => 50, 'enterprise' => null];
try {
    foreach (($db->getRowsAssoc("SELECT plan_name, max_storage_gb FROM paket_ozellikleri") ?: []) as $pl) {
        $kod = sd_plan_kodu($pl['plan_name']);
        if (($kod === 'basic' || $kod === 'pro') && (int) $pl['max_storage_gb'] > 0) {
            $limitler[$kod] = (int) $pl['max_storage_gb'];
        }
    }
} catch (Throwable $e) { /* varsayılanlar geçerli */ }

$paketler = [];   // kurum_id => plan kodu
try {
    foreach (($db->getRowsAssoc("SELECT $kimlikKolonu AS kid, musteri_paketi FROM kurumlar") ?: []) as $k) {
        $paketler[(int) $k['kid']] = ['kod' => sd_plan_kodu($k['musteri_paketi']), 'ad' => (string) $k['musteri_paketi']];
    }
} catch (Throwable $e) { /* musteri_paketi kolonu yoksa paket gösterilmez */ }

$rapor = [];      // kid => ['alan' => [...], 'size','count','mtime','php','kesik']
$genelAdet = 0;
if ($kok !== null) {
    // Kapalı alan: uploads/K<id>/<alan>   |  Açık alan: uploads/acik/K<id>/<alan>
    foreach (['' => 'kapali', 'acik' => 'acik'] as $alt => $tip) {
        $taban = $alt === '' ? $kok : $kok . DIRECTORY_SEPARATOR . $alt;
        foreach ((@scandir($taban) ?: []) as $ad) {
            if (!preg_match('/^K(\d+)$/', $ad, $m)) { continue; }
            $kid = (int) $m[1];
            $kdir = $taban . DIRECTORY_SEPARATOR . $ad;
            if (!is_dir($kdir) || is_link($kdir)) { continue; }
            foreach ((@scandir($kdir) ?: []) as $alan) {
                if ($alan === '.' || $alan === '..') { continue; }
                $adir = $kdir . DIRECTORY_SEPARATOR . $alan;
                if (is_link($adir)) { continue; }
                if (is_dir($adir)) {
                    $s = sd_tara($adir, $genelAdet);
                } else { // alan klasörü dışında doğrudan duran dosya
                    $s = ['size' => (int) @filesize($adir), 'count' => 1, 'mtime' => (int) @filemtime($adir),
                          'php' => preg_match('/\.(php\d?|phtml|phar)$/i', $alan) ? 1 : 0, 'kesik' => false];
                    $alan = '(kökte) ' . $alan;
                }
                $etiket = ($tip === 'acik' ? 'açık/' : '') . $alan;
                $rapor[$kid]['alanlar'][$etiket] = $s;
                foreach (['size', 'count', 'php'] as $c) { $rapor[$kid][$c] = ($rapor[$kid][$c] ?? 0) + $s[$c]; }
                $rapor[$kid]['mtime'] = max($rapor[$kid]['mtime'] ?? 0, $s['mtime']);
                $rapor[$kid]['kesik'] = ($rapor[$kid]['kesik'] ?? false) || $s['kesik'];
            }
            $rapor[$kid]['alanlar'] = $rapor[$kid]['alanlar'] ?? [];
        }
    }
    uasort($rapor, fn($a, $b) => ($b['size'] ?? 0) <=> ($a['size'] ?? 0));
}

$topSize = $topCount = $topPhp = $yetim = $kotaUyari = 0;
foreach ($rapor as $kid => $r) {
    $topSize += $r['size'] ?? 0; $topCount += $r['count'] ?? 0; $topPhp += $r['php'] ?? 0;
    if (!isset($kurumlar[$kid])) { $yetim++; }
    $kod = $paketler[$kid]['kod'] ?? null;
    if ($kod && $limitler[$kod] !== null && ($r['size'] ?? 0) >= 0.9 * $limitler[$kod] * 1073741824) { $kotaUyari++; }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Sistem Dosyaları</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">Yönetim Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Kiracı Depolama Raporu</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

<?php if ($kok === null): ?>
                <div class="alert alert-warning">
                    Uygulamanın yükleme klasörü bulunamadı. Sunucudaki <code>.env</code> dosyasına
                    <code>PROLYN_UPLOADS_PATH=/mutlak/yol/uploads</code> satırını ekleyin (app'in <code>uploads</code> klasörü).
                </div>
<?php else: ?>
                <div class="row g-5 mb-5">
                    <div class="col-6 col-lg-3"><div class="card card-body"><div class="text-muted fs-7">Toplam boyut</div><div class="fs-2 fw-bold"><?= sd_boyut($topSize) ?></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-body"><div class="text-muted fs-7">Dosya sayısı</div><div class="fs-2 fw-bold"><?= number_format($topCount, 0, ',', '.') ?></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-body"><div class="text-muted fs-7">Klasörü olan kurum</div><div class="fs-2 fw-bold"><?= count($rapor) ?></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-body"><div class="text-muted fs-7">Uyarı</div>
                        <div class="fs-2 fw-bold <?= ($topPhp || $yetim || $kotaUyari || isset($rapor[0])) ? 'text-danger' : 'text-success' ?>"><?= $topPhp + $yetim + $kotaUyari + (isset($rapor[0]) ? 1 : 0) ?></div></div></div>
                </div>

                <?php if ($kotaUyari): ?>
                    <div class="alert alert-warning"><strong>Kota:</strong> <?= (int) $kotaUyari ?> kurum paket depolama limitinin %90'ına ulaştı veya aştı.</div>
                <?php endif; ?>
                <?php if ($topPhp): ?>
                    <div class="alert alert-danger"><strong>Güvenlik:</strong> yükleme klasörlerinde <?= (int) $topPhp ?> adet <code>.php</code> uzantılı dosya var. Yükleme alanında çalıştırılabilir dosya bulunmamalı; incelenip silinmeli.</div>
                <?php endif; ?>
                <?php if (isset($rapor[0])): ?>
                    <div class="alert alert-warning"><strong>K0 klasörü var:</strong> kurum kimliği çözülemeden yapılmış yüklemeler olmuş. Dosyalar hangi kuruma ait olduğu belirlenerek taşınmalı.</div>
                <?php endif; ?>
                <?php if ($genelAdet > SD_MAX_DOSYA): ?>
                    <div class="alert alert-info">Tarama <?= number_format(SD_MAX_DOSYA, 0, ',', '.') ?> dosyada kesildi; rakamlar eksik olabilir.</div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header pt-6"><div class="card-title"><h3 class="fw-bold m-0">Kurum bazlı depolama</h3></div></div>
                    <div class="card-body pt-0 table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                <th>Klasör</th><th>Kurum / Paket</th><th>Kullanım</th><th>Alan dağılımı</th><th class="text-end">Dosya</th><th class="text-end">Boyut</th><th>Son yükleme</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rapor as $kid => $r): ?>
                                <tr>
                                    <td><span class="badge badge-light-primary">K<?= (int) $kid ?></span></td>
                                    <td>
                                        <?php if (isset($kurumlar[$kid])): ?>
                                            <?= sd_h($kurumlar[$kid]) ?>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">Kurum kaydı yok</span>
                                        <?php endif; ?>
                                        <?php if (!empty($paketler[$kid]['ad'])): ?><span class="badge badge-light-info ms-1"><?= sd_h($paketler[$kid]['ad']) ?></span><?php endif; ?>
                                        <?php if (!empty($r['php'])): ?><span class="badge badge-danger ms-1"><?= (int) $r['php'] ?> .php</span><?php endif; ?>
                                    </td>
                                    <td style="min-width:200px">
                                        <?php
                                        $kod = $paketler[$kid]['kod'] ?? null;
                                        $lim = $kod ? $limitler[$kod] : false;      // false: paket bilinmiyor, null: sınırsız
                                        $kul = (int) ($r['size'] ?? 0);
                                        if ($lim === false): ?>
                                            <span class="text-muted fs-7">Paket tanımsız</span>
                                        <?php elseif ($lim === null): ?>
                                            <span class="fs-7"><?= sd_boyut($kul) ?> / Sınırsız</span>
                                        <?php else:
                                            $limB = $lim * 1073741824;
                                            $oran = $limB > 0 ? $kul / $limB * 100 : 0;
                                            $renk = $oran >= 90 ? 'danger' : ($oran >= 70 ? 'warning' : 'success'); ?>
                                            <div class="d-flex justify-content-between fs-7 mb-1">
                                                <span><?= sd_boyut($kul) ?> / <?= (int) $lim ?> GB</span>
                                                <span class="fw-bold text-<?= $renk ?>">%<?= number_format($oran, $oran < 10 ? 1 : 0, ',', '.') ?></span>
                                            </div>
                                            <div class="progress h-6px"><div class="progress-bar bg-<?= $renk ?>" style="width:<?= min(100, round($oran, 1)) ?>%"></div></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fs-7 text-gray-600">
                                        <?php foreach ($r['alanlar'] as $alan => $s): ?>
                                            <div><?= sd_h($alan) ?>: <?= (int) $s['count'] ?> dosya · <?= sd_boyut($s['size']) ?></div>
                                        <?php endforeach; ?>
                                        <?php if (!$r['alanlar']): ?><span class="text-muted">boş</span><?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= number_format($r['count'] ?? 0, 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold"><?= sd_boyut($r['size'] ?? 0) ?></td>
                                    <td><?= !empty($r['mtime']) ? sd_h(date('d.m.Y H:i', $r['mtime'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$rapor): ?>
                                <tr><td colspan="7" class="text-center text-muted py-10">Yükleme klasöründe kurum klasörü (K&lt;id&gt;) yok.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="text-muted fs-8 mt-3">Kök: <?= sd_h(basename(dirname($kok)) . '/' . basename($kok)) ?> · salt okunur rapor (silme/taşıma yok) · belgeler kapalı klasörde, <em>açık/</em> altındakiler (profil fotoğrafı, logo) herkese açık.</div>
                    </div>
                </div>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../config/footer.php'; ?>
