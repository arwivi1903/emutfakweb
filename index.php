<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prolyn ERP - Yeni Nesil İşletme Yönetimi</title>
    <meta name="description" content="İşletmenizi geleceğe taşıyan, bulut tabanlı, performanslı ve esnek ERP çözümü.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo">Prolyn.</a>
            <nav class="nav-links">
                <a href="#ozellikler">Özellikler</a>
                <a href="pricing.php">Fiyatlandırma</a>
                <a href="contact.php">İletişim</a>
            </nav>
            <div class="nav-actions">
                <a href="#" class="btn btn-glass" style="margin-right: 10px;">Giriş Yap</a>
                <a href="pricing.php" class="btn btn-primary">Ücretsiz Dene</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content reveal">
                <div class="hero-badge">✨ ERP'nin Yeni Çağı Başlıyor</div>
                <h1>İşletmenizi <span class="text-gradient">Geleceğe</span> Taşıyın.</h1>
                <p>Karmaşık süreçlere son verin. Bulut tabanlı, ışık hızında ve tamamen izole mimari ile iş akışlarınızı anında dijitalleştirin.</p>
                <div class="hero-buttons">
                    <a href="pricing.php" class="btn btn-primary">Hemen Başla</a>
                    <a href="#ozellikler" class="btn btn-glass">Nasıl Çalışır?</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="ozellikler" class="section-padding">
        <div class="container">
            <div class="section-header reveal">
                <h2>Neden Prolyn?</h2>
                <p>Kurumsal seviyede güvenlik ve performansı bir araya getirdik.</p>
            </div>
            
            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-card reveal">
                    <div class="feature-icon">🚀</div>
                    <h3>Yüksek Performans</h3>
                    <p>PgBouncer ve Redis destekli altyapımızla binlerce veriyi milisaniyeler içinde işleyin. Connection kopmalarına son.</p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card reveal" style="transition-delay: 0.1s;">
                    <div class="feature-icon">🛡️</div>
                    <h3>Tam İzolasyon</h3>
                    <p>Her müşteriye (tenant) özel ayrı veritabanı (Multi-DB) mimarisi ile verileriniz %100 güvende ve izole.</p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card reveal" style="transition-delay: 0.2s;">
                    <div class="feature-icon">📊</div>
                    <h3>Gelişmiş Analitik</h3>
                    <p>Tüm hareketlerinizi anlık takip edin. Log ve denetim mekanizmaları sayesinde kim, ne zaman, ne yaptı her zaman bilin.</p>
                </div>
                <!-- Feature 4 -->
                <div class="feature-card reveal" style="transition-delay: 0.3s;">
                    <div class="feature-icon">🔄</div>
                    <h3>Kolay Entegrasyon</h3>
                    <p>E-Fatura, banka sistemleri ve 3. parti yazılımlarınızla sorunsuz entegre çalışacak modern API altyapısı.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="index.php" class="logo" style="margin-bottom: 1rem; display: block;">Prolyn.</a>
                    <p>Kurumsal kaynak planlamasında yeni nesil çözüm ortağınız.</p>
                </div>
                <div class="footer-col">
                    <h4>Ürün</h4>
                    <ul class="footer-links">
                        <li><a href="#ozellikler">Özellikler</a></li>
                        <li><a href="pricing.php">Fiyatlandırma</a></li>
                        <li><a href="#">Sürüm Notları</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Destek</h4>
                    <ul class="footer-links">
                        <li><a href="#">Dokümantasyon</a></li>
                        <li><a href="#">API Referansı</a></li>
                        <li><a href="contact.php">İletişim</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Yasal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Kullanım Koşulları</a></li>
                        <li><a href="#">Gizlilik Politikası</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> Prolyn ERP. Tüm hakları saklıdır.
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
