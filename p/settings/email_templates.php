<?php
/**
 * Prolyn Admin Panel - Email Templates Management
 * prolynweb/p/settings/email_templates.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// Yeni Şablon Ekleme veya Güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_template') {
        $template_id = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : 0;
        $template_key = trim($_POST['template_key']);
        $template_name = trim($_POST['template_name']);
        $subject = trim($_POST['subject']);
        $body_html = $_POST['body_html'];
        $body_text = $_POST['body_text'] ?? null;
        $variables = trim($_POST['variables']) ?? null;
        $status = isset($_POST['status']) ? 'active' : 'inactive';

        if ($template_id > 0) {
            $db->Update(
                "UPDATE eposta_sablonlari SET template_name=?, subject=?, body_html=?, body_text=?, variables=?, status=?, updated_at=NOW() WHERE template_id=?",
                [$template_name, $subject, $body_html, $body_text, $variables, $status, $template_id]
            );
            echo "<script>window.location.href='settings/email_templates.php?success=update';</script>";
        } else {
            $db->Insert(
                "INSERT INTO eposta_sablonlari (template_key, template_name, subject, body_html, body_text, variables, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$template_key, $template_name, $subject, $body_html, $body_text, $variables, $status]
            );
            echo "<script>window.location.href='settings/email_templates.php?success=add';</script>";
        }
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['template_id'];
        $db->Delete("DELETE FROM eposta_sablonlari WHERE template_id = ?", [$id]);
        echo "<script>window.location.href='settings/email_templates.php?success=delete';</script>";
        exit;
    }
}

// Şablonları Çek
$query = "SELECT * FROM eposta_sablonlari ORDER BY created_at DESC";
$templates = $db->getRowsAssoc($query);
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        E-posta Şablonları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">E-posta Şablonları</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <button type="button" class="btn btn-sm fw-bold btn-primary" onclick="openTemplateModal()">
                        Yeni Şablon Ekle
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
                                <input type="text" data-kt-template-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Şablon Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_templates_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Şablon Adı / Key</th>
                                        <th>Konu (Subject)</th>
                                        <th>Değişkenler</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($templates)): ?>
                                        <?php foreach ($templates as $t): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($t['template_name']); ?></div>
                                                <div class="text-muted fs-7 font-monospace"><?php echo htmlspecialchars($t['template_key']); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800"><?php echo htmlspecialchars($t['subject']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted fs-8" style="word-break: break-all; max-width: 200px; display: inline-block;"><?php echo htmlspecialchars($t['variables'] ?? '-'); ?></span>
                                            </td>
                                            <td>
                                                <?php if($t['status'] === 'active'): ?>
                                                    <span class="badge badge-light-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">Pasif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <!-- Düzenle Butonu (Verileri JSON olarak data attribute'a ekle) -->
                                                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-1" title="Düzenle" 
                                                    onclick="editTemplate(<?php echo htmlspecialchars(json_encode($t)); ?>)">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                <!-- Silme -->
                                                <form action="settings/email_templates.php" method="POST" class="d-inline" onsubmit="return confirm('Bu şablonu silmek istediğinize emin misiniz?');">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="template_id" value="<?php echo $t['template_id']; ?>">
                                                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Sil">
                                                        <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-5">Kayıtlı e-posta şablonu bulunamadı.</td></tr>
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

<!-- Modal: Şablon Ekle/Düzenle -->
<div class="modal fade" id="kt_modal_template" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_title">Yeni E-posta Şablonu</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form action="settings/email_templates.php" method="POST" id="template_form">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="save_template">
                <input type="hidden" name="template_id" id="inp_template_id" value="0">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    
                    <div class="row g-9 mb-7">
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Şablon Key (Benzersiz)</label>
                            <input type="text" class="form-control form-control-solid" name="template_key" id="inp_template_key" required placeholder="ornek_sablon_1" />
                            <div class="text-muted fs-8 mt-1">Sistem tarafından çağrılırken kullanılır. (Örn: welcome_email)</div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Şablon Adı</label>
                            <input type="text" class="form-control form-control-solid" name="template_name" id="inp_template_name" required placeholder="Hoşgeldin E-postası" />
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">E-posta Konusu (Subject)</label>
                        <input type="text" class="form-control form-control-solid" name="subject" id="inp_subject" required placeholder="Sistemimize Hoşgeldiniz" />
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Kullanılabilir Değişkenler</label>
                        <input type="text" class="form-control form-control-solid" name="variables" id="inp_variables" placeholder="{{user_name}}, {{reset_link}}" />
                        <div class="text-muted fs-8 mt-1">Sadece not amaçlıdır. Virgülle ayırarak yazabilirsiniz.</div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">HTML İçerik (Body HTML)</label>
                        <textarea class="form-control form-control-solid font-monospace" rows="10" name="body_html" id="inp_body_html" required placeholder="<html><body>Merhaba {{user_name}}...</body></html>"></textarea>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fs-6 fw-semibold mb-2">Düz Metin (Body Text - Opsiyonel)</label>
                        <textarea class="form-control form-control-solid" rows="4" name="body_text" id="inp_body_text" placeholder="Merhaba {{user_name}}..."></textarea>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="status" id="inp_status" value="active" checked />
                            <span class="form-check-label fw-semibold text-gray-700">Şablon Aktif (Kullanılabilir)</span>
                        </label>
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
function openTemplateModal() {
    document.getElementById('modal_title').innerText = 'Yeni E-posta Şablonu';
    document.getElementById('inp_template_id').value = '0';
    document.getElementById('inp_template_key').value = '';
    document.getElementById('inp_template_key').readOnly = false;
    document.getElementById('inp_template_name').value = '';
    document.getElementById('inp_subject').value = '';
    document.getElementById('inp_variables').value = '';
    document.getElementById('inp_body_html').value = '';
    document.getElementById('inp_body_text').value = '';
    document.getElementById('inp_status').checked = true;
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_template'));
    modal.show();
}

function editTemplate(data) {
    document.getElementById('modal_title').innerText = 'Şablonu Düzenle';
    document.getElementById('inp_template_id').value = data.template_id;
    document.getElementById('inp_template_key').value = data.template_key;
    document.getElementById('inp_template_key').readOnly = true; // Key değiştirilmesin
    document.getElementById('inp_template_name').value = data.template_name;
    document.getElementById('inp_subject').value = data.subject;
    document.getElementById('inp_variables').value = data.variables;
    document.getElementById('inp_body_html').value = data.body_html;
    document.getElementById('inp_body_text').value = data.body_text;
    document.getElementById('inp_status').checked = (data.status === 'active');
    
    var modal = new bootstrap.Modal(document.getElementById('kt_modal_template'));
    modal.show();
}
</script>

<?php require_once '../config/footer.php'; ?>
