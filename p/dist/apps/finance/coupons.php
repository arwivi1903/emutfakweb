<?php
require_once __DIR__ . '/../../../classes/database.class.php';

$db = new Database();

// Session başlat (eğer başlatılmamışsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Yeni kupon ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_coupon') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount_type = $_POST['discount_type'] ?? 'percentage';
    $discount_value = floatval($_POST['discount_value'] ?? 0);
    $min_spend = floatval($_POST['min_spend'] ?? 0);
    $starts_at = !empty($_POST['starts_at']) ? $_POST['starts_at'] : null;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($code && $discount_value > 0) {
        $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_spend, starts_at, expires_at, usage_limit, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        try {
            $db->Update($sql, [$code, $discount_type, $discount_value, $min_spend, $starts_at, $expires_at, $usage_limit, $is_active]);
            $_SESSION['success_message'] = 'Kupon başarıyla eklendi.';
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Kupon eklenirken hata oluştu: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Kupon kodu ve indirim değeri zorunludur.";
    }
}

// Kupon durumu değiştirme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $coupon_id = intval($_POST['coupon_id'] ?? 0);
    $new_status = intval($_POST['new_status'] ?? 0);
    
    if ($coupon_id > 0) {
        $sql = "UPDATE coupons SET is_active = ? WHERE coupon_id = ?";
        try {
            $db->Update($sql, [$new_status, $coupon_id]);
            $_SESSION['success_message'] = 'Kupon durumu güncellendi.';
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Durum güncellenirken hata oluştu: " . $e->getMessage();
        }
    }
}

// Schema'da coupons tablosu var
$coupons = $db->getRows("SELECT * FROM coupons ORDER BY created_at DESC");

require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Kuponlar</h3></div>
                        <div class="card-toolbar">
                             <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_coupon">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Kupon
                             </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if(isset($_SESSION['success_message'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        text: "<?= addslashes($_SESSION['success_message']) ?>",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Tamam",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });
                                });
                            </script>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        text: "<?= addslashes($_SESSION['error_message']) ?>",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Tamam",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });
                                });
                            </script>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Kupon Kodu</th>
                                    <th>İndirim</th>
                                    <th>Son Kullanma</th>
                                    <th>Durum</th>
                                    <th class="text-end min-w-100px">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $coupon): ?>
                                <tr>
                                    <td><?= htmlspecialchars($coupon->code) ?></td>
                                    <td>
                                        <?= $coupon->discount_type == 'percentage' ? '%' . $coupon->discount_value : $coupon->discount_value . ' TL' ?>
                                    </td>
                                    <td><?= $coupon->expires_at ? date('d.m.Y H:i', strtotime($coupon->expires_at)) : '-' ?></td>
                                    <td>
                                        <span class="badge badge-light-<?= $coupon->is_active ? 'success' : 'danger' ?>">
                                            <?= $coupon->is_active ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="coupon_id" value="<?= $coupon->coupon_id ?>">
                                            <input type="hidden" name="new_status" value="<?= $coupon->is_active ? 0 : 1 ?>">
                                            <button type="submit" class="btn btn-sm btn-light-<?= $coupon->is_active ? 'danger' : 'success' ?>">
                                                <?= $coupon->is_active ? 'Pasif Yap' : 'Aktif Yap' ?>
                                            </button>
                                        </form>
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

<!-- Add Coupon Modal -->
<div class="modal fade" id="kt_modal_add_coupon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Yeni Kupon Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_coupon_form" class="form" method="POST">
                    <input type="hidden" name="action" value="add_coupon">
                    
                    <!-- Kupon Kodu -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Kupon Kodu</label>
                        <input type="text" class="form-control form-control-solid" placeholder="YENI2026" name="code" required />
                        <div class="form-text">Büyük harflerle girilecektir</div>
                    </div>

                    <!-- İndirim Tipi -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">İndirim Tipi</label>
                        <select class="form-select form-select-solid" name="discount_type" required>
                            <option value="percentage">Yüzde (%)</option>
                            <option value="fixed">Sabit Tutar (TL)</option>
                        </select>
                    </div>

                    <!-- İndirim Değeri -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">İndirim Değeri</label>
                        <input type="number" step="0.01" class="form-control form-control-solid" placeholder="20" name="discount_value" required />
                    </div>

                    <!-- Minimum Harcama -->
                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Minimum Harcama (TL)</label>
                        <input type="number" step="0.01" class="form-control form-control-solid" placeholder="0" name="min_spend" value="0" />
                    </div>

                    <div class="row">
                        <!-- Başlangıç Tarihi -->
                        <div class="col-md-6 fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Başlangıç Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="starts_at" />
                        </div>

                        <!-- Bitiş Tarihi -->
                        <div class="col-md-6 fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Bitiş Tarihi</label>
                            <input type="datetime-local" class="form-control form-control-solid" name="expires_at" />
                        </div>
                    </div>

                    <!-- Kullanım Limiti -->
                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Kullanım Limiti</label>
                        <input type="number" class="form-control form-control-solid" placeholder="Sınırsız" name="usage_limit" />
                        <div class="form-text">Boş bırakılırsa sınırsız kullanım</div>
                    </div>

                    <!-- Durum -->
                    <div class="fv-row mb-7">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked />
                            <label class="form-check-label fw-semibold" for="is_active">
                                Kupon Aktif
                            </label>
                        </div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Kaydet</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
