<?php
/**
 * Prolyn Admin Panel - Video silme onayı (ayrı sayfa, modal yok)
 * GET: onay sayfası gösterir (silmez). POST + CSRF: kaydı listeden kaldırır (YouTube'daki video etkilenmez).
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$v = $id > 0 ? $db->getRowAssoc("SELECT id, title, thumbnail_url, youtube_video_id FROM videolar WHERE id = ?", [$id]) : null;
if (!$v) { echo "<script>window.location.href='website/videos.php';</script>"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->Delete("DELETE FROM videolar WHERE id = ?", [$id]);
    echo "<script>window.location.href='website/videos.php?success=3';</script>";
    exit;
}
$e = static fn($x) => htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid"><h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Videoyu Listeden Kaldır</h1></div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <form method="post" action="website/video_sil.php?id=<?= (int)$id ?>" class="card mw-600px">
          <?= csrfTokenField() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
          <div class="card-body">
            <img src="<?= $e($v['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . $v['youtube_video_id'] . '/hqdefault.jpg') ?>" alt="" class="w-100 rounded mb-5" style="aspect-ratio:16/9;object-fit:cover">
            <p class="fs-5 mb-2"><b><?= $e($v['title']) ?></b> sitedeki listeden kaldırılacak.</p>
            <p class="text-muted fs-7 mb-0">YouTube'daki video silinmez. Geçici olarak gizlemek için silmek yerine "Taslak" yapabilirsiniz.</p>
          </div>
          <div class="card-footer d-flex justify-content-end gap-3">
            <a href="website/videos.php" class="btn btn-light">Vazgeç</a>
            <button class="btn btn-danger">Evet, kaldır</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
