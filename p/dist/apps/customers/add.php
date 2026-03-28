<?php
require_once __DIR__ . '/../../../classes/database.class.php';
$db = new Database();

// POST İşlemi (Ekleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $customer_code = trim($_POST['customer_code'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $company_phone = trim($_POST['company_phone'] ?? '');
    $company_email = trim($_POST['company_email'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basit validasyon
    if (empty($company_name) || empty($company_email) || empty($password)) {
        $error = "Şirket adı, E-posta ve Şifre zorunludur.";
    } else {
        // SQL
        $sql = "INSERT INTO customers (company_name, customer_code, contact_name, company_phone, company_email, industry, password_hash, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'trial', NOW())";
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $params = [$company_name, $customer_code, $contact_name, $company_phone, $company_email, $industry, $password_hash];
        
        $insert = $db->Insert($sql, $params);
        
        if ($insert) {
            header('Location: dist/apps/customers/list.php?created=1');
            exit;
        } else {
            $error = "Ekleme sırasında bir hata oluştu: " . $db->getLastError();
        }
    }
}

require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Yeni Müşteri Ekle</h3>
                        </div>
                    </div>

                    <div class="collapse show">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mx-9 mt-5"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form class="form" method="post">
                            <div class="card-body border-top p-9">
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Şirket Adı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="company_name" class="form-control form-control-lg form-control-solid" placeholder="Şirket Adı" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Müşteri Kodu</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="customer_code" class="form-control form-control-lg form-control-solid" placeholder="Örn: CUST001" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">İletişim Kişisi</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="contact_name" class="form-control form-control-lg form-control-solid" placeholder="Ad Soyad" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Telefon</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="company_phone" class="form-control form-control-lg form-control-solid" placeholder="Telefon" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="company_email" class="form-control form-control-lg form-control-solid" placeholder="E-posta Adresi" />
                                    </div>
                                </div>
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Şifre</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="password" name="password" class="form-control form-control-lg form-control-solid" placeholder="Şifre" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Sektör</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="industry" class="form-control form-control-lg form-control-solid" placeholder="Sektör" />
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <a href="dist/apps/customers/list.php" class="btn btn-light btn-active-light-primary me-2">İptal</a>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
