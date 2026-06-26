<?php
/**
 * Prolyn Admin Panel - Support Tickets List
 * prolynweb/p/crm/tickets.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_support');
require_once '../config/sidebar.php';

// Talep Durumu Güncelleme POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $ticket_id = (int)$_POST['ticket_id'];
    $new_status = $_POST['status'];
    
    $query = "UPDATE destek_talepleri SET status = ?, updated_at = NOW() WHERE ticket_id = ?";
    $db->Update($query, [$new_status, $ticket_id]);
    
    if ($new_status === 'resolved' || $new_status === 'closed') {
        $db->Update("UPDATE destek_talepleri SET resolved_at = NOW(), closed_at = NOW() WHERE ticket_id = ?", [$ticket_id]);
    }
    
    echo "<script>window.location.href='crm/tickets.php?success=1';</script>";
    exit;
}

// Destek Taleplerini çek
$query = "SELECT t.*, k.company_name 
    FROM destek_talepleri t
    LEFT JOIN kurumlar k ON t.customer_id = k.id
    WHERE t.del_status = 1 ORDER BY t.created_at DESC";
$tickets = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Destek Talepleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">CRM Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Destek Talepleri</li>
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
                        <span>Talep durumu başarıyla güncellendi.</span>
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
                                <input type="text" data-kt-ticket-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Talep Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_tickets_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Talep No / Tarih</th>
                                        <th>Müşteri (Kurum)</th>
                                        <th>Konu</th>
                                        <th>Öncelik</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($tickets)): ?>
                                        <?php foreach ($tickets as $t): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold">#<?php echo htmlspecialchars($t['ticket_number']); ?></div>
                                                <div class="text-muted fs-7"><?php echo date('d.m.Y H:i', strtotime($t['created_at'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars($t['company_name'] ?? 'Bilinmeyen Müşteri'); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($t['subject']); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $pri_class = 'badge-light';
                                                    if ($t['priority'] == 'high') $pri_class = 'badge-light-danger';
                                                    elseif ($t['priority'] == 'medium') $pri_class = 'badge-light-warning';
                                                    elseif ($t['priority'] == 'low') $pri_class = 'badge-light-success';
                                                ?>
                                                <span class="badge <?php echo $pri_class; ?> text-uppercase"><?php echo htmlspecialchars($t['priority']); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($t['status'] == 'open' || $t['status'] == 'new') $status_class = 'badge-light-info';
                                                    elseif ($t['status'] == 'in_progress') $status_class = 'badge-light-warning';
                                                    elseif ($t['status'] == 'resolved' || $t['status'] == 'closed') $status_class = 'badge-light-success';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($t['status']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="crm/ticket_detail.php?id=<?php echo $t['ticket_id']; ?>" class="btn btn-sm btn-light-primary">
                                                    İncele ve Yanıtla
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Kayıtlı destek talebi bulunamadı.</td></tr>
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
