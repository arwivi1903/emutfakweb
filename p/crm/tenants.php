<?php

// Bitiş tarihine göre kalan gün rozeti (tarih bugünse "Bugün bitiyor", geçmişse "N gün geçti")
if (!function_exists('kalan_gun_rozet')) {
    function kalan_gun_rozet(?string $tarih): string
    {
        if (empty($tarih) || $tarih === '0000-00-00') { return ''; }
        $bitis = strtotime($tarih);
        if ($bitis === false) { return ''; }
        $fark = (int)floor(($bitis - strtotime(date('Y-m-d'))) / 86400);
        if ($fark < 0)       { $sinif = 'badge-light-danger';  $metin = abs($fark) . ' gün geçti'; }
        elseif ($fark === 0) { $sinif = 'badge-light-danger';  $metin = 'Bugün bitiyor'; }
        elseif ($fark <= 7)  { $sinif = 'badge-light-danger';  $metin = $fark . ' gün kaldı'; }
        elseif ($fark <= 30) { $sinif = 'badge-light-warning'; $metin = $fark . ' gün kaldı'; }
        else                 { $sinif = 'badge-light-success'; $metin = $fark . ' gün kaldı'; }
        return '<span class="badge ' . $sinif . ' fs-8" style="width:fit-content;" title="Bitiş: ' . date('d.m.Y', $bitis) . '">' . $metin . '</span>';
    }
}
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
        
        // database_name kasıtlı olarak $_POST'tan okunmuyor: kiracının bağlandığı
        // veritabanı adı sadece provisioning akışıyla (tenant_form.php) belirlenir,
        // aksi halde bir yönetici bu alanı değiştirerek kiracıyı başka bir
        // veritabanına (potansiyel olarak başka bir kiracının verisine) yönlendirebilir.
        if ($customer_id > 0) {
            $db->Update("
                UPDATE kurumlar
                SET company_name=?, company_email=?, company_phone=?, industry=?, status=?, trial_end_date=?,
                    musteri_paketi=?, contact_person=?, tax_office=?, tax_number=?, billing_address=?, account_manager_id=?, tenant_alias=?, sozlesme_bitis_tarihi=?
                WHERE customer_id=?
            ", [$company_name, $company_email, $company_phone, $industry, $status, $trial_end_date,
                $musteri_paketi, $contact_person, $tax_office, $tax_number, $billing_address, $account_manager_id, $tenant_alias, $sozlesme_bitis_tarihi, $customer_id]);
        } else {
            $db->Insert("
                INSERT INTO kurumlar
                (company_name, company_email, company_phone, industry, status, trial_end_date,
                 musteri_paketi, contact_person, tax_office, tax_number, billing_address, account_manager_id, tenant_alias, sozlesme_bitis_tarihi)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$company_name, $company_email, $company_phone, $industry, $status, $trial_end_date,
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
                                        <th>Kalan Gün</th>
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
                                            <?php
                                                // DataTables sıralaması için sayısal değer (tarih yoksa en sona)
                                                $kg_t = (!empty($tenant['sozlesme_bitis_tarihi']) && $tenant['sozlesme_bitis_tarihi'] !== '0000-00-00')
                                                    ? $tenant['sozlesme_bitis_tarihi'] : ($tenant['trial_end_date'] ?? null);
                                                $kg_sira = (!empty($kg_t) && strtotime($kg_t) !== false) ? (int)floor((strtotime($kg_t) - strtotime(date('Y-m-d'))) / 86400) : 999999;
                                            ?>
                                            <td data-order="<?php echo $kg_sira; ?>">
                                                <?php
                                                    // Sözleşme bitişi varsa o, yoksa deneme bitişi esas alınır
                                                    $kg_tarih = (!empty($tenant['sozlesme_bitis_tarihi']) && $tenant['sozlesme_bitis_tarihi'] !== '0000-00-00')
                                                        ? $tenant['sozlesme_bitis_tarihi'] : ($tenant['trial_end_date'] ?? null);
                                                    $kg_html = kalan_gun_rozet($kg_tarih);
                                                    echo $kg_html !== '' ? $kg_html : '<span class="text-muted fs-8">-</span>';
                                                ?>
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
                                        <tr><td colspan="7" class="text-center text-muted py-5">Sistemde henüz kayıtlı bir kurum bulunmuyor.</td></tr>
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
<script>
// Kurum tablosu: arama, sıralama, sayfalama (istemci tarafı; kurum sayısı küçük olduğundan yeterli)
document.addEventListener('DOMContentLoaded', function () {
    var tablo = document.getElementById('kt_tenants_table');
    if (!tablo || !window.jQuery || !jQuery.fn.DataTable || tablo.querySelector('tbody td[colspan]')) { return; }
    var dt = jQuery(tablo).DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [],                                   // sunucudan gelen sıra korunur
        columnDefs: [{ orderable: false, targets: -1 }],   // İşlemler sütunu
        dom: "<'table-responsive'tr><'row mt-4'<'col-sm-12 col-md-5 d-flex align-items-center'li><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
        language: {
            emptyTable: 'Kayıt bulunamadı', zeroRecords: 'Eşleşen kurum bulunamadı',
            info: '_TOTAL_ kurumdan _START_–_END_ arası', infoEmpty: 'Kayıt yok', infoFiltered: '(toplam _MAX_ kurum)',
            lengthMenu: '_MENU_ satır', paginate: { previous: '‹', next: '›' }
        }
    });
    var ara = document.querySelector('[data-kt-tenant-table-filter="search"]');
    if (ara) { ara.addEventListener('input', function () { dt.search(this.value).draw(); }); }
});
</script>

