<?php
/**
 * Prolyn Admin Panel - API Keys Management
 * Prolyn/p/settings/api_keys.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

// POST İşlemleri (Ekleme / İptal Etme)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // API Key Ekleme
    if ($action === 'create_api_key' && !empty($_POST['customer_id']) && !empty($_POST['name'])) {
        $customer_id = (int)$_POST['customer_id'];
        $name = $_POST['name'];
        $rate_limit = isset($_POST['rate_limit']) ? (int)$_POST['rate_limit'] : 1000;
        
        // Rastgele key ve secret oluştur (Gerçek projede daha güvenli kütüphaneler kullanılabilir)
        $api_key = 'pk_' . bin2hex(random_bytes(16));
        $api_secret = 'sk_' . bin2hex(random_bytes(32));
        
        $db->Insert("INSERT INTO api_anahtarlari (customer_id, api_key, api_secret, name, rate_limit, status) VALUES (?, ?, ?, ?, ?, 'active')", [
            $customer_id, $api_key, $api_secret, $name, $rate_limit
        ]);
        
        echo "<script>window.location.href='settings/api_keys.php?success=created';</script>";
        exit;
    }
    
    // API Key İptal Etme (Revoke)
    if ($action === 'revoke_api_key' && isset($_POST['api_key_id'])) {
        $api_key_id = (int)$_POST['api_key_id'];
        $db->Update("UPDATE api_anahtarlari SET status = 'revoked' WHERE api_key_id = ?", [$api_key_id]);
        
        echo "<script>window.location.href='settings/api_keys.php?success=revoked';</script>";
        exit;
    }
}

// Tüm müşterileri (kurumları) çek (Yeni Ekle formundaki select için)
$customers = $db->getRowsAssoc("SELECT id, company_name FROM kurumlar ORDER BY company_name ASC");

// API Anahtarlarını çek
$query = "
    SELECT a.*, k.company_name 
    FROM api_anahtarlari a
    LEFT JOIN kurumlar k ON a.customer_id = k.id
    ORDER BY a.api_key_id DESC
";
$api_keys = $db->getRowsAssoc($query);

// Güvenli gösterim için API Key maskeleme fonksiyonu
function maskApiKey($key) {
    if (strlen($key) > 8) {
        return substr($key, 0, 8) . str_repeat('*', 20);
    }
    return $key;
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        API Anahtarları
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Sistem Ayarları</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">API Anahtarları</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">İşlem Başarılı</h4>
                        <span>
                            <?php 
                                if($_GET['success'] == 'created') echo 'Yeni API Anahtarı başarıyla oluşturuldu.';
                                elseif($_GET['success'] == 'revoked') echo 'API Anahtarı başarıyla iptal edildi (revoked).';
                            ?>
                        </span>
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
                                <input type="text" data-kt-api-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="API Anahtarı Ara..." />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_api_key">
                                <i class="ki-duotone ki-plus fs-2"></i> Yeni API Anahtarı
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_api_keys_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Anahtar Adı / Müşteri</th>
                                        <th>API Key (Maskeli)</th>
                                        <th>Kullanım Durumu</th>
                                        <th>Oluşturulma / Son Kullanım</th>
                                        <th>Durum</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($api_keys)): ?>
                                        <?php foreach ($api_keys as $key): ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold"><?php echo htmlspecialchars($key['name'] ?? 'İsimsiz Anahtar'); ?></div>
                                                <div class="text-muted fs-7"><?php echo htmlspecialchars($key['company_name'] ?? 'Bilinmeyen Müşteri'); ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-gray-600 me-2" style="font-family: monospace;"><?php echo maskApiKey($key['api_key']); ?></span>
                                                    <!-- Sadece görsel kopyalama butonu (gerçek keyi kopyalamaz, maskeli kopyalar güvenlik gereği) -->
                                                    <button class="btn btn-icon btn-sm btn-light" data-clipboard-text="<?php echo maskApiKey($key['api_key']); ?>" title="Kopyala">
                                                        <i class="ki-duotone ki-copy fs-4"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column w-100 me-2">
                                                    <div class="d-flex flex-stack mb-2">
                                                        <span class="text-muted me-2 fs-7 fw-bold"><?php echo number_format($key['requests_used']); ?> / <?php echo number_format($key['rate_limit']); ?> req</span>
                                                    </div>
                                                    <div class="progress h-6px w-100">
                                                        <?php 
                                                            $percent = ($key['rate_limit'] > 0) ? ($key['requests_used'] / $key['rate_limit']) * 100 : 0;
                                                            $bg_class = 'bg-success';
                                                            if($percent > 80) $bg_class = 'bg-danger';
                                                            elseif($percent > 50) $bg_class = 'bg-warning';
                                                        ?>
                                                        <div class="progress-bar <?php echo $bg_class; ?>" role="progressbar" style="width: <?php echo min(100, $percent); ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-gray-800 fs-7">Oluş: <?php echo date('d.m.Y', strtotime($key['created_at'])); ?></div>
                                                <div class="text-muted fs-8">Son: <?php echo $key['last_used_at'] ? date('d.m.Y H:i', strtotime($key['last_used_at'])) : 'Hiç kullanılmadı'; ?></div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $status_class = 'badge-light-info';
                                                    if ($key['status'] == 'active') $status_class = 'badge-light-success';
                                                    elseif ($key['status'] == 'revoked') $status_class = 'badge-light-danger';
                                                    elseif ($key['status'] == 'expired') $status_class = 'badge-light-warning';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> text-uppercase"><?php echo htmlspecialchars($key['status']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($key['status'] === 'active'): ?>
                                                <form action="settings/api_keys.php" method="POST" style="display:inline-block;" onsubmit="event.preventDefault(); Swal.fire({title: 'Emin misiniz?', text: 'Bu API anahtarını iptal etmek istediğinize emin misiniz? Bu işlem geri alınamaz ve API çağrıları başarısız olacaktır.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet', cancelButtonText: 'Hayır'}).then((result) => { if(result.isConfirmed) { this.submit(); } });">
                            <?= csrfTokenField() ?>
                                                    <input type="hidden" name="action" value="revoke_api_key">
                                                    <input type="hidden" name="api_key_id" value="<?php echo $key['api_key_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-light-danger">İptal Et</button>
                                                </form>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Henüz oluşturulmuş bir API anahtarı bulunmuyor.</td></tr>
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

<!-- Modal: Yeni API Anahtarı Ekle -->
<div class="modal fade" id="kt_modal_add_api_key" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form class="form" action="settings/api_keys.php" method="POST" id="kt_modal_add_api_key_form">
                            <?= csrfTokenField() ?>
                <input type="hidden" name="action" value="create_api_key">
                <div class="modal-header" id="kt_modal_add_api_key_header">
                    <h2 class="fw-bold">Yeni API Anahtarı Oluştur</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                
                <div class="modal-body py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7" id="kt_modal_add_api_key_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_api_key_header" data-kt-scroll-wrappers="#kt_modal_add_api_key_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Anahtar Adı</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Örn: ERP Entegrasyonu" name="name" required />
                            <div class="text-muted fs-7 mt-2">Bu anahtarın hangi uygulama veya amaçla kullanılacağını belirtin.</div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Müşteri / Kurum</label>
                            <select name="customer_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Müşteri Seçin" required>
                                <option></option>
                                <?php foreach($customers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['company_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Aylık İstek Limiti (Rate Limit)</label>
                            <input type="number" class="form-control form-control-solid" value="1000" name="rate_limit" required />
                        </div>

                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                            <i class="ki-duotone ki-information fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Önemli Not</h4>
                                    <div class="fs-6 text-gray-700">API Anahtarı oluşturulduktan sonra ekranda sadece bir kez gösterilecek veya maskeli olarak listelenecektir. Güvenlik gereği Secret Key arka planda oluşturulup müşteriye iletilmelidir.</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_add_api_key_cancel" class="btn btn-light me-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" id="kt_modal_add_api_key_submit" class="btn btn-primary">
                        <span class="indicator-label">Oluştur</span>
                        <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../config/footer.php'; ?>
