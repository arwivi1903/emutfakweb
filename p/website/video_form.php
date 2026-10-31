<?php
/**
 * Prolyn Admin Panel - Video Ekle/Düzenle (ayrı sayfa, modal yok)
 * Ekleme: yalnızca YouTube linki/ID'si gerekir; başlık, kanal ve kapak YouTube oEmbed'den (API anahtarsız) gelir.
 * Not: oEmbed "Listelenmemiş" videolarda çalışır, "Özel" videolarda çalışmaz.
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

/** Çeşitli YouTube link biçimlerinden 11 karakterlik video ID'sini çıkarır. */
function prolyn_youtube_id(string $input): ?string
{
    $input = trim($input);
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) { return $input; }
    foreach (['~youtu\.be/([a-zA-Z0-9_-]{11})~', '~youtube\.com/watch\?(?:[^#]*&)?v=([a-zA-Z0-9_-]{11})~', '~youtube\.com/(?:shorts|embed|live)/([a-zA-Z0-9_-]{11})~', '~youtube-nocookie\.com/embed/([a-zA-Z0-9_-]{11})~'] as $p) {
        if (preg_match($p, $input, $m)) { return $m[1]; }
    }
    return null;
}

/** YouTube oEmbed: başlık/kanal/kapak. Yalnızca sabit youtube.com adresine istek atılır (SSRF yok). */
function prolyn_youtube_oembed(string $videoId): ?array
{
    $url = 'https://www.youtube.com/oembed?format=json&url=' . urlencode('https://www.youtube.com/watch?v=' . $videoId);
    $ctx = stream_context_create(['http' => ['timeout' => 6], 'https' => ['timeout' => 6]]);
    $json = @file_get_contents($url, false, $ctx);
    $d = $json === false ? null : json_decode($json, true);
    if (!is_array($d) || empty($d['title'])) { return null; }
    return [
        'title' => (string)$d['title'],
        'author_name' => (string)($d['author_name'] ?? ''),
        'thumbnail_url' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
    ];
}

$id = (int)($_GET['id'] ?? 0);
$video = ['youtube_video_id' => '', 'title' => '', 'description' => '', 'kategori' => '', 'sure' => '', 'status' => 'published', 'order_no' => 0];
$duzenle = false;
if ($id > 0) {
    $var = $db->getRowAssoc("SELECT * FROM videolar WHERE id = ?", [$id]);
    if (!$var) { echo "<script>window.location.href='website/videos.php';</script>"; exit; }
    $video = array_merge($video, $var);
    $duzenle = true;
}

$hata = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baslik = trim((string)($_POST['title'] ?? ''));
    $aciklama = trim((string)($_POST['description'] ?? ''));
    $kategori = mb_substr(trim((string)($_POST['kategori'] ?? '')), 0, 60);
    $sure = trim((string)($_POST['sure'] ?? ''));
    $durum = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

    if ($sure !== '' && !preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $sure)) {
        $hata = 'Süreyi 04:35 veya 1:02:10 biçiminde yazın.';
    } elseif ($duzenle) {
        if ($baslik === '') {
            $hata = 'Başlık zorunludur.';
        } else {
            $db->Update("UPDATE videolar SET title=?, description=?, kategori=?, sure=?, status=? WHERE id=?",
                [$baslik, $aciklama ?: null, $kategori ?: null, $sure ?: null, $durum, $id]);
            echo "<script>window.location.href='website/videos.php?success=2';</script>";
            exit;
        }
    } else {
        $ytId = prolyn_youtube_id((string)($_POST['youtube_url'] ?? ''));
        if (!$ytId) {
            $hata = 'Geçerli bir YouTube video linki veya ID girin.';
        } elseif ($db->getRowAssoc("SELECT id FROM videolar WHERE youtube_video_id = ?", [$ytId])) {
            $hata = 'Bu video zaten listede.';
        } else {
            $meta = prolyn_youtube_oembed($ytId);
            if (!$meta) {
                $hata = 'Video bilgileri YouTube\'dan alınamadı. Video "Özel" olabilir; "Listelenmemiş" yapıp tekrar deneyin.';
            } else {
                $sira = (int)$db->getColumn("SELECT COALESCE(MAX(order_no), 0) FROM videolar") + 1;
                $db->Insert("INSERT INTO videolar (youtube_video_id, title, description, kategori, sure, thumbnail_url, author_name, order_no, status, created_by, created_at)
                             VALUES (?,?,?,?,?,?,?,?,?,?,NOW())",
                    [$ytId, $baslik !== '' ? $baslik : $meta['title'], $aciklama ?: null, $kategori ?: null, $sure ?: null,
                     $meta['thumbnail_url'], $meta['author_name'], $sira, $durum, $_SESSION['admin_id'] ?? null]);
                echo "<script>window.location.href='website/videos.php?success=1';</script>";
                exit;
            }
        }
    }
    $video = array_merge($video, ['title' => $baslik, 'description' => $aciklama, 'kategori' => $kategori, 'sure' => $sure, 'status' => $durum, 'youtube_video_id' => $_POST['youtube_url'] ?? $video['youtube_video_id']]);
}
$kategoriler = array_column((array)$db->getRowsAssoc("SELECT DISTINCT kategori FROM videolar WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori"), 'kategori');
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid d-flex flex-stack">
        <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0"><?= $duzenle ? 'Videoyu Düzenle' : 'Video Ekle' ?></h1>
        <a href="website/videos.php" class="btn btn-sm btn-light">Listeye dön</a>
      </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <?php if ($hata): ?><div class="alert alert-danger p-5 mb-8"><?= $e($hata) ?></div><?php endif; ?>
        <form method="post" action="website/video_form.php<?= $duzenle ? '?id=' . $id : '' ?>" class="card">
          <?= csrfTokenField() ?>
          <div class="card-body">
            <?php if ($duzenle): ?>
              <div class="mb-7 d-flex gap-5 align-items-center">
                <img src="<?= $e($video['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . $video['youtube_video_id'] . '/hqdefault.jpg') ?>" alt="" style="width:160px;aspect-ratio:16/9;object-fit:cover;border-radius:8px">
                <div class="text-muted fs-7">YouTube ID: <code><?= $e($video['youtube_video_id']) ?></code><br>Video değiştirilecekse bu kaydı silip yenisini ekleyin.</div>
              </div>
            <?php else: ?>
              <div class="mb-7">
                <label class="required fs-6 fw-semibold mb-2" for="yt">YouTube video linki</label>
                <input id="yt" type="text" class="form-control form-control-solid" name="youtube_url" value="<?= $e($video['youtube_video_id']) ?>" placeholder="https://www.youtube.com/watch?v=..." required>
                <div class="form-text">Başlık boş bırakılırsa YouTube'daki başlık kullanılır. Kapak görseli otomatik alınır.</div>
              </div>
            <?php endif; ?>
            <div class="mb-7">
              <label class="fs-6 fw-semibold mb-2 <?= $duzenle ? 'required' : '' ?>" for="t">Başlık</label>
              <input id="t" type="text" class="form-control form-control-solid" name="title" maxlength="255" value="<?= $e($video['title']) ?>" <?= $duzenle ? 'required' : '' ?>>
            </div>
            <div class="mb-7">
              <label class="fs-6 fw-semibold mb-2" for="d">Açıklama</label>
              <textarea id="d" class="form-control form-control-solid" name="description" rows="4" placeholder="Videoda neyin gösterildiğini 1-2 cümleyle yazın (sayfada ve arama sonuçlarında görünür)."><?= $e($video['description']) ?></textarea>
            </div>
            <div class="row">
              <div class="col-md-5 mb-7">
                <label class="fs-6 fw-semibold mb-2" for="k">Kategori</label>
                <input id="k" type="text" class="form-control form-control-solid" name="kategori" maxlength="60" list="kat" value="<?= $e($video['kategori']) ?>" placeholder="ör. Başlangıç">
                <datalist id="kat"><?php foreach ($kategoriler as $k): ?><option value="<?= $e($k) ?>"><?php endforeach; ?></datalist>
              </div>
              <div class="col-md-3 mb-7">
                <label class="fs-6 fw-semibold mb-2" for="s">Süre</label>
                <input id="s" type="text" class="form-control form-control-solid" name="sure" maxlength="8" value="<?= $e($video['sure']) ?>" placeholder="04:35">
              </div>
              <div class="col-md-4 mb-7">
                <label class="fs-6 fw-semibold mb-2" for="st">Durum</label>
                <select id="st" class="form-select form-select-solid" name="status">
                  <option value="published" <?= $video['status'] === 'published' ? 'selected' : '' ?>>Yayında</option>
                  <option value="draft" <?= $video['status'] === 'draft' ? 'selected' : '' ?>>Taslak (sitede görünmez)</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end gap-3">
            <a href="website/videos.php" class="btn btn-light">İptal</a>
            <button class="btn btn-primary">Kaydet</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
