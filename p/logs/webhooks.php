<?php
/**
 * Prolyn Admin Panel - Webhooks Management
 * prolynweb/p/logs/webhooks.php
 */

require_once '../config/header.php';
Auth::requireModule('view_logs');
require_once '../config/sidebar.php';

// Form Submit (Update State)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'toggle_status') {
        $webhook_id = (int)$_POST['webhook_id'];
        $db->Update("UPDATE webhooklar SET is_active = NOT is_active, updated_at = NOW() WHERE webhook_id = ?", [$webhook_id]);
        echo "<script>window.location.href='logs/webhooks.php?success=1';</script>";
        exit;
    }
}

$query = "SELECT w.*, k.company_name FROM webhooklar w LEFT JOIN kurumlar k ON w.customer_id = k.customer_id ORDER BY w.created_at DESC";
$webhooks = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Webhook Tanımları ve Logları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Log ve Sistem Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Webhooklar</li>
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
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Webhook Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Kurum / Adı</th>
                                        <th>Bağlantı (URL)</th>
                                        <th>Olaylar (Events)</th>
                                        <th>Son Tetiklenme</th>
                                        <th>Durum</th>
                                        <th class="text-end">Aktifleştir</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($webhooks)): ?>
                                        <?php foreach ($webhooks as $wh): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($wh['name']); ?></div>
                                                <div class="text-muted fs-8"><?php echo htmlspecialchars($wh['company_name'] ?? 'Genel Sistem Webhook'); ?></div>
                                            </td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($wh['url']); ?>" target="_blank" class="text-primary text-hover-primary text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($wh['url']); ?>">
                                                    <?php echo htmlspecialchars($wh['url']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($wh['events']); ?>">
                                                    <?php echo htmlspecialchars($wh['events']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($wh['last_triggered_at']): ?>
                                                    <div><?php echo date('d.m.Y H:i:s', strtotime($wh['last_triggered_at'])); ?></div>
                                                    <div class="text-muted fs-8 mt-1">HTTP <?php echo $wh['last_response_code'] ?? '-'; ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">Hiç tetiklenmedi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($wh['is_active']): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <form action="logs/webhooks.php" method="POST" style="display:inline-block;">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="webhook_id" value="<?php echo $wh['webhook_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-icon btn-<?php echo $wh['is_active'] ? 'light-danger' : 'light-success'; ?>" title="<?php echo $wh['is_active'] ? 'Durdur' : 'Aktifleştir'; ?>">
                                                        <i class="ki-duotone ki-power fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </form>
                                                <?php if($wh['last_error']): ?>
                                                    <button class="btn btn-sm btn-icon btn-light-warning ms-1" title="Hata Detayını Gör" onclick='Swal.fire({title: "Hata Detayı", text: <?php echo json_encode($wh['last_error'], JSON_HEX_APOS | JSON_HEX_QUOT); ?>, icon: "warning"})'>
                                                        <i class="ki-duotone ki-message-text-2 fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-5">Kayıtlı webhook bulunamadı.</td></tr>
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
