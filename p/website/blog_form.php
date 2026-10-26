<?php
/**
 * Prolyn Admin Panel - Blog Yazısı Ekle/Düzenle
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

function prolyn_blog_slug(string $title): string
{
    $t = function_exists('mb_strtolower') ? mb_strtolower(trim($title), 'UTF-8') : strtolower(trim($title));
    $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u'];
    $t = strtr($t, $map);
    $t = preg_replace('/[^a-z0-9]+/', '-', $t);
    return trim($t, '-');
}

/** Kapak görseli dosyasını uploads/blog altına kaydeder, public URL döndürür. */
function prolyn_blog_kapak_yukle(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if ((int)$file['size'] > 5242880) {
        return null;
    }

    $izinliUzanti = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $izinliMime   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    $uz = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($uz, $izinliUzanti, true) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $mime = '';
    if (function_exists('finfo_open') && ($fi = finfo_open(FILEINFO_MIME_TYPE))) {
        $mime = (string)finfo_file($fi, $file['tmp_name']);
        finfo_close($fi);
    }
    if (!in_array($mime, $izinliMime, true)) {
        return null;
    }

    $kok = dirname(__DIR__, 2) . '/uploads/blog';
    if (!is_dir($kok) && !@mkdir($kok, 0755, true) && !is_dir($kok)) {
        return null;
    }

    $dosyaAdi = bin2hex(random_bytes(8)) . '.' . $uz;
    if (!@move_uploaded_file($file['tmp_name'], $kok . '/' . $dosyaAdi)) {
        return null;
    }
    @chmod($kok . '/' . $dosyaAdi, 0644);

    return 'uploads/blog/' . $dosyaAdi;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = ['title' => '', 'excerpt' => '', 'cover_image' => '', 'content' => '', 'status' => 'draft'];
$isEdit = false;

if ($id > 0) {
    $existing = $db->getRowAssoc("SELECT * FROM blog_yazilari WHERE id = ?", [$id]);
    if (!$existing) {
        echo "<script>window.location.href='blog.php';</script>";
        exit;
    }
    $post = $existing;
    $isEdit = true;
}

$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $excerpt     = trim($_POST['excerpt'] ?? '');
    $content     = $_POST['content'] ?? '';
    $status      = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    $cover_image = trim($_POST['cover_image'] ?? '') ?: null;

    // Dosya yüklendiyse URL alanının önüne geçer
    if (!empty($_FILES['cover_image_file']['name'])) {
        $yuklenen = prolyn_blog_kapak_yukle($_FILES['cover_image_file']);
        if ($yuklenen) {
            $cover_image = $yuklenen;
        } else {
            $formError = 'Görsel yüklenemedi. jpg, png, gif veya webp (en fazla 5 MB) yükleyin.';
        }
    }

    if ($title === '') {
        $formError = 'Başlık zorunludur.';
    } elseif (!$formError) {
        if ($isEdit) {
            $published_at = $post['published_at'] ?? null;
            if ($status === 'published' && empty($published_at)) {
                $published_at = date('Y-m-d H:i:s');
            }
            $db->Update(
                "UPDATE blog_yazilari SET title=?, excerpt=?, content=?, cover_image=?, status=?, published_at=?, updated_at=NOW() WHERE id=?",
                [$title, $excerpt, $content, $cover_image, $status, $published_at, $id]
            );
        } else {
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
        }

        echo "<script>window.location.href='website/blog.php?success=1';</script>";
        exit;
    }

    // Hata varsa formu girilen değerlerle yeniden göster
    $post = array_merge($post, ['title' => $title, 'excerpt' => $excerpt, 'content' => $content, 'status' => $status, 'cover_image' => $cover_image]);
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        <?php echo $isEdit ? 'Yazıyı Düzenle' : 'Yeni Yazı Ekle'; ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="website/blog.php" class="text-muted text-hover-primary">Blog</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark"><?php echo $isEdit ? 'Düzenle' : 'Yeni'; ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php if ($formError): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column"><span><?php echo htmlspecialchars($formError); ?></span></div>
                </div>
                <?php endif; ?>

                <form action="website/blog_form.php<?php echo $isEdit ? '?id=' . $id : ''; ?>" method="post" enctype="multipart/form-data">
                    <?= csrfTokenField() ?>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-6">
                                <div class="card-body">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                        <input type="text" class="form-control form-control-solid" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="fs-6 fw-semibold mb-2">Kısa Özet</label>
                                        <textarea class="form-control form-control-solid" name="excerpt" rows="2" placeholder="Liste sayfasında görünecek kısa açıklama"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                                        <textarea class="form-control form-control-solid" name="content" rows="16" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                                        <div class="form-text">Paragraf aralarına boş satır bırakın. Biçimlendirme: <code>## Başlık</code> (içindekiler listesine eklenir), <code>### Alt başlık</code>, <code>- madde</code>, <code>1. madde</code>, <code>&gt; not kutusu</code>, <code>**kalın**</code>, <code>[bağlantı metni](https://adres)</code>. İpucu: ilk paragrafta konunun kısa cevabını verin; arama motorları ve yapay zekâ özetleri bunu alıntılar.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card mb-6">
                                <div class="card-header"><h3 class="card-title">Yayın</h3></div>
                                <div class="card-body">
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Durum</label>
                                        <select class="form-select form-select-solid" name="status">
                                            <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Taslak</option>
                                            <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Yayında</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-6">
                                <div class="card-header"><h3 class="card-title">Kapak Görseli</h3></div>
                                <div class="card-body">
                                    <?php if (!empty($post['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars(strpos($post['cover_image'], 'http') === 0 ? $post['cover_image'] : '../' . $post['cover_image']); ?>" class="w-100 rounded mb-4" style="max-height:160px;object-fit:cover;" alt="" />
                                    <?php endif; ?>
                                    <div class="fv-row mb-5">
                                        <label class="fs-6 fw-semibold mb-2">Görsel Yükle</label>
                                        <input type="file" class="form-control form-control-solid" name="cover_image_file" accept=".jpg,.jpeg,.png,.gif,.webp" />
                                        <div class="form-text">jpg, png, gif, webp · en fazla 5 MB</div>
                                    </div>
                                    <div class="fv-row">
                                        <label class="fs-6 fw-semibold mb-2">veya Görsel URL</label>
                                        <input type="text" class="form-control form-control-solid" name="cover_image" placeholder="https://..." value="<?php echo htmlspecialchars((!empty($post['cover_image']) && strpos($post['cover_image'], 'http') === 0) ? $post['cover_image'] : ''); ?>" />
                                        <div class="form-text">Dosya yüklerseniz bu alan yok sayılır.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <a href="website/blog.php" class="btn btn-light">İptal</a>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <?php require_once '../config/footer.php'; ?>
</div>
