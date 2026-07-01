<?php
/**
 * Prolyn Admin Panel - Sales Content Management
 * prolynweb/p/content/sales.php
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// Form Submit (Ekle/Güncelle)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_content') {
        $sales_id = !empty($_POST['sales_id']) ? (int)$_POST['sales_id'] : 0;
        
        $params = [
            $_POST['section'],
            $_POST['title_tr'],
            $_POST['title_en'],
            $_POST['content_tr'],
            $_POST['content_en'],
            $_POST['icon'],
            $_POST['image_url'],
            (int)$_POST['sort_order'],
            $_POST['status']
        ];
        
        if ($sales_id > 0) {
            $params[] = $sales_id;
            $db->Update("UPDATE satis_icerigi SET 
                section=?, title_tr=?, title_en=?, content_tr=?, content_en=?, icon=?, image_url=?, sort_order=?, status=?, updated_at=NOW()
                WHERE sales_id=?", $params);
        } else {
            $db->Insert("INSERT INTO satis_icerigi (section, title_tr, title_en, content_tr, content_en, icon, image_url, sort_order, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", $params);
        }
        
        echo "<script>window.location.href='content/sales.php?success=1';</script>";
        exit;
    }
    
    // Silme İşlemi
    if ($_POST['action'] === 'delete_content') {
        $sales_id = (int)$_POST['sales_id'];
        $db->Delete("DELETE FROM satis_icerigi WHERE sales_id=?", [$sales_id]);
        echo "<script>window.location.href='content/sales.php?success=deleted';</script>";
        exit;
    }
}

$contents = $db->getRowsAssoc("SELECT * FROM satis_icerigi ORDER BY section ASC, sort_order ASC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Satış ve Pazarlama İçerikleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="../index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">İçerik Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Satış İçerikleri</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" onclick="openContentModal()">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni İçerik Ekle
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span>İçerik kaydı başarıyla kaydedildi/silindi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Bölüm (Section)</th>
                                        <th>Başlık (TR)</th>
                                        <th>Sıra</th>
                                        <th>Durum</th>
                                        <th>Son Güncelleme</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($contents)): ?>
                                        <?php foreach ($contents as $item): ?>
                                        <tr>
                                            <td><span class="badge badge-light-info fw-bold"><?php echo htmlspecialchars($item['section']); ?></span></td>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($item['title_tr'] ?? '-'); ?></div>
                                                <div class="text-muted fs-8"><?php echo htmlspecialchars($item['title_en'] ?? '-'); ?></div>
                                            </td>
                                            <td><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <?php 
                                                $s = $item['status'];
                                                $c = $s == 'published' ? 'success' : ($s == 'draft' ? 'warning' : 'secondary');
                                                ?>
                                                <span class="badge badge-light-<?php echo $c; ?> text-uppercase"><?php echo $s; ?></span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($item['updated_at'])); ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-icon btn-light-primary me-1" onclick='editContent(<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                <form action="content/sales.php" method="POST" style="display:inline-block;" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Silmek istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="delete_content">
                                                    <input type="hidden" name="sales_id" value="<?php echo $item['sales_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger">
                                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5">Kayıtlı satış/pazarlama içeriği bulunamadı.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: İçerik Yönetimi -->
<div class="modal fade" id="kt_modal_content" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_title">İçerik Ekle/Düzenle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="content/sales.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_content">
                <input type="hidden" name="sales_id" id="inp_sales_id" value="">
                
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="required fs-6 fw-semibold mb-2">Bölüm (Section)</label>
                            <input type="text" class="form-control form-control-solid" name="section" id="inp_section" placeholder="Örn: hero, features, pricing" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="required fs-6 fw-semibold mb-2">Sıralama</label>
                            <input type="number" class="form-control form-control-solid" name="sort_order" id="inp_sort_order" value="999" required />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select class="form-select form-select-solid" name="status" id="inp_status" required>
                                <option value="published">Yayınlandı</option>
                                <option value="draft">Taslak</option>
                                <option value="archived">Arşivlendi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Başlık (Türkçe)</label>
                            <input type="text" class="form-control form-control-solid" name="title_tr" id="inp_title_tr" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Başlık (İngilizce)</label>
                            <input type="text" class="form-control form-control-solid" name="title_en" id="inp_title_en" />
                        </div>
                    </div>

                    <div class="fv-row mb-5">
                        <label class="fs-6 fw-semibold mb-2">İçerik (Türkçe)</label>
                        <textarea class="form-control form-control-solid" name="content_tr" id="inp_content_tr" rows="3"></textarea>
                    </div>
                    
                    <div class="fv-row mb-5">
                        <label class="fs-6 fw-semibold mb-2">İçerik (İngilizce)</label>
                        <textarea class="form-control form-control-solid" name="content_en" id="inp_content_en" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="col-md-6 mb-3">
                            <label class="fs-6 fw-semibold mb-2">İkon (FontAwesome / Ki-Duotone)</label>
                            <input type="text" class="form-control form-control-solid" name="icon" id="inp_icon" placeholder="ki-duotone ki-abstract-26" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fs-6 fw-semibold mb-2">Görsel URL (Image URL)</label>
                            <input type="text" class="form-control form-control-solid" name="image_url" id="inp_image_url" placeholder="/assets/media/image.png" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openContentModal() {
    document.getElementById('modal_title').innerText = 'Yeni İçerik Ekle';
    document.getElementById('inp_sales_id').value = '';
    document.getElementById('inp_section').value = '';
    document.getElementById('inp_sort_order').value = '999';
    document.getElementById('inp_status').value = 'published';
    document.getElementById('inp_title_tr').value = '';
    document.getElementById('inp_title_en').value = '';
    document.getElementById('inp_content_tr').value = '';
    document.getElementById('inp_content_en').value = '';
    document.getElementById('inp_icon').value = '';
    document.getElementById('inp_image_url').value = '';
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_content'));
    modal.show();
}

function editContent(item) {
    document.getElementById('modal_title').innerText = 'İçerik Düzenle';
    document.getElementById('inp_sales_id').value = item.sales_id;
    document.getElementById('inp_section').value = item.section;
    document.getElementById('inp_sort_order').value = item.sort_order;
    document.getElementById('inp_status').value = item.status;
    document.getElementById('inp_title_tr').value = item.title_tr;
    document.getElementById('inp_title_en').value = item.title_en;
    document.getElementById('inp_content_tr').value = item.content_tr;
    document.getElementById('inp_content_en').value = item.content_en;
    document.getElementById('inp_icon').value = item.icon;
    document.getElementById('inp_image_url').value = item.image_url;
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_content'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
