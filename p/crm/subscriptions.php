<?php
/**
 * Prolyn Admin Panel - Subscriptions Management
 * prolynweb/p/crm/subscriptions.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_subscriptions');
require_once '../config/sidebar.php';

// İşlem Yakalama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['subscription_id'])) {
    $action = $_POST['action'];
    $subscription_id = (int)$_POST['subscription_id'];
    
    // İşlem yapacak admin
    $admin_id = $_SESSION['admin_id'] ?? 1; // Fallback to 1 if not set

    // Eski veriyi al (Loglama için)
    $old_data = $db->getRowAssoc("SELECT * FROM abonelikler WHERE del_status = 1 AND subscription_id = ?", [$subscription_id]);
    
    if ($old_data) {
        if ($action === 'cancel') {
            $db->Update("UPDATE abonelikler SET status = 'cancelled', cancelled_date = CURRENT_TIMESTAMP WHERE subscription_id = ?", [$subscription_id]);
            
            // Tarihçeye ekle
            $db->Insert("INSERT INTO abonelik_gecmisi (subscription_id, customer_id, old_plan_name, new_plan_name, change_type, reason, changed_by) 
                         VALUES (?, ?, ?, ?, 'cancellation', 'Admin tarafından iptal edildi', ?)",
                         [$subscription_id, $old_data['customer_id'], $old_data['plan_name'], $old_data['plan_name'], $admin_id]);
                         
            // Log ekle
            $new_data = $old_data;
            $new_data['status'] = 'cancelled';
            $db->Insert("INSERT INTO yonetici_loglari (admin_id, action_type, table_name, record_id, old_value, new_value, ip_address) 
                         VALUES (?, 'Update', 'abonelikler', ?, ?, ?, ?)", 
                         [$admin_id, $subscription_id, json_encode($old_data), json_encode($new_data), $_SERVER['REMOTE_ADDR']]);
        } elseif ($action === 'activate') {
            $db->Update("UPDATE abonelikler SET status = 'active', cancelled_date = NULL WHERE subscription_id = ?", [$subscription_id]);
            
            // Tarihçeye ekle
            $db->Insert("INSERT INTO abonelik_gecmisi (subscription_id, customer_id, old_plan_name, new_plan_name, change_type, reason, changed_by) 
                         VALUES (?, ?, ?, ?, 'renewal', 'Admin tarafından tekrar aktif edildi', ?)",
                         [$subscription_id, $old_data['customer_id'], $old_data['plan_name'], $old_data['plan_name'], $admin_id]);
                         
            // Log ekle
            $new_data = $old_data;
            $new_data['status'] = 'active';
            $new_data['cancelled_date'] = null;
            $db->Insert("INSERT INTO yonetici_loglari (admin_id, action_type, table_name, record_id, old_value, new_value, ip_address) 
                         VALUES (?, 'Update', 'abonelikler', ?, ?, ?, ?)", 
                         [$admin_id, $subscription_id, json_encode($old_data), json_encode($new_data), $_SERVER['REMOTE_ADDR']]);
        }
    }
    
    echo "<script>window.location.href='subscriptions.php';</script>";
    exit;
}

// Abonelikleri Kurumlarla birlikte çek
$subscriptions = $db->getRowsAssoc("SELECT a.*, k.company_name FROM abonelikler a LEFT JOIN kurumlar k ON a.customer_id = k.customer_id WHERE a.del_status = 1 ORDER BY a.subscription_id DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Abonelik Listesi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Abonelikler</li>
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
                                <input type="text" data-kt-subscription-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Abonelik Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_subscriptions_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>ID</th>
                                        <th>Firma Adı</th>
                                        <th>Plan Adı</th>
                                        <th>Fiyat (₺)</th>
                                        <th>Başlangıç/Bitiş</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($subscriptions)): ?>
                                        <?php foreach ($subscriptions as $sub): ?>
                                        <tr>
                                            <td><?php echo $sub['subscription_id']; ?></td>
                                            <td>
                                                <a href="#" class="text-gray-800 text-hover-primary mb-1"><?php echo htmlspecialchars($sub['company_name'] ?? 'Bilinmeyen Firma'); ?></a>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary text-uppercase"><?php echo htmlspecialchars($sub['plan_name']); ?></span><br>
                                                <small class="text-muted"><?php echo $sub['num_branches']; ?> Branş / <?php echo $sub['billing_cycle']; ?> Ay</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success"><?php echo number_format($sub['total_price'], 2, ',', '.'); ?> ₺</span>
                                            </td>
                                            <td>
                                                <div><i class="ki-duotone ki-calendar text-success fs-7"></i> <?php echo date('d.m.Y', strtotime($sub['start_date'])); ?></div>
                                                <div><i class="ki-duotone ki-calendar text-danger fs-7"></i> <?php echo date('d.m.Y', strtotime($sub['end_date'])); ?></div>
                                            </td>
                                            <td>
                                                <?php if ($sub['status'] === 'active'): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php elseif ($sub['status'] === 'cancelled'): ?>
                                                    <span class="badge badge-light-danger">İptal Edildi</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-warning"><?php echo htmlspecialchars($sub['status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <form action="subscriptions.php" method="POST" style="display:inline-block;">
                                                    <?= csrfTokenField() ?>
                                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['subscription_id']; ?>">
                                                    <?php if ($sub['status'] === 'active'): ?>
                                                        <input type="hidden" name="action" value="cancel">
                                                        <button type="submit" class="btn btn-sm btn-light-danger" onclick="return confirm('Bu aboneliği iptal etmek istediğinize emin misiniz?');">İptal Et</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="btn btn-sm btn-light-success" onclick="return confirm('Bu aboneliği tekrar aktifleştirmek istediğinize emin misiniz?');">Aktifleştir</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">Sistemde henüz kayıtlı bir abonelik bulunmuyor.</td>
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
require_once '../config/footer.php';
?>
