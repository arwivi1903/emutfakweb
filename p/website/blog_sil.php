<?php
/**
 * Prolyn Admin Panel - Blog yazısı silme onayı (ayrı sayfa, modal yok)
 * GET: onay sayfası (silmez). POST + CSRF: yazıyı siler. Geçici gizleme için "Taslak" önerilir.
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$y = $id > 0 ? $db->getRowAssoc("SELECT id, title, status FROM blog_yazilari WHERE id = ?", [$id]) : null;
if (!$y) { echo "<script>window.location.href='website/blog.php';</script>"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->Delete("DELETE FROM blog_yazilari WHERE id = ?", [$id]);
    echo "<script>window.location.href='website/blog.php?success=3';</script>";
    exit;
}
$e = static fn($x) => htmlspecialchars((string)$x, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid"><h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Yazıyı Sil</h1></div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <form method="post" action="website/blog_sil.php?id=<?= (int)$id ?>" class="card mw-600px">
          <?= csrfTokenField() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
          <div class="card-body">
            <p class="fs-5 mb-2"><b><?= $e($y['title']) ?></b> kalıcı olarak silinecek.</p>
            <p class="text-muted fs-7 mb-0">Bu işlem geri alınamaz<?= $y['status'] === 'published' ? '; yazı sitede yayında olduğu için bağlantısı da çalışmaz hale gelir' : '' ?>. Yalnızca gizlemek istiyorsanız yazıyı düzenleyip "Taslak" yapın.</p>
          </div>
          <div class="card-footer d-flex justify-content-end gap-3">
            <a href="website/blog.php" class="btn btn-light">Vazgeç</a>
            <button class="btn btn-danger">Evet, sil</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
