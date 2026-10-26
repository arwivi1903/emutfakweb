<?php 
require_once '../../config/header.php'; 
require_once '../../config/sidebar.php'; 
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php require_once 'account_header.php'; ?>

                <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
                    <div class="card-header cursor-pointer">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Profil Detayları</h3>
                        </div>
                        <a href="dist/account/settings.php" class="btn btn-sm btn-primary align-self-center">Profili
                            Düzenle</a>
                    </div>
                    <div class="card-body p-9">
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Tam Adı</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?php echo $_SESSION['full_name']; ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Kullanıcı Adı</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?php echo $_SESSION['username'] ?? ''; ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">E-posta</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?php echo $_SESSION['email'] ?? ''; ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Rolü</label>
                            <div class="col-lg-8 fv-row">
                                <span class="fw-semibold text-gray-800 fs-6"><?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Hesap Durumu</label>
                            <div class="col-lg-8">
                                <?php if (isset($_SESSION['status']) && $_SESSION['status'] === 'active'): ?>
                                    <span class="badge badge-light-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-light-danger">Pasif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Son Giriş IP</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold fs-6 text-gray-800"><?php echo $_SESSION['last_ip'] ?? 'Bilinmiyor'; ?></span>
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