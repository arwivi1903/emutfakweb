<?php
/**
 * Boş durum illüstrasyonu (zarf). Kullanım:
 *   $bosMetin = 'Henüz mesaj yok.'; require __DIR__ . '/partials/ill_bos.php';
 */
$bosMetin = isset($bosMetin) ? (string)$bosMetin : 'Henüz kayıt yok.';
?>
<div class="pl-bos">
    <svg class="pi" viewBox="0 0 200 130" role="img" aria-hidden="true">
        <ellipse class="f-soft" cx="100" cy="112" rx="70" ry="9"/>
        <g class="a-float" style="--d:0s">
            <rect class="f-card sh" x="42" y="38" width="116" height="72" rx="12"/>
            <path class="s-line" d="M46 44 L100 82 L154 44"/>
            <path class="f-soft" d="M42 50 L100 90 L158 50 V98 a12 12 0 0 1 -12 12 H54 a12 12 0 0 1 -12 -12 z" opacity=".8"/>
        </g>
        <circle class="f-c a-blink" cx="40" cy="30" r="4" style="--d:.2s"/>
        <circle class="f-a a-blink" cx="168" cy="24" r="3.5" style="--d:1s"/>
        <circle class="f-b a-blink" cx="150" cy="14" r="2.5" style="--d:1.6s"/>
    </svg>
    <p><?= htmlspecialchars($bosMetin, ENT_QUOTES, 'UTF-8') ?></p>
</div>
