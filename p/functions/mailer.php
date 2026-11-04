<?php
/**
 * Prolyn - E-posta gönderici
 * --------------------------
 * SMTP ve gönderen adresleri artık panel > Mail Ayarları (system_settings) üzerinden okunur;
 * panelde boş bırakılan alan .env değerine düşer (geriye uyum).
 *
 * Gönderen kimlikleri (kimlik → adres):
 *   destek  → support_email  (lead / kampanya e-postaları; yanıtlar destek kutusuna gelir)
 *   bilgi   → from_email     (iletişim formu yanıtları, bilgilendirme)
 *   noreply → noreply_email  (sistem bildirimleri, geri dönüşsüz e-postalar)
 * Kuyruktaki kaydın "source" alanı kimliği belirler (bkz. prolyn_mail_kimlik_kaynaktan).
 *
 * Yerleşik SMTP istemcisi kullanılır (SMTP ayarı yoksa PHP mail()). Gönderilen her iletinin birebir kopyası
 * IMAP APPEND ile gönderen kutusunun "Gönderilenler" klasörüne eklenir (panelden kapatılabilir).
 */

require_once __DIR__ . '/../../env.php';

if (!function_exists('prolyn_send_mail')) {

    /** Kaynak (mail_kuyrugu.source) → gönderen kimliği. Bilinmeyen kaynak güvenli tarafta kalır: noreply. */
    function prolyn_mail_kimlik_kaynaktan(string $source): string
    {
        switch ($source) {
            case 'lead_kampanya':
                return 'destek';
            case 'iletisim_yanit':
            case 'bilgi':
                return 'bilgi';
            default:
                return 'noreply';
        }
    }

    /** Ayar okumak için ayrı, hafif PDO (worker CLI'da ve site tarafında Database sınıfı olmayabilir). */
    function prolyn_mail_pdo(): ?PDO
    {
        static $pdo = false;
        if ($pdo !== false) { return $pdo; }
        try {
            $pdo = new PDO(
                'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';port=' . prolynweb_env('DB_PORT', '3306')
                    . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
                (string)prolynweb_env('DB_USER', ''), (string)prolynweb_env('DB_PASS', ''),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Throwable $e) {
            error_log('[mailer] ayar DB bağlantısı kurulamadı: ' . $e->getMessage());
            $pdo = null;
        }
        return $pdo;
    }

    /**
     * Bir kerelik ayar göçü (mail_kimlik_surum=1): gönderen adreslerini destek / info / noreply
     * düzenine çeker ve WhatsApp API URL'sini Meta Cloud API kalıbıyla doldurur.
     * Yalnızca boş veya eski varsayılan değerdeki alanlara dokunur; elle girilmiş değer korunur.
     */
    function prolyn_mail_ayar_gocu(PDO $pdo): void
    {
        static $yapildi = false;
        if ($yapildi) { return; }
        $yapildi = true;
        try {
            $st = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'mail_kimlik_surum'");
            $st->execute();
            if ($st->fetchColumn() !== false) { return; }

            $mevcut = [];
            foreach ($pdo->query("SELECT setting_key, setting_value FROM system_settings") as $r) {
                $mevcut[$r['setting_key']] = trim((string)$r['setting_value']);
            }
            // anahtar => [yeni değer, bu değerlerden biriyse (veya boşsa) üzerine yaz]
            $plan = [
                'from_email'       => ['info@prolyn.net',    ['noreply@prolyn.net', 'destek@prolyn.net']],
                'support_email'    => ['destek@prolyn.net',  []],
                'noreply_email'    => ['noreply@prolyn.net', []],
                'notify_email'     => ['info@prolyn.net',    ['destek@prolyn.net', 'noreply@prolyn.net']],
                'whatsapp_api_url' => ['https://graph.facebook.com/v23.0/{phone_id}/messages', []],
            ];
            $up = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
                                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($plan as $k => [$yeni, $eskiVarsayilan]) {
                $v = $mevcut[$k] ?? '';
                if ($v === '' || in_array(strtolower($v), $eskiVarsayilan, true)) { $up->execute([$k, $yeni]); }
            }
            $up->execute(['mail_kimlik_surum', '1']);
        } catch (Throwable $e) {
            error_log('[mailer] ayar göçü yapılamadı: ' . $e->getMessage());   // tablo yoksa vb.; .env'e düşülür
        }
    }

    /** Panel ayarları + .env yedeği. Her istek / worker çalışmasında bir kez okunur. */
    function prolyn_mail_ayarlari(bool $yenile = false): array
    {
        static $c = null;
        if ($c !== null && !$yenile) { return $c; }
        $db = [];
        $pdo = prolyn_mail_pdo();
        if ($pdo) {
            prolyn_mail_ayar_gocu($pdo);
            try {
                foreach ($pdo->query("SELECT setting_key, setting_value FROM system_settings") as $r) {
                    $db[$r['setting_key']] = trim((string)$r['setting_value']);
                }
            } catch (Throwable $e) { /* tablo yoksa .env kullanılır */ }
        }
        $al = static function (string $k, string $envK, string $vars) use ($db): string {
            if (($db[$k] ?? '') !== '') { return $db[$k]; }
            return $envK !== '' ? trim((string)prolynweb_env($envK, $vars)) : $vars;
        };
        $adres = static function (string $v, string $vars): string {
            return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : $vars;
        };
        return $c = [
            'host'      => $al('smtp_host', 'MAIL_HOST', ''),
            'port'      => (int)$al('smtp_port', 'MAIL_PORT', '587'),
            'secure'    => $al('smtp_secure', 'MAIL_SECURE', 'tls'),
            'user'      => $al('smtp_user', 'MAIL_USER', ''),
            'pass'      => $al('smtp_pass', 'MAIL_PASS', ''),
            'from_name' => $al('from_name', 'MAIL_FROM_NAME', 'Prolyn'),
            'kimlikler' => [
                'bilgi'   => $adres($al('from_email', '', ''), 'info@prolyn.net'),
                'destek'  => $adres($al('support_email', '', ''), 'destek@prolyn.net'),
                'noreply' => $adres($al('noreply_email', '', ''), 'noreply@prolyn.net'),
            ],
            'notify_email' => $adres($al('notify_email', 'CONTACT_NOTIFY_TO', ''), 'info@prolyn.net'),
            // Kimlik başına posta kutusu şifresi (isteğe bağlı). Doluysa SMTP ve IMAP o adresle oturum açar:
            // gönderen adres = oturum adresi olur ve kopya o kutunun "Gönderilenler" klasörüne düşer.
            'kimlik_sifre' => [
                'bilgi'   => $db['mail_pass_bilgi'] ?? '',
                'destek'  => $db['mail_pass_destek'] ?? '',
                'noreply' => $db['mail_pass_noreply'] ?? '',
            ],
            // "Gönderilenler" kopyası (IMAP APPEND)
            'kopya' => [
                'enabled' => ($db['mail_sent_copy'] ?? '1') === '1',
                'host'    => $al('imap_host', 'IMAP_HOST', '') ?: $al('smtp_host', 'MAIL_HOST', ''),
                'port'    => (int)($al('imap_port', 'IMAP_PORT', '993') ?: 993),
                'klasor'  => $al('imap_sent_folder', '', ''),   // boşsa sunucudan otomatik bulunur (\Sent)
            ],
            'whatsapp' => [
                'enabled'  => ($db['whatsapp_enabled'] ?? '0') === '1',
                'api_url'  => $al('whatsapp_api_url', 'WHATSAPP_API_URL', ''),
                'phone_id' => $al('whatsapp_phone_id', 'WHATSAPP_PHONE_ID', ''),
                'token'    => $al('whatsapp_token', 'WHATSAPP_TOKEN', ''),
                'notify'   => preg_replace('/\D/', '', $al('whatsapp_notify_number', '', '')),
            ],
        ];
    }

    /** Son gönderim hatası (kuyrukta last_error alanına yazılır; şifre içermez). */
    function prolyn_mail_son_hata(?string $yeni = null): string
    {
        static $h = '';
        if ($yeni !== null) { $h = $yeni; }
        return $h;
    }

    /** Son "Gönderilenler" kopyası sonucu: '' = başarılı veya kapalı, aksi halde hata metni. */
    function prolyn_mail_kopya_hata(?string $yeni = null): string
    {
        static $h = '';
        if ($yeni !== null) { $h = $yeni; }
        return $h;
    }

    /**
     * Tam RFC 5322 iletisini üretir (SMTP DATA ve IMAP APPEND aynı metni kullanır; kopya birebir aynı olur).
     * Satır sonları CRLF; gövde base64 (nokta doldurma / 8bit sorunları olmaz).
     */
    function prolyn_mail_mesaj_olustur(string $fromEmail, string $fromName, string $to, string $subject,
                                       string $body, bool $isHtml, array $extraHeaders): string
    {
        $clean = static function (string $v): string { return trim(preg_replace('/[\r\n]+/', ' ', $v)); };
        $alan = substr(strrchr($fromEmail, '@') ?: '@prolyn.net', 1);
        $hdr  = 'Date: ' . date('r') . "\r\n";
        $hdr .= 'From: =?UTF-8?B?' . base64_encode($clean($fromName)) . '?= <' . $clean($fromEmail) . ">\r\n";
        $hdr .= 'To: <' . $clean($to) . ">\r\n";
        $hdr .= 'Subject: =?UTF-8?B?' . base64_encode($clean($subject)) . "?=\r\n";
        $hdr .= 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $alan . ">\r\n";
        foreach ($extraHeaders as $hk => $hv) {
            if (preg_match('/^[A-Za-z0-9-]+$/', (string)$hk)) { $hdr .= $hk . ': ' . $clean((string)$hv) . "\r\n"; }
        }
        $hdr .= "MIME-Version: 1.0\r\n";
        $hdr .= 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";
        $hdr .= "Content-Transfer-Encoding: base64\r\n\r\n";
        return $hdr . rtrim(chunk_split(base64_encode($body), 76, "\r\n")) . "\r\n";
    }

    /**
     * Yerleşik, bağımlılıksız SMTP istemcisi (AUTH LOGIN, SSL/STARTTLS). Hazır iletiyi ($raw) gönderir.
     */
    function prolyn_smtp_send(string $host, int $port, string $secure, string $user, string $pass,
                              string $fromEmail, string $to, string $raw): bool
    {
        $to = trim(preg_replace('/[\r\n]+/', ' ', $to));
        $fromEmail = trim(preg_replace('/[\r\n]+/', ' ', $fromEmail));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('[mailer] SMTP: geçersiz adres'); return false;
        }
        $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
        $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) { error_log("[mailer] SMTP bağlantı hatası: $errstr"); return false; }
        stream_set_timeout($fp, 20);
        $read = function () use ($fp): string {
            $out = '';
            while (($line = fgets($fp, 515)) !== false) {
                $out .= $line;
                if (strlen($line) < 4 || $line[3] === ' ') { break; }
            }
            return $out;
        };
        $son = '';
        $cmd = function (string $c, array $ok) use ($fp, $read, &$son): bool {
            fwrite($fp, $c . "\r\n");
            $son = $read();
            return in_array(substr($son, 0, 3), $ok, true);
        };
        try {
            if (substr($read(), 0, 3) !== '220') { throw new Exception('banner'); }
            $ehlo = 'prolyn.net';
            if (!$cmd("EHLO $ehlo", ['250'])) { throw new Exception('EHLO'); }
            if ($secure === 'tls') {
                if (!$cmd('STARTTLS', ['220'])) { throw new Exception('STARTTLS'); }
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { throw new Exception('TLS'); }
                if (!$cmd("EHLO $ehlo", ['250'])) { throw new Exception('EHLO2'); }
            }
            if ($user !== '') {
                if (!$cmd('AUTH LOGIN', ['334'])) { throw new Exception('AUTH'); }
                if (!$cmd(base64_encode($user), ['334'])) { throw new Exception('USER'); }
                if (!$cmd(base64_encode($pass), ['235'])) { throw new Exception('PASS (kullanıcı/şifre hatalı)'); }
            }
            if (!$cmd("MAIL FROM:<$fromEmail>", ['250'])) { throw new Exception('MAIL FROM reddedildi: ' . trim($son)); }
            if (!$cmd("RCPT TO:<$to>", ['250', '251'])) { throw new Exception('Alıcı reddedildi: ' . trim($son)); }
            if (!$cmd('DATA', ['354'])) { throw new Exception('DATA'); }
            fwrite($fp, $raw . "\r\n.\r\n");
            $son = $read();
            $ok = substr($son, 0, 3) === '250';
            if (!$ok) { prolyn_mail_son_hata('Sunucu iletiyi kabul etmedi: ' . mb_substr(trim($son), 0, 180)); }
            $cmd('QUIT', ['221']);
            fclose($fp);
            return $ok;
        } catch (\Throwable $e) {
            prolyn_mail_son_hata('SMTP: ' . mb_substr($e->getMessage(), 0, 200));
            error_log('[mailer] SMTP hatası: ' . $e->getMessage());
            @fclose($fp);
            return false;
        }
    }

    /**
     * Yerleşik IMAP istemcisi: gönderilen iletiyi posta kutusunun "Gönderilenler" klasörüne ekler (APPEND).
     * imap eklentisi gerekmez (paylaşımlı hostingte çoğu zaman yoktur). Port 993 = SSL, 143 = STARTTLS.
     * Klasör ayarda verilmemişse LIST yanıtındaki \Sent işaretiyle, yoksa yaygın adlarla bulunur.
     * @return array [bool başarı, string hata]
     */
    function prolyn_imap_gonderilenlere_ekle(string $host, int $port, string $user, string $pass, string $klasor, string $raw): array
    {
        if ($host === '' || $user === '') { return [false, 'IMAP sunucusu / kullanıcı yok']; }
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
        $fp = @stream_socket_client(($port === 993 ? 'ssl://' : 'tcp://') . $host . ':' . $port, $no, $str, 10, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) { return [false, 'IMAP bağlantısı kurulamadı: ' . $str]; }
        stream_set_timeout($fp, 15);
        $n = 0;
        // Etiketli komut: etiketle başlayan satıra kadar okur; [OK mu, tüm yanıt]
        $komut = static function (string $c, bool $literalBekle = false) use ($fp, &$n): array {
            $etiket = 'P' . (++$n);
            fwrite($fp, $etiket . ' ' . $c . "\r\n");
            $out = '';
            while (($line = fgets($fp, 8192)) !== false) {
                $out .= $line;
                if ($literalBekle && strncmp($line, '+', 1) === 0) { return [true, $out, '+']; }   // sunucu literal bekliyor
                if (strncmp($line, $etiket . ' ', strlen($etiket) + 1) === 0) {
                    return [stripos($line, $etiket . ' OK') === 0, $out, $etiket];
                }
            }
            return [false, $out ?: 'zaman aşımı', $etiket];
        };
        $tirnak = static fn(string $v): string => '"' . addcslashes($v, "\\\"") . '"';
        try {
            fgets($fp, 8192);   // karşılama satırı
            if ($port !== 993) {
                [$ok] = $komut('STARTTLS');
                if (!$ok || !stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { throw new Exception('STARTTLS'); }
            }
            [$ok] = $komut('LOGIN ' . $tirnak($user) . ' ' . $tirnak($pass));
            if (!$ok) { throw new Exception('IMAP oturumu açılamadı (kullanıcı/şifre)'); }

            if ($klasor === '') {
                [, $liste] = $komut('LIST "" "*"');
                $adaylar = [];
                foreach (preg_split('/\r\n/', $liste) as $satir) {
                    if (!preg_match('/^\* LIST \(([^)]*)\) (?:"[^"]*"|NIL) (?:"((?:[^"\\\\]|\\\\.)*)"|(\S+))$/i', $satir, $m)) { continue; }
                    $ad = ($m[2] ?? '') !== '' ? stripcslashes($m[2]) : ($m[3] ?? '');
                    if (stripos($m[1], '\\Sent') !== false) { $klasor = $ad; break; }
                    $adaylar[] = $ad;
                }
                if ($klasor === '') {
                    foreach ($adaylar as $ad) {
                        if (preg_match('/(^|[.\/])(Sent|Sent Items|Sent Messages)$/i', $ad)) { $klasor = $ad; break; }
                    }
                }
                if ($klasor === '') { $klasor = 'INBOX.Sent'; }   // cPanel (Dovecot) varsayılanı
            }

            [, $yanit, $tip] = $komut('APPEND ' . $tirnak($klasor) . ' (\\Seen) {' . strlen($raw) . '}', true);
            if ($tip !== '+') {
                throw new Exception('APPEND reddedildi (' . $klasor . '): ' . mb_substr(trim($yanit), 0, 150));
            }
            fwrite($fp, $raw . "\r\n");
            $out = '';
            $etiket = 'P' . $n;
            while (($line = fgets($fp, 8192)) !== false) {
                $out .= $line;
                if (strncmp($line, $etiket . ' ', strlen($etiket) + 1) === 0) { break; }
            }
            if (stripos($out, $etiket . ' OK') === false) { throw new Exception('APPEND tamamlanamadı (' . $klasor . '): ' . mb_substr(trim($out), 0, 150)); }
            $komut('LOGOUT');
            fclose($fp);
            return [true, ''];
        } catch (\Throwable $e) {
            @fclose($fp);
            error_log('[mailer] IMAP kopya hatası: ' . $e->getMessage());
            return [false, mb_substr($e->getMessage(), 0, 200)];
        }
    }

    /**
     * @param string $kimlik 'destek' | 'bilgi' | 'noreply' — gönderen adresini seçer (bkz. dosya başı)
     * Başarılı gönderimden sonra (ayar açıksa) kopyayı gönderen kutusunun "Gönderilenler" klasörüne ekler;
     * kopya hatası gönderimi başarısız saymaz, prolyn_mail_kopya_hata() ile okunur.
     */
    function prolyn_send_mail(string $to, string $subject, string $body, bool $isHtml = false, array $extraHeaders = [], string $kimlik = 'noreply'): bool
    {
        prolyn_mail_son_hata('');
        prolyn_mail_kopya_hata('');
        $ay = prolyn_mail_ayarlari();
        if (!isset($ay['kimlikler'][$kimlik])) { $kimlik = 'noreply'; }
        $fromEmail = $ay['kimlikler'][$kimlik];
        $fromName  = $ay['from_name'];
        $host      = $ay['host'];
        $port      = $ay['port'];
        $secure    = $ay['secure'];
        // Kimliğin kendi şifresi varsa o kutuyla oturum aç (From = oturum adresi; sunucu reddetmez)
        $kimlikSifre = (string)($ay['kimlik_sifre'][$kimlik] ?? '');
        $user = $kimlikSifre !== '' ? $fromEmail : $ay['user'];
        $pass = $kimlikSifre !== '' ? $kimlikSifre : $ay['pass'];

        // From/Sender/To/Cc/Bcc başlıkları dışarıdan ezilemez; Reply-To yalnızca geçerli tek adresse kabul edilir
        $temizBaslik = [];
        foreach ($extraHeaders as $hk => $hv) {
            $hk = (string)$hk;
            if (!preg_match('/^[A-Za-z0-9-]+$/', $hk) || in_array(strtolower($hk), ['from', 'sender', 'to', 'bcc', 'cc', 'date', 'message-id', 'subject'], true)) { continue; }
            if (strtolower($hk) === 'reply-to') {
                $rt = trim(preg_replace('/[\r\n]+/', ' ', (string)$hv));
                if (filter_var($rt, FILTER_VALIDATE_EMAIL)) { $temizBaslik['Reply-To'] = $rt; }
                continue;
            }
            $temizBaslik[$hk] = (string)$hv;
        }

        $to = trim(preg_replace('/[\r\n]+/', ' ', $to));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            prolyn_mail_son_hata('Geçersiz alıcı adresi');
            error_log('[mailer] Geçersiz alıcı adresi: ' . $to);
            return false;
        }
        $raw = prolyn_mail_mesaj_olustur($fromEmail, $fromName, $to, $subject, $body, $isHtml, $temizBaslik);
        $gitti = false;

        // 1) SMTP (yerleşik istemci). PHPMailer artık gerekmiyor; kopya için iletinin ham hali elimizde olmalı.
        if ($host !== '') {
            $gitti = prolyn_smtp_send($host, $port, (string)$secure, (string)$user, (string)$pass, $fromEmail, $to, $raw);
            if (!$gitti && prolyn_mail_son_hata() === '') {
                prolyn_mail_son_hata('SMTP gönderimi başarısız (' . $host . ':' . $port . ', gönderen ' . $fromEmail . ')');
            }
        }

        // 2) Yedek: PHP mail() — yalnızca SMTP ayarı hiç yoksa (SMTP hata verdiyse aynı iletiyi
        // ikinci kez, kimliği doğrulanmamış yoldan göndermeyiz: spam'e düşer ve çift gönderim olur).
        if (!$gitti && $host === '') {
            [$hdrBlok, $govde] = explode("\r\n\r\n", $raw, 2);
            $hdrSatir = array_filter(explode("\r\n", $hdrBlok), static function ($l) {
                return stripos($l, 'To:') !== 0 && stripos($l, 'Subject:') !== 0;   // mail() bunları kendisi ekler
            });
            $gitti = @mail($to, '=?UTF-8?B?' . base64_encode(trim(preg_replace('/[\r\n]+/', ' ', $subject))) . '?=', $govde, implode("\r\n", $hdrSatir));
            if (!$gitti) {
                prolyn_mail_son_hata('mail() gönderimi başarısız (SMTP ayarı boş)');
                error_log('[mailer] mail() gönderimi başarısız: ' . $to);
            }
        }
        if (!$gitti) { return false; }
        prolyn_mail_son_hata('');

        // 3) Gönderilenler kopyası (best-effort)
        if ($ay['kopya']['enabled']) {
            [$kok, $khata] = prolyn_imap_gonderilenlere_ekle($ay['kopya']['host'], $ay['kopya']['port'], (string)$user, (string)$pass, $ay['kopya']['klasor'], $raw);
            if (!$kok) { prolyn_mail_kopya_hata($khata); }
        }
        return true;
    }
}
