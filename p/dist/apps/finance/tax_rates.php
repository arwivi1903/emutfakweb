<?php
ob_start();
require_once __DIR__ . '/../../../classes/database.class.php';

$db = new Database();

// Oturum kontrolü
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-migration: is_active sütunu yoksa ekle
try {
    $checkCols = $db->getRows("SHOW COLUMNS FROM tax_rates LIKE 'is_active'");
    if (empty($checkCols)) {
        $db->Update("ALTER TABLE tax_rates ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER is_default");
    }
} catch (Exception $e) { }

// Backend Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Vergi Ekleme
    if ($action == 'add_tax') {
        $tax_name = trim($_POST['tax_name'] ?? '');
        $tax_percent = floatval($_POST['tax_percent'] ?? 0);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if ($tax_name && $tax_percent >= 0) {
            try {
                if ($is_default) $db->Update("UPDATE tax_rates SET is_default = 0");
                $sql = "INSERT INTO tax_rates (tax_name, tax_percent, is_default, is_active, created_at) VALUES (?, ?, ?, 1, NOW())";
                $db->Update($sql, [$tax_name, $tax_percent, $is_default]);
                $_SESSION['success_message'] = 'Vergi oranı başarıyla eklendi.';
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Vergi eklenirken hata oluştu: " . $e->getMessage();
            }
        }
        header("Location: tax_rates.php");
        exit;
    }

    // Vergi Düzenleme
    if ($action == 'edit_tax') {
        $tax_id = intval($_POST['tax_id'] ?? 0);
        $tax_name = trim($_POST['tax_name'] ?? '');
        $tax_percent = floatval($_POST['tax_percent'] ?? 0);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if ($tax_id > 0 && $tax_name && $tax_percent >= 0) {
            try {
                if ($is_default) $db->Update("UPDATE tax_rates SET is_default = 0");
                $sql = "UPDATE tax_rates SET tax_name = ?, tax_percent = ?, is_default = ? WHERE tax_id = ?";
                $db->Update($sql, [$tax_name, $tax_percent, $is_default, $tax_id]);
                $_SESSION['success_message'] = 'Vergi oranı başarıyla güncellendi.';
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Vergi güncellenirken hata oluştu: " . $e->getMessage();
            }
        }
        header("Location: tax_rates.php");
        exit;
    }

    // Durum Değiştirme
    if ($action == 'toggle_status') {
        $tax_id = intval($_POST['tax_id'] ?? 0);
        $new_status = intval($_POST['new_status'] ?? 0);
        if ($tax_id > 0) {
            try {
                $sql = ($new_status == 0) ? "UPDATE tax_rates SET is_active = ?, is_default = 0 WHERE tax_id = ?" : "UPDATE tax_rates SET is_active = ? WHERE tax_id = ?";
                $db->Update($sql, [$new_status, $tax_id]);
                $_SESSION['success_message'] = 'Vergi durumu güncellendi.';
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Durum güncellenirken hata oluştu: " . $e->getMessage();
            }
        }
        header("Location: tax_rates.php");
        exit;
    }
}

$taxes = $db->getRows("SELECT * FROM tax_rates ORDER BY tax_id ASC");

require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Vergi Oranları</h3></div>
                        <div class="card-toolbar">
                             <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_tax">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Vergi Ekle
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
                                        customClass: { confirmButton: "btn btn-primary" }
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
                                        customClass: { confirmButton: "btn btn-primary" }
                                    });
                                });
                            </script>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>ID</th>
                                    <th>Vergi Adı</th>
                                    <th>Oran (%)</th>
                                    <th>Varsayılan</th>
                                    <th>Durum</th>
                                    <th>Oluşturulma</th>
                                    <th class="text-end">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($taxes as $tax): 
                                    // is_active sütunu yoksa varsayılan olarak 1 kabul et (geriye dönük uyumluluk)
                                    $is_active = isset($tax->is_active) ? $tax->is_active : 1; 
                                ?>
                                <tr>
                                    <td><?= $tax->tax_id ?></td>
                                    <td><?= htmlspecialchars($tax->tax_name) ?></td>
                                    <td>%<?= $tax->tax_percent ?></td>
                                    <td>
                                        <?php if ($tax->is_default == 1): ?>
                                            <span class="badge badge-light-success">Varsayılan</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-<?= $is_active ? 'success' : 'danger' ?>">
                                            <?= $is_active ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($tax->created_at)) ?></td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light-primary btn-edit-tax" 
                                           data-id="<?= $tax->tax_id ?>" 
                                           data-name="<?= htmlspecialchars($tax->tax_name) ?>" 
                                           data-percent="<?= $tax->tax_percent ?>" 
                                           data-default="<?= $tax->is_default ?>"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#kt_modal_edit_tax">Düzenle</a>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="tax_id" value="<?= $tax->tax_id ?>">
                                            <input type="hidden" name="new_status" value="<?= $is_active ? 0 : 1 ?>">
                                            <button type="submit" class="btn btn-sm btn-light-<?= $is_active ? 'danger' : 'success' ?>">
                                                <?= $is_active ? 'Pasif Yap' : 'Aktif Yap' ?>
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

<!-- Add Tax Modal -->
<div class="modal fade" id="kt_modal_add_tax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Yeni Vergi Oranı Ekle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_tax_form" class="form" method="POST">
                    <input type="hidden" name="action" value="add_tax">
                    
                    <!-- Vergi Adı -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Vergi Adı</label>
                        <input type="text" class="form-control form-control-solid" placeholder="KDV %18" name="tax_name" required />
                    </div>

                    <!-- Vergi Oranı -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Vergi Oranı (%)</label>
                        <input type="number" step="0.01" class="form-control form-control-solid" placeholder="18" name="tax_percent" required />
                    </div>

                    <!-- Varsayılan -->
                    <div class="fv-row mb-7">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default" />
                            <label class="form-check-label fw-semibold" for="is_default">
                                Varsayılan Olarak Ayarla
                            </label>
                        </div>
                        <div class="form-text">Bu seçenek işaretlenirse diğer vergilerin varsayılan özelliği kaldırılır.</div>
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

<!-- Edit Tax Modal -->
<div class="modal fade" id="kt_modal_edit_tax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Vergi Oranını Düzenle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_edit_tax_form" class="form" method="POST">
                    <input type="hidden" name="action" value="edit_tax">
                    <input type="hidden" name="tax_id" id="edit_tax_id">
                    
                    <!-- Vergi Adı -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Vergi Adı</label>
                        <input type="text" class="form-control form-control-solid" name="tax_name" id="edit_tax_name" required />
                    </div>

                    <!-- Vergi Oranı -->
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Vergi Oranı (%)</label>
                        <input type="number" step="0.01" class="form-control form-control-solid" name="tax_percent" id="edit_tax_percent" required />
                    </div>

                    <!-- Varsayılan -->
                    <div class="fv-row mb-7">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_default" id="edit_is_default" />
                            <label class="form-check-label fw-semibold" for="edit_is_default">
                                Varsayılan Olarak Ayarla
                            </label>
                        </div>
                        <div class="form-text">Bu seçenek işaretlenirse diğer vergilerin varsayılan özelliği kaldırılır.</div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Güncelle</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Modal Data Populator
    const editButtons = document.querySelectorAll('.btn-edit-tax');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const percent = this.getAttribute('data-percent');
            const isDefault = this.getAttribute('data-default');

            document.getElementById('edit_tax_id').value = id;
            document.getElementById('edit_tax_name').value = name;
            document.getElementById('edit_tax_percent').value = percent;
            document.getElementById('edit_is_default').checked = (isDefault == '1');
        });
    });
});
</script>
