<?php
/**
 * Prolyn Admin Panel - Form Güvenliği (Google reCAPTCHA v3 / Enterprise)
 * İletişim ve kayıt formlarını botlara karşı korur. Gizli değerler sayfaya geri basılmaz.
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once '../config/sidebar.php';

$kaydet = static function ($db, string $k, string $v) {
    $db->Insert("INSERT INTO sistem_ayarlari (setting_key, setting_value, category, is_public, updated_at)
                 VALUES (?, ?, 'security', 0, NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), category = 'security', is_public = 0, updated_at = NOW()",
        [$k, $v]);
};
$oku = static function ($db): array {
    $c = [];
    foreach ((array)$db->getRowsAssoc("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE setting_key LIKE 'recaptcha\\_%'") as $r) {
        $c[$r['setting_key']] = trim((string)$r['setting_value']);
    }
    return $c;
};
$anahtarBicimi = '/^[A-Za-z0-9_\-]{20,100}$/';

$hata = null;
$c = $oku($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_recaptcha') {
    $mode = ($_POST['recaptcha_mode'] ?? '') === 'enterprise' ? 'enterprise' : 'classic';
    $site = trim((string)($_POST['recaptcha_site_key'] ?? ''));
    $secret = trim((string)($_POST['recaptcha_secret_key'] ?? ''));
    $project = trim((string)($_POST['recaptcha_project_id'] ?? ''));
    $apiKey = trim((string)($_POST['recaptcha_api_key'] ?? ''));
    $score = (float)str_replace(',', '.', (string)($_POST['recaptcha_min_score'] ?? '0.5'));
    $aktif = isset($_POST['recaptcha_enabled']) ? '1' : '0';

    $sonSecret = $secret !== '' ? $secret : ($c['recaptcha_secret_key'] ?? '');
    $sonApi = $apiKey !== '' ? $apiKey : ($c['recaptcha_api_key'] ?? '');
    if (isset($_POST['gizlileri_sil'])) { $sonSecret = ''; $sonApi = ''; $aktif = '0'; }

    if ($site !== '' && !preg_match($anahtarBicimi, $site)) {
        $hata = 'Site anahtarı geçersiz görünüyor.';
    } elseif ($secret !== '' && !preg_match($anahtarBicimi, $secret)) {
        $hata = 'Gizli anahtar geçersiz görünüyor.';
    } elseif ($apiKey !== '' && !preg_match('/^[A-Za-z0-9_\-]{30,60}$/', $apiKey)) {
        $hata = 'API anahtarı geçersiz görünüyor (genellikle "AIza" ile başlar).';
    } elseif ($project !== '' && !preg_match('/^[a-z][a-z0-9\-]{4,61}[a-z0-9]$/', $project)) {
        $hata = 'Proje ID geçersiz görünüyor (küçük harf, rakam ve tire; ör. prolyn-123456).';
    } elseif ($aktif === '1' && $site === '') {
        $hata = 'Korumayı açmak için site anahtarı gerekli.';
    } elseif ($aktif === '1' && $mode === 'classic' && $sonSecret === '') {
        $hata = 'Klasik v3 için gizli anahtar gerekli.';
    } elseif ($aktif === '1' && $mode === 'enterprise' && ($project === '' || $sonApi === '')) {
        $hata = 'Enterprise için proje ID ve API anahtarı gerekli.';
    } else {
        $kaydet($db, 'recaptcha_mode', $mode);
        $kaydet($db, 'recaptcha_site_key', $site);
        $kaydet($db, 'recaptcha_secret_key', $sonSecret);
        $kaydet($db, 'recaptcha_project_id', $project);
        $kaydet($db, 'recaptcha_api_key', $sonApi);
        $kaydet($db, 'recaptcha_min_score', number_format(max(0.1, min(0.9, $score)), 1, '.', ''));
        $kaydet($db, 'recaptcha_enabled', $aktif);
        echo "<script>window.location.href='website/form_security.php?success=1';</script>";
        exit;
    }
}

$mode = ($c['recaptcha_mode'] ?? 'classic') === 'enterprise' ? 'enterprise' : 'classic';
$secretVar = ($c['recaptcha_secret_key'] ?? '') !== '';
$apiVar = ($c['recaptcha_api_key'] ?? '') !== '';
$aktif = ($c['recaptcha_enabled'] ?? '0') === '1' && ($c['recaptcha_site_key'] ?? '') !== ''
    && ($mode === 'enterprise' ? (($c['recaptcha_project_id'] ?? '') !== '' && $apiVar) : $secretVar);
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-xxl d-flex flex-stack">
        <div>
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Form Güvenliği</h1>
          <span class="text-muted fs-7">İletişim ve kayıt formlarını Google reCAPTCHA ile botlara karşı koru</span>
        </div>
        <span class="badge badge-light-<?= $aktif ? 'success' : 'secondary' ?> fs-7"><?= $aktif ? 'Koruma açık · ' . ($mode === 'enterprise' ? 'Enterprise' : 'v3') : 'Koruma kapalı' ?></span>
      </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-xxl">
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success p-5 mb-6">Ayarlar kaydedildi. İletişim formundan bir deneme mesajı göndererek kontrol edin.</div><?php endif; ?>
        <?php if ($hata): ?><div class="alert alert-danger p-5 mb-6"><?= $e($hata) ?></div><?php endif; ?>
        <div class="row g-6">
          <div class="col-xl-7">
            <form method="post" action="website/form_security.php" class="card" autocomplete="off">
              <?= csrfTokenField() ?><input type="hidden" name="action" value="save_recaptcha">
              <div class="card-header"><h3 class="card-title fw-bold">Google reCAPTCHA</h3></div>
              <div class="card-body">
                <label class="form-check form-switch form-check-custom form-check-solid mb-8">
                  <input class="form-check-input" type="checkbox" name="recaptcha_enabled" value="1" <?= ($c['recaptcha_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                  <span class="form-check-label fw-semibold text-gray-800">İletişim ve kayıt (30 gün deneme) formlarında korumayı aç</span>
                </label>

                <div class="mb-6">
                  <label class="form-label">Anahtar türü</label>
                  <div class="d-flex gap-6">
                    <label class="form-check form-check-custom form-check-solid"><input class="form-check-input rc-mode" type="radio" name="recaptcha_mode" value="enterprise" <?= $mode === 'enterprise' ? 'checked' : '' ?>><span class="form-check-label">reCAPTCHA Enterprise <span class="text-muted fs-8">(Google Cloud)</span></span></label>
                    <label class="form-check form-check-custom form-check-solid"><input class="form-check-input rc-mode" type="radio" name="recaptcha_mode" value="classic" <?= $mode === 'classic' ? 'checked' : '' ?>><span class="form-check-label">Klasik v3 <span class="text-muted fs-8">(gizli anahtarlı)</span></span></label>
                  </div>
                  <div class="form-text">Google'dan gelen kod <code>enterprise.js</code> içeriyorsa Enterprise seçin.</div>
                </div>

                <div class="mb-6">
                  <label class="form-label">Site anahtarı (Site key)</label>
                  <input type="text" name="recaptcha_site_key" class="form-control form-control-solid font-monospace" value="<?= $e($c['recaptcha_site_key'] ?? '') ?>" placeholder="6L...">
                </div>

                <div class="rc-ent">
                  <div class="mb-6">
                    <label class="form-label">Google Cloud proje ID</label>
                    <input type="text" name="recaptcha_project_id" class="form-control form-control-solid font-monospace" value="<?= $e($c['recaptcha_project_id'] ?? '') ?>" placeholder="ör. prolyn-123456">
                  </div>
                  <div class="mb-6">
                    <label class="form-label">API anahtarı</label>
                    <input type="password" name="recaptcha_api_key" class="form-control form-control-solid font-monospace" value="" autocomplete="new-password"
                           placeholder="<?= $apiVar ? 'Kayıtlı — değiştirmek için yenisini yazın' : 'AIza...' ?>">
                    <div class="form-text">Kayıtlı anahtar bu sayfada gösterilmez; boş bırakırsanız korunur.</div>
                  </div>
                </div>

                <div class="rc-classic mb-6">
                  <label class="form-label">Gizli anahtar (Secret key)</label>
                  <input type="password" name="recaptcha_secret_key" class="form-control form-control-solid font-monospace" value="" autocomplete="new-password"
                         placeholder="<?= $secretVar ? 'Kayıtlı — değiştirmek için yenisini yazın' : '6L...' ?>">
                  <div class="form-text">Kayıtlı anahtar bu sayfada gösterilmez; boş bırakırsanız korunur.</div>
                </div>

                <?php if ($secretVar || $apiVar): ?>
                <label class="form-check form-check-custom form-check-sm mb-6"><input class="form-check-input" type="checkbox" name="gizlileri_sil" value="1"><span class="form-check-label text-danger">Kayıtlı gizli anahtarları sil (koruma kapanır)</span></label>
                <?php endif; ?>

                <div class="mb-2">
                  <label class="form-label">En düşük puan</label>
                  <select name="recaptcha_min_score" class="form-select form-select-solid w-200px">
                    <?php foreach (['0.3' => '0.3 — gevşek', '0.5' => '0.5 — önerilen', '0.7' => '0.7 — sıkı'] as $v => $t): ?>
                      <option value="<?= $v ?>" <?= ($c['recaptcha_min_score'] ?? '0.5') === $v ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Google her gönderime 0,0 (bot) ile 1,0 (insan) arası puan verir; bunun altındakiler reddedilir.</div>
                </div>
              </div>
              <div class="card-footer text-end"><button class="btn btn-primary">Kaydet</button></div>
            </form>
          </div>
          <div class="col-xl-5">
            <div class="card mb-6 rc-ent">
              <div class="card-header"><h3 class="card-title fw-bold">Enterprise bilgileri nereden?</h3></div>
              <div class="card-body fs-6 text-gray-700">
                <ol class="ps-5 mb-0">
                  <li class="mb-2"><b>Site anahtarı:</b> Google'ın gönderdiği koddaki <code>render=</code> sonrasındaki değer (6L ile başlar).</li>
                  <li class="mb-2"><b>Proje ID:</b> <a href="https://console.cloud.google.com/home/dashboard" target="_blank" rel="noopener">Google Cloud Console</a> ana sayfasındaki "Proje kimliği" (Project ID).</li>
                  <li class="mb-2"><b>API anahtarı:</b> Console → API'ler ve Hizmetler → <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Kimlik bilgileri</a> → Kimlik bilgisi oluştur → API anahtarı. Anahtarı yalnızca "reCAPTCHA Enterprise API" ile sınırlandırın.</li>
                  <li class="mb-2">reCAPTCHA Enterprise API projede etkin olmalı (anahtarı oluştururken genelde otomatik açılır).</li>
                  <li>reCAPTCHA anahtarının ayarlarında alan adı olarak <code>prolyn.net</code> ekli olmalı.</li>
                </ol>
              </div>
            </div>
            <div class="card rc-classic">
              <div class="card-header"><h3 class="card-title fw-bold">Klasik v3 anahtarları</h3></div>
              <div class="card-body fs-6 text-gray-700">
                <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">reCAPTCHA yönetim sayfasında</a> v3 anahtarı oluşturup site ve gizli anahtarı girin; alan adı <code>prolyn.net</code>.
              </div>
            </div>
            <div class="text-muted fs-7 mt-4">Reddedilen gönderimler sunucunun error_log dosyasına "[contact] reCAPTCHA reddetti" veya "[register] reCAPTCHA reddetti" ve nedeniyle yazılır. Ücretsiz kullanım sınırı Google tarafından belirlenir.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
<script>
(function () {
  function guncelle() {
    var ent = (document.querySelector('.rc-mode:checked') || {}).value === 'enterprise';
    document.querySelectorAll('.rc-ent').forEach(function (e) { e.style.display = ent ? '' : 'none'; });
    document.querySelectorAll('.rc-classic').forEach(function (e) { e.style.display = ent ? 'none' : ''; });
  }
  document.querySelectorAll('.rc-mode').forEach(function (r) { r.addEventListener('change', guncelle); });
  guncelle();
})();
</script>
