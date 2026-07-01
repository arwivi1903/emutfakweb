<?php
/**
 * Prolyn Admin Panel - Files Management
 * prolynweb/p/settings/files.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Format bytes to human readable format
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

// Dosya silme isteği (Hard delete DB only for now, physical file deletion logic could be added)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_file') {
    $file_id = (int)$_POST['file_id'];
    $db->Delete("DELETE FROM dosyalar WHERE id = ?", [$file_id]);
    echo "<script>window.location.href='settings/files.php?success=1';</script>";
    exit;
}

// Dosyaları çek (Owner type vb. ile)
$query = "
    SELECT d.* 
    FROM dosyalar d
    ORDER BY d.created_at DESC
    LIMIT 1000
";
$files = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Sistem Dosyaları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Yönetim Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Sistem Dosyaları</li>
                    </ul>
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
                        <span>Dosya kaydı başarıyla silindi.</span>
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
                                <input type="text" data-kt-file-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Dosya Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_files_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-50px">ID</th>
                                        <th>Dosya Adı</th>
                                        <th>Sahiplik (Owner)</th>
                                        <th>Boyut</th>
                                        <th>Tip</th>
                                        <th>Yüklenme Tarihi</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($files)): ?>
                                        <?php foreach ($files as $f): ?>
                                        <tr>
                                            <td><?php echo $f['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                        $icon = 'ki-file';
                                                        $mime = strtolower($f['mime_type']);
                                                        if (strpos($mime, 'image') !== false) $icon = 'ki-picture';
                                                        elseif (strpos($mime, 'pdf') !== false) $icon = 'ki-document';
                                                        elseif (strpos($mime, 'zip') !== false || strpos($mime, 'tar') !== false) $icon = 'ki-abstract-26';
                                                    ?>
                                                    <i class="ki-duotone <?php echo $icon; ?> fs-2x text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold mb-1" style="word-break: break-all; max-width: 250px;"><?php echo htmlspecialchars($f['file_name']); ?></span>
                                                        <span class="text-muted fs-7"><?php echo htmlspecialchars($f['file_path']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info text-uppercase"><?php echo htmlspecialchars($f['owner_type']); ?></span>
                                                <div class="text-muted fs-8 mt-1">ID: <?php echo $f['owner_id']; ?></div>
                                            </td>
                                            <td>
                                                <?php echo formatBytes($f['file_size']); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-light"><?php echo htmlspecialchars($f['mime_type'] ?? 'Bilinmiyor'); ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('d.m.Y H:i', strtotime($f['created_at'])); ?>
                                            </td>
                                            <td class="text-end">
                                                <!-- Download Link (Eğer path erişilebilir ise, şimdilik sadece görsel) -->
                                                <a href="#" class="btn btn-icon btn-light-primary btn-sm me-1" title="İndir">
                                                    <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                                <!-- Sil Butonu Formu -->
                                                <form action="settings/files.php" method="POST" class="d-inline" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Bu dosya kaydını silmek istediğinize emin misiniz? FİZİKSEL DOSYA DA SİLİNEBİLİR!', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="delete_file">
                                                    <input type="hidden" name="file_id" value="<?php echo $f['id']; ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Sil">
                                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">Kayıtlı dosya bulunamadı.</td></tr>
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

<?php require_once '../config/footer.php'; ?>
