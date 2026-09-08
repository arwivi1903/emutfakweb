<?php
/**
 * Prolyn Admin Panel - Invoices Management
 * Prolyn/p/finance/invoices.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_billing');
require_once '../config/sidebar.php';

// Fatura Durumu Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $invoice_id = (int)$_POST['invoice_id'];
    $new_status = $_POST['status'];
    
    $db->Update("UPDATE faturalar SET status = ?, updated_at = NOW() WHERE invoice_id = ?", [$new_status, $invoice_id]);
    echo "<script>window.location.href='finance/invoices.php?success=status';</script>";
    exit;
}

// Faturaları Çek
$query = "
    SELECT f.*, k.company_name as customer_name
    FROM faturalar f
    LEFT JOIN kurumlar k ON f.customer_id = k.customer_id
    ORDER BY f.issue_date DESC, f.created_at DESC
";
$invoices = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Faturalar
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Finans Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Faturalar</li>
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
                        <span>İşlem başarıyla gerçekleştirildi.</span>
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
                                <input type="text" data-kt-invoice-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Fatura veya Müşteri Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_invoices_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Fatura Bilgisi</th>
                                        <th>Müşteri</th>
                                        <th>Tarihler</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($invoices)): ?>
                                        <?php foreach ($invoices as $inv): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold fs-5"><?php echo htmlspecialchars($inv['invoice_number']); ?></div>
                                                <div class="text-muted fs-7">ID: <?php echo $inv['invoice_id']; ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($inv['customer_name'] ?? 'Bilinmiyor'); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800">Düzenleme: <?php echo date('d.m.Y', strtotime($inv['issue_date'])); ?></span>
                                                    <span class="text-muted fs-8">Son Ödeme: <?php echo $inv['due_date'] ? date('d.m.Y', strtotime($inv['due_date'])) : '-'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-gray-800 fw-bold fs-5">
                                                    <?php echo number_format($inv['total_amount'], 2, ',', '.'); ?> <?php echo htmlspecialchars($inv['currency']); ?>
                                                </div>
                                                <div class="text-muted fs-8">Vergi: <?php echo number_format($inv['tax_amount'], 2, ',', '.'); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($inv['status'] == 'draft') $status_class = 'badge-light-secondary text-gray-700';
                                                    elseif ($inv['status'] == 'issued') $status_class = 'badge-light-info';
                                                    elseif ($inv['status'] == 'paid') $status_class = 'badge-light-success';
                                                    elseif ($inv['status'] == 'cancelled') $status_class = 'badge-light-danger';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($inv['status']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" title="Durum Güncelle" 
                                                    onclick="updateStatusModal(<?php echo $inv['invoice_id']; ?>, '<?php echo $inv['status']; ?>', '<?php echo htmlspecialchars($inv['invoice_number']); ?>')">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                <?php if($inv['pdf_url']): ?>
                                                <a href="<?php echo htmlspecialchars($inv['pdf_url']); ?>" target="_blank" class="btn btn-icon btn-light-info btn-sm" title="PDF Görüntüle">
                                                    <i class="ki-duotone ki-document fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Kayıtlı fatura bulunamadı.</td></tr>
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

<!-- Modal: Durum Güncelleme -->
<div class="modal fade" id="kt_modal_update_status" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Fatura Durumunu Güncelle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="finance/invoices.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="invoice_id" id="inp_invoice_id" value="">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    
                    <div class="d-flex flex-column mb-5">
                        <span class="fw-semibold text-muted">Fatura No:</span>
                        <span class="fw-bold fs-4 text-gray-800" id="view_invoice_number"></span>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Yeni Durum</label>
                        <select class="form-select form-select-solid" name="status" id="inp_status" required>
                            <option value="draft">Taslak (Draft)</option>
                            <option value="issued">Kesildi/Gönderildi (Issued)</option>
                            <option value="paid">Ödendi (Paid)</option>
                            <option value="cancelled">İptal Edildi (Cancelled)</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStatusModal(invoice_id, status, invoice_number) {
    document.getElementById('inp_invoice_id').value = invoice_id;
    document.getElementById('view_invoice_number').innerText = invoice_number;
    document.getElementById('inp_status').value = status;
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_update_status'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
