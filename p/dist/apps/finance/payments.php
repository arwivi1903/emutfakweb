<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$payments = $db->getRows("
    SELECT p.*, c.company_name 
    FROM payments p 
    JOIN customers c ON p.customer_id = c.customer_id 
    ORDER BY p.payment_date DESC
");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-ecommerce-order-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Ödeme Ara" />
                            </div>
                        </div>
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                            <div class="w-100 mw-150px">
                                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Durum">
                                    <option></option>
                                    <option value="all">Tümü</option>
                                    <option value="completed">Başarılı</option>
                                    <option value="pending">Beklemede</option>
                                    <option value="failed">Hatalı</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_sales_table">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_sales_table .form-check-input" value="1" />
                                        </div>
                                    </th>
                                    <th class="min-w-100px">Ödeme ID</th>
                                    <th class="min-w-175px">Müşteri</th>
                                    <th class="text-end min-w-70px">Durum</th>
                                    <th class="text-end min-w-100px">Tutar</th>
                                    <th class="text-end min-w-100px">Tarih</th>
                                    <th class="text-end min-w-100px">Yöntem</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1" />
                                        </div>
                                    </td>
                                    <td data-kt-ecommerce-order-filter="order_id">
                                        <a href="#" class="text-gray-800 text-hover-primary fw-bold">#<?= $pay->payment_id ?></a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-5">
                                                <a href="dist/apps/customers/detail.php?id=<?= $pay->customer_id ?>" class="text-gray-800 text-hover-primary fs-5 fw-bold"><?= htmlspecialchars($pay->company_name) ?></a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-0">
                                        <?php if ($pay->payment_status == 'completed'): ?>
                                            <span class="badge badge-light-success">Tamamlandı</span>
                                        <?php elseif ($pay->payment_status == 'pending'): ?>
                                            <span class="badge badge-light-warning">Beklemede</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">Hatalı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bold"><?= number_format($pay->amount, 2) ?> <?= $pay->currency ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold"><?= date('d.m.Y H:i', strtotime($pay->payment_date)) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold"><?= $pay->payment_method ?></span>
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
