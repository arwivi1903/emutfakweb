<?php
/**
 * Prolyn Admin Panel - Video Rehberleri (liste)
 * Ekleme/düzenleme: video_form.php, silme onayı: video_sil.php (modal kullanılmaz).
 * Videolar YouTube'da "Listelenmemiş" (unlisted) yüklenir; sitede yalnızca burada yayında olanlar görünür.
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Sıra değiştirme (yukarı/aşağı): komşu kayıtla order_no takas edilir.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sirala') {
    $id = (int)($_POST['id'] ?? 0);
    $yon = ($_POST['yon'] ?? '') === 'yukari' ? 'yukari' : 'asagi';
    $tum = $db->getRowsAssoc("SELECT id FROM videolar ORDER BY order_no ASC, id DESC");
    $idler = array_map('intval', array_column((array)$tum, 'id'));
    $k = array_search($id, $idler, true);
    if ($k !== false) {
        $h = $yon === 'yukari' ? $k - 1 : $k + 1;
        if (isset($idler[$h])) { [$idler[$k], $idler[$h]] = [$idler[$h], $idler[$k]]; }
        // Sıra numaralarını 1..n olarak yeniden yaz (eşit order_no çakışmalarını da giderir)
        foreach ($idler as $n => $vid) { $db->Update("UPDATE videolar SET order_no = ? WHERE id = ?", [$n + 1, $vid]); }
    }
    echo "<script>window.location.href='website/videos.php';</script>";
    exit;
}

$videolar = $db->getRowsAssoc("SELECT * FROM videolar ORDER BY order_no ASC, id DESC");
$bildirim = ['1' => 'Video eklendi.', '2' => 'Video güncellendi.', '3' => 'Video listeden kaldırıldı.'][$_GET['success'] ?? ''] ?? null;
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid d-flex flex-stack">
        <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
          <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Video Rehberleri</h1>
          <span class="text-muted fs-7">Sitede <a href="../../video" target="_blank" rel="noopener">/video</a> sayfasında, buradaki sıraya göre görünür</span>
        </div>
        <a href="website/video_form.php" class="btn btn-sm fw-bold btn-primary"><i class="ki-duotone ki-plus fs-2"></i> Video Ekle</a>
      </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div id="kt_app_content_container" class="app-container container-xxl">
        <?php if ($bildirim): ?><div class="alert alert-success p-5 mb-8"><?= $e($bildirim) ?></div><?php endif; ?>

        <div class="alert alert-light-primary border border-primary border-dashed p-5 mb-8 fs-7">
          <b>YouTube'a yüklerken görünürlük:</b> "Listelenmemiş" (Unlisted) seçin. "Özel" (Private) videolar sitede oynatılamaz.
          Listelenmemiş video kanalda ve aramada görünmez; yalnızca bu sayfadan izlenir.
        </div>

        <div class="card">
          <div class="card-body py-4">
            <div class="table-responsive">
              <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                  <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th style="width:140px">Kapak</th><th>Başlık</th><th>Kategori</th><th>Süre</th><th>Durum</th><th class="text-end">İşlemler</th>
                  </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                <?php if ($videolar): foreach ($videolar as $n => $v): ?>
                  <tr>
                    <td><img src="<?= $e($v['thumbnail_url'] ?: 'https://i.ytimg.com/vi/' . $v['youtube_video_id'] . '/hqdefault.jpg') ?>" alt="" style="width:120px;aspect-ratio:16/9;object-fit:cover;border-radius:6px" loading="lazy"></td>
                    <td>
                      <span class="text-gray-800 fw-bold d-block"><?= ($n + 1) ?>. <?= $e($v['title']) ?></span>
                      <?php if (trim((string)($v['description'] ?? '')) !== ''): ?><span class="text-muted fs-8 d-block"><?= $e(mb_strimwidth($v['description'], 0, 90, '…')) ?></span><?php endif; ?>
                    </td>
                    <td><?= $e($v['kategori'] ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                    <td><?= $e($v['sure'] ?? '') ?: '<span class="text-muted">-</span>' ?></td>
                    <td><span class="badge badge-light-<?= $v['status'] === 'published' ? 'success' : 'warning' ?>"><?= $v['status'] === 'published' ? 'Yayında' : 'Taslak' ?></span></td>
                    <td class="text-end text-nowrap">
                      <form method="post" action="website/videos.php" class="d-inline">
                        <?= csrfTokenField() ?><input type="hidden" name="action" value="sirala"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                        <button name="yon" value="yukari" class="btn btn-icon btn-light btn-sm" title="Yukarı taşı" aria-label="Yukarı taşı" <?= $n === 0 ? 'disabled' : '' ?>><i class="bi bi-arrow-up"></i></button>
                        <button name="yon" value="asagi" class="btn btn-icon btn-light btn-sm" title="Aşağı taşı" aria-label="Aşağı taşı" <?= $n === count($videolar) - 1 ? 'disabled' : '' ?>><i class="bi bi-arrow-down"></i></button>
                      </form>
                      <a href="https://www.youtube.com/watch?v=<?= urlencode($v['youtube_video_id']) ?>" target="_blank" rel="noopener" class="btn btn-icon btn-light-danger btn-sm" title="YouTube'da aç"><i class="bi bi-youtube"></i></a>
                      <a href="website/video_form.php?id=<?= (int)$v['id'] ?>" class="btn btn-icon btn-light-primary btn-sm" title="Düzenle"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></a>
                      <a href="website/video_sil.php?id=<?= (int)$v['id'] ?>" class="btn btn-icon btn-light-danger btn-sm" title="Sil"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></a>
                    </td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="6" class="text-center text-muted py-10">Henüz video eklenmemiş. "Video Ekle" ile YouTube bağlantısını girin.</td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
