<?php
/**
 * Prolyn Admin Panel - PHP Hata Dosyaları (error_log) Görüntüleyici
 *
 * Amaç: prolynweb + prolyn (app) altındaki tüm error_log dosyalarını sunucuda tek tek
 * gezmeden panelden izlemek.
 *
 * Güvenlik:
 *  - Kullanıcıdan DOSYA YOLU alınmaz. Dosyalar sunucuda taranır, "f" parametresi sadece
 *    tarama sonucundaki dosyanın sha1 kimliğidir (path traversal / LFI mümkün değil).
 *  - Taranan kökler dışına çıkış realpath ile ayrıca doğrulanır.
 *  - Sadece belirli dosya adları (error_log, php_error_log, *.log) listelenir.
 *  - Temizleme: yalnızca superadmin + POST + CSRF + ayrı onay sayfası (modal yok).
 *  - Çıktı htmlspecialchars ile escape edilir.
 *
 * Yapılandırma (.env): LOG_SCAN_ROOTS=/home/teknikse/public_html/prolyn,/home/teknikse/app.prolyn.net
 * (virgülle ayrılmış mutlak yollar). Boşsa yalnızca prolynweb kökü taranır.
 */

require_once __DIR__ . '/../config/header.php';
Auth::requireModule('view_logs');
require_once __DIR__ . '/../config/sidebar.php';

const PEL_MAX_DEPTH   = 6;          // tarama derinliği
const PEL_TAIL_BYTES  = 262144;     // dosyanın son 256 KB'ı okunur (büyük log'ta RAM koruması)
const PEL_MAX_ENTRIES = 300;        // ekranda gösterilecek en fazla kayıt
const PEL_SKIP_DIRS   = ['vendor', 'node_modules', '.git', 'storage', 'uploads', 'assets', 'dist'];

/** Taranacak kök dizinleri (realpath ile, tekrarsız). */
function pel_roots(): array
{
    $roots = [realpath(__DIR__ . '/../..')]; // prolynweb kökü
    $extra = function_exists('prolynweb_env') ? (string) prolynweb_env('LOG_SCAN_ROOTS', '') : '';
    foreach (array_filter(array_map('trim', explode(',', $extra))) as $p) {
        $rp = realpath($p);
        if ($rp !== false && is_dir($rp)) {
            $roots[] = $rp;
        }
    }
    return array_values(array_unique(array_filter($roots)));
}

/** Dosya adı izinli mi? */
function pel_is_log_name(string $name): bool
{
    return $name === 'error_log' || $name === 'php_error_log' || $name === 'php_errors.log'
        || (bool) preg_match('/^[A-Za-z0-9_.-]+\.log$/', $name);
}

/** Köklerde log dosyalarını bulur: id => [path, root, rel, size, mtime]. */
function pel_scan(): array
{
    $found = [];
    foreach (pel_roots() as $root) {
        $stack = [[$root, 0]];
        while ($stack) {
            [$dir, $depth] = array_pop($stack);
            $items = @scandir($dir);
            if ($items === false) { continue; }
            foreach ($items as $it) {
                if ($it === '.' || $it === '..') { continue; }
                $full = $dir . DIRECTORY_SEPARATOR . $it;
                if (is_link($full)) { continue; }                  // symlink ile kök dışına çıkışı engelle
                if (is_dir($full)) {
                    if ($depth < PEL_MAX_DEPTH && !in_array($it, PEL_SKIP_DIRS, true)) {
                        $stack[] = [$full, $depth + 1];
                    }
                } elseif (is_file($full) && pel_is_log_name($it)) {
                    $rp = realpath($full);
                    if ($rp === false || strpos($rp, $root . DIRECTORY_SEPARATOR) !== 0) { continue; }
                    $found[sha1($rp)] = [
                        'path'  => $rp,
                        'root'  => basename($root),
                        'rel'   => ltrim(substr($rp, strlen($root)), DIRECTORY_SEPARATOR),
                        'size'  => (int) @filesize($rp),
                        'mtime' => (int) @filemtime($rp),
                    ];
                }
            }
        }
    }
    // Proje / Dosya sütununa göre A-Z: önce proje adı, sonra dosya yolu (büyük/küçük harf duyarsız, doğal sıralama).
    uasort($found, static function ($a, $b) {
        $byRoot = strnatcasecmp($a['root'], $b['root']);
        return $byRoot !== 0 ? $byRoot : strnatcasecmp($a['rel'], $b['rel']);
    });
    return $found;
}

/** Dosyanın son PEL_TAIL_BYTES baytını okur. */
function pel_tail(string $path): string
{
    $size = (int) @filesize($path);
    $fh = @fopen($path, 'rb');
    if (!$fh) { return ''; }
    if ($size > PEL_TAIL_BYTES) {
        fseek($fh, -PEL_TAIL_BYTES, SEEK_END);
        fgets($fh); // yarım kalan ilk satırı at
    }
    $data = stream_get_contents($fh);
    fclose($fh);
    return (string) $data;
}

/** Ham metni kayıtlara böler ([tarih] ile başlayan satır = yeni kayıt, diğerleri devam satırı). */
function pel_parse(string $raw): array
{
    $entries = [];
    $cur = null;
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        if (preg_match('/^\[(\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2})(?: ([^\]]+))?\] (.*)$/', $line, $m)) {
            if ($cur) { $entries[] = $cur; }
            $msg = $m[3];
            $level = 'diger';
            if (stripos($msg, 'Fatal error') !== false)       { $level = 'fatal'; }
            elseif (stripos($msg, 'Parse error') !== false)   { $level = 'fatal'; }
            elseif (stripos($msg, 'Warning') !== false)       { $level = 'warning'; }
            elseif (stripos($msg, 'Notice') !== false)        { $level = 'notice'; }
            elseif (stripos($msg, 'Deprecated') !== false)    { $level = 'deprecated'; }
            elseif (stripos($msg, 'Hatası') !== false || stripos($msg, 'Exception') !== false) { $level = 'fatal'; }
            $cur = ['time' => $m[1], 'tz' => $m[2] ?? '', 'level' => $level, 'msg' => $msg];
        } elseif ($cur !== null && trim($line) !== '') {
            $cur['msg'] .= "\n" . $line; // stack trace vb.
        }
    }
    if ($cur) { $entries[] = $cur; }
    return array_reverse($entries); // en yeni üstte
}

function pel_h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function pel_size($b): string
{
    if ($b >= 1048576) { return number_format($b / 1048576, 1, ',', '.') . ' MB'; }
    if ($b >= 1024)    { return number_format($b / 1024, 1, ',', '.') . ' KB'; }
    return $b . ' B';
}

// Temizleme yalnızca superadmin (sunucu tarafında zorunlu; menüde gizlemek yetki sayılmaz)
$isSuper = (($_SESSION['role'] ?? '') === 'superadmin');
$files  = pel_scan();
$fileId = (string) ($_GET['f'] ?? '');
$cur    = ($fileId !== '' && preg_match('/^[a-f0-9]{40}$/', $fileId) && isset($files[$fileId])) ? $files[$fileId] : null;
$mode   = $cur ? (($_GET['sil'] ?? '') === '1' && $isSuper ? 'confirm' : 'view') : 'list';

// --- Temizleme (yalnızca POST + CSRF + geçerli dosya kimliği) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_log') {
    $pid = (string) ($_POST['f'] ?? '');
    if (!$isSuper || !validateCSRFToken($_POST['csrf_token'] ?? '') || !isset($files[$pid])) {
        echo "<script>window.location.href='logs/php_error_logs.php?hata=1';</script>";
        exit;
    }
    $ok = is_writable($files[$pid]['path']) && ($fh = @fopen($files[$pid]['path'], 'w')) && fclose($fh);
    error_log('[php_error_logs] Log temizlendi: ' . $files[$pid]['rel'] . ' admin_id=' . (int) ($_SESSION['admin_id'] ?? 0));
    echo "<script>window.location.href='logs/php_error_logs.php?" . ($ok ? 'temizlendi=1' : 'hata=2') . "';</script>";
    exit;
}

// --- Görünüm verisi ---
$entries = [];
$q       = trim((string) ($_GET['q'] ?? ''));
$lvl     = (string) ($_GET['lvl'] ?? '');
$levels  = ['fatal' => ['Fatal / Hata', 'danger'], 'warning' => ['Warning', 'warning'], 'notice' => ['Notice', 'info'],
            'deprecated' => ['Deprecated', 'secondary'], 'diger' => ['Diğer', 'dark']];
if ($mode === 'view') {
    $entries = pel_parse(pel_tail($cur['path']));
    if ($lvl !== '' && isset($levels[$lvl])) {
        $entries = array_values(array_filter($entries, fn($e) => $e['level'] === $lvl));
    }
    if ($q !== '') {
        $entries = array_values(array_filter($entries, fn($e) => stripos($e['msg'], $q) !== false));
    }
    $entries = array_slice($entries, 0, PEL_MAX_ENTRIES);
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">PHP Hata Dosyaları</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="logs/php_error_logs.php" class="text-muted text-hover-primary">Hata Dosyaları</a></li>
                        <?php if ($cur): ?>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark"><?= pel_h($cur['root'] . '/' . $cur['rel']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if (isset($_GET['temizlendi'])): ?>
                    <div class="alert alert-success mb-5">Log dosyası temizlendi.</div>
                <?php elseif (isset($_GET['hata'])): ?>
                    <div class="alert alert-danger mb-5">İşlem yapılamadı (dosya bulunamadı, yazma izni yok, oturum doğrulaması geçersiz veya yetkiniz yok; temizleme yalnızca superadmin).</div>
                <?php endif; ?>

<?php if ($mode === 'list'): ?>
                <div class="card shadow-sm">
                    <div class="card-header pt-6"><div class="card-title"><h3 class="fw-bold m-0">Bulunan log dosyaları (<?= count($files) ?>)</h3></div></div>
                    <div class="card-body pt-0 table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase">
                                <th>Proje / Dosya</th><th>Boyut</th><th>Son değişiklik</th><th class="text-end">İşlem</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($files as $id => $f): ?>
                                <tr>
                                    <td><span class="badge badge-light-primary me-2"><?= pel_h($f['root']) ?></span><?= pel_h($f['rel']) ?></td>
                                    <td><?= pel_size($f['size']) ?></td>
                                    <td><?= $f['mtime'] ? pel_h(date('d.m.Y H:i:s', $f['mtime'])) : '-' ?></td>
                                    <td class="text-end"><a class="btn btn-sm btn-light-primary" href="logs/php_error_logs.php?f=<?= pel_h($id) ?>">Görüntüle</a></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$files): ?>
                                <tr><td colspan="4" class="text-center text-muted py-10">Log dosyası bulunamadı. Taranan kökler: <?= pel_h(implode(', ', array_map('basename', pel_roots()))) ?>. Diğer proje için .env içine LOG_SCAN_ROOTS ekleyin.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

<?php elseif ($mode === 'confirm'): ?>
                <!-- Silme onayı: modal yerine ayrı adım -->
                <div class="card shadow-sm"><div class="card-body">
                    <h3 class="fw-bold text-danger mb-3">Log dosyası temizlensin mi?</h3>
                    <p><strong><?= pel_h($cur['root'] . '/' . $cur['rel']) ?></strong> (<?= pel_size($cur['size']) ?>) dosyasının tüm içeriği silinecek. Bu işlem geri alınamaz.</p>
                    <form method="post" action="logs/php_error_logs.php">
                        <?= csrfTokenField() ?>
                        <input type="hidden" name="action" value="clear_log">
                        <input type="hidden" name="f" value="<?= pel_h($fileId) ?>">
                        <button type="submit" class="btn btn-danger me-2">Evet, temizle</button>
                        <a href="logs/php_error_logs.php?f=<?= pel_h($fileId) ?>" class="btn btn-light">Vazgeç</a>
                    </form>
                </div></div>

<?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-header pt-6 flex-wrap gap-3">
                        <form method="get" action="logs/php_error_logs.php" class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="hidden" name="f" value="<?= pel_h($fileId) ?>">
                            <select name="lvl" class="form-select form-select-sm w-150px">
                                <option value="">Tüm seviyeler</option>
                                <?php foreach ($levels as $k => $l): ?>
                                    <option value="<?= pel_h($k) ?>" <?= $lvl === $k ? 'selected' : '' ?>><?= pel_h($l[0]) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="q" value="<?= pel_h($q) ?>" class="form-control form-control-sm w-250px" placeholder="Ara (mesaj / dosya yolu)">
                            <button class="btn btn-sm btn-primary">Filtrele</button>
                            <a class="btn btn-sm btn-light" href="logs/php_error_logs.php?f=<?= pel_h($fileId) ?>">Sıfırla</a>
                        </form>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-light" href="logs/php_error_logs.php">← Dosya listesi</a>
                            <?php if ($isSuper): ?>
                            <a class="btn btn-sm btn-light-danger" href="logs/php_error_logs.php?f=<?= pel_h($fileId) ?>&sil=1">Dosyayı temizle</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="text-muted fs-7 mb-4">
                            <?= pel_size($cur['size']) ?> · son değişiklik <?= pel_h(date('d.m.Y H:i:s', $cur['mtime'])) ?> ·
                            dosyanın son <?= (int) (PEL_TAIL_BYTES / 1024) ?> KB'ı okunur, en yeni üstte, en fazla <?= PEL_MAX_ENTRIES ?> kayıt gösterilir.
                        </div>
                        <?php if (!$entries): ?>
                            <div class="text-center text-muted py-10">Kayıt yok.</div>
                        <?php endif; ?>
                        <?php foreach ($entries as $e): $lv = $levels[$e['level']]; ?>
                            <div class="border-bottom py-3">
                                <span class="badge badge-light-<?= pel_h($lv[1]) ?> me-2"><?= pel_h($lv[0]) ?></span>
                                <span class="text-muted fs-7"><?= pel_h($e['time'] . ' ' . $e['tz']) ?></span>
                                <pre class="mb-0 mt-2 fs-7" style="white-space:pre-wrap;word-break:break-all;"><?= pel_h($e['msg']) ?></pre>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../config/footer.php'; ?>
