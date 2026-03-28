<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
// support_tickets tablosundan verileri çek
$tickets = $db->getRows("
    SELECT t.*, c.company_name, a.username as assignee_name 
    FROM support_tickets t 
    JOIN customers c ON t.customer_id = c.customer_id 
    LEFT JOIN admins a ON t.assigned_to = a.admin_id 
    ORDER BY t.created_at DESC
");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3>Destek Talepleri</h3>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_ticket">
                                    <i class="ki-duotone ki-plus fs-2"></i>Yeni Talep
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_tickets">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-100px">Talep No</th>
                                    <th class="min-w-125px">Müşteri</th>
                                    <th class="min-w-125px">Konu</th>
                                    <th class="min-w-125px">Atanan</th>
                                    <th class="min-w-125px">Durum</th>
                                    <th class="min-w-125px">Öncelik</th>
                                    <th class="min-w-125px">Tarih</th>
                                    <th class="text-end min-w-100px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($ticket->ticket_number) ?></td>
                                    <td><?= htmlspecialchars($ticket->company_name) ?></td>
                                    <td><?= htmlspecialchars($ticket->subject) ?></td>
                                    <td><?= htmlspecialchars($ticket->assignee_name ?? '-') ?></td>
                                    <td>
                                        <span class="badge badge-light-<?= $ticket->status == 'open' ? 'primary' : ($ticket->status == 'closed' ? 'success' : 'warning') ?>">
                                            <?= $ticket->status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-<?= $ticket->priority == 'high' ? 'danger' : ($ticket->priority == 'medium' ? 'warning' : 'info') ?>">
                                            <?= $ticket->priority ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($ticket->created_at)) ?></td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm">Görüntüle</a>
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
