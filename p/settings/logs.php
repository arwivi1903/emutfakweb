<?php
require_once __DIR__ . '/../classes/database.class.php';
require_once __DIR__ . '/../config/header.php';
Auth::requireModule('manage_settings');
require_once __DIR__ . '/../config/sidebar.php';

$db = new Database();

// POST İşlemleri (Arşivleme)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'archive_logs') {
    $days = intval($_POST['archive_days'] ?? 30);
    
    try {
        $countSql = "SELECT COUNT(*) as c FROM yonetici_loglari WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $countResult = $db->getRow($countSql, [$days]);
        $count = $countResult->c ?? 0;

        if ($count > 0) {
            $db->beginTransaction();
            
            // Taşıma sorgusu (INSERT INTO ... SELECT)
            $sqlInsert = "INSERT INTO yonetici_loglari_yedek (log_id, log_type, log_description, log_status, admin_id, ip_address, user_agent, request_method, request_path, old_value, new_value, entity_type, entity_id, additional_data, created_at)
                          SELECT log_id, log_type, log_description, log_status, admin_id, ip_address, user_agent, request_method, request_path, old_value, new_value, entity_type, entity_id, additional_data, created_at 
                          FROM yonetici_loglari 
                          WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $db->Update($sqlInsert, [$days]);
            
            // Silme sorgusu
            $sqlDelete = "DELETE FROM yonetici_loglari WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $db->Delete($sqlDelete, [$days]);
            
            $db->commit();
            $success_msg = "$count adet (>$days günlük) eski log başarıyla arşivlendi.";
        } else {
            $warning_msg = "Arşivlenecek $days günden eski log kaydı bulunamadı.";
        }
        
    } catch(Exception $e) {
        if(isset($count) && $count > 0) {
            $db->rollback();
        }
        $error_msg = "Arşivleme sırasında hata: " . $e->getMessage();
    }
}

// Logları Çek
// Note: Gerçek projelerde sayfalama (pagination) şarttır, şimdilik DESC limit 500 çekiyoruz.
$logs = $db->getRows("
    SELECT l.*, a.full_name, a.username 
    FROM yonetici_loglari l 
    LEFT JOIN yoneticiler a ON l.admin_id = a.admin_id 
    ORDER BY l.created_at DESC 
    LIMIT 500
");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Yönetici Logları</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-gray-900">Aktif Loglar</li>
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

                <?php if(isset($warning_msg)): ?>
                <div class="alert alert-warning d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-4 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-dark">Uyarı</h4>
                        <span class="text-dark"><?= $warning_msg ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Denetim Logları (Son 500 Kayıt)</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Yöneticilerin sistem üzerindeki işlemlerinin dökümü.</span>
                            </h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#kt_modal_archive_logs">
                                <i class="ki-duotone ki-save-2 fs-2"><span class="path1"></span><span class="path2"></span></i>Arşive Taşı
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-150px">Tarih</th>
                                        <th class="min-w-150px">Yönetici</th>
                                        <th class="min-w-100px">İşlem Tipi</th>
                                        <th class="min-w-250px">Açıklama</th>
                                        <th class="min-w-100px">IP Adresi</th>
                                        <th class="min-w-100px text-end">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i:s', strtotime($log->created_at)) ?></td>
                                        <td>
                                            <?php if($log->full_name): ?>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold"><?= htmlspecialchars($log->full_name) ?></span>
                                                    <span class="text-muted fs-8"><?= htmlspecialchars($log->username) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Sistem / Silinmiş (ID: <?= $log->admin_id ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $descText = (string)($log->log_description ?? '');
                                                $actionFromDesc = '';
                                                if (preg_match('/^(INSERT|UPDATE|DELETE|CREATE|LOGIN|LOGOUT|ERROR|WARNING|INFO|DEBUG)\b/i', $descText, $m)) {
                                                    $actionFromDesc = strtoupper($m[1]);
                                                }

                                                $actionLabel = '';
                                                $actionBadge = 'badge-light';

                                                if ($actionFromDesc !== '') {
                                                    $actionLabel = $actionFromDesc;
                                                    $actionBadge = match ($actionFromDesc) {
                                                        'DELETE' => 'badge-light-danger',
                                                        'UPDATE' => 'badge-light-info',
                                                        'INSERT', 'CREATE' => 'badge-light-primary',
                                                        'ERROR' => 'badge-light-danger',
                                                        'WARNING' => 'badge-light-warning',
                                                        'LOGIN', 'LOGOUT' => 'badge-light-success',
                                                        'DEBUG' => 'badge-light-dark',
                                                        default => 'badge-light-success',
                                                    };
                                                } else {
                                                    $actionLabel = match ((int)$log->log_type) {
                                                        1 => 'Hata',
                                                        2 => 'Uyarı',
                                                        3 => 'İşlem',
                                                        4 => 'Bilgi',
                                                        5 => 'Debug',
                                                        default => 'Diğer',
                                                    };
                                                    $actionBadge = match ((int)$log->log_type) {
                                                        1 => 'badge-light-danger',
                                                        2 => 'badge-light-warning',
                                                        3 => 'badge-light-success',
                                                        4 => 'badge-light-info',
                                                        5 => 'badge-light-dark',
                                                        default => 'badge-light',
                                                    };
                                                }
                                            ?>
                                            <span class="badge <?= $actionBadge ?>"><?= htmlspecialchars($actionLabel) ?></span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($log->log_description) ?>
                                            <?php if(!empty($log->old_value) || !empty($log->new_value)): ?>
                                                <br>
                                                <button type="button" class="btn btn-sm btn-light-info mt-2 py-1 px-3 view-log-details"
                                                    data-old="<?= htmlspecialchars($log->old_value ?? '') ?>"
                                                    data-new="<?= htmlspecialchars($log->new_value ?? '') ?>">
                                                    Detayları Gör
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($log->ip_address) ?></td>
                                        <td class="text-end">
                                            <?php if ($log->log_status == 'success'): ?>
                                                <div class="badge badge-success">Başarılı</div>
                                            <?php elseif ($log->log_status == 'error' || $log->log_status == 'failed'): ?>
                                                <div class="badge badge-danger">Hata</div>
                                            <?php elseif ($log->log_status == 'warning'): ?>
                                                <div class="badge badge-warning">Uyarı</div>
                                            <?php else: ?>
                                                <div class="badge badge-info">Bilgi</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-10">Hiç log kaydı bulunamadı.</td>
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

<!-- Modal: Arşivle -->
<div class="modal fade" id="kt_modal_archive_logs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form class="form" action="settings/logs.php" method="post">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="archive_logs">
                <div class="modal-header">
                    <h2 class="fw-bold">Logları Arşivle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6 mb-8">
                        <i class="ki-duotone ki-information fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Dikkat!</h4>
                                <div class="fs-6 text-gray-700">Seçtiğiniz süreden daha eski olan tüm loglar, aktif tablodan silinip <code class="text-gray-900">yonetici_loglari_yedek</code> tablosuna taşınacaktır. Bu işlem veri tabanını rahatlatır. Geri dönmek için yedekleme tablosuna bakabilirsiniz.</div>
                            </div>
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Kaç Günden Eski Loglar Arşivlensin?</label>
                        <select name="archive_days" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                            <option value="30">30 Gün</option>
                            <option value="60">60 Gün</option>
                            <option value="90">90 Gün</option>
                            <option value="180">6 Ay</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="indicator-label">Şimdi Arşivle</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Log Detayları -->
<div class="modal fade" id="kt_modal_log_details" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">İşlem Detayları</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-10 px-lg-17">
                <div id="log_details_body" class="fs-6 text-gray-700">
                    <!-- JS ile doldurulacak -->
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../config/footer.php'; ?>

<script>
document.addEventListener('click', function(ev) {
    const btn = ev.target.closest('.view-log-details');
    if (!btn) return;
    (function() {
        const oldValStr = this.getAttribute('data-old');
        const newValStr = this.getAttribute('data-new');
        
        let html = '';
        
        try {
            if (oldValStr && oldValStr.trim() !== '') {
                const oldObj = JSON.parse(oldValStr);
                html += '<h5 class="mb-3 text-danger">Eski Değerler</h5>';
                html += '<div class="bg-light-danger p-4 rounded mb-6"><pre class="mb-0" style="white-space: pre-wrap;">' + JSON.stringify(oldObj, null, 2) + '</pre></div>';
            }
        } catch(e) {
            if (oldValStr) {
                html += '<h5 class="mb-3 text-danger">Eski Değerler</h5>';
                html += '<div class="bg-light-danger p-4 rounded mb-6"><pre class="mb-0" style="white-space: pre-wrap;">' + oldValStr + '</pre></div>';
            }
        }

        try {
            if (newValStr && newValStr.trim() !== '') {
                const newObj = JSON.parse(newValStr);
                html += '<h5 class="mb-3 text-success">Yeni Değerler</h5>';
                html += '<div class="bg-light-success p-4 rounded"><pre class="mb-0" style="white-space: pre-wrap;">' + JSON.stringify(newObj, null, 2) + '</pre></div>';
            }
        } catch(e) {
            if (newValStr) {
                html += '<h5 class="mb-3 text-success">Yeni Değerler</h5>';
                html += '<div class="bg-light-success p-4 rounded"><pre class="mb-0" style="white-space: pre-wrap;">' + newValStr + '</pre></div>';
            }
        }
        
        if (html === '') {
            html = '<p class="text-muted">Detay bulunamadı.</p>';
        }
        
        document.getElementById('log_details_body').innerHTML = html;
        new bootstrap.Modal(document.getElementById('kt_modal_log_details')).show();
    }).call(btn);
});

document.addEventListener('DOMContentLoaded', function () {
    <?php if (!empty($logs)): ?>
    if (typeof window.initDataTable === 'function') {
        window.initDataTable('#kt_logs_table', {
            order: [[0, 'desc']]
        });
    }
    <?php endif; ?>
});
</script>
