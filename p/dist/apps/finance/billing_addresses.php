<?php
require_once __DIR__ . '/../../../classes/database.class.php';
require_once __DIR__ . '/../../../config/header.php';
require_once __DIR__ . '/../../../config/sidebar.php';

$db = new Database();
$addresses = [];
try {
    $addresses = $db->getRows("SELECT b.*, c.company_name FROM billing_addresses b JOIN customers c ON b.customer_id = c.customer_id");
} catch(Exception $e) {
    $error = "Tablo bulunamadı (billing_addresses).";
}
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title"><h3>Fatura Adresleri</h3></div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-warning"><?= $error ?></div>
                        <?php else: ?>
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Müşteri</th>
                                    <th>Adres Tipi</th>
                                    <th>Vergi No</th>
                                    <th>Ülke/Şehir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($addresses)): ?>
                                    <tr><td colspan="4" class="text-center">Kayıt yok.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($addresses as $addr): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($addr->company_name) ?></td>
                                        <td><?= htmlspecialchars($addr->address_type ?? '-') ?></td>
                                        <td><?= htmlspecialchars($addr->tax_id ?? '-') ?></td>
                                        <td><?= htmlspecialchars($addr->country . ' / ' . $addr->city) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../../../config/footer.php'; ?>
</div>
