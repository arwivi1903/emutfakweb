<?php
/**
 * Prolyn Admin Panel - Subscription History
 * Prolyn/p/crm/subscription_history.php
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// Abonelik Geçmişini Çek
$query = "
    SELECT h.*, 
           k.company_name, 
           y.full_name as changed_by_name 
    FROM abonelik_gecmisi h 
    LEFT JOIN kurumlar k ON h.customer_id = k.customer_id 
    LEFT JOIN yoneticiler y ON h.changed_by = y.admin_id 
    ORDER BY h.history_id DESC
";
$history = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Abonelik Geçmişi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Abonelikler</li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Geçmiş</li>
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
                                <input type="text" data-kt-history-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Geçmişte Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_history_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Tarih</th>
                                        <th>Firma Adı</th>
                                        <th>Abonelik ID</th>
                                        <th>Değişim Türü</th>
                                        <th>Eski Plan -> Yeni Plan</th>
                                        <th>Sebep</th>
                                        <th>İşlemi Yapan</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($history)): ?>
                                        <?php foreach ($history as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></div>
                                                <div class="text-muted fs-7"><?php echo date('H:i:s', strtotime($item['created_at'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($item['company_name'] ?? 'Bilinmeyen Firma'); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-dark">#<?php echo $item['subscription_id']; ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $type_class = 'badge-light-primary';
                                                    if ($item['change_type'] === 'upgrade') $type_class = 'badge-light-success';
                                                    elseif ($item['change_type'] === 'downgrade') $type_class = 'badge-light-warning';
                                                    elseif ($item['change_type'] === 'cancellation') $type_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $type_class; ?> text-uppercase"><?php echo htmlspecialchars($item['change_type']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($item['old_plan_name'] || $item['new_plan_name']): ?>
                                                    <span class="text-muted"><?php echo htmlspecialchars($item['old_plan_name'] ?? '-'); ?></span>
                                                    <i class="ki-duotone ki-arrow-right fs-4 mx-2"></i>
                                                    <span class="text-primary fw-bold"><?php echo htmlspecialchars($item['new_plan_name'] ?? '-'); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($item['reason'] ?? '-'); ?>
                                            </td>
                                            <td>
                                                <?php if ($item['changed_by_name']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px symbol-circle me-3">
                                                            <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                                <?php echo strtoupper(mb_substr($item['changed_by_name'], 0, 1)); ?>
                                                            </span>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-800"><?php echo htmlspecialchars($item['changed_by_name']); ?></span>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Sistem</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-5">Geçmiş kaydı bulunmuyor.</td>
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
