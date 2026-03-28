<?php
require_once __DIR__ . '/../../../classes/database.class.php';
$db = new Database();

// ID kontrolü
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// If no ID, redirect to list
if ($id <= 0) {
    header('Location: dist/apps/customers/list.php?error=invalid_id');
    exit;
}

// Müşteri verisini çek
$customer = $db->getRow('SELECT * FROM customers WHERE customer_id = ?', array($id));

if (!$customer) {
    header('Location: dist/apps/customers/list.php?notfound=1');
    exit;
}

require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

// İstatistikler (Mock)
$active_subs = $db->getRow("SELECT COUNT(*) as cnt FROM subscriptions WHERE customer_id = ? AND status='active'", [$id])->cnt ?? 0;
$total_paid = $db->getRow("SELECT SUM(amount) as total FROM payments WHERE customer_id = ? AND payment_status='completed'", [$id])->total ?? 0;
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                <!-- Navbar -->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                            <div class="me-7 mb-4">
                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                    <?php if($customer->logo_url): ?>
                                        <img src="<?= htmlspecialchars($customer->logo_url) ?>" alt="image" />
                                    <?php else: ?>
                                        <div class="symbol-label fs-1 fw-bold bg-light-primary text-primary">
                                            <?= mb_substr($customer->company_name, 0, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1"><?= htmlspecialchars($customer->company_name) ?></a>
                                            <a href="#"><i class="ki-duotone ki-verify fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i></a>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                            <i class="ki-duotone ki-profile-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i><?= htmlspecialchars($customer->contact_name) ?></a>
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                            <i class="ki-duotone ki-geolocation fs-4 me-1"><span class="path1"></span><span class="path2"></span></i><?= htmlspecialchars($customer->city ?? 'Belirtilmedi') ?></a>
                                            <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                            <i class="ki-duotone ki-sms fs-4 me-1"><span class="path1"></span><span class="path2"></span></i><?= htmlspecialchars($customer->company_email) ?></a>
                                        </div>
                                    </div>
                                    <div class="d-flex my-4">
                                        <a href="#" class="btn btn-sm btn-light me-2" id="kt_user_follow_button">
                                            <i class="ki-duotone ki-check fs-3 d-none"></i>
                                            <span class="indicator-label">Takip Et</span>
                                            <span class="indicator-progress">Lütfen bekleyin... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </a>
                                        <a href="dist/account/customer-edit.php?id=<?= $id ?>" class="btn btn-sm btn-primary me-3">Düzenle</a>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap flex-stack">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="<?= number_format($total_paid, 2) ?>" data-kt-countup-prefix="₺">0</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Toplam Ödeme</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-arrow-up fs-3 text-success me-2"><span class="path1"></span><span class="path2"></span></i>
                                                    <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="<?= $active_subs ?>">0</div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-400">Aktif Abonelik</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detaylar -->
                <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
                    <div class="card-header cursor-pointer">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Profil Detayları</h3>
                        </div>
                    </div>
                    <div class="card-body p-9">
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Şirket Adı</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?= htmlspecialchars($customer->company_name) ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Müşteri Kodu</label>
                            <div class="col-lg-8 fv-row">
                                <span class="fw-semibold text-gray-800 fs-6"><?= htmlspecialchars($customer->customer_code) ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Telefon</label>
                            <div class="col-lg-8 d-flex align-items-center">
                                <span class="fw-bold fs-6 text-gray-800 me-2"><?= htmlspecialchars($customer->company_phone) ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Sektör</label>
                            <div class="col-lg-8">
                                <a href="#" class="fw-semibold fs-6 text-gray-800 text-hover-primary"><?= htmlspecialchars($customer->industry) ?></a>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Durum</label>
                            <div class="col-lg-8">
                                <span class="badge badge-light-<?= $customer->status == 'active' ? 'success' : 'warning' ?> fw-bold"><?= ucfirst($customer->status) ?></span>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <label class="col-lg-4 fw-semibold text-muted">Kayıt Tarihi</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?= date('d.m.Y', strtotime($customer->created_at)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
