<?php
/**
 * Prolyn Admin Panel - Tenants Management
 * prolynweb/p/tenants.php
 */

require_once 'config/header.php';
require_once 'config/sidebar.php';

// İşlem Yakalama (Durum Güncelleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['tenant_id'])) {
    $action = $_POST['action'];
    $tenant_id = (int)$_POST['tenant_id'];
    
    if ($action === 'suspend') {
        $db->Update("UPDATE tenants SET status = 'suspended' WHERE id = ?", [$tenant_id]);
    } elseif ($action === 'activate') {
        $db->Update("UPDATE tenants SET status = 'active' WHERE id = ?", [$tenant_id]);
    }
    
    // Refresh page to avoid re-post
    echo "<script>window.location.href='tenants.php';</script>";
    exit;
}

// Tenant Listesini Çek (Soft deleted olmayanları getir)
$tenants = $db->getRowsAssoc("SELECT * FROM tenants WHERE deleted_at IS NULL ORDER BY id DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        İşletmeler (Tenants)
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">İşletmeler</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input type="text" data-kt-customer-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="İşletme Ara..." />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_tenant">
                                <i class="ki-duotone ki-plus fs-2"></i> Yeni İşletme
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_tenants_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>ID</th>
                                        <th>İşletme Adı</th>
                                        <th>Domain</th>
                                        <th>DB Adı</th>
                                        <th>Durum</th>
                                        <th>Kayıt Tarihi</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($tenants)): ?>
                                        <?php foreach ($tenants as $tenant): ?>
                                        <tr>
                                            <td><?php echo $tenant['id']; ?></td>
                                            <td>
                                                <a href="#" class="text-gray-800 text-hover-primary mb-1"><?php echo htmlspecialchars($tenant['company_name']); ?></a>
                                            </td>
                                            <td><?php echo htmlspecialchars($tenant['domain']); ?></td>
                                            <td><span class="badge badge-light-info"><?php echo htmlspecialchars($tenant['db_name']); ?></span></td>
                                            <td>
                                                <?php if ($tenant['status'] === 'active'): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php elseif ($tenant['status'] === 'suspended'): ?>
                                                    <span class="badge badge-light-warning">Askıya Alındı</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger"><?php echo htmlspecialchars($tenant['status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y', strtotime($tenant['created_at'])); ?></td>
                                            <td class="text-end">
                                                <form method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">
                                                    <?php if ($tenant['status'] === 'active'): ?>
                                                        <input type="hidden" name="action" value="suspend">
                                                        <button type="submit" class="btn btn-sm btn-light-warning" onclick="return confirm('Bu işletmeyi askıya almak istediğinize emin misiniz?');">Askıya Al</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="btn btn-sm btn-light-success" onclick="return confirm('Bu işletmeyi aktifleştirmek istediğinize emin misiniz?');">Aktifleştir</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">Sistemde henüz kayıtlı bir işletme bulunmuyor.</td>
                                        </tr>
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

<?php
require_once 'config/footer.php';
?>
