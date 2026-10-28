<?php
/**
 * Prolyn Admin Panel - Video (YouTube) Yönetimi
 *
 * Video eklerken sadece YouTube linki/ID'si girilir; başlık, kanal adı ve
 * kapak görseli YouTube'un herkese açık oEmbed servisinden (API anahtarı
 * gerektirmez) otomatik çekilir. Hangi videonun siteye ekleneceğine burada
 * karar verilir - kanaldaki tüm videolar otomatik listelenmez.
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

/** Çeşitli YouTube link formatlarından 11 karakterlik video ID'sini çıkarır. */
function prolyn_youtube_id(string $input): ?string
{
    $input = trim($input);
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
        return $input;
    }
    $patterns = [
        '~youtu\.be/([a-zA-Z0-9_-]{11})~',
        '~youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})~',
        '~youtube\.com/shorts/([a-zA-Z0-9_-]{11})~',
        '~youtube\.com/embed/([a-zA-Z0-9_-]{11})~',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $input, $m)) {
            return $m[1];
        }
    }
    return null;
}

/** YouTube oEmbed servisinden başlık/kanal/kapak görseli çeker. API anahtarı gerekmez. */
function prolyn_youtube_oembed(string $videoId): ?array
{
    $url = 'https://www.youtube.com/oembed?format=json&url=' . urlencode('https://www.youtube.com/watch?v=' . $videoId);
    $context = stream_context_create(['http' => ['timeout' => 6], 'https' => ['timeout' => 6]]);
    $json = @file_get_contents($url, false, $context);
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['title'])) {
        return null;
    }
    return [
        'title' => (string)$data['title'],
        'author_name' => (string)($data['author_name'] ?? ''),
        'thumbnail_url' => (string)($data['thumbnail_url'] ?? ('https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg')),
    ];
}

$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_video') {
        $videoId = prolyn_youtube_id(trim($_POST['youtube_url'] ?? ''));
        if (!$videoId) {
            $formError = 'Geçerli bir YouTube video linki veya ID girin.';
        } else {
            $existing = $db->getRowAssoc("SELECT id FROM videolar WHERE youtube_video_id = ?", [$videoId]);
            if ($existing) {
                $formError = 'Bu video zaten listeye eklenmiş.';
            } else {
                $meta = prolyn_youtube_oembed($videoId);
                if (!$meta) {
                    $formError = 'Video bilgileri YouTube\'dan alınamadı. Linki kontrol edip tekrar deneyin.';
                } else {
                    $maxOrder = (int)$db->getColumn("SELECT COALESCE(MAX(order_no), 0) FROM videolar");
                    $db->Insert(
                        "INSERT INTO videolar (youtube_video_id, title, thumbnail_url, author_name, order_no, status, created_by, created_at)
                         VALUES (?, ?, ?, ?, ?, 'published', ?, NOW())",
                        [$videoId, $meta['title'], $meta['thumbnail_url'], $meta['author_name'], $maxOrder + 1, $_SESSION['admin_id'] ?? null]
                    );
                    echo "<script>window.location.href='website/videos.php?success=1';</script>";
                    exit;
                }
            }
        }
    } elseif ($_POST['action'] === 'edit_video') {
        $id = (int)$_POST['id'];
        $title = trim($_POST['title']);
        $order_no = (int)$_POST['order_no'];
        $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        $db->Update("UPDATE videolar SET title = ?, order_no = ?, status = ? WHERE id = ?", [$title, $order_no, $status, $id]);
        echo "<script>window.location.href='website/videos.php?success=2';</script>";
        exit;
    } elseif ($_POST['action'] === 'delete_video') {
        $id = (int)$_POST['id'];
        $db->Delete("DELETE FROM videolar WHERE id = ?", [$id]);
        echo "<script>window.location.href='website/videos.php?success=3';</script>";
        exit;
    }
}

$videos = $db->getRowsAssoc("SELECT * FROM videolar ORDER BY order_no ASC, id DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Video Yönetimi</h1>
                    <span class="text-muted fs-7">YouTube kanalı: <a href="https://www.youtube.com/@echoLabs2" target="_blank">@echoLabs2</a></span>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add">
                        <i class="ki-duotone ki-plus fs-2"></i> Video Ekle
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

                <?php if ($formError): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column"><span><?= htmlspecialchars($formError) ?></span></div>
                </div>
                <?php endif; ?>

                <div class="row g-6">
                    <?php if ($videos): ?>
                        <?php foreach ($videos as $v): ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="<?= htmlspecialchars($v['thumbnail_url']) ?>" class="card-img-top" alt="" style="aspect-ratio:16/9;object-fit:cover;" />
                                <div class="card-body">
                                    <span class="text-gray-800 fw-bold d-block fs-6 mb-1"><?= htmlspecialchars($v['title']) ?></span>
                                    <span class="text-muted fs-8 d-block mb-3"><?= htmlspecialchars($v['author_name'] ?? '') ?></span>
                                    <?php if ($v['status'] === 'published'): ?>
                                        <span class="badge badge-light-success mb-3">Yayında</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-warning mb-3">Taslak</span>
                                    <?php endif; ?>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-icon btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_<?= $v['id'] ?>">
                                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_<?= $v['id'] ?>">
                                            <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                        <a href="https://www.youtube.com/watch?v=<?= urlencode($v['youtube_video_id']) ?>" target="_blank" class="btn btn-icon btn-light-danger btn-sm">
                                            <i class="bi bi-youtube fs-3"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="kt_modal_edit_<?= $v['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mw-550px">
                                <div class="modal-content">
                                    <form class="form" action="" method="post">
                                        <?= csrfTokenField() ?>
                                        <input type="hidden" name="action" value="edit_video">
                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                        <div class="modal-header">
                                            <h2 class="fw-bold">Videoyu Düzenle</h2>
                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                            </div>
                                        </div>
                                        <div class="modal-body py-10 px-lg-17">
                                            <div class="fv-row mb-7">
                                                <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                                <input type="text" class="form-control form-control-solid" name="title" value="<?= htmlspecialchars($v['title']) ?>" required />
                                            </div>
                                            <div class="fv-row mb-7">
                                                <label class="required fs-6 fw-semibold mb-2">Sıra No</label>
                                                <input type="number" class="form-control form-control-solid" name="order_no" value="<?= (int)$v['order_no'] ?>" required />
                                            </div>
                                            <div class="fv-row mb-7">
                                                <label class="required fs-6 fw-semibold mb-2">Durum</label>
                                                <select class="form-select form-select-solid" name="status">
                                                    <option value="published" <?= $v['status'] === 'published' ? 'selected' : '' ?>>Yayında</option>
                                                    <option value="draft" <?= $v['status'] === 'draft' ? 'selected' : '' ?>>Taslak</option>
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
                        <div class="modal fade" id="kt_modal_delete_<?= $v['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mw-400px">
                                <div class="modal-content">
                                    <form class="form" action="" method="post">
                                        <?= csrfTokenField() ?>
                                        <input type="hidden" name="action" value="delete_video">
                                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                        <div class="modal-header">
                                            <h2 class="fw-bold">Videoyu Sil</h2>
                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                            </div>
                                        </div>
                                        <div class="modal-body py-10">
                                            <p><strong><?= htmlspecialchars($v['title']) ?></strong> videosunu listeden kaldırmak istediğinize emin misiniz?</p>
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
                        <div class="col-12 text-center text-muted py-10">Henüz video eklenmemiş.</div>
                    <?php endif; ?>
                </div>

                <!-- Add Modal -->
                <div class="modal fade" id="kt_modal_add" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered mw-550px">
                        <div class="modal-content">
                            <form class="form" action="" method="post">
                                <?= csrfTokenField() ?>
                                <input type="hidden" name="action" value="add_video">
                                <div class="modal-header">
                                    <h2 class="fw-bold">YouTube Videosu Ekle</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-lg-17">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">YouTube Video Linki</label>
                                        <input type="text" class="form-control form-control-solid" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required />
                                        <div class="form-text">Başlık, kanal adı ve kapak görseli YouTube'dan otomatik çekilir.</div>
                                    </div>
                                </div>
                                <div class="modal-footer flex-center">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                                    <button type="submit" class="btn btn-primary"><span class="indicator-label">Ekle</span></button>
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
