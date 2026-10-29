<?php
/**
 * Prolyn Admin Panel - Payments Management
 * Prolyn/p/finance/payments.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_billing');
require_once '../config/sidebar.php';

// Ödeme Durumu Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $payment_id = (int)$_POST['payment_id'];
    $new_status = $_POST['payment_status'] ?? '';

    if (!in_array($new_status, ['pending', 'completed', 'failed', 'refunded'], true)) {
        echo "<script>window.location.href='finance/payments.php';</script>";
        exit;
    }

    $db->Update("UPDATE odemeler SET payment_status = ?, updated_at = NOW() WHERE payment_id = ?", [$new_status, $payment_id]);
    echo "<script>window.location.href='finance/payments.php?success=status_updated';</script>";
    exit;
}

// Yeni Ödeme Ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_payment') {
    $subscription_id = (int)$_POST['subscription_id'];
    $sub = $db->getRowAssoc("SELECT customer_id FROM abonelikler WHERE subscription_id = ?", [$subscription_id]);

    if ($sub) {
        $amount            = (float)str_replace(',', '.', $_POST['amount'] ?? '0');
        $payment_method    = ($_POST['payment_method'] ?? '') === 'credit_card' ? 'credit_card' : 'bank_transfer';
        $payment_status    = in_array($_POST['payment_status'] ?? '', ['pending', 'completed', 'failed', 'refunded'], true) ? $_POST['payment_status'] : 'completed';
        $invoice_number    = trim($_POST['invoice_number'] ?? '') ?: null;
        $reference_code    = trim($_POST['reference_code'] ?? '') ?: null;
        $notes             = trim($_POST['notes'] ?? '') ?: null;
        $paid_period_start = !empty($_POST['paid_period_start']) ? $_POST['paid_period_start'] : null;
        $paid_period_end   = !empty($_POST['paid_period_end']) ? $_POST['paid_period_end'] : null;

        if ($amount > 0) {
            $db->Insert(
                "INSERT INTO odemeler (customer_id, subscription_id, amount, currency, payment_method, payment_status,
                                       invoice_number, reference_code, notes, payment_date, paid_period_start, paid_period_end, created_at)
                 VALUES (?, ?, ?, 'TRY', ?, ?, ?, ?, ?, NOW(), ?, ?, NOW())",
                [$sub['customer_id'], $subscription_id, $amount, $payment_method, $payment_status,
                 $invoice_number, $reference_code, $notes, $paid_period_start, $paid_period_end]
            );

            // Aboneliğin son ödeme bilgisini güncelle (subscriptions.php'deki "Kalan" hesabı için)
            if ($payment_status === 'completed') {
                $db->Update(
                    "UPDATE abonelikler SET last_payment_date = NOW() WHERE subscription_id = ?",
                    [$subscription_id]
                );
            }
        }
    }

    echo "<script>window.location.href='finance/payments.php?success=payment_added';</script>";
    exit;
}

// Aktif/deneme aboneliklerini seçim listesi için çek (kurum adı + kalan bakiye)
$subscriptionsForSelect = $db->getRowsAssoc(
    "SELECT a.subscription_id, a.total_price, k.company_name,
            COALESCE((SELECT SUM(o.amount) FROM odemeler o WHERE o.subscription_id = a.subscription_id AND o.payment_status = 'completed'), 0) AS alinan
     FROM abonelikler a
     LEFT JOIN kurumlar k ON a.customer_id = k.customer_id
     WHERE a.status = 'active'
     ORDER BY k.company_name ASC"
);

// Ödemeleri Çek
$query = "
    SELECT o.*, k.company_name as customer_name
    FROM odemeler o
    LEFT JOIN kurumlar k ON o.customer_id = k.customer_id
    ORDER BY o.payment_date DESC
";
$payments = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Ödemeler Listesi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Finans Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Ödemeler</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_payment">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Ödeme Ekle
                    </button>
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
                        <span><?php echo ($_GET['success'] ?? '') === 'payment_added' ? 'Ödeme başarıyla eklendi.' : 'Ödeme durumu başarıyla güncellendi.'; ?></span>
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
                                <input type="text" data-kt-payment-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Ödeme veya Kurum Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_payments_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Ödeme (ID) / Müşteri</th>
                                        <th>Tutar</th>
                                        <th>Ödeme Yöntemi</th>
                                        <th>Fatura No / İşlem ID</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($payments)): ?>
                                        <?php foreach ($payments as $pay): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold fs-5 mb-1"><?php echo htmlspecialchars($pay['customer_name'] ?? 'Bilinmiyor'); ?></div>
                                                <div class="text-muted fs-8">Ödeme ID: <?php echo $pay['payment_id']; ?> | Abone ID: <?php echo $pay['subscription_id']; ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold fs-5">
                                                    <?php echo number_format($pay['amount'], 2, ',', '.'); ?> <?php echo htmlspecialchars($pay['currency']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($pay['payment_method'] == 'credit_card'): ?>
                                                    <span class="badge badge-light-primary text-uppercase"><i class="ki-duotone ki-credit-cart fs-6 text-primary me-1"><span class="path1"></span><span class="path2"></span></i> Kredi Kartı</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-info text-uppercase"><i class="ki-duotone ki-bank fs-6 text-info me-1"><span class="path1"></span><span class="path2"></span></i> Havale/EFT</span>
                                                <?php endif; ?>
                                                <?php if($pay['payment_gateway']): ?>
                                                    <div class="text-muted fs-8 mt-1">Altyapı: <?php echo htmlspecialchars($pay['payment_gateway']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-gray-800"><?php echo htmlspecialchars($pay['invoice_number'] ?? '-'); ?></div>
                                                <div class="text-muted fs-8">Trx: <?php echo htmlspecialchars($pay['transaction_id'] ?? '-'); ?></div>
                                            </td>
                                            <td>
                                                <?php echo date('d.m.Y H:i', strtotime($pay['payment_date'])); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-primary';
                                                    if ($pay['payment_status'] == 'pending') $status_class = 'badge-light-warning';
                                                    elseif ($pay['payment_status'] == 'completed') $status_class = 'badge-light-success';
                                                    elseif ($pay['payment_status'] == 'failed') $status_class = 'badge-light-danger';
                                                    elseif ($pay['payment_status'] == 'refunded') $status_class = 'badge-light-dark';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($pay['payment_status']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-info btn-sm me-1" title="Ödeme Detayları" 
                                                    onclick='viewPaymentDetails(<?php echo json_encode($pay, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ki-duotone ki-eye fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm" title="Durum Güncelle" 
                                                    onclick="updateStatusModal(<?php echo (int) $pay['payment_id']; ?>, <?php echo htmlspecialchars(json_encode((string) $pay['payment_status']), ENT_QUOTES, 'UTF-8'); ?>)">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">Kayıtlı ödeme bulunamadı.</td></tr>
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

<!-- Modal: Yeni Ödeme Ekle -->
<div class="modal fade" id="kt_modal_add_payment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form action="finance/payments.php" method="POST">
                <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="add_payment">
                <div class="modal-header">
                    <h2 class="fw-bold">Yeni Ödeme Ekle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Kurum / Abonelik</label>
                        <select class="form-select form-select-solid" name="subscription_id" required onchange="prolynOdemeKalanGoster(this)">
                            <option value="">Seçiniz...</option>
                            <?php foreach ($subscriptionsForSelect as $s): ?>
                                <?php $kalan = max(0, (float)$s['total_price'] - (float)$s['alinan']); ?>
                                <option value="<?php echo $s['subscription_id']; ?>" data-kalan="<?php echo htmlspecialchars(number_format($kalan, 2, ',', '.')); ?>">
                                    <?php echo htmlspecialchars($s['company_name'] ?? 'Bilinmeyen Firma'); ?> —
                                    Toplam <?php echo number_format($s['total_price'], 2, ',', '.'); ?> ₺,
                                    Kalan <?php echo number_format($kalan, 2, ',', '.'); ?> ₺
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="prolyn_odeme_kalan_bilgi"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Tutar (₺)</label>
                            <input type="text" class="form-control form-control-solid" name="amount" placeholder="0.00" required />
                        </div>
                        <div class="col-md-6 fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Ödeme Yöntemi</label>
                            <select class="form-select form-select-solid" name="payment_method" required>
                                <option value="bank_transfer">Havale/EFT</option>
                                <option value="credit_card">Kredi Kartı</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Durum</label>
                            <select class="form-select form-select-solid" name="payment_status" required>
                                <option value="completed">Tamamlandı</option>
                                <option value="pending">Bekliyor</option>
                            </select>
                        </div>
                        <div class="col-md-6 fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Fatura No</label>
                            <input type="text" class="form-control form-control-solid" name="invoice_number" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Ödenen Dönem Başlangıç</label>
                            <input type="date" class="form-control form-control-solid" name="paid_period_start" />
                        </div>
                        <div class="col-md-6 fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Ödenen Dönem Bitiş</label>
                            <input type="date" class="form-control form-control-solid" name="paid_period_end" />
                        </div>
                    </div>
                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Referans Kodu</label>
                        <input type="text" class="form-control form-control-solid" name="reference_code" placeholder="Dekont no, işlem no vb." />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Not</label>
                        <textarea class="form-control form-control-solid" name="notes" rows="2"></textarea>
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

<!-- Modal: Durum Güncelleme -->
<div class="modal fade" id="kt_modal_update_status" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-450px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Ödeme Durumunu Güncelle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="finance/payments.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="payment_id" id="inp_payment_id" value="">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Yeni Durum</label>
                        <select class="form-select form-select-solid" name="payment_status" id="inp_payment_status" required>
                            <option value="pending">Bekliyor (Pending)</option>
                            <option value="completed">Tamamlandı (Completed)</option>
                            <option value="failed">Başarısız (Failed)</option>
                            <option value="refunded">İade Edildi (Refunded)</option>
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

<!-- Modal: Detay Görünümü -->
<div class="modal fade" id="kt_modal_view_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Ödeme Detayları</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Banka / IBAN:</div>
                    <div class="col-sm-8 text-gray-600" id="view_bank_info">-</div>
                </div>
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Kart Markası / Son 4:</div>
                    <div class="col-sm-8 text-gray-600" id="view_card_info">-</div>
                </div>
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Ödenen Dönem:</div>
                    <div class="col-sm-8 text-gray-600" id="view_paid_period">-</div>
                </div>
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Süresi Doluyor (Expires):</div>
                    <div class="col-sm-8 text-gray-600" id="view_expires_at">-</div>
                </div>
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Referans Kodu:</div>
                    <div class="col-sm-8 text-gray-600" id="view_reference">-</div>
                </div>
                <div class="row mb-5">
                    <div class="col-sm-4 fw-bold text-gray-800">Sistem Notları:</div>
                    <div class="col-sm-8 text-gray-600" id="view_notes">-</div>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
function prolynOdemeKalanGoster(sel) {
    var opt = sel.options[sel.selectedIndex];
    var info = document.getElementById('prolyn_odeme_kalan_bilgi');
    var kalan = opt ? opt.getAttribute('data-kalan') : null;
    info.textContent = kalan ? ('Bu abonelikte kalan bakiye: ' + kalan + ' ₺') : '';
}

function updateStatusModal(payment_id, status) {
    document.getElementById('inp_payment_id').value = payment_id;
    document.getElementById('inp_payment_status').value = status;
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_update_status'));
    modal.show();
}

function viewPaymentDetails(pay) {
    document.getElementById('view_bank_info').innerText = (pay.bank_code || '') + ' ' + (pay.iban || '-');
    document.getElementById('view_card_info').innerText = (pay.card_brand || '-') + ' / ****' + (pay.card_last4 || '-');
    
    let period = '-';
    if(pay.paid_period_start && pay.paid_period_end) {
        period = new Date(pay.paid_period_start).toLocaleDateString('tr-TR') + ' - ' + new Date(pay.paid_period_end).toLocaleDateString('tr-TR');
    }
    document.getElementById('view_paid_period').innerText = period;
    
    document.getElementById('view_expires_at').innerText = pay.expires_at ? new Date(pay.expires_at).toLocaleString('tr-TR') : '-';
    document.getElementById('view_reference').innerText = pay.reference_code || '-';
    document.getElementById('view_notes').innerText = pay.notes || 'Not bulunmuyor.';
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_view_details'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
