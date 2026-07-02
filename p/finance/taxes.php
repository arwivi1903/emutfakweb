<?php
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_billing');
require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();

// POST İşlemleri (Ekleme/Düzenleme)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $logAdminId = $_SESSION['admin_id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($action == 'save_tax') {
        $tax_id = intval($_POST['tax_id'] ?? 0);
        $tax_name = trim($_POST['tax_name'] ?? '');
        $tax_percent = floatval($_POST['tax_percent'] ?? 0);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (!empty($tax_name) && $tax_percent >= 0) {
            try {
                $db->beginTransaction();
                
                // Eğer is_default = 1 olarak seçildiyse diğerlerini 0 yap
                if ($is_default == 1) {
                    $db->Update("UPDATE vergi_oranlari SET is_default = 0", []);
                }

                if ($tax_id > 0) {
                    $oldData = $db->getRow("SELECT tax_name, tax_percent, is_default, is_active FROM vergi_oranlari WHERE tax_id=?", [$tax_id]);

                    $sql = "UPDATE vergi_oranlari SET tax_name=?, tax_percent=?, is_default=?, is_active=? WHERE tax_id=?";
                    $db->Update($sql, [$tax_name, $tax_percent, $is_default, $is_active, $tax_id]);

                    $newData = [
                        'tax_name' => $tax_name,
                        'tax_percent' => $tax_percent,
                        'is_default' => $is_default,
                        'is_active' => $is_active
                    ];

                    xlog('UPDATE', 'Vergi oranı güncellendi', $tax_id, $logAdminId, $db, $oldData, $newData, 'vergi_oranlari');
                    $success_msg = "Vergi oranı başarıyla güncellendi.";
                } else {
                    $sql = "INSERT INTO vergi_oranlari (tax_name, tax_percent, is_default, is_active, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $new_id = $db->Insert($sql, [$tax_name, $tax_percent, $is_default, $is_active]);

                    $newData = [
                        'tax_name' => $tax_name,
                        'tax_percent' => $tax_percent,
                        'is_default' => $is_default,
                        'is_active' => $is_active
                    ];

                    xlog('INSERT', 'Yeni vergi oranı eklendi', $new_id, $logAdminId, $db, null, $newData, 'vergi_oranlari');
                    $success_msg = "Vergi oranı başarıyla eklendi.";
                }
                
                $db->commit();
            } catch (Exception $e) {
                $db->rollback();
                $error_msg = "İşlem sırasında hata: " . $e->getMessage();
            }
        } else {
            $error_msg = "Vergi Adı ve Oranı zorunludur. Oran 0 veya daha büyük olmalıdır.";
        }
    }
}

// Tüm Vergileri Çek
$taxes = $db->getRows("SELECT * FROM vergi_oranlari ORDER BY tax_percent ASC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Vergi Oranları</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Finans Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Vergi Oranları</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <?php if(isset($success_msg)): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Başarılı</h4>
                        <span><?= $success_msg ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Hata</h4>
                        <span><?= $error_msg ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Vergi Parametreleri</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Sistemde kullanılacak güncel vergiler</span>
                            </h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_tax" onclick="clearTaxForm()">
                                <i class="ki-duotone ki-plus fs-2"></i>Yeni Vergi Ekle
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-150px">Vergi Adı</th>
                                        <th class="min-w-100px text-center">Oran (%)</th>
                                        <th class="min-w-100px text-center">Varsayılan</th>
                                        <th class="min-w-100px text-center">Durum</th>
                                        <th class="min-w-150px">Oluşturulma</th>
                                        <th class="text-end min-w-100px">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php foreach ($taxes as $tax): ?>
                                    <tr>
                                        <td>
                                            <span class="text-gray-800 fw-bold fs-5"><?= htmlspecialchars($tax->tax_name) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light-primary fs-6 fw-bold">%<?= number_format($tax->tax_percent, 2, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($tax->is_default == 1): ?>
                                                <div class="badge badge-light-success">Evet</div>
                                            <?php else: ?>
                                                <span class="text-muted fs-7">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($tax->is_active == 1): ?>
                                                <div class="badge badge-light-info">Aktif</div>
                                            <?php else: ?>
                                                <div class="badge badge-light-danger">Pasif</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($tax->created_at)) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" 
                                                onclick="editTax(<?= $tax->tax_id ?>, '<?= htmlspecialchars($tax->tax_name, ENT_QUOTES) ?>', <?= $tax->tax_percent ?>, <?= $tax->is_default ?>, <?= $tax->is_active ?>)">
                                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($taxes)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-10">Henüz bir vergi tanımlanmamış.</td>
                                    </tr>
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

<!-- Modal: Yeni / Düzenle Vergi -->
<div class="modal fade" id="kt_modal_add_tax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form class="form" action="finance/taxes.php" method="post" id="kt_modal_add_tax_form">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_tax">
                <input type="hidden" name="tax_id" id="tax_id" value="0">
                <div class="modal-header" id="kt_modal_add_tax_header">
                    <h2 class="fw-bold" id="modal_tax_title">Vergi Ekle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row mb-7">
                        <div class="col-md-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Vergi Adı</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Örn: KDV %20" name="tax_name" id="tax_name" required />
                        </div>
                        <div class="col-md-4 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Oran (%)</label>
                            <input type="number" step="0.01" min="0" class="form-control form-control-solid" placeholder="20.00" name="tax_percent" id="tax_percent" required />
                        </div>
                    </div>
                    
                    <div class="d-flex flex-stack mb-5">
                        <div class="me-5">
                            <label class="fs-6 fw-semibold">Varsayılan Vergi</label>
                            <div class="fs-7 fw-semibold text-muted">Yeni oluşturulan faturalarda ve kalemlerde otomatik seçili gelsin mi? Sadece 1 vergi varsayılan olabilir.</div>
                        </div>
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1"/>
                            <span class="form-check-label fw-semibold text-muted">Aktif</span>
                        </label>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="d-flex flex-stack mb-5">
                        <div class="me-5">
                            <label class="fs-6 fw-semibold">Sistem Durumu</label>
                            <div class="fs-7 fw-semibold text-muted">Pasife alınırsa fatura keserken seçim listelerinde görünmez.</div>
                        </div>
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked="checked"/>
                            <span class="form-check-label fw-semibold text-muted">Aktif</span>
                        </label>
                    </div>

                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_add_tax_cancel" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" id="kt_modal_add_tax_submit" class="btn btn-primary">
                        <span class="indicator-label">Kaydet</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../config/footer.php'; ?>

<script>
function clearTaxForm() {
    document.getElementById('tax_id').value = 0;
    document.getElementById('tax_name').value = '';
    document.getElementById('tax_percent').value = '';
    document.getElementById('is_default').checked = false;
    document.getElementById('is_active').checked = true;
    document.getElementById('modal_tax_title').innerText = "Yeni Vergi Ekle";
}

function editTax(id, name, percent, isDefault, isActive) {
    document.getElementById('tax_id').value = id;
    document.getElementById('tax_name').value = name;
    document.getElementById('tax_percent').value = percent;
    document.getElementById('is_default').checked = (isDefault == 1);
    document.getElementById('is_active').checked = (isActive == 1);
    document.getElementById('modal_tax_title').innerText = "Vergi Oranını Düzenle";
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_add_tax'));
    modal.show();
}
</script>
