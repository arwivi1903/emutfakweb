<?php
/**
 * Prolyn Admin Panel - Kuyruk kaydı detayı / alıcıyı düzeltip tekrar gönder
 * Prolyn/p/settings/email_resend.php?id=QUEUE_ID
 *
 * Neden ayrı sayfa: projede modal kullanılmıyor; içerik görüntüleme ve tekrar gönderim burada.
 * Eski kayıt silinmez/değiştirilmez (iz kalır); yeni alıcıya kopyası kuyruğa eklenir.
 *
 * Güvenlik: alıcı değiştirme yalnızca kampanya / iletişim kaynaklarında açıktır. Şifre sıfırlama gibi
 * bağlantı veya anahtar taşıyan sistem e-postaları başka bir adrese yönlendirilemez (hesap ele geçirme riski);
 * bunlar yalnızca aynı alıcıya tekrar denenebilir.
 */
require_once '../config/header.php';
Auth::requireModule('manage_settings');
require_once __DIR__ . '/../functions/mailer.php';
require_once __DIR__ . '/../functions/lead.php';

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$id = (int)($_GET['id'] ?? 0);
$q = $id > 0 ? $db->getRowAssoc("SELECT * FROM mail_kuyrugu WHERE queue_id = ?", [$id]) : null;

// Alıcısı değiştirilebilen kaynaklar (beyaz liste)
$degistirilebilir = ['lead_kampanya', 'iletisim_yanit', 'iletisim_formu'];
$flash = null;

if ($q && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend') {
    $kanal = (string)($q['channel'] ?: 'email');
    $alici = strtolower(trim((string)($_POST['recipient'] ?? '')));
    if ($kanal === 'whatsapp') { $alici = preg_replace('/\D/', '', $alici); }
    $aliciDegisti = $alici !== strtolower(trim((string)$q['recipient']));
    $payload = json_decode((string)($q['payload'] ?? ''), true) ?: [];
    $leadId = (int)($payload['lead_id'] ?? 0);

    if ($kanal === 'email' && !filter_var($alici, FILTER_VALIDATE_EMAIL)) {
        $flash = ['danger', 'Geçerli bir e-posta adresi girin.'];
    } elseif ($kanal === 'whatsapp' && (strlen($alici) < 10 || strlen($alici) > 15)) {
        $flash = ['danger', 'Geçerli bir WhatsApp numarası girin (905xxxxxxxxx).'];
    } elseif ($aliciDegisti && !in_array($q['source'], $degistirilebilir, true)) {
        $flash = ['danger', 'Bu tür sistem e-postası başka bir adrese yönlendirilemez; yalnızca aynı alıcıya tekrar gönderilebilir.'];
    } elseif ($q['source'] === 'lead_kampanya' && $leadId > 0
              && ($l = $db->getRowAssoc("SELECT ret_tarihi, durum FROM potansiyel_musteriler WHERE lead_id = ?", [$leadId]))
              && (!empty($l['ret_tarihi']) || $l['durum'] === 'ret')) {
        $flash = ['danger', 'Bu firma iletişim istemediğini bildirdi; kampanya e-postası gönderilemez.'];
    } else {
        $payload['tekrar_of'] = (int)$q['queue_id'];
        if ($leadId > 0) {   // lead_son_kampanya_maili() eşleşmesi için anahtar sırası korunur
            $payload = ['headers' => $payload['headers'] ?? [], 'lead_id' => $leadId] + $payload;
        }
        $yeniId = $db->Insert("INSERT INTO mail_kuyrugu (channel, recipient, subject, message, source, status, attempts, payload, created_at) VALUES (?, ?, ?, ?, ?, 'pending', 0, ?, NOW())",
            [$kanal, $alici, $q['subject'], $q['message'], $q['source'], json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        if ($yeniId === false) {
            $flash = ['danger', 'Kuyruğa eklenemedi.'];
        } else {
            if ($q['status'] === 'pending') {
                $db->Update("UPDATE mail_kuyrugu SET status = 'cancelled', last_error = 'Yerine yeni kayıt gönderildi', processed_at = NOW() WHERE queue_id = ? AND status = 'pending'", [$id]);
            }
            // Lead kaydındaki adresi de düzelt (istenirse); gelecekteki e-postalar doğru adrese gitsin
            if ($aliciDegisti && $leadId > 0 && !empty($_POST['lead_guncelle'])) {
                $db->Update("UPDATE potansiyel_musteriler SET eposta = ? WHERE lead_id = ?", [$alici, $leadId]);
                lead_aktivite($db, $leadId, 'sistem', 'E-posta kuyruktan düzeltildi: ' . $q['recipient'] . ' → ' . $alici);
            }
            if ($leadId > 0) {
                lead_aktivite($db, $leadId, 'eposta', 'Kuyruktan tekrar gönderim: ' . $q['recipient'] . ($aliciDegisti ? ' → ' . $alici : '') . ' · konu: ' . $q['subject']);
            }
            if (function_exists('xlog')) { xlog('E-posta tekrar gönderimi', '#' . $id . ' → #' . $yeniId . ' (' . $q['recipient'] . ($aliciDegisti ? ' → ' . $alici : '') . ')'); }
            echo "<script>window.location.href='settings/email_queue.php?success=resend';</script>";
            exit;
        }
    }
}

require_once '../config/sidebar.php';

if (!$q) {
    echo '<div class="app-main flex-column flex-row-fluid"><div class="app-container container-xxl py-10"><div class="alert alert-warning">Kuyruk kaydı bulunamadı. <a href="settings/email_queue.php">Kuyruğa dön</a></div></div></div>';
    require_once '../config/footer.php';
    exit;
}

$payload = json_decode((string)($q['payload'] ?? ''), true) ?: [];
$leadId = (int)($payload['lead_id'] ?? 0);
$kanal = (string)($q['channel'] ?: 'email');
$kimlik = in_array($payload['kimlik'] ?? '', ['destek', 'bilgi', 'noreply'], true) ? $payload['kimlik'] : prolyn_mail_kimlik_kaynaktan((string)$q['source']);
$gonderen = prolyn_mail_ayarlari()['kimlikler'][$kimlik] ?? '';
$aliciSerbest = in_array($q['source'], $degistirilebilir, true);
$durumRenk = ['pending' => 'warning', 'sent' => 'success', 'completed' => 'success', 'failed' => 'danger', 'cancelled' => 'dark'][$q['status']] ?? 'primary';
?>
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
  <div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
      <div class="app-container container-fluid d-flex flex-stack flex-wrap gap-3">
        <div class="page-title d-flex flex-column me-3">
          <h1 class="page-heading text-gray-900 fw-bold fs-3 my-0">Kuyruk kaydı #<?= (int)$q['queue_id'] ?></h1>
          <span class="text-muted fs-7 fw-semibold mt-1">İçeriği inceleyin, gerekirse alıcıyı düzeltip tekrar gönderin</span>
        </div>
        <a href="settings/email_queue.php" class="btn btn-sm btn-light">← Kuyruğa dön</a>
      </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
      <div class="app-container container-fluid">
        <?php if ($flash): ?><div class="alert alert-<?= $e($flash[0]) ?> p-4 mb-6 fw-semibold"><?= $e($flash[1]) ?></div><?php endif; ?>

        <div class="row g-6">
          <div class="col-xl-7">
            <div class="card">
              <div class="card-header"><h3 class="card-title fw-bold">İçerik</h3></div>
              <div class="card-body">
                <div class="row g-4 mb-6 fs-7">
                  <div class="col-sm-6"><div class="text-muted fw-bold">Alıcı</div><div class="text-gray-900 fw-semibold"><?= $e($q['recipient']) ?></div></div>
                  <div class="col-sm-6"><div class="text-muted fw-bold">Gönderen</div><div class="text-gray-900 fw-semibold"><?= $kanal === 'email' ? $e($gonderen) . ' <span class="text-muted">(' . $e($kimlik) . ')</span>' : 'WhatsApp' ?></div></div>
                  <div class="col-sm-6"><div class="text-muted fw-bold">Kaynak</div><span class="badge badge-light-info text-uppercase"><?= $e($q['source']) ?></span></div>
                  <div class="col-sm-6"><div class="text-muted fw-bold">Durum</div><span class="badge badge-light-<?= $durumRenk ?> text-uppercase"><?= $e($q['status']) ?></span> <span class="text-muted">· <?= (int)$q['attempts'] ?> deneme</span></div>
                  <?php if ($q['status'] === 'sent'): ?><div class="col-12 text-muted fs-8">"Sent": e-posta sunucumuz iletiyi kabul etti. Alıcıya ulaşamazsa geri dönüş bildirimi <?= $e($gonderen) ?> kutusunun Gelen klasörüne gelir.</div><?php endif; ?>
                  <div class="col-sm-6"><div class="text-muted fw-bold">Kayıt</div><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?><?= !empty($q['processed_at']) ? ' · işlendi ' . date('d.m.Y H:i', strtotime($q['processed_at'])) : '' ?></div>
                  <?php if ($leadId > 0): ?><div class="col-sm-6"><div class="text-muted fw-bold">Lead</div><a href="crm/lead_detail.php?id=<?= $leadId ?>">#<?= $leadId ?> detayına git</a></div><?php endif; ?>
                </div>
                <?php if (!empty($q['last_error'])): ?>
                  <?php $notMu = $q['status'] === 'sent'; ?>
                  <div class="p-4 rounded border border-dashed mb-6 fs-7 <?= $notMu ? 'bg-light-warning text-warning border-warning' : 'bg-light-danger text-danger border-danger' ?>"><strong><?= $notMu ? 'Not:' : 'Son hata:' ?></strong> <?= $e($q['last_error']) ?></div>
                <?php endif; ?>
                <div class="text-muted fs-7 fw-bold mb-1">Konu</div>
                <div class="text-gray-900 fw-semibold mb-5"><?= $e($q['subject']) ?></div>
                <div class="text-muted fs-7 fw-bold mb-1">Metin</div>
                <div class="p-5 bg-light rounded border border-gray-300" style="max-height:480px; overflow-y:auto">
                  <pre class="mb-0 text-gray-800 fs-7" style="white-space:pre-wrap; word-break:break-word; font-family:inherit"><?= $e($q['message']) ?></pre>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-5">
            <form method="post" action="settings/email_resend.php?id=<?= (int)$q['queue_id'] ?>" class="card" id="resendForm">
              <?= csrfTokenField() ?><input type="hidden" name="action" value="resend">
              <div class="card-header"><h3 class="card-title fw-bold">Tekrar gönder</h3></div>
              <div class="card-body">
                <label class="form-label required"><?= $kanal === 'whatsapp' ? 'WhatsApp numarası' : 'Alıcı e-posta' ?></label>
                <input name="recipient" type="<?= $kanal === 'whatsapp' ? 'text' : 'email' ?>" class="form-control form-control-solid mb-2" value="<?= $e($q['recipient']) ?>"
                       <?= $aliciSerbest ? '' : 'readonly' ?> required autocomplete="off">
                <?php if ($aliciSerbest): ?>
                  <div class="form-text mb-5">Adres hatalı yazıldıysa (ör. <code>.te</code> yerine <code>.tr</code>) burada düzeltin. İçerik aynen yeni adrese gönderilir; eski kayıt geçmişte kalır.</div>
                  <?php if ($leadId > 0): ?>
                  <label class="form-check form-check-custom form-check-solid mb-5">
                    <input class="form-check-input" type="checkbox" name="lead_guncelle" value="1" checked>
                    <span class="form-check-label">Lead kaydındaki e-posta adresini de düzelt</span>
                  </label>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="form-text mb-5">Bu sistem e-postası güvenlik nedeniyle yalnızca aynı alıcıya tekrar gönderilebilir.</div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary w-100" id="resendBtn">Kuyruğa ekle ve gönder</button>
                <div class="text-muted fs-8 mt-3">Kuyruk dakikada bir işlenir.</div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once '../config/footer.php'; ?>
</div>
<script>
(function () {
  // Onay: SweetAlert2 (tarayıcı confirm() kullanılmaz)
  var f = document.getElementById('resendForm');
  if (!f) { return; }
  f.addEventListener('submit', function (ev) {
    if (f.dataset.onay === '1') { return; }
    ev.preventDefault();
    Swal.fire({
      title: 'Tekrar gönderilsin mi?', text: f.recipient.value + ' adresine bu içerik kuyruğa eklenecek.', icon: 'question',
      showCancelButton: true, confirmButtonText: 'Evet, gönder', cancelButtonText: 'Vazgeç', buttonsStyling: false,
      customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-2' }
    }).then(function (r) { if (r.isConfirmed) { f.dataset.onay = '1'; f.submit(); } });
  });
})();
</script>
