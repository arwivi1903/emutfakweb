<?php
/**
 * Prolyn Admin Panel - Blog Yönetimi
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    $id = (int)$_POST['id'];
    $db->Delete("DELETE FROM blog_yazilari WHERE id = ?", [$id]);
    echo "<script>window.location.href='blog.php?success=3';</script>";
    exit;
}

$posts = $db->getRowsAssoc("SELECT * FROM blog_yazilari ORDER BY created_at DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Blog Yönetimi</h1>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="website/blog_form.php" class="btn btn-sm fw-bold btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Yazı Ekle
                    </a>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İşlem başarıyla tamamlandı.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Yazı Listesi</span>
                        </h3>
                    </div>
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-70px">Kapak</th>
                                        <th class="min-w-250px">Başlık</th>
                                        <th class="min-w-100px">Durum</th>
                                        <th class="min-w-125px">Yayın Tarihi</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php if ($posts): ?>
                                        <?php foreach ($posts as $p): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($p['cover_image'])): ?>
                                                    <?php $kapakSrc = strpos($p['cover_image'], 'http') === 0 ? $p['cover_image'] : '../' . $p['cover_image']; ?>
                                                    <img src="<?= htmlspecialchars($kapakSrc) ?>" style="width:56px;height:40px;object-fit:cover;border-radius:6px;" alt="" />
                                                <?php else: ?>
                                                    <div style="width:56px;height:40px;border-radius:6px;background:linear-gradient(135deg,#667eea,#764ba2);"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6"><?= htmlspecialchars($p['title']) ?></span>
                                                <span class="text-muted fw-semibold d-block fs-8">/blog/<?= htmlspecialchars($p['slug']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($p['status'] === 'published'): ?>
                                                    <span class="badge badge-light-success">Yayında</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-warning">Taslak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= !empty($p['published_at']) ? date('d.m.Y H:i', strtotime($p['published_at'])) : '-' ?></td>
                                            <td class="text-end">
                                                <a href="website/blog_form.php?id=<?= $p['id'] ?>" class="btn btn-icon btn-light-primary btn-sm me-1" title="Düzenle">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                                <form action="website/blog.php" method="post" style="display:inline-block;">
                                                    <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="delete_post">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm" title="Sil"
                                                        onclick="Swal.fire({title: 'Emin misiniz?', text: '<?= htmlspecialchars(addslashes($p['title'])) ?> başlıklı yazı silinecek.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, Sil', cancelButtonText: 'İptal'}).then((result) => { if(result.isConfirmed) { this.closest('form').submit(); } });">
                                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted">Henüz kayıt eklenmemiş.</td></tr>
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
