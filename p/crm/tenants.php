<?php
/**
 * Prolyn Admin Panel - Tenants Management
 * Prolyn/p/crm/tenants.php
 */

require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once '../config/sidebar.php';

// Kurum Ekle / Düzenle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_tenant') {
        $customer_id    = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
        $company_name   = trim($_POST['company_name']);
        $company_email  = trim($_POST['company_email']);
        $company_phone  = trim($_POST['company_phone']);
        $database_name  = trim($_POST['database_name']);
        $industry       = trim($_POST['industry']);
        $status         = $_POST['status'] ?? 'active';
        $trial_end_date = !empty($_POST['trial_end_date']) ? date('Y-m-d', strtotime($_POST['trial_end_date'])) : null;
        
        $musteri_paketi = trim($_POST['musteri_paketi'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $tax_office = trim($_POST['tax_office'] ?? '');
        $tax_number = trim($_POST['tax_number'] ?? '');
        $billing_address = trim($_POST['billing_address'] ?? '');
        $account_manager_id = !empty($_POST['account_manager_id']) ? (int)$_POST['account_manager_id'] : null;
        $tenant_alias = trim($_POST['tenant_alias'] ?? '');
        $sozlesme_bitis_tarihi = !empty($_POST['sozlesme_bitis_tarihi']) ? date('Y-m-d', strtotime($_POST['sozlesme_bitis_tarihi'])) : null;
        
        if ($customer_id > 0) {
            $db->Update("
                UPDATE kurumlar 
                SET company_name=?, company_email=?, company_phone=?, database_name=?, industry=?, status=?, trial_end_date=?,
                    musteri_paketi=?, contact_person=?, tax_office=?, tax_number=?, billing_address=?, account_manager_id=?, tenant_alias=?, sozlesme_bitis_tarihi=?
                WHERE customer_id=?
            ", [$company_name, $company_email, $company_phone, $database_name, $industry, $status, $trial_end_date, 
                $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $account_manager_id, $tenant_alias, $sozlesme_bitis_tarihi, $customer_id]);
        } else {
            $db->Insert("
                INSERT INTO kurumlar 
                (company_name, company_email, company_phone, database_name, industry, status, trial_end_date,
                 musteri_paketi, contact_person, tax_office, tax_number, billing_address, account_manager_id, tenant_alias, sozlesme_bitis_tarihi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$company_name, $company_email, $company_phone, $database_name, $industry, $status, $trial_end_date,
                $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $account_manager_id, $tenant_alias, $sozlesme_bitis_tarihi]);
        }
        echo "<script>window.location.href='crm/tenants.php?success=saved';</script>";
        exit;
    }
}

// Kurum Listesini Çek
$tenants = $db->getRowsAssoc("SELECT k.*, y.full_name as account_manager_name 
    FROM kurumlar k 
    LEFT JOIN yoneticiler y ON k.account_manager_id = y.admin_id 
    WHERE k.deleted_at IS NULL
    ORDER BY k.customer_id DESC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Müşteri / Kurum Yönetimi
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">CRM Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Müşteri Listesi</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="crm/tenant_form.php" class="btn btn-sm fw-bold btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Yeni Kurum Ekle
                    </a>
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
                        <span>Kurum bilgileri başarıyla kaydedildi.</span>
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
                                <input type="text" data-kt-tenant-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Kurum Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_tenants_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Kurum / Firma Adı</th>
                                        <th>Yetkili & İletişim</th>
                                        <th>Müşteri Paketi & DB</th>
                                        <th>Durum</th>
                                        <th>Sözleşme Bitiş & Kayıt</th>
                                        <th class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($tenants)): ?>
                                        <?php foreach ($tenants as $tenant): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3">
                                                        <?php if($tenant['logo_url']): ?>
                                                            <img src="<?php echo htmlspecialchars($tenant['logo_url']); ?>" alt="" />
                                                        <?php else: ?>
                                                            <span class="symbol-label bg-light-primary text-primary fw-bold"><?php echo mb_substr($tenant['company_name'], 0, 1); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <a href="crm/tenant_detail.php?id=<?php echo $tenant['customer_id']; ?>" class="text-gray-800 text-hover-primary fw-bold fs-5 mb-1"><?php echo htmlspecialchars($tenant['company_name']); ?></a>
                                                        <span class="text-muted fs-8">ID: <?php echo $tenant['customer_id']; ?> | Sektör: <?php echo htmlspecialchars($tenant['industry'] ?? '-'); ?></span>
                                                        <?php if($tenant['tax_office'] || $tenant['tax_number']): ?>
                                                            <span class="text-muted fs-8">Vergi: <?php echo htmlspecialchars($tenant['tax_office'] . ' / ' . $tenant['tax_number']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold"><?php echo htmlspecialchars($tenant['contact_person'] ?: 'Yetkili Belirtilmedi'); ?></span>
                                                    <span class="text-muted fs-8"><i class="ki-duotone ki-sms fs-6 me-1"><span class="path1"></span><span class="path2"></span></i><?php echo htmlspecialchars($tenant['company_email']); ?></span>
                                                    <span class="text-muted fs-8"><i class="ki-duotone ki-phone fs-6 me-1"><span class="path1"></span><span class="path2"></span></i><?php echo htmlspecialchars($tenant['company_phone'] ?? '-'); ?></span>
                                                    <?php if($tenant['account_manager_name']): ?>
                                                        <span class="text-primary fs-8 mt-1"><i class="ki-duotone ki-user fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>MT: <?php echo htmlspecialchars($tenant['account_manager_name']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <?php if($tenant['musteri_paketi']): ?>
                                                        <span class="text-primary fs-8 fw-bold mb-1">Paket: <?php echo htmlspecialchars($tenant['musteri_paketi']); ?></span>
                                                    <?php elseif(!empty($tenant['trial_end_date']) && (empty($tenant['sozlesme_bitis_tarihi']) || $tenant['sozlesme_bitis_tarihi'] === '0000-00-00')): ?>
                                                        <span class="text-info fs-8 fw-bold mb-1">Paket: Deneme Kullanıcısı</span>
                                                    <?php else: ?>
                                                        <span class="text-muted fs-8 mb-1">Paket: Belirtilmedi</span>
                                                    <?php endif; ?>
                                                    <span class="badge badge-light-info mb-1" style="width:fit-content;">DB: <?php echo htmlspecialchars($tenant['database_name'] ?? 'Bilinmiyor'); ?></span>
                                                    <?php if($tenant['tenant_alias']): ?>
                                                        <span class="text-muted fs-8">Alias: <?php echo htmlspecialchars($tenant['tenant_alias']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $is_trial_expired = !empty($tenant['trial_end_date']) && (empty($tenant['sozlesme_bitis_tarihi']) || $tenant['sozlesme_bitis_tarihi'] === '0000-00-00') && strtotime($tenant['trial_end_date'] . ' 23:59:59') < time();
                                                    $display_status = htmlspecialchars($tenant['status']);
                                                    
                                                    if ($is_trial_expired) {
                                                        $badge = 'badge-light-danger';
                                                        $display_status = 'SÜRESİ DOLMUŞ';
                                                    } elseif ($tenant['status'] === 'active') {
                                                        $badge = 'badge-light-success';
                                                    } elseif ($tenant['status'] === 'suspended') {
                                                        $badge = 'badge-light-danger';
                                                    } else {
                                                        $badge = 'badge-light-warning';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge; ?> text-uppercase"><?php echo $display_status; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <?php if(!empty($tenant['sozlesme_bitis_tarihi']) && $tenant['sozlesme_bitis_tarihi'] !== '0000-00-00'): ?>
                                                        <?php $is_lic_expired = strtotime($tenant['sozlesme_bitis_tarihi'] . ' 23:59:59') < time(); ?>
                                                        <span class="<?php echo $is_lic_expired ? 'text-danger fw-bold' : 'text-success fw-bold'; ?> fs-8 mb-1" title="Sözleşme Bitiş Tarihi">Sözleşme Bitiş: <?php echo date('d.m.Y', strtotime($tenant['sozlesme_bitis_tarihi'])); ?></span>
                                                    <?php elseif(!empty($tenant['trial_end_date'])): ?>
                                                        <span class="text-info fw-bold fs-8 mb-1">Sözleşme: Deneme Kullanıcısı</span>
                                                    <?php else: ?>
                                                        <span class="text-muted fs-8 mb-1">Sözleşme: Yok</span>
                                                    <?php endif; ?>
                                                    <span class="text-gray-800 fs-8" title="Kayıt Tarihi">Kayıt: <?php echo date('d.m.Y', strtotime($tenant['created_at'])); ?></span>
                                                    <?php if(!empty($tenant['trial_end_date']) && (empty($tenant['sozlesme_bitis_tarihi']) || $tenant['sozlesme_bitis_tarihi'] === '0000-00-00')): ?>
                                                        <?php $is_expired = strtotime($tenant['trial_end_date'] . ' 23:59:59') < time(); ?>
                                                        <span class="<?php echo $is_expired ? 'text-danger' : 'text-warning'; ?> fs-8" title="Deneme Bitiş">Deneme Bitiş: <?php echo date('d.m.Y', strtotime($tenant['trial_end_date'])); ?></span>
                                                        <?php if($is_expired): ?>
                                                            <span class="badge badge-light-danger fs-9 mt-1">Giriş Engellendi</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <a href="crm/tenant_form.php?id=<?php echo $tenant['customer_id']; ?>" class="btn btn-icon btn-light-primary btn-sm me-1" title="Düzenle">
                                                    <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                                <a href="crm/tenant_detail.php?id=<?php echo $tenant['customer_id']; ?>" class="btn btn-icon btn-light-info btn-sm" title="Detaylar & Notlar">
                                                    <i class="ki-duotone ki-arrow-right fs-3"><span class="path1"></span><span class="path2"></span></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-5">Sistemde henüz kayıtlı bir kurum bulunmuyor.</td></tr>
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

<!-- Modal removed, now using tenant_form.php -->

<?php require_once '../config/footer.php'; ?>
