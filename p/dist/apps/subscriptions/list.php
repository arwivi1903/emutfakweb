<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$subscriptions = $db->getRows("
    SELECT s.*, c.company_name, c.logo_url 
    FROM subscriptions s 
    JOIN customers c ON s.customer_id = c.customer_id 
    ORDER BY s.end_date ASC
");
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" data-kt-subscription-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Abonelik Ara" />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-subscription-table-toolbar="base">
                                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>Filtrele
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                    <div class="px-7 py-5">
                                        <div class="fs-5 text-dark fw-bold">Filtre Seçenekleri</div>
                                    </div>
                                    <div class="separator border-gray-200"></div>
                                    <div class="px-7 py-5" data-kt-subscription-table-filter="form">
                                        <div class="mb-10">
                                            <label class="form-label fs-6 fw-semibold">Durum:</label>
                                            <select class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Seçiniz" data-allow-clear="true" data-kt-subscription-table-filter="status" data-hide-search="true">
                                                <option></option>
                                                <option value="Active">Aktif</option>
                                                <option value="Expired">Süresi Dolmuş</option>
                                                <option value="Suspended">Askıda</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-subscription-table-filter="reset">Sıfırla</button>
                                            <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-subscription-table-filter="filter">Uygula</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary">
                                    <i class="ki-duotone ki-plus fs-2"></i>Yeni Abonelik
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_subscriptions_table">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_subscriptions_table .form-check-input" value="1" />
                                        </div>
                                    </th>
                                    <th class="min-w-125px">Müşteri</th>
                                    <th class="min-w-125px">Durum</th>
                                    <th class="min-w-125px">Fatura Döngüsü</th>
                                    <th class="min-w-125px">Fiyat</th>
                                    <th class="min-w-125px">Bitiş Tarihi</th>
                                    <th class="text-end min-w-70px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px me-5">
                                                <?php if($sub->logo_url): ?>
                                                    <img src="<?= htmlspecialchars($sub->logo_url) ?>" alt="" />
                                                <?php else: ?>
                                                    <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                        <?= mb_substr($sub->company_name, 0, 1) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="dist/apps/customers/detail.php?id=<?= $sub->customer_id ?>" class="text-gray-800 text-hover-primary mb-1"><?= htmlspecialchars($sub->company_name) ?></a>
                                                <span><?= htmlspecialchars($sub->plan_name) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($sub->status == 'active'): ?>
                                            <div class="badge badge-light-success">Aktif</div>
                                        <?php elseif($sub->status == 'expired'): ?>
                                            <div class="badge badge-light-danger">Süresi Dolmuş</div>
                                        <?php else: ?>
                                            <div class="badge badge-light-warning"><?= htmlspecialchars($sub->status) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $sub->billing_cycle ?> Gün</td>
                                    <td><?= number_format($sub->price_per_month, 2) ?> TL</td>
                                    <td><?= date('d.m.Y', strtotime($sub->end_date)) ?></td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            İşlem
                                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                        </a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a href="dist/apps/subscriptions/edit.php?id=<?= $sub->subscription_id ?>" class="menu-link px-3">Düzenle</a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3" data-kt-subscriptions-table-filter="delete_row">Sil</a>
                                            </div>
                                        </div>
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
