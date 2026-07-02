<?php
/**
 * Prolyn Admin Panel - General System Settings
 * Prolyn/p/settings/general.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_setting') {
    $setting_id = (int)$_POST['setting_id'];
    $setting_value = $_POST['setting_value'];
    
    $db->Update("UPDATE sistem_ayarlari SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_id = ?", 
        [$setting_value, $_SESSION['user_id'], $setting_id]);
        
    echo "<script>window.location.href='settings/general.php?success=1';</script>";
    exit;
}

$query = "SELECT * FROM sistem_ayarlari ORDER BY category ASC, setting_key ASC";
$settings = $db->getRowsAssoc($query);

// Group by category
$grouped_settings = [];
if($settings) {
    foreach($settings as $s) {
        $grouped_settings[$s['category']][] = $s;
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Genel Sistem Ayarları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Yönetim Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Genel Ayarlar</li>
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
                        <span>Sistem ayarı başarıyla güncellendi.</span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($grouped_settings)): ?>
                    <?php foreach ($grouped_settings as $cat => $items): ?>
                    <div class="card shadow-sm mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title text-uppercase fw-bold text-primary">Kategori: <?php echo htmlspecialchars($cat); ?></h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-200px">Ayar (Key)</th>
                                            <th class="min-w-300px">Değer (Value)</th>
                                            <th>Tür / Genel mi?</th>
                                            <th class="text-end">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold fs-6 mb-1"><?php echo htmlspecialchars($item['setting_key']); ?></div>
                                                <div class="text-muted fs-8"><?php echo htmlspecialchars($item['description'] ?? ''); ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $val = $item['setting_value'];
                                                    if (strlen($val) > 80) $val = substr($val, 0, 80) . '...';
                                                    echo htmlspecialchars($val); 
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info"><?php echo $item['setting_type']; ?></span>
                                                <?php if($item['is_public']): ?>
                                                    <span class="badge badge-light-success">Public</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-icon btn-light-primary" title="Düzenle" onclick='editSetting(<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card shadow-sm"><div class="card-body text-center text-muted">Kayıtlı sistem ayarı bulunamadı.</div></div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Düzenle -->
<div class="modal fade" id="kt_modal_edit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Ayar Düzenle</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="settings/general.php" method="POST">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_setting">
                <input type="hidden" name="setting_id" id="inp_setting_id">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Ayar (Key)</label>
                        <input type="text" class="form-control form-control-solid" id="inp_setting_key" readonly />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Değer</label>
                        <textarea class="form-control form-control-solid" name="setting_value" id="inp_setting_value" rows="4" required></textarea>
                    </div>
                    <div class="text-muted fs-8" id="inp_description"></div>
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
function editSetting(setting) {
    document.getElementById('inp_setting_id').value = setting.setting_id;
    document.getElementById('inp_setting_key').value = setting.setting_key;
    document.getElementById('inp_setting_value').value = setting.setting_value;
    document.getElementById('inp_description').innerText = setting.description || '';
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_edit'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
