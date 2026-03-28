<?php
require_once __DIR__ . '/../../classes/database.class.php';
$db = new Database();

// ID kontrolü
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: customers.php?error=invalid_id');
    exit;
}

// POST İşlemi (Güncelleme)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name'] ?? '');
    $customer_code = trim($_POST['customer_code'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $company_phone = trim($_POST['company_phone'] ?? '');
    $company_email = trim($_POST['company_email'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $trial_start_date = !empty($_POST['trial_start_date']) ? $_POST['trial_start_date'] : null;
    $trial_end_date = !empty($_POST['trial_end_date']) ? $_POST['trial_end_date'] : null;

    // Basit validasyon
    if (empty($company_name)) {
        $error = "Şirket adı zorunludur.";
    } else {
        $updateData = [
            'company_name' => $company_name,
            'customer_code' => $customer_code,
            'contact_name' => $contact_name,
            'company_phone' => $company_phone,
            'company_email' => $company_email,
            'industry' => $industry,
            'status' => $status,
            'trial_start_date' => $trial_start_date,
            'trial_end_date' => $trial_end_date
        ];

        // Veritabanı güncelleme
        $where = array($id);
        // SQL Injection koruması için parameterized query kullanıldığını varsayıyorum (Database sınıfına bağlı)
        // Ancak Database sınıfının yapısını tam bilmiyoruz, customers.php'de $db->Update örneği var:
        // $db->Update('UPDATE customers SET status = ? WHERE customer_id = ?', array('suspended', $sil));
        
        $sql = "UPDATE customers SET 
                company_name = ?, 
                customer_code = ?, 
                contact_name = ?, 
                company_phone = ?, 
                company_email = ?, 
                industry = ?, 
                status = ?, 
                trial_start_date = ?, 
                trial_end_date = ? 
                WHERE customer_id = ?";
        
        $params = [
            $company_name, 
            $customer_code, 
            $contact_name, 
            $company_phone, 
            $company_email, 
            $industry, 
            $status, 
            $trial_start_date, 
            $trial_end_date,
            $id
        ];

        $update = $db->Update($sql, $params);

        if ($update) {
            header('Location: customers.php?updated=1');
            exit;
        } else {
            $error = "Güncelleme sırasında bir hata oluştu veya değişiklik yapılmadı.";
        }
    }
}

// Müşteri verisini çek
$customer = $db->getRow('SELECT * FROM customers WHERE customer_id = ?', array($id));

if (!$customer) {
    header('Location: customers.php?notfound=1');
    exit;
}

require_once __DIR__ . '/../../config/header.php';
require_once __DIR__ . '/../../config/sidebar.php';
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Müşteri Düzenle: <?php echo htmlspecialchars($customer->company_name); ?></h3>
                        </div>
                    </div>

                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mx-9 mt-5"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form id="kt_account_profile_details_form" class="form" method="post">
                            <div class="card-body border-top p-9">
                                
                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Şirket Adı</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="company_name" class="form-control form-control-lg form-control-solid" placeholder="Şirket Adı" value="<?php echo htmlspecialchars($customer->company_name ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Müşteri Kodu</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="customer_code" class="form-control form-control-lg form-control-solid" placeholder="Örn: CUST001" value="<?php echo htmlspecialchars($customer->customer_code ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">İletişim Kişisi</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="contact_name" class="form-control form-control-lg form-control-solid" placeholder="Ad Soyad" value="<?php echo htmlspecialchars($customer->contact_name ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Telefon</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="company_phone" class="form-control form-control-lg form-control-solid" placeholder="Telefon" value="<?php echo htmlspecialchars($customer->company_phone ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">E-posta</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="email" name="company_email" class="form-control form-control-lg form-control-solid" placeholder="E-posta Adresi" value="<?php echo htmlspecialchars($customer->company_email ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Sektör</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="text" name="industry" class="form-control form-control-lg form-control-solid" placeholder="Sektör" value="<?php echo htmlspecialchars($customer->industry ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Durum</label>
                                    <div class="col-lg-8 fv-row">
                                        <select name="status" class="form-select form-select-solid form-select-lg fw-semibold">
                                            <option value="trial" <?php echo ($customer->status === 'trial') ? 'selected' : ''; ?>>Deneme</option>
                                            <option value="active" <?php echo ($customer->status === 'active') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="suspended" <?php echo ($customer->status === 'suspended') ? 'selected' : ''; ?>>Askıda</option>
                                            <option value="expired" <?php echo ($customer->status === 'expired') ? 'selected' : ''; ?>>Süresi Dolmuş</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Deneme Başlangıç</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="date" name="trial_start_date" class="form-control form-control-lg form-control-solid" value="<?php echo htmlspecialchars($customer->trial_start_date ?? ''); ?>" />
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-4 col-form-label fw-semibold fs-6">Deneme Bitiş</label>
                                    <div class="col-lg-8 fv-row">
                                        <input type="date" name="trial_end_date" class="form-control form-control-lg form-control-solid" value="<?php echo htmlspecialchars($customer->trial_end_date ?? ''); ?>" />
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <a href="dist/account/customers.php" class="btn btn-light btn-active-light-primary me-2">İptal</a>
                                <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../config/footer.php'; ?>
</div>
