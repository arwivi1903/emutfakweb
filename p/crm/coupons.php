<?php
/**
 * Prolyn Admin Panel - Coupons Management
 * Prolyn/p/crm/coupons.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_subscriptions');
require_once '../config/sidebar.php';

// Kupon Ekle / Düzenle (POST İşlemi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_coupon') {
        $coupon_id      = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
        $code           = strtoupper(trim($_POST['code']));
        $discount_type  = $_POST['discount_type'];
        $discount_value = (float)$_POST['discount_value'];
        $min_spend      = isset($_POST['min_spend']) && $_POST['min_spend'] !== '' ? (float)$_POST['min_spend'] : 0.00;
        $starts_at      = !empty($_POST['starts_at']) ? date('Y-m-d H:i:s', strtotime($_POST['starts_at'])) : null;
        $expires_at     = !empty($_POST['expires_at']) ? date('Y-m-d H:i:s', strtotime($_POST['expires_at'])) : null;
        $usage_limit    = isset($_POST['usage_limit']) && $_POST['usage_limit'] !== '' ? (int)$_POST['usage_limit'] : null;
        $is_active      = isset($_POST['is_active']) ? 1 : 0;
        
        if ($coupon_id > 0) {
            // Update
            $db->Update("
                UPDATE kuponlar 
                SET code = ?, discount_type = ?, discount_value = ?, min_spend = ?, starts_at = ?, expires_at = ?, usage_limit = ?, is_active = ?
                WHERE coupon_id = ?
            ", [$code, $discount_type, $discount_value, $min_spend, $starts_at, $expires_at, $usage_limit, $is_active, $coupon_id]);
        } else {
            // Insert
            $db->Insert("
                INSERT INTO kuponlar 
                (code, discount_type, discount_value, min_spend, starts_at, expires_at, usage_limit, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [$code, $discount_type, $discount_value, $min_spend, $starts_at, $expires_at, $usage_limit, $is_active]);
        }
        echo "<script>window.location.href='crm/coupons.php?success=saved';</script>";
        exit;
    }
}

// Kuponları Çek
$query = "SELECT * FROM kuponlar ORDER BY created_at DESC";
$coupons = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Kupon Yönetimi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">CRM Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Kuponlar</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" onclick="openCouponModal()">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Kupon Ekle
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
                        <span>Kupon başarıyla kaydedildi.</span>
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
                                <input type="text" data-kt-coupon-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Kupon Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_coupons_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Kupon Kodu</th>
                                        <th>İndirim</th>
                                        <th>Min. Sepet</th>
                                        <th>Kullanım Limiti</th>
                                        <th>Tarih Aralığı</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($coupons)): ?>
                                        <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td>
                                                <span class="text-gray-800 fw-bold fs-5 text-uppercase"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                            </td>
                                            <td>
                                                <?php if($coupon['discount_type'] == 'percentage'): ?>
                                                    <span class="badge badge-light-success fs-6">%<?php echo (float)$coupon['discount_value']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-info fs-6"><?php echo number_format($coupon['discount_value'], 2, ',', '.'); ?> ₺</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $coupon['min_spend'] > 0 ? number_format($coupon['min_spend'], 2, ',', '.') . ' ₺' : '<span class="text-muted">Limit Yok</span>'; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800">Kullanılan: <?php echo $coupon['used_count']; ?></span>
                                                    <span class="text-muted fs-8">Limit: <?php echo $coupon['usage_limit'] ? $coupon['usage_limit'] : 'Sınırsız'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800">Başlangıç: <?php echo $coupon['starts_at'] ? date('d.m.Y H:i', strtotime($coupon['starts_at'])) : '-'; ?></span>
                                                    <span class="text-muted fs-8">Bitiş: <?php echo $coupon['expires_at'] ? date('d.m.Y H:i', strtotime($coupon['expires_at'])) : '-'; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($coupon['is_active']): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" title="Düzenle" 
                                                    onclick='editCoupon(<?php echo json_encode($coupon, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted py-5">Kayıtlı kupon bulunamadı.</td></tr>
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

<!-- Modal: Kupon Ekle / Düzenle -->
<div class="modal fade" id="kt_modal_coupon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_coupon_title">Yeni Kupon Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="crm/coupons.php" method="POST" id="form_coupon">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_coupon">
                <input type="hidden" name="coupon_id" id="inp_coupon_id" value="0">
                
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    
                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Kupon Kodu</label>
                            <input type="text" class="form-control form-control-solid text-uppercase" name="code" id="inp_code" placeholder="Örn: YAZ10" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İndirim Türü</label>
                            <select class="form-select form-select-solid" name="discount_type" id="inp_discount_type" required>
                                <option value="percentage">Yüzdelik (%)</option>
                                <option value="fixed">Sabit Tutar (₺)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">İndirim Miktarı</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="discount_value" id="inp_discount_value" placeholder="Örn: 10 veya 50.00" required />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Minimum Sepet Tutarı (₺)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="min_spend" id="inp_min_spend" placeholder="Boş bırakılabilir" />
                        </div>
                    </div>

                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="starts_at" id="inp_starts_at" />
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="expires_at" id="inp_expires_at" />
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Kullanım Limiti</label>
                        <input type="number" class="form-control form-control-solid" name="usage_limit" id="inp_usage_limit" placeholder="Toplam kaç kişi kullanabilir? (Boş = Sınırsız)" />
                    </div>

                    <div class="fv-row mb-7">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="inp_is_active" value="1" checked="checked" />
                            <label class="form-check-label fw-semibold text-gray-800" for="inp_is_active">
                                Kupon Aktif
                            </label>
                        </div>
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
function openCouponModal() {
    document.getElementById('form_coupon').reset();
    document.getElementById('inp_coupon_id').value = '0';
    document.getElementById('modal_coupon_title').innerText = 'Yeni Kupon Ekle';
    document.getElementById('inp_is_active').checked = true;
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_coupon'));
    modal.show();
}

function editCoupon(coupon) {
    document.getElementById('form_coupon').reset();
    document.getElementById('modal_coupon_title').innerText = 'Kupon Düzenle';
    
    document.getElementById('inp_coupon_id').value = coupon.coupon_id;
    document.getElementById('inp_code').value = coupon.code;
    document.getElementById('inp_discount_type').value = coupon.discount_type;
    document.getElementById('inp_discount_value').value = coupon.discount_value;
    document.getElementById('inp_min_spend').value = coupon.min_spend;
    
    // Format datetime-local for input
    if(coupon.starts_at) {
        let sd = new Date(coupon.starts_at);
        document.getElementById('inp_starts_at').value = sd.toISOString().slice(0,16);
    }
    if(coupon.expires_at) {
        let ed = new Date(coupon.expires_at);
        document.getElementById('inp_expires_at').value = ed.toISOString().slice(0,16);
    }
    
    document.getElementById('inp_usage_limit').value = coupon.usage_limit;
    document.getElementById('inp_is_active').checked = (coupon.is_active == 1);
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_coupon'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
