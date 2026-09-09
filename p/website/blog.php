<?php
/**
 * Prolyn Admin Panel - Blog Yönetimi
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

function prolyn_blog_slug(string $title): string
{
    $t = function_exists('mb_strtolower') ? mb_strtolower(trim($title), 'UTF-8') : strtolower(trim($title));
    $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
    $t = strtr($t, $map);
    $t = preg_replace('/[^a-z0-9]+/', '-', $t);
    return trim($t, '-');
}

// İşlemler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_post') {
        $title = trim($_POST['title']);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $cover_image = trim($_POST['cover_image'] ?? '');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $slug = prolyn_blog_slug($title);

        $base_slug = $slug;
        $i = 2;
        while ($db->getRowAssoc("SELECT id FROM blog_yazilari WHERE slug = ?", [$slug])) {
            $slug = $base_slug . '-' . $i;
            $i++;
        }

        $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $db->Insert(
            "INSERT INTO blog_yazilari (title, slug, excerpt, content, cover_image, status, published_at, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$title, $slug, $excerpt, $content, $cover_image, $status, $published_at, $_SESSION['admin_id'] ?? null]
        );

        echo "<script>window.location.href='blog.php?success=1';</script>";
        exit;
    } elseif ($_POST['action'] === 'edit_post') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $cover_image = trim($_POST['cover_image'] ?? '');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

        $existing = $db->getRowAssoc("SELECT status, published_at FROM blog_yazilari WHERE id = ?", [$id]);
        $published_at = $existing['published_at'] ?? null;
        if ($status === 'published' && empty($published_at)) {
            $published_at = date('Y-m-d H:i:s');
        }

        $db->Update(
            "UPDATE blog_yazilari SET title=?, excerpt=?, content=?, cover_image=?, status=?, published_at=?, updated_at=NOW() WHERE id=?",
            [$title, $excerpt, $content, $cover_image, $status, $published_at, $id]
        );

        echo "<script>window.location.href='blog.php?success=2';</script>";
        exit;
    } elseif ($_POST['action'] === 'delete_post') {
        $id = (int)$_POST['id'];
        $db->Delete("DELETE FROM blog_yazilari WHERE id = ?", [$id]);

        echo "<script>window.location.href='blog.php?success=3';</script>";
        exit;
    }
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
                    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Yazı Ekle
                    </button>
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
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_<?= $p['id'] ?>">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-light-danger btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_<?= $p['id'] ?>">
                                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="kt_modal_edit_<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered mw-750px">
                                                <div class="modal-content">
                                                    <form class="form" action="" method="post">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="edit_post">
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Yazıyı Düzenle</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10 px-lg-17">
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                                                <input type="text" class="form-control form-control-solid" name="title" value="<?= htmlspecialchars($p['title']) ?>" required />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="fs-6 fw-semibold mb-2">Kısa Özet</label>
                                                                <textarea class="form-control form-control-solid" name="excerpt" rows="2"><?= htmlspecialchars($p['excerpt'] ?? '') ?></textarea>
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="fs-6 fw-semibold mb-2">Kapak Görseli URL</label>
                                                                <input type="text" class="form-control form-control-solid" name="cover_image" value="<?= htmlspecialchars($p['cover_image'] ?? '') ?>" />
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                                                                <textarea class="form-control form-control-solid" name="content" rows="10" required><?= htmlspecialchars($p['content']) ?></textarea>
                                                            </div>
                                                            <div class="fv-row mb-7">
                                                                <label class="required fs-6 fw-semibold mb-2">Durum</label>
                                                                <select class="form-select form-select-solid" name="status">
                                                                    <option value="draft" <?= $p['status'] === 'draft' ? 'selected' : '' ?>>Taslak</option>
                                                                    <option value="published" <?= $p['status'] === 'published' ? 'selected' : '' ?>>Yayında</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer flex-center">
                                                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-primary"><span class="indicator-label">Kaydet</span></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="kt_modal_delete_<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered mw-400px">
                                                <div class="modal-content">
                                                    <form class="form" action="" method="post">
                                                        <?= csrfTokenField() ?>
                                                        <input type="hidden" name="action" value="delete_post">
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <div class="modal-header">
                                                            <h2 class="fw-bold">Yazıyı Sil</h2>
                                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </div>
                                                        </div>
                                                        <div class="modal-body py-10">
                                                            <p><strong><?= htmlspecialchars($p['title']) ?></strong> başlıklı yazıyı silmek istediğinize emin misiniz?</p>
                                                        </div>
                                                        <div class="modal-footer flex-center">
                                                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                                            <button type="submit" class="btn btn-danger">Evet, Sil</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted">Henüz kayıt eklenmemiş.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Modal -->
                <div class="modal fade" id="kt_modal_add" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered mw-750px">
                        <div class="modal-content">
                            <form class="form" action="" method="post">
                                <?= csrfTokenField() ?>
                                <input type="hidden" name="action" value="add_post">
                                <div class="modal-header">
                                    <h2 class="fw-bold">Yeni Yazı Ekle</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-lg-17">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                        <input type="text" class="form-control form-control-solid" name="title" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="fs-6 fw-semibold mb-2">Kısa Özet</label>
                                        <textarea class="form-control form-control-solid" name="excerpt" rows="2" placeholder="Liste sayfasında görünecek kısa açıklama"></textarea>
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="fs-6 fw-semibold mb-2">Kapak Görseli URL</label>
                                        <input type="text" class="form-control form-control-solid" name="cover_image" placeholder="https://..." />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                                        <textarea class="form-control form-control-solid" name="content" rows="10" required></textarea>
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Durum</label>
                                        <select class="form-select form-select-solid" name="status">
                                            <option value="draft">Taslak</option>
                                            <option value="published">Yayında</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer flex-center">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                    <button type="submit" class="btn btn-primary"><span class="indicator-label">Kaydet</span></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once '../config/footer.php'; ?>
</div>
