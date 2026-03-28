<?php
require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
				<!-- Başlık ve Bilgi -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="alert alert-primary d-flex align-items-center p-5">
                            <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-dark">prolyn Multi-Tenant Sistem</h4>
                                <span>Sistem yapılandırması ve veritabanı testleri</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                try {
                    // Master veritabanına bağlan
                    $db_master = new Database('prolyn_master');
                    
                    $tenantDbs = $db_master->allAssoc("
                        SELECT 
                            customer_id as id, 
                            database_name as db_name, 
                            database_password as db_password,
                            company_name, 
                            status 
                        FROM customers
                    ");
                    
                    // Master DB İstatistikleri
                    $masterTables = $db_master->allAssoc("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'prolyn_master'");
                    $packages = $db_master->allAssoc("SELECT plan_name, max_admin_users, max_total_users FROM package_features");
                    
                    // Reference schema (prolyn_master ana şablon veritabanı)
                    $referenceSchema = [];
                    $refTablesResult = $db_master->allAssoc("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'prolyn_default'");
                    if ($refTablesResult && is_array($refTablesResult)) {
                        foreach ($refTablesResult as $row) {
                            $referenceSchema[] = $row['TABLE_NAME'];
                        }
                    }
                    
                    // Tüm tenant'lar için özet veriler
                    $tenantSummaries = [];
                    $totalCustomers = 0;
                    $totalContracts = 0;
                    $totalPayments = 0;
                    $healthyTenants = 0;
                    $problematicTenants = 0;
                    
                    foreach ($tenantDbs as $tenant) {
                        try {
                            $db = new Database($tenant['db_name']);
                            $tablesResult = $db->allAssoc("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$tenant['db_name']}'");
                            $tables = [];
                            if ($tablesResult && is_array($tablesResult)) {
                                foreach ($tablesResult as $row) {
                                    $tables[] = $row['TABLE_NAME'];
                                }
                            }
                            
                            $dataStats = [];
                            
                            // Müşteri tabloları
                            $customerTables = ['musteriler', 'customers', 'musteri'];
                            foreach ($customerTables as $tableName) {
                                if (in_array($tableName, $tables)) {
                                    try {
                                        $result = $db->rowAssoc("SELECT COUNT(*) as cnt FROM `$tableName`");
                                        $dataStats['customers'] = ($dataStats['customers'] ?? 0) + ($result['cnt'] ?? 0);
                                    } catch (Exception $e) {}
                                    break;
                                }
                            }
                            
                            // Sözleşme tabloları
                            $contractTables = ['sozlesmeler', 'contracts', 'sozlesme'];
                            foreach ($contractTables as $tableName) {
                                if (in_array($tableName, $tables)) {
                                    try {
                                        $result = $db->rowAssoc("SELECT COUNT(*) as cnt FROM `$tableName`");
                                        $dataStats['contracts'] = ($dataStats['contracts'] ?? 0) + ($result['cnt'] ?? 0);
                                    } catch (Exception $e) {}
                                    break;
                                }
                            }
                            
                            // Ödeme tabloları
                            $paymentTables = ['tahsilat', 'payments', 'odemeler'];
                            foreach ($paymentTables as $tableName) {
                                if (in_array($tableName, $tables)) {
                                    try {
                                        $result = $db->rowAssoc("SELECT COUNT(*) as cnt FROM `$tableName`");
                                        $dataStats['payments'] = ($dataStats['payments'] ?? 0) + ($result['cnt'] ?? 0);
                                    } catch (Exception $e) {}
                                    break;
                                }
                            }
                            
                            $customerCount = $dataStats['customers'] ?? 0;
                            $contractCount = $dataStats['contracts'] ?? 0;
                            
                            if (!is_array($referenceSchema)) $referenceSchema = (array)$referenceSchema;
                            
                            $missing = array_values(array_diff($referenceSchema, $tables));
                            $extra = array_values(array_diff($tables, $referenceSchema));
                            $isHealthy = (count($missing) == 0 && count($extra) == 0);
                            
                            if ($isHealthy) {
                                $healthyTenants++;
                            } else {
                                $problematicTenants++;
                            }
                            
                            $totalCustomers += $customerCount;
                            $totalContracts += $contractCount;
                            $totalPayments += ($dataStats['payments'] ?? 0);
                            
                            $tenantSummaries[] = [
                                'id' => $tenant['id'],
                                'db_name' => $tenant['db_name'],
                                'db_password' => $tenant['db_password'] ?? '-',
                                'company_name' => $tenant['company_name'],
                                'status' => $tenant['status'],
                                'customers' => $customerCount,
                                'contracts' => $contractCount,
                                'table_count' => count($tables),
                                'missing' => $missing,
                                'extra' => $extra,
                                'is_healthy' => $isHealthy,
                                'error' => null
                            ];
                        } catch (Exception $e) {
                            $tenantSummaries[] = [
                                'id' => $tenant['id'],
                                'db_name' => $tenant['db_name'],
                                'db_password' => $tenant['db_password'] ?? '-',
                                'company_name' => $tenant['company_name'],
                                'status' => $tenant['status'],
                                'error' => $e->getMessage(),
                                'is_healthy' => false,
                                'missing' => [],
                                'extra' => [],
                                'customers' => 0,
                                'contracts' => 0,
                                'table_count' => 0
                            ];
                            $problematicTenants++;
                        }
                    }
                ?>

                <!-- İstatistik Kartları -->
                <div class="row g-6 g-xl-9 mb-6">
                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">Toplam</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?= count($tenantDbs) ?></span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Tenant Sayısı</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center card-p pt-3">
                                    <div class="symbol symbol-45px me-3">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">Sağlıklı</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2"><?= $healthyTenants ?></span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Sorunsuz Tenant</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center card-p pt-3">
                                    <div class="symbol symbol-45px me-3">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-duotone ki-check-circle fs-2x text-success">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">Sorunlu</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2"><?= $problematicTenants ?></span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Dikkat Gerekiyor</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center card-p pt-3">
                                    <div class="symbol symbol-45px me-3">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-information fs-2x text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column p-0">
                                <div class="flex-grow-1 card-p pb-0">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">Toplam</span>
                                        </div>
                                        <span class="fs-2hx fw-bold text-info me-2 lh-1 ls-n2"><?= number_format($totalCustomers) ?></span>
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Müşteri Kaydı</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center card-p pt-3">
                                    <div class="symbol symbol-45px me-3">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-duotone ki-profile-user fs-2x text-info">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Master Database -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-duotone ki-cloud fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Prolyn Master Database
                                    </span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">prloyn_master</span>
                                </h3>
                            </div>
                            <div class="card-body py-4">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-8 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="symbol symbol-50px me-3">
                                                    <span class="symbol-label bg-light-info">
                                                        <i class="ki-duotone ki-chart fs-2x text-info">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                                <div class="text-start">
                                                    <div class="fs-2hx fw-bold text-gray-800"><?= count($masterTables) ?></div>
                                                    <div class="fs-7 fw-semibold text-muted">Tablolar</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-8 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="symbol symbol-50px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="ki-duotone ki-package fs-2x text-primary">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                                <div class="text-start">
                                                    <div class="fs-2hx fw-bold text-gray-800"><?= count($packages) ?></div>
                                                    <div class="fs-7 fw-semibold text-muted">Paket Planları</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-8 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="symbol symbol-50px me-3">
                                                    <span class="symbol-label bg-light-warning">
                                                        <i class="ki-duotone ki-abstract-26 fs-2x text-warning">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                                <div class="text-start">
                                                    <div class="fs-2hx fw-bold text-gray-800"><?= count($tenantDbs) ?></div>
                                                    <div class="fs-7 fw-semibold text-muted">Kayıtlı Tenant</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Veritabanları Tablosu -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-duotone ki-folder fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Tenant Veritabanları
                                    </span>
                                </h3>
                            </div>
                            <div class="card-body py-4">
                                <div class="table-responsive">
                                    <table id="kt_tenant_table" class="table align-middle table-row-dashed fs-6 gy-5">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-50px">ID</th>
                                                <th class="min-w-150px">Şirket Adı</th>
                                                <th class="min-w-125px">DB Adı</th>
                                                <th class="min-w-125px">DB Şifre</th>
                                                <th class="min-w-75px text-center">Müşteri</th>
                                                <th class="min-w-75px text-center">Sözleşme</th>
                                                <th class="min-w-75px text-center">Tablo</th>
                                                <th class="min-w-100px text-center">Durum</th>
                                                <th class="min-w-100px text-center">İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            <?php foreach ($tenantSummaries as $summary): ?>
                                            <tr class="<?= !$summary['is_healthy'] ? 'bg-light-warning' : '' ?>">
                                                <td><?= $summary['id'] ?></td>
                                                <td class="d-flex align-items-center">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold mb-1"><?= htmlspecialchars($summary['company_name'] ?? 'N/A') ?></span>
                                                    </div>
                                                </td>
                                                <td><code class="text-gray-700"><?= htmlspecialchars($summary['db_name'] ?? 'N/A') ?></code></td>
                                                <td><code class="text-gray-700"><?= htmlspecialchars(!empty($summary['db_password']) ? $summary['db_password'] : '-') ?></code></td>
                                                <td class="text-center"><?= $summary['error'] ? '-' : number_format($summary['customers']) ?></td>
                                                <td class="text-center"><?= $summary['error'] ? '-' : number_format($summary['contracts']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-light-secondary">
                                                        <?= $summary['error'] ? '-' : $summary['table_count'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($summary['error']): ?>
                                                        <span class="badge badge-light-danger">
                                                            <i class="ki-duotone ki-cross-circle fs-5 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            Hata
                                                        </span>
                                                    <?php elseif ($summary['is_healthy']): ?>
                                                        <span class="badge badge-light-success">
                                                            <i class="ki-duotone ki-check-circle fs-5 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            Sağlıklı
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-light-warning">
                                                            <i class="ki-duotone ki-information fs-5 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                            </i>
                                                            Uyarı
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button class="btn btn-sm btn-light-info" 
                                                                onclick='showTenantDiagnosis(<?= json_encode($summary, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>
                                                            <i class="ki-duotone ki-shield-search fs-5 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                            </i>
                                                            Teşhis
                                                        </button>
                                                        <a href="dist/toolbars/extended.php?db=<?= urlencode($summary['db_name']) ?>" 
                                                           class="btn btn-sm btn-light-primary" 
                                                           target="_blank">
                                                            <i class="ki-duotone ki-chart-simple fs-5 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            </i>
                                                            Detay
                                                        </a>
                                                    </div>
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

                <!-- Database Isolation Test -->
                <div class="row mb-6">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">
                                        <i class="ki-duotone ki-shield-tick fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Database Isolation Test
                                    </span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Veritabanı bağımsızlık testi</span>
                                </h3>
                            </div>
                            <div class="card-body py-4">
                                <?php
                                $isolationTest = true;
                                $connections = [];
                                
                                try {
                                    $db_m = new Database('prolyn_master');
                                    $connections['Prolyn Master'] = ['db' => 'prolyn_master', 'status' => true];
                                } catch (Exception $e) {
                                    $isolationTest = false;
                                    $connections['Prolyn Master'] = ['db' => 'prolyn_master', 'status' => false];
                                }
                                
                                $testTenants = array_slice($tenantDbs, 0, 3);
                                foreach ($testTenants as $tenant) {
                                    try {
                                        $db = new Database($tenant['db_name']);
                                        $connections[$tenant['company_name']] = ['db' => $tenant['db_name'], 'status' => true];
                                    } catch (Exception $e) {
                                        $isolationTest = false;
                                        $connections[$tenant['company_name']] = ['db' => $tenant['db_name'], 'status' => false];
                                    }
                                }
                                ?>
                                
                                <div class="row g-6">
                                    <?php foreach ($connections as $label => $info): ?>
                                    <div class="col-md-3">
                                        <div class="card border <?= $info['status'] ? 'border-success' : 'border-danger' ?> h-100">
                                            <div class="card-body text-center p-9">
                                                <div class="symbol symbol-65px mb-5 mx-auto">
                                                    <span class="symbol-label bg-light-<?= $info['status'] ? 'success' : 'danger' ?>">
                                                        <i class="ki-duotone ki-data fs-3x text-<?= $info['status'] ? 'success' : 'danger' ?>">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                            <span class="path5"></span>
                                                        </i>
                                                    </span>
                                                </div>
                                                <div class="fs-5 fw-bold text-gray-800 mb-2"><?= htmlspecialchars($label) ?></div>
                                                <code class="fs-7 text-gray-600"><?= htmlspecialchars($info['db']) ?></code>
                                                <div class="mt-4">
                                                    <?php if($info['status']): ?>
                                                        <span class="badge badge-success">
                                                            <i class="ki-duotone ki-check fs-5">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            Bağlı
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="ki-duotone ki-cross fs-5">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            Hata
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if ($isolationTest && !empty($testTenants)): ?>
                                <div class="alert alert-success d-flex align-items-center p-5 mt-6">
                                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-success">Başarılı!</h4>
                                        <span>Tüm bağlantılar bağımsız çalışıyor!</span>
                                    </div>
                                </div>
                                <?php elseif(empty($testTenants)): ?>
                                <div class="alert alert-warning d-flex align-items-center p-5 mt-6">
                                    <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-warning">Bilgi</h4>
                                        <span>Test edilecek tenant bulunamadı.</span>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-danger d-flex align-items-center p-5 mt-6">
                                    <i class="ki-duotone ki-information fs-2hx text-danger me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-danger">Dikkat!</h4>
                                        <span>Bazı bağlantılarda sorun tespit edildi!</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                } catch (Exception $e) {
                    echo '<div class="row"><div class="col-12">';
                    echo '<div class="alert alert-danger d-flex align-items-center p-5">';
                    echo '<i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
                    echo '<div class="d-flex flex-column"><h4 class="mb-1 text-danger">Kritik Hata</h4><span>' . htmlspecialchars($e->getMessage()) . '</span></div>';
                    echo '</div>';
                    echo '<div class="card"><div class="card-header"><h3 class="card-title">Stack Trace</h3></div>';
                    echo '<div class="card-body"><pre class="mb-0">' . htmlspecialchars($e->getTraceAsString()) . '</pre></div></div>';
                    echo '</div></div>';
                }
                ?>

                <!-- Teşhis Modalı -->
                <div class="modal fade" id="kt_modal_diagnosis" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" id="kt_modal_diagnosis_header">
                                <h2 class="fw-bold">
                                    <i class="ki-duotone ki-shield-search fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Tenant Teşhis Detayı
                                </h2>
                                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                    <i class="ki-duotone ki-cross fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                                <div id="kt_modal_diagnosis_body"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    <i class="ki-duotone ki-cross fs-2 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Kapat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

	        </div>
        </div>
    </div>
    <?php 
require_once '../../config/footer.php'; 
?>

<script>
"use strict";

// DataTables başlat
var KTDatabaseControl = function() {
    var table;
    var dt;

    var initTable = function() {
        dt = $("#kt_tenant_table").DataTable({
            info: false,
            order: [[0, "asc"]],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tümü"]],
            columnDefs: [
                { orderable: true, targets: 0 },
                { orderable: false, targets: 7 }
            ],
            language: {
                "sDecimal": ",",
                "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
                "sInfo": "_TOTAL_ kayıttan _START_ - _END_ arası kayıt gösteriliyor",
                "sInfoEmpty": "Kayıt yok",
                "sInfoFiltered": "(_MAX_ kayıt içerisinden bulunan)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "Sayfada _MENU_ kayıt göster",
                "sLoadingRecords": "Yükleniyor...",
                "sProcessing": "İşleniyor...",
                "sSearch": "Ara:",
                "sZeroRecords": "Eşleşen kayıt bulunamadı",
                "oPaginate": {
                    "sFirst": "İlk",
                    "sLast": "Son",
                    "sNext": "Sonraki",
                    "sPrevious": "Önceki"
                },
                "oAria": {
                    "sSortAscending": ": artan sütun sıralamasını aktifleştir",
                    "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
                }
            }
        });

        table = dt.$;
    }

    return {
        init: function() {
            initTable();
        }
    };
}();

// Teşhis modalı
function showTenantDiagnosis(summary) {
    var html = '<div class="row g-6 g-xl-9">';
    
    // Sol Kolon - Genel Bilgiler
    html += '<div class="col-md-6">';
    html += '<h5 class="fw-bold mb-5">';
    html += '<i class="ki-duotone ki-information-5 fs-2 me-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
    html += 'Genel Bilgiler</h5>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">';
    html += '<tbody>';
    html += '<tr><td class="fw-bold text-muted min-w-100px">Şirket</td><td class="text-gray-800">' + (summary.company_name || '-') + '</td></tr>';
    html += '<tr><td class="fw-bold text-muted">Veritabanı</td><td><code>' + (summary.db_name || '-') + '</code></td></tr>';
    html += '<tr><td class="fw-bold text-muted">Statü</td><td><span class="badge badge-light-' + (summary.status === 'active' ? 'success' : 'warning') + '">' + (summary.status || '-') + '</span></td></tr>';
    html += '<tr><td class="fw-bold text-muted">Sağlık</td><td>' + (summary.is_healthy ? '<span class="badge badge-success"><i class="ki-duotone ki-check fs-5"><span class="path1"></span><span class="path2"></span></i> Sağlıklı</span>' : '<span class="badge badge-warning"><i class="ki-duotone ki-information fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Sorunlu</span>') + '</td></tr>';
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    // Sağ Kolon - İstatistikler
    html += '<div class="col-md-6">';
    html += '<h5 class="fw-bold mb-5">';
    html += '<i class="ki-duotone ki-chart-simple fs-2 me-2 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>';
    html += 'İstatistikler</h5>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">';
    html += '<tbody>';
    html += '<tr><td class="fw-bold text-muted min-w-100px">Müşteri</td><td class="text-gray-800 fw-bold">' + (summary.customers || '0') + '</td></tr>';
    html += '<tr><td class="fw-bold text-muted">Sözleşme</td><td class="text-gray-800 fw-bold">' + (summary.contracts || '0') + '</td></tr>';
    html += '<tr><td class="fw-bold text-muted">Tablo Sayısı</td><td class="text-gray-800 fw-bold">' + (summary.table_count || '0') + '</td></tr>';
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    html += '</div>';
    
    html += '</div>';
    
    // Hata varsa göster
    if (summary.error) {
        html += '<div class="alert alert-danger d-flex align-items-center p-5 mt-6">';
        html += '<i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
        html += '<div class="d-flex flex-column"><h4 class="mb-1 text-danger">Hata</h4><span>' + summary.error + '</span></div>';
        html += '</div>';
    }
    
    // Eksik ve Fazla Tablolar
    html += '<div class="separator border-2 my-10"></div>';
    html += '<div class="row g-6 g-xl-9">';
    
    // Eksik Tablolar
    html += '<div class="col-md-6">';
    html += '<h6 class="fw-bold text-danger mb-4">';
    html += '<i class="ki-duotone ki-cross-circle fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>';
    html += 'Eksik Tablolar</h6>';
    if (summary.missing && summary.missing.length > 0) {
        html += '<div class="alert alert-danger">';
        html += '<ul class="mb-0">';
        summary.missing.forEach(function(table) {
            html += '<li class="d-flex align-items-center py-2"><i class="ki-duotone ki-minus-square fs-3 text-danger me-3"><span class="path1"></span><span class="path2"></span></i><span>' + table + '</span></li>';
        });
        html += '</ul></div>';
    } else {
        html += '<div class="alert alert-success d-flex align-items-center p-5">';
        html += '<i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>';
        html += '<span class="fw-semibold">Eksik tablo yok</span>';
        html += '</div>';
    }
    html += '</div>';
    
    // Fazla Tablolar
    html += '<div class="col-md-6">';
    html += '<h6 class="fw-bold text-warning mb-4">';
    html += '<i class="ki-duotone ki-information fs-2 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
    html += 'Fazla Tablolar</h6>';
    if (summary.extra && summary.extra.length > 0) {
        html += '<div class="alert alert-warning">';
        html += '<ul class="mb-0">';
        summary.extra.forEach(function(table) {
            html += '<li class="d-flex align-items-center py-2"><i class="ki-duotone ki-plus-square fs-3 text-warning me-3"><span class="path1"></span><span class="path2"></span></i><span>' + table + '</span></li>';
        });
        html += '</ul></div>';
    } else {
        html += '<div class="alert alert-success d-flex align-items-center p-5">';
        html += '<i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>';
        html += '<span class="fw-semibold">Fazla tablo yok</span>';
        html += '</div>';
    }
    html += '</div>';
    html += '</div>';
    
    document.getElementById('kt_modal_diagnosis_body').innerHTML = html;
    
    // Bootstrap 5 Modal
    var myModal = new bootstrap.Modal(document.getElementById('kt_modal_diagnosis'));
    myModal.show();
}

// Sayfa yüklendiğinde
KTUtil.onDOMContentLoaded(function() {
    KTDatabaseControl.init();
});
</script>