<?php
/**
 * Prolyn Admin Panel - Plans Management
 * prolynweb/p/plans.php
 */

require_once 'config/header.php';
require_once 'config/sidebar.php';

// Fiyat ve Limit Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_plan') {
    $plan_id = (int)$_POST['plan_id'];
    $price_monthly = (float)$_POST['price_monthly'];
    $price_yearly = (float)$_POST['price_yearly'];
    $max_users = (int)$_POST['max_users'];
    $max_branches = (int)$_POST['max_branches'];
    
    $query = "UPDATE plans SET price_monthly = ?, price_yearly = ?, max_users = ?, max_branches = ? WHERE id = ?";
    $db->Update($query, [$price_monthly, $price_yearly, $max_users, $max_branches, $plan_id]);
    
    echo "<script>window.location.href='plans.php?success=1';</script>";
    exit;
}

// Plan Listesini Çek
$plans = $db->getRowsAssoc("SELECT * FROM plans ORDER BY price_monthly ASC");
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!-- Toolbar -->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        SaaS Paketleri
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.php" class="text-muted text-hover-primary">Ana Sayfa</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">SaaS Paketleri</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-fluid">
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success bg-light-success border-success d-flex flex-column flex-sm-row p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="fw-semibold">Başarılı</h4>
                        <span>SaaS paketi başarıyla güncellendi. Yeni fiyatlar kurumsal web sitesine anında yansıdı.</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row g-5 g-xl-10">
                    <?php if (!empty($plans)): ?>
                        <?php foreach ($plans as $plan): ?>
                        <div class="col-xl-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h3 class="card-title fw-bold text-gray-800"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                    <div class="card-toolbar">
                                        <?php if ($plan['is_active']): ?>
                                            <span class="badge badge-light-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-danger">Pasif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="plans.php">
                                        <input type="hidden" name="action" value="update_plan">
                                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                        
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Aylık Fiyat (₺)</label>
                                            <input type="number" step="0.01" class="form-control" name="price_monthly" value="<?php echo $plan['price_monthly']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Yıllık Fiyat (₺)</label>
                                            <input type="number" step="0.01" class="form-control" name="price_yearly" value="<?php echo $plan['price_yearly']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Max Kullanıcı</label>
                                            <input type="number" class="form-control" name="max_users" value="<?php echo $plan['max_users']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-5">
                                            <label class="form-label fw-semibold">Max Şube</label>
                                            <input type="number" class="form-control" name="max_branches" value="<?php echo $plan['max_branches']; ?>" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary w-100 mt-3">Güncelle</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Henüz tanımlı bir SaaS paketi bulunmamaktadır.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
require_once 'config/footer.php';
?>
