<?php
/**
 * Prolyn Admin Panel - Announcements Management
 * Prolyn/p/crm/announcements.php
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// Yeni Duyuru Ekleme veya Silme/Durum Değiştirme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_announcement') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $type = $_POST['type'];
        $target_audience = $_POST['target_audience'];
        $show_from = !empty($_POST['show_from']) ? $_POST['show_from'] : null;
        $show_until = !empty($_POST['show_until']) ? $_POST['show_until'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $created_by = 1; // Sabit yönetici ID (oturumdan alınmalı)

        $db->Insert(
            "INSERT INTO duyurular (title, content, type, target_audience, is_active, show_from, show_until, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $content, $type, $target_audience, $is_active, $show_from, $show_until, $created_by]
        );
        
        echo "<script>window.location.href='crm/announcements.php?success=add';</script>";
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        $id = (int)$_POST['announcement_id'];
        $status = (int)$_POST['status'] === 1 ? 0 : 1;
        $db->Update("UPDATE duyurular SET is_active = ?, updated_at = NOW() WHERE announcement_id = ?", [$status, $id]);
        echo "<script>window.location.href='crm/announcements.php?success=status';</script>";
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['announcement_id'];
        $db->Delete("DELETE FROM duyurular WHERE announcement_id = ?", [$id]);
        echo "<script>window.location.href='crm/announcements.php?success=delete';</script>";
        exit;
    }
}

// Duyuruları Çek
$query = "SELECT d.*, y.full_name as creator_name
    FROM duyurular d
    LEFT JOIN yoneticiler y ON d.created_by = y.admin_id
    WHERE d.is_active = 1 ORDER BY d.created_at DESC";
$announcements = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Duyuru Yönetimi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">CRM Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Duyurular</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_announcement">
                        Yeni Duyuru Ekle
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
                        <span>İşlem başarıyla gerçekleştirildi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-announcement-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Duyuru Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_announcements_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Başlık / Tip</th>
                                        <th>Hedef Kitle</th>
                                        <th>Görüntülenme Tarihleri</th>
                                        <th>Oluşturan</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($announcements)): ?>
                                        <?php foreach ($announcements as $a): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold mb-1"><?php echo htmlspecialchars($a['title']); ?></div>
                                                <?php 
                                                    $type_class = 'badge-light-primary';
                                                    if ($a['type'] == 'info') $type_class = 'badge-light-info';
                                                    elseif ($a['type'] == 'success') $type_class = 'badge-light-success';
                                                    elseif ($a['type'] == 'warning') $type_class = 'badge-light-warning';
                                                    elseif ($a['type'] == 'danger') $type_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $type_class; ?> text-uppercase"><?php echo htmlspecialchars($a['type']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light fw-bold text-uppercase"><?php echo htmlspecialchars($a['target_audience']); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span><i class="ki-duotone ki-calendar-tick fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> <?php echo $a['show_from'] ? date('d.m.Y H:i', strtotime($a['show_from'])) : 'Sürekli'; ?></span>
                                                    <span><i class="ki-duotone ki-calendar-remove fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> <?php echo $a['show_until'] ? date('d.m.Y H:i', strtotime($a['show_until'])) : 'Süresiz'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars($a['creator_name'] ?? 'Bilinmiyor'); ?></span><br>
                                                <span class="text-muted fs-8"><?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></span>
                                            </td>
                                            <td>
                                                <form action="crm/announcements.php" method="POST" class="d-inline">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="announcement_id" value="<?php echo $a['announcement_id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $a['is_active']; ?>">
                                                    <?php if ($a['is_active']): ?>
                                                        <button type="submit" class="btn btn-sm btn-light-success px-3" title="Pasife Al">Aktif</button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-light-danger px-3" title="Aktife Al">Pasif</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                            <td class="text-end">
                                                <!-- Detay modal gösterimi için eklenebilir -->
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" title="İncele" onclick="Swal.fire({title: 'İçerik', html: '<?php echo htmlspecialchars(addslashes(preg_replace('/\s+/', ' ', $a['content']))); ?>', icon: 'info'});">
                                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                                <!-- Silme -->
                                                <form action="crm/announcements.php" method="POST" class="d-inline" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Bu duyuruyu silmek istediğinize emin misiniz?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="announcement_id" value="<?php echo $a['announcement_id']; ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Sil">
                                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Kayıtlı duyuru bulunamadı.</td></tr>
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

<!-- Modal: Yeni Duyuru Ekle -->
<div class="modal fade" id="kt_modal_add_announcement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Yeni Duyuru Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="crm/announcements.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="add_announcement">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Başlık</label>
                        <input type="text" class="form-control form-control-solid" name="title" required placeholder="Duyuru Başlığı" />
                    </div>

                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">İçerik</label>
                        <textarea class="form-control form-control-solid" rows="4" name="content" required placeholder="Duyuru metni..."></textarea>
                    </div>

                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Tip</label>
                            <select class="form-select form-select-solid" name="type" required>
                                <option value="info">Bilgi (Info)</option>
                                <option value="success">Başarılı (Success)</option>
                                <option value="warning">Uyarı (Warning)</option>
                                <option value="danger">Kritik (Danger)</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Hedef Kitle</label>
                            <select class="form-select form-select-solid" name="target_audience" required>
                                <option value="all">Herkes</option>
                                <option value="customers">Müşteriler</option>
                                <option value="admins">Yöneticiler</option>
                                <option value="specific">Belirli Müşteriler</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="show_from" />
                            <div class="text-muted fs-8 mt-1">Boş bırakılırsa hemen yayınlanır.</div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="show_until" />
                            <div class="text-muted fs-8 mt-1">Boş bırakılırsa süresiz kalır.</div>
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked />
                            <span class="form-check-label fw-semibold text-gray-700">Duyuruyu Aktifleştir</span>
                        </label>
                    </div>

                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet ve Yayınla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>
