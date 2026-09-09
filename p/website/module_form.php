<?php
/**
 * Prolyn Admin Panel - Web Modülü Ekle/Düzenle
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$module = ['title' => '', 'description' => '', 'icon' => '', 'order_no' => 0, 'status' => 1];
$isEdit = false;

if ($id > 0) {
    $existing = $db->getRowAssoc("SELECT * FROM website_modules WHERE id = ?", [$id]);
    if (!$existing) {
        echo "<script>window.location.href='website/modules.php';</script>";
        exit;
    }
    $module = $existing;
    $isEdit = true;
}

$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon        = trim($_POST['icon'] ?? '');
    $order_no    = (int)($_POST['order_no'] ?? 0);
    $status      = isset($_POST['status']) ? 1 : 0;

    if ($title === '' || $description === '' || $icon === '') {
        $formError = 'Başlık, açıklama ve ikon zorunludur.';
    } else {
        if ($isEdit) {
            $db->Update(
                "UPDATE website_modules SET title=?, description=?, icon=?, order_no=?, status=? WHERE id=?",
                [$title, $description, $icon, $order_no, $status, $id]
            );
        } else {
            $db->Insert(
                "INSERT INTO website_modules (title, description, icon, order_no, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$title, $description, $icon, $order_no, $status]
            );
        }
        echo "<script>window.location.href='website/modules.php?success=1';</script>";
        exit;
    }

    $module = array_merge($module, compact('title', 'description', 'icon', 'order_no', 'status'));
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                        <?php echo $isEdit ? 'Modülü Düzenle' : 'Yeni Modül Ekle'; ?>
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted"><a href="website/modules.php" class="text-muted text-hover-primary">Web Modülleri</a></li>
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

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <form action="website/module_form.php<?php echo $isEdit ? '?id=' . $id : ''; ?>" method="post">
                                    <?= csrfTokenField() ?>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                                        <input type="text" class="form-control form-control-solid" name="title" value="<?php echo htmlspecialchars($module['title']); ?>" required />
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">Açıklama</label>
                                        <textarea class="form-control form-control-solid" name="description" rows="3" required><?php echo htmlspecialchars($module['description']); ?></textarea>
                                    </div>
                                    <div class="fv-row mb-7">
                                        <label class="required fs-6 fw-semibold mb-2">İkon Sınıfı (Örn: bi bi-box-seam)</label>
                                        <input type="text" class="form-control form-control-solid" name="icon" value="<?php echo htmlspecialchars($module['icon']); ?>" required />
                                        <div class="form-text">
                                            <i class="fs-2 <?php echo htmlspecialchars($module['icon'] ?: 'bi bi-question-circle'); ?> text-primary"></i>
                                            Bootstrap Icons sınıf adı (bkz. icons.getbootstrap.com)
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 fv-row mb-7">
                                            <label class="required fs-6 fw-semibold mb-2">Sıra No</label>
                                            <input type="number" class="form-control form-control-solid" name="order_no" value="<?php echo (int)$module['order_no']; ?>" required />
                                        </div>
                                        <div class="col-md-6 fv-row mb-7 d-flex align-items-end">
                                            <label class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="status" <?php echo $module['status'] ? 'checked' : ''; ?> />
                                                <span class="form-check-label fw-semibold text-muted">Aktif (Sitede Görünsün)</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="website/modules.php" class="btn btn-light">İptal</a>
                                        <button type="submit" class="btn btn-primary">Kaydet</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once '../config/footer.php'; ?>
</div>
