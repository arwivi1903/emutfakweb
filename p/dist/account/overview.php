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
                            <label class="col-lg-4 fw-semibold text-muted">Şirket</label>
                            <div class="col-lg-8 fv-row">
                                <span
                                    class="fw-semibold text-gray-800 fs-6"><?php echo ($_SESSION['company'] ?? 'Prolyn'); ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">İletişim Telefonu
                                <span class="ms-1" data-bs-toggle="tooltip" title="Telefon numarası aktif olmalıdır">
                                    <i class="ki-duotone ki-information fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span></label>
                            <div class="col-lg-8 d-flex align-items-center">
                                <span
                                    class="fw-bold fs-6 text-gray-800 me-2"><?php echo ($_SESSION['phone'] ?? ''); ?></span>
                                <span class="badge badge-success">Doğrulandı</span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Şirket Sitesi</label>
                            <div class="col-lg-8">
                                <a href="#"
                                    class="fw-semibold fs-6 text-gray-800 text-hover-primary"><?php echo ($_SESSION['company_website'] ?? '#'); ?></a>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Ülke
                                <span class="ms-1" data-bs-toggle="tooltip" title="Menşe ülkesi">
                                    <i class="ki-duotone ki-information fs-7">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span></label>
                            <div class="col-lg-8">
                                <span
                                    class="fw-bold fs-6 text-gray-800"><?php echo ($_SESSION['country'] ?? 'Türkiye'); ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">İletişim</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800">Email, Telefon</span>
                            </div>
                        </div>
                        <div class="row mb-10">
                            <label class="col-lg-4 fw-semibold text-muted">Değişikliklere İzin Ver</label>
                            <div class="col-lg-8">
                                <span class="fw-semibold fs-6 text-gray-800">Evet</span>
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