<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim - Prolyn ERP</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-form {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            padding: 3rem;
            border-radius: 24px;
            backdrop-filter: blur(12px);
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }
        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: var(--font-sans);
            transition: var(--transition-smooth);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 10px var(--accent-glow);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
    </style>
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
                <h1>Bizimle <span class="text-gradient">İletişime Geçin</span></h1>
                <p>Sorularınız veya özel kurumsal talepleriniz için uzman ekibimizle görüşün.</p>
            </div>

            <div class="contact-form reveal">
                <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mesajınız başarıyla gönderildi (Demo)');">
                    <div class="form-group">
                        <label for="name">Adınız Soyadınız</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-posta Adresiniz</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Mesajınız</label>
                        <textarea id="message" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Mesajı Gönder</button>
                </form>
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
