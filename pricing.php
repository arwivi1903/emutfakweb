<?php
require_once 'db.php';

// Veritabanından paket özelliklerini çek
$stmt = $pdo->prepare("SELECT * FROM plans WHERE is_active = 1 ORDER BY price_monthly ASC");
$stmt->execute();
$packages = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiyatlandırma - Prolyn ERP</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo">Prolyn.</a>
            <nav class="nav-links">
                <a href="index.php#ozellikler">Özellikler</a>
                <a href="pricing.php">Fiyatlandırma</a>
                <a href="contact.php">İletişim</a>
            </nav>
            <div class="nav-actions">
                <a href="#" class="btn btn-glass" style="margin-right: 10px;">Giriş Yap</a>
            </div>
        </div>
    </header>

    <section class="hero" style="min-height: auto; padding: 10rem 0 5rem;">
        <div class="container">
            <div class="section-header reveal">
                <h1>Sade ve Şeffaf <span class="text-gradient">Fiyatlandırma</span></h1>
                <p>İşletmenizin ölçeğine en uygun planı seçin. Gizli ücret yok.</p>
            </div>

            <div class="pricing-grid">
                <?php foreach($packages as $pkg): 
                    $planCode = $pkg['code'];
                    $isPopular = ($planCode === 'pro' || strpos(strtolower($pkg['name']), 'pro') !== false);
                ?>
                <div class="pricing-card reveal <?php echo $isPopular ? 'popular' : ''; ?>">
                    <div class="pricing-header">
                        <h3 class="pricing-title"><?php echo htmlspecialchars($pkg['name']); ?></h3>
                        <div class="pricing-price">
                            ₺<?php echo number_format($pkg['price_monthly'], 2, ',', '.'); ?>
                            <span>/ Aylık</span>
                        </div>
                        <?php if(!empty($pkg['description'])): ?>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 10px;"><?php echo htmlspecialchars($pkg['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <ul class="pricing-features">
                        <li><strong><?php echo $pkg['max_users'] > 0 ? $pkg['max_users'] : 'Sınırsız'; ?></strong> Kullanıcı Kapasitesi</li>
                        <li><strong><?php echo $pkg['max_branches'] > 0 ? $pkg['max_branches'] : 'Sınırsız'; ?></strong> Şube Desteği</li>
                        <li>Ön Muhasebe & Finans</li>
                        <li>Gelişmiş Raporlama</li>
                        <li>7/24 Teknik Destek</li>
                    </ul>

                    <a href="contact.php?plan=<?php echo urlencode($planCode); ?>" class="btn <?php echo $isPopular ? 'btn-primary' : 'btn-glass'; ?>">
                        Hemen Başla
                    </a>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($packages)): ?>
                    <p style="text-align:center; grid-column: 1/-1;">Paket verileri yüklenemedi veya bulunmuyor.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> Prolyn ERP. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
