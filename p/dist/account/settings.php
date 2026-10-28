<?php 
require_once __DIR__ . '/../../config/header.php'; 
require_once __DIR__ . '/../../config/sidebar.php'; 

// Update logic
$updateMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    require_once __DIR__ . '/../../classes/allClass.php';
    $db = new Database();
    
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $admin_id = $_SESSION['admin_id'] ?? 0;
    
    if (!empty($full_name) && !empty($username) && $admin_id > 0) {
        $update = $db->update("UPDATE yoneticiler SET full_name = ?, username = ? WHERE admin_id = ?", [$full_name, $username, $admin_id]);
        if ($update) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['username'] = $username;
            $updateMsg = '<div class="alert alert-success">Profil başarıyla güncellendi.</div>';
        } else {
            $updateMsg = '<div class="alert alert-danger">Güncelleme sırasında bir hata oluştu veya bir değişiklik yapmadınız.</div>';
        }
    } else {
        $updateMsg = '<div class="alert alert-warning">Lütfen gerekli tüm alanları doldurun.</div>';
    }
}
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <?php require_once __DIR__ . '/account_header.php'; ?>

                <div class="card mb-5 mb-xl-10">
                    <?php if(!empty($updateMsg)) echo $updateMsg; ?>
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                        data-bs-target="#kt_account_profile_details" aria-expanded="true"
                        aria-controls="kt_account_profile_details">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Profil Detayları</h3>
                        </div>
                    </div>
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <form id="kt_account_profile_details_form" class="form" method="POST">
                            <?php echo csrfTokenField(); ?>
                            <div class="card-body border-top p-9">
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                                    <div class="col-lg-8">
                                        <div class="image-input image-input-outline" data-kt-image-input="true"
                                            style="background-image: url('dist/assets/media/svg/avatars/blank.svg')">
                                            <div class="image-input-wrapper w-125px h-125px"
                                                style="background-image: url('<?php echo htmlspecialchars((string) ($_SESSION['admin_pic'] ?? 'dist/assets/media/svg/avatars/blank.svg'), ENT_QUOTES, 'UTF-8'); ?>')"></div>
                                            <label
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                title="Avatar değiştir">
                                                <i class="ki-duotone ki-pencil fs-7">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                title="İptal">
                                                <i class="ki-duotone ki-cross fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <div class="form-text">İzin verilen dosya türleri: png, jpg, jpeg.</div>
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tam Adı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="full_name"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="Tam Adınız" value="<?php echo $_SESSION['full_name'] ?? ''; ?>" />
                                    </div>
                                </div>
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Kullanıcı Adı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="username"
                                            class="form-control form-control-lg form-control-solid"
                                            placeholder="Kullanıcı Adınız" value="<?php echo $_SESSION['username'] ?? ''; ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2">İptal</button>
                                <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Değişiklikleri Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                        data-bs-target="#kt_account_signin_method">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Giriş Bilgileri</h3>
                        </div>
                    </div>
                    <div id="kt_account_settings_signin_method" class="collapse show">
                        <div class="card-body border-top p-9">
                            <div class="d-flex flex-wrap align-items-center">
                                <div id="kt_signin_email">
                                    <div class="fs-6 fw-bold mb-1">E-posta Adresi</div>
                                    <div class="fw-semibold text-gray-600"><?php echo $_SESSION['email'] ?? ''; ?></div>
                                </div>
                                <div id="kt_signin_email_edit" class="flex-row-fluid d-none">
                                    <form id="kt_signin_change_email" class="form" novalidate="novalidate">
                                        <div class="row mb-6">
                                            <div class="col-lg-6 mb-4 mb-lg-0">
                                                <div class="fv-row mb-0">
                                                    <label for="emailaddress" class="form-label fs-6 fw-bold mb-3">Yeni E-posta Adresi</label>
                                                    <input type="email"
                                                        class="form-control form-control-lg form-control-solid"
                                                        id="emailaddress" placeholder="E-posta Adresi"
                                                        name="emailaddress" value="<?php echo $_SESSION['email'] ?? ''; ?>" />
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="fv-row mb-0">
                                                    <label for="confirmemailpassword"
                                                        class="form-label fs-6 fw-bold mb-3">Şifrenizi Doğrulayın</label>
                                                    <input type="password"
                                                        class="form-control form-control-lg form-control-solid"
                                                        name="confirmemailpassword" id="confirmemailpassword" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <button id="kt_signin_submit" type="button"
                                                class="btn btn-primary me-2 px-6">E-postayı Güncelle</button>
                                            <button id="kt_signin_cancel" type="button"
                                                class="btn btn-color-gray-400 btn-active-light-primary px-6">İptal</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="kt_signin_email_button" class="ms-auto">
                                    <button class="btn btn-light btn-active-light-primary">E-posta Değiştir</button>
                                </div>
                            </div>
                            <div class="separator separator-dashed my-6"></div>
                            <div class="d-flex flex-wrap align-items-center mb-10">
                                <div id="kt_signin_password">
                                    <div class="fs-6 fw-bold mb-1">Şifre</div>
                                    <div class="fw-semibold text-gray-600">************</div>
                                </div>
                                <div id="kt_signin_password_edit" class="flex-row-fluid d-none">
                                    <form id="kt_signin_change_password" class="form" novalidate="novalidate">
                                        <div class="row mb-1">
                                            <div class="col-lg-4">
                                                <div class="fv-row mb-0">
                                                    <label for="currentpassword"
                                                        class="form-label fs-6 fw-bold mb-3">Mevcut Şifre</label>
                                                    <input type="password"
                                                        class="form-control form-control-lg form-control-solid"
                                                        name="currentpassword" id="currentpassword" />
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="fv-row mb-0">
                                                    <label for="newpassword" class="form-label fs-6 fw-bold mb-3">Yeni Şifre</label>
                                                    <input type="password"
                                                        class="form-control form-control-lg form-control-solid"
                                                        name="newpassword" id="newpassword" />
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="fv-row mb-0">
                                                    <label for="confirmpassword"
                                                        class="form-label fs-6 fw-bold mb-3">Yeni Şifre (Tekrar)</label>
                                                    <input type="password"
                                                        class="form-control form-control-lg form-control-solid"
                                                        name="confirmpassword" id="confirmpassword" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text mb-5">Şifreniz en az 8 karakter olmalı ve sembol içermelidir.</div>
                                        <div class="d-flex">
                                            <button id="kt_password_submit" type="button"
                                                class="btn btn-primary me-2 px-6">Şifreyi Güncelle</button>
                                            <button id="kt_password_cancel" type="button"
                                                class="btn btn-color-gray-400 btn-active-light-primary px-6">İptal</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="kt_signin_password_button" class="ms-auto">
                                    <button class="btn btn-light btn-active-light-primary">Şifre Sıfırla</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/../../config/footer.php'; ?>
