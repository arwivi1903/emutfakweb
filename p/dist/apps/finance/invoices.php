<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$invoices = $db->getRows("
    SELECT i.*, c.company_name 
    FROM invoices i 
    JOIN customers c ON i.customer_id = c.customer_id 
    ORDER BY i.issue_date DESC
");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Faturalar</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Tüm kesilen faturalar</span>
                            </h3>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-100px">Fatura No</th>
                                    <th class="min-w-175px">Müşteri</th>
                                    <th class="min-w-100px">Düzenlenme Tarihi</th>
                                    <th class="min-w-100px">Son Ödeme</th>
                                    <th class="text-end min-w-100px">Tutar</th>
                                    <th class="text-end min-w-100px">Durum</th>
                                    <th class="text-end min-w-100px">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td>
                                        <a href="#" class="text-gray-800 text-hover-primary fw-bold"><?= htmlspecialchars($inv->invoice_number) ?></a>
                                    </td>
                                    <td>
                                        <a href="../../account/customer-detail.php?id=<?= $inv->customer_id ?>" class="text-gray-800 text-hover-primary fw-bold"><?= htmlspecialchars($inv->company_name) ?></a>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($inv->issue_date)) ?></td>
                                    <td><?= $inv->due_date ? date('d.m.Y', strtotime($inv->due_date)) : '-' ?></td>
                                    <td class="text-end">
                                        <?= number_format($inv->total_amount, 2) ?> <?= $inv->currency ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($inv->status == 'paid'): ?>
                                            <span class="badge badge-light-success">Ödendi</span>
                                        <?php elseif ($inv->status == 'issued'): ?>
                                            <span class="badge badge-light-primary">Kesildi</span>
                                        <?php elseif ($inv->status == 'draft'): ?>
                                            <span class="badge badge-light-secondary">Taslak</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">İptal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary">İndir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
