<?php
/**
 * Hesap sayfaları ortak başlığı (profil kartı + sekmeler).
 * overview.php, settings.php ve logs.php tarafından include edilir.
 */
$__e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Kullanıcı adı girişte oturuma alınmıyor; boşsa veritabanından bir kez yükle (e-posta şablonlarındaki {gonderen} için de gerekli)
if (empty($_SESSION['username']) && !empty($_SESSION['admin_id'])) {
    try {
        $__dbh = (isset($db) && $db instanceof Database) ? $db : new Database();
        $__u = $__dbh->getColumn("SELECT username FROM yoneticiler WHERE admin_id = ?", [(int)$_SESSION['admin_id']]);
        if (is_string($__u) && trim($__u) !== '') { $_SESSION['username'] = trim($__u); }
    } catch (Throwable $__ex) { /* kullanıcı adı alınamazsa sayfa yine de açılır */ }
}

$__sayfa = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$__ad = (string)($_SESSION['full_name'] ?? '');
$__bas = mb_strtoupper(mb_substr($__ad !== '' ? $__ad : 'P', 0, 1));
$__resim = trim((string)($_SESSION['admin_pic'] ?? ''));
$__aktif = (($_SESSION['status'] ?? '') === 'active');
$__son = !empty($_SESSION['last_login']) ? tr_datetime($_SESSION['last_login']) : '-';
$__sekmeler = ['overview.php' => 'Genel Bakış', 'settings.php' => 'Ayarlar', 'logs.php' => 'Kayıtlar'];
?>
<style>
    .hesap-kapak{height:110px;border-radius:.75rem .75rem 0 0;background:linear-gradient(120deg,#1b2a6b 0%,#3b6cf6 55%,#7aa2ff 100%)}
    .hesap-avatar{width:112px;height:112px;margin-top:-56px;border:4px solid var(--bs-body-bg,#fff);border-radius:50%;background:#e9eefc;color:#3b6cf6;font-size:2.6rem;font-weight:700;display:flex;align-items:center;justify-content:center;overflow:hidden;flex:none}
    .hesap-avatar img{width:100%;height:100%;object-fit:cover}
    .hesap-chip{display:inline-flex;align-items:center;gap:.4rem;font-size:.85rem}
</style>
<div class="card mb-5 mb-xl-8 overflow-hidden">
    <div class="hesap-kapak"></div>
    <div class="card-body pt-0 pb-0">
        <div class="d-flex flex-wrap flex-md-nowrap align-items-end gap-5 mb-5">
            <div class="hesap-avatar">
                <?php if ($__resim !== ''): ?>
                    <img src="<?= $__e($__resim) ?>" alt="Profil fotoğrafı">
                <?php else: ?>
                    <?= $__e($__bas) ?>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 pt-4">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <span class="fs-2 fw-bold text-gray-900"><?= $__e($__ad) ?></span>
                    <span class="badge badge-light-primary"><?= $__e(ucfirst((string)($_SESSION['role'] ?? 'Admin'))) ?></span>
                    <span class="badge <?= $__aktif ? 'badge-light-success' : 'badge-light-danger' ?>"><?= $__aktif ? 'Aktif' : 'Pasif' ?></span>
                </div>
                <div class="d-flex flex-wrap gap-5 text-gray-500 fw-semibold">
                    <?php if (!empty($_SESSION['username'])): ?><span class="hesap-chip">@<?= $__e($_SESSION['username']) ?></span><?php endif; ?>
                    <span class="hesap-chip"><?= $__e($_SESSION['email'] ?? '') ?></span>
                    <span class="hesap-chip">Son giriş: <?= $__e($__son) ?></span>
                </div>
            </div>
        </div>
        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
            <?php foreach ($__sekmeler as $__dosya => $__baslik): ?>
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 <?= $__sayfa === $__dosya ? 'active' : '' ?>" href="dist/account/<?= $__e($__dosya) ?>"><?= $__e($__baslik) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
