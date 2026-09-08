<?php
/**
 * Prolyn Admin Panel - Invoice Addresses Management
 * Prolyn/p/finance/invoice_addresses.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_billing');
require_once '../config/sidebar.php';

// Fatura Adreslerini Çek
$query = "
    SELECT fa.*, k.company_name as customer_company_name 
    FROM fatura_adresleri fa
    LEFT JOIN kurumlar k ON fa.customer_id = k.customer_id
    ORDER BY fa.created_at DESC
";
$addresses = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Fatura Adresleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Finans Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Fatura Adresleri</li>
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
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-address-table-filter="search" class="form-control form-control-solid w-300px ps-12" placeholder="Firma Adı veya Vergi No Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_addresses_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Firma Unvanı (Kayıtlı)</th>
                                        <th>Müşteri Hesabı</th>
                                        <th>Adres Tipi</th>
                                        <th>Açık Adres</th>
                                        <th>Vergi No / Dairesi</th>
                                        <th>Varsayılan</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($addresses)): ?>
                                        <?php foreach ($addresses as $addr): ?>
                                        <tr>
                                            <td>
                                                <span class="text-gray-800 fw-bold fs-6"><?php echo htmlspecialchars($addr['company_name']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary"><?php echo htmlspecialchars($addr['customer_company_name'] ?? 'Bilinmiyor'); ?></span>
                                            </td>
                                            <td>
                                                <?php if($addr['address_type'] == 'billing'): ?>
                                                    <span class="badge badge-light-info text-uppercase">Fatura (Billing)</span>
                                                <?php elseif($addr['address_type'] == 'shipping'): ?>
                                                    <span class="badge badge-light-warning text-uppercase">Teslimat (Shipping)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light"><?php echo htmlspecialchars($addr['address_type']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-gray-800 mb-1" style="max-width:300px; white-space:pre-wrap;"><?php echo htmlspecialchars($addr['address_line1']); ?> <?php echo htmlspecialchars($addr['address_line2']); ?></div>
                                                <div class="text-muted fs-8"><?php echo htmlspecialchars($addr['city']); ?> / <?php echo htmlspecialchars($addr['state']); ?> - <?php echo htmlspecialchars($addr['country']); ?> (PK: <?php echo htmlspecialchars($addr['postal_code']); ?>)</div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($addr['tax_id'] ?? '-'); ?></span>
                                            </td>
                                            <td>
                                                <?php if($addr['is_default']): ?>
                                                    <i class="ki-duotone ki-check-circle fs-1 text-success" title="Varsayılan Adres"><span class="path1"></span><span class="path2"></span></i>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Kayıtlı fatura adresi bulunamadı.</td></tr>
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
