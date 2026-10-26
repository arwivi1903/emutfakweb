<?php
/**
 * Sağ alttaki hızlı iletişim butonları.
 * Numaralar Vitrin Yönetimi > SEO ve Temel Ayarlar > İletişim Bilgileri'nden gelir;
 * numara girilmemişse ilgili buton gösterilmez.
 */
$__fabTel = preg_replace('/[^0-9+]/', '', (string)($appSettings['contact_phone'] ?? ''));
$__fabWa  = preg_replace('/\D/', '', (string)($appSettings['contact_whatsapp'] ?? ''));
if ($__fabWa === '' && str_starts_with($__fabTel, '+905')) { $__fabWa = preg_replace('/\D/', '', $__fabTel); }
if ($__fabWa !== '' && strlen($__fabWa) === 11 && $__fabWa[0] === '0') { $__fabWa = '9' . $__fabWa; }
$__fabWaText = $fabWhatsappText ?? 'Merhaba, Prolyn hakkında bilgi almak istiyorum.';
?>
<div class="floating-action-buttons">
    <?php if ($__fabTel !== ''): ?>
    <a href="tel:<?= htmlspecialchars($__fabTel, ENT_QUOTES, 'UTF-8') ?>" class="fab-btn fab-phone" title="Bizi Arayın" aria-label="Bizi arayın">
        <i class="bi bi-telephone-fill"></i>
    </a>
    <?php endif; ?>
    <?php if ($__fabWa !== ''): ?>
    <a href="https://wa.me/<?= htmlspecialchars($__fabWa, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($__fabWaText) ?>" class="fab-btn fab-whatsapp" target="_blank" rel="noopener" title="WhatsApp" aria-label="WhatsApp ile yazın">
        <i class="bi bi-whatsapp"></i>
    </a>
    <?php endif; ?>
    <button class="fab-btn fab-scrolltop" onclick="window.scrollTo({top: 0, behavior: 'smooth'});" title="Yukarı Çık" aria-label="Yukarı çık">
        <i class="bi bi-arrow-up-short"></i>
    </button>
</div>
