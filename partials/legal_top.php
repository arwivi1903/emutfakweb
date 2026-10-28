<?php
/**
 * Yasal sayfa iskeleti - üst kısım.
 * Sayfa şunları tanımlamalıdır: $legalTitle, $legalDesc, $legalSlug (dosya adı, .php olmadan)
 * Ardından içerik yazılır ve partials/legal_bottom.php include edilir.
 */
require_once __DIR__ . '/../db.php';
$navActive = '';
$__lBase = rtrim((string)prolynweb_env('BASE_URL', ''), '/');
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light" data-tema="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php require __DIR__ . '/tema_head.php'; ?>
    <title><?= htmlspecialchars($legalTitle) ?> | Prolyn</title>
    <meta name="description" content="<?= htmlspecialchars($legalDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars(prolynweb_current_url()) ?>">
    <meta name="robots" content="index,follow">
    <link rel="icon" href="p/dist/logo/favicon.ico" sizes="any" />
    <link rel="icon" type="image/png" sizes="32x32" href="p/dist/logo/favicon-32x32.png" />
    <link rel="apple-touch-icon" href="p/dist/logo/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800&display=swap" />
    <link href="p/dist/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="p/dist/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css?v=12" rel="stylesheet" type="text/css" />
    <link href="assets/css/tema.css?v=2" rel="stylesheet" type="text/css" />
    <link href="assets/css/legal.css?v=1" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank">
    <div class="d-flex flex-column flex-root" id="kt_app_root">
<?php require __DIR__ . '/site_header.php'; ?>
        <main class="legal-page">
            <div class="container-xxl">
                <nav class="legal-crumb" aria-label="Sayfa yolu"><a href="index.php">Ana sayfa</a><span>/</span><span><?= htmlspecialchars($legalTitle) ?></span></nav>
                <div class="legal-layout">
                    <aside class="legal-side" aria-label="Yasal sayfalar">
                        <?php
                        $__legalLinks = [
                            'kvkk-aydinlatma-metni' => 'KVKK Aydınlatma Metni',
                            'gizlilik-politikasi'   => 'Gizlilik Politikası',
                            'cerez-politikasi'      => 'Çerez Politikası',
                            'kullanim-kosullari'    => 'Kullanım Koşulları',
                        ];
                        foreach ($__legalLinks as $__s => $__t): ?>
                        <a href="<?= $__s ?>.php" class="<?= $__s === $legalSlug ? 'is-active' : '' ?>"><?= $__t ?></a>
                        <?php endforeach; ?>
                    </aside>
                    <article class="legal-body glass-card">
                        <h1><?= htmlspecialchars($legalTitle) ?></h1>
                        <p class="legal-updated">Son güncelleme: <?= htmlspecialchars($legalUpdated ?? '28.10.2026') ?></p>
