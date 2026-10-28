<?php
/**
 * Veri sorumlusu bilgileri (yasal sayfalarda ortak).
 * Kaynak: sistem_ayarlari (kvkk_* anahtarları). Boş olan satır gösterilmez;
 * böylece uydurma bir ünvan/adres yayınlanmaz. Canlıya almadan önce
 * p/database/2026_10_28_kvkk_ve_site_metinleri.sql içindeki değerleri doldurun.
 */
$__vs = [
    'Ticaret ünvanı'         => trim((string)($appSettings['kvkk_unvan'] ?? '')),
    'Adres'                  => trim((string)($appSettings['kvkk_adres'] ?? ($appSettings['contact_address'] ?? ''))),
    'MERSİS / Vergi No'      => trim((string)($appSettings['kvkk_mersis'] ?? '')),
    'KVKK başvuru e-postası' => trim((string)($appSettings['kvkk_basvuru_eposta'] ?? ($appSettings['contact_email'] ?? ''))),
    'Telefon'                => trim((string)($appSettings['contact_phone'] ?? '')),
];
$__vs = array_filter($__vs, static fn($v) => $v !== '');
if (empty($__vs['KVKK başvuru e-postası'])) { $__vs['KVKK başvuru e-postası'] = 'info@prolyn.net'; }
?>
<dl class="legal-dl">
    <?php foreach ($__vs as $k => $v): ?>
    <div><dt><?= htmlspecialchars($k) ?></dt><dd><?= nl2br(htmlspecialchars($v)) ?></dd></div>
    <?php endforeach; ?>
</dl>
