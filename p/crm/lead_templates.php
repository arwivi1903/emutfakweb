<?php
/**
 * Prolyn Admin Panel - Lead mesaj şablonları (WhatsApp / e-posta)
 */
require_once '../config/header.php';
Auth::requireModule('manage_tenants');
require_once __DIR__ . '/../functions/lead.php';
lead_sema_kur($db);

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['sablon_id'] ?? 0);
    $govde = trim(str_replace("\r\n", "\n", (string)($_POST['govde'] ?? '')));
    $konu = trim((string)($_POST['konu'] ?? ''));
    $ad = trim((string)($_POST['ad'] ?? ''));
    $mevcut = $db->getRowAssoc("SELECT kanal FROM lead_sablonlari WHERE sablon_id = ?", [$id]);
    if (!$mevcut || $govde === '' || $ad === '') {
        $flash = ['danger', 'Ad ve metin zorunlu.'];
    } elseif ($mevcut['kanal'] === 'eposta' && strpos($govde, '{ret_link}') === false) {
        $flash = ['danger', 'E-posta metninde {ret_link} bulunmalı: alıcıya istemediğini bildirme yolu sunmak yasal zorunluluk.'];
    } else {
        $db->Update("UPDATE lead_sablonlari SET ad = ?, konu = ?, govde = ?, aktif = ? WHERE sablon_id = ?",
            [$ad, $konu !== '' ? $konu : null, $govde, isset($_POST['aktif']) ? 1 : 0, $id]);
        $flash = ['success', 'Şablon kaydedildi.'];
    }
}
$sablonlar = $db->getRowsAssoc("SELECT * FROM lead_sablonlari ORDER BY kanal DESC, sira");
$ornek = ['lead_id' => 1, 'firma_adi' => 'Örnek Mutfak', 'yetkili' => '', 'il' => 'Kocaeli', 'ret_token' => str_repeat('0', 32)];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
require_once '../config/sidebar.php';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-xxl d-flex flex-stack">
        <div>
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Mesaj Şablonları</h1>
          <span class="text-muted fs-7">Değişkenler: <code>{firma}</code> <code>{il}</code> <code>{gonderen}</code> <code>{link}</code> <code>{kayit_link}</code> <code>{ret_link}</code> <code>{site}</code></span>
        </div>
        <a href="crm/leads.php" class="btn btn-sm btn-light">← Listeye dön</a>
      </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <?php if ($flash): ?><div class="alert alert-<?= $e($flash[0]) ?> p-5 mb-6"><?= $e($flash[1]) ?></div><?php endif; ?>
        <div class="row g-6">
        <?php foreach ($sablonlar as $s): ?>
          <div class="col-xl-6">
            <form method="post" action="crm/lead_templates.php" class="card h-100">
              <?= csrfTokenField() ?><input type="hidden" name="action" value="save"><input type="hidden" name="sablon_id" value="<?= (int)$s['sablon_id'] ?>">
              <div class="card-header align-items-center">
                <h3 class="card-title fw-bold"><span class="badge badge-light-<?= $s['kanal'] === 'whatsapp' ? 'success' : 'info' ?> me-2"><?= $s['kanal'] === 'whatsapp' ? 'WhatsApp' : 'E-posta' ?></span><?= $e($s['ad']) ?></h3>
                <label class="form-check form-switch form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" name="aktif" <?= (int)$s['aktif'] ? 'checked' : '' ?>><span class="form-check-label">Aktif</span></label>
              </div>
              <div class="card-body">
                <label class="form-label fs-7">Ad</label>
                <input name="ad" class="form-control form-control-sm form-control-solid mb-3" value="<?= $e($s['ad']) ?>">
                <?php if ($s['kanal'] === 'eposta'): ?>
                  <label class="form-label fs-7">Konu</label>
                  <input name="konu" class="form-control form-control-sm form-control-solid mb-3" value="<?= $e($s['konu']) ?>">
                <?php endif; ?>
                <label class="form-label fs-7">Metin</label>
                <textarea name="govde" rows="10" class="form-control form-control-solid fs-7"><?= $e($s['govde']) ?></textarea>
                <details class="mt-3"><summary class="text-muted fs-7 cursor-pointer">Önizleme</summary>
                  <div class="bg-light rounded p-4 mt-2 fs-7" style="white-space:pre-line"><?= $e(lead_sablon_doldur((string)$s['govde'], $ornek, $s['kanal'], lead_gonderen_adi())) ?></div>
                </details>
              </div>
              <div class="card-footer text-end py-4"><button class="btn btn-sm btn-primary">Kaydet</button></div>
            </form>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
