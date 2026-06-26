<?php
/**
 * Prolyn Admin Panel - CRM Feature Usage Tracking
 * prolynweb/p/crm/feature_usage.php
 */

require_once '../config/header.php';
require_once '../config/sidebar.php';

// Yeni Kullanım İstatistiklerini Çek (Canlı DB sorguları ile)
$query = "
    SELECT 
        k.customer_id, 
        k.company_name, 
        k.database_name, 
        k.trial_start_date,
        k.trial_end_date,
        k.sozlesme_baslangic_tarihi,
        k.sozlesme_bitis_tarihi,
        a.start_date as period_start, 
        a.end_date as period_end, 
        po.max_total_users 
    FROM kurumlar k
    LEFT JOIN abonelikler a ON k.customer_id = a.customer_id AND a.status = 'active'
    LEFT JOIN paket_ozellikleri po ON (
        a.plan_name = po.plan_name 
        OR (a.plan_name IS NULL AND po.plan_name = CASE 
            WHEN k.musteri_paketi LIKE '%Başlangıç%' THEN 'basic'
            WHEN k.musteri_paketi LIKE '%Pro%' THEN 'pro'
            WHEN k.musteri_paketi LIKE '%Kurumsal%' THEN 'enterprise'
        END)
    )
    WHERE k.status = 'active' AND k.database_name IS NOT NULL
";
$customers = $db->getRowsAssoc($query);

$usages = [];

if ($customers) {
    foreach ($customers as $customer) {
        $dbName = $customer['database_name'];
        $limit = $customer['max_total_users'] ?? 0;
        $current = 0;
        
        try {
            // Canlı veritabanından kullanıcı sayısını çek
            $countQuery = "SELECT COUNT(*) as cnt FROM `{$dbName}`.users WHERE UserStatus = 1";
            $row = $db->getRowAssoc($countQuery);
            if ($row) {
                $current = (int)$row['cnt'];
            }
        } catch (Exception $e) {
            // Veritabanı yoksa veya hata varsa 0 kabul et
            $current = 0;
        }
        
        // Period Start: abonelik > sözleşme başlangıç > trial başlangıç > bugün
        $p_start = $customer['period_start'] 
            ?? ($customer['sozlesme_baslangic_tarihi'] 
            ?? ($customer['trial_start_date'] 
            ?? date('Y-m-d')));
        
        // Period End: abonelik > sözleşme bitiş > trial bitiş > +1 ay
        $p_end = $customer['period_end'] 
            ?? ($customer['sozlesme_bitis_tarihi'] 
            ?? ($customer['trial_end_date'] 
            ?? date('Y-m-d', strtotime('+1 month'))));
        
        $usages[] = [
            'customer_id' => $customer['customer_id'],
            'company_name' => $customer['company_name'],
            'feature_key' => 'Kullanıcı Sayısı',
            'usage_count' => $current,
            'limit_count' => $limit,
            'period_start' => $p_start,
            'period_end' => $p_end,
            'updated_at' => date('Y-m-d H:i:s')
        ];
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
                        Müşteri Kullanım Limitleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted"><a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">CRM Yönetimi</li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-dark">Müşteri Kullanımları</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">

                <div class="card shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <input type="text" data-kt-usage-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Kurum veya Özellik Ara..." />
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_usages_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Kurum / Müşteri</th>
                                        <th>Özellik (Feature Key)</th>
                                        <th>Kullanım Durumu</th>
                                        <th>Geçerlilik Periyodu</th>
                                        <th>Son Güncelleme</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    <?php if (!empty($usages)): ?>
                                        <?php foreach ($usages as $usage): 
                                            // Progress Bar Hesabı
                                            $limit = $usage['limit_count'];
                                            $current = $usage['usage_count'];
                                            $percent = 0;
                                            
                                            if ($limit > 0) {
                                                $percent = min(100, round(($current / $limit) * 100));
                                            }
                                            
                                            $color = 'success';
                                            if ($percent > 75) $color = 'warning';
                                            if ($percent > 90) $color = 'danger';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="text-gray-800 fw-bold fs-6"><?php echo htmlspecialchars($usage['company_name']); ?></div>
                                                <div class="text-muted fs-8">Müşteri ID: <?php echo $usage['customer_id']; ?></div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary fw-bold text-uppercase"><?php echo htmlspecialchars($usage['feature_key']); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center flex-column mt-3 w-100">
                                                    <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                                        <span class="fw-bold fs-6 text-gray-800"><?php echo number_format($current); ?> / <?php echo $limit > 0 ? number_format($limit) : 'Sınırsız'; ?></span>
                                                        <span class="fw-bold fs-6 text-gray-400"><?php echo $limit > 0 ? $percent.'%' : '∞'; ?></span>
                                                    </div>
                                                    <?php if($limit > 0): ?>
                                                    <div class="h-5px mx-3 w-100 bg-light mb-3">
                                                        <div class="bg-<?php echo $color; ?> rounded h-5px" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo date('d.m.Y', strtotime($usage['period_start'])); ?> - 
                                                <?php echo date('d.m.Y', strtotime($usage['period_end'])); ?>
                                            </td>
                                            <td>
                                                <?php echo date('d.m.Y H:i', strtotime($usage['updated_at'])); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-5">Kayıtlı kullanım verisi bulunamadı.</td></tr>
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

<?php require_once '../config/footer.php'; ?>
