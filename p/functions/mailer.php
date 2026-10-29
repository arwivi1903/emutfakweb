<?php
/**
 * Prolyn - E-posta gönderici
 * SMTP ayarları .env'den okunur.
 * PHPMailer kuruluysa (composer require phpmailer/phpmailer) onu kullanır;
 * yoksa PHP mail() fonksiyonuna düşer.
 *
 * @return bool Gönderim başarılıysa true
 */

require_once __DIR__ . '/../../env.php';

if (!function_exists('prolyn_send_mail')) {

    /**
     * PHPMailer olmadan yerleşik, bağımlılıksız SMTP istemcisi (AUTH LOGIN, SSL/STARTTLS).
     */
    function prolyn_smtp_send(string $host, int $port, string $secure, string $user, string $pass,
                              string $fromEmail, string $fromName, string $to, string $subject,
                              string $body, bool $isHtml, array $extraHeaders = []): bool
    {
        $clean = function (string $v): string { return trim(preg_replace('/[\r\n]+/', ' ', $v)); };
        $to = $clean($to); $fromEmail = $clean($fromEmail); $fromName = $clean($fromName); $subject = $clean($subject);
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
        $cmd = function (string $c, array $ok) use ($fp, $read): bool {
            fwrite($fp, $c . "\r\n");
            $r = $read();
            return in_array(substr($r, 0, 3), $ok, true);
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
                if (!$cmd(base64_encode($pass), ['235'])) { throw new Exception('PASS'); }
            }
            if (!$cmd("MAIL FROM:<$fromEmail>", ['250'])) { throw new Exception('MAIL FROM'); }
            if (!$cmd("RCPT TO:<$to>", ['250', '251'])) { throw new Exception('RCPT'); }
            if (!$cmd('DATA', ['354'])) { throw new Exception('DATA'); }
            $hdr  = 'Date: ' . date('r') . "\r\n";
            $hdr .= 'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <$fromEmail>\r\n";
            $hdr .= "To: <$to>\r\n";
            $hdr .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
            $hdr .= 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . ">\r\n";
            foreach ($extraHeaders as $hk => $hv) {
                if (preg_match('/^[A-Za-z0-9-]+$/', (string)$hk)) { $hdr .= $hk . ': ' . $clean((string)$hv) . "\r\n"; }
            }
            $hdr .= "MIME-Version: 1.0\r\n";
            $hdr .= 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . "; charset=UTF-8\r\n";
            $hdr .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $data = $hdr . chunk_split(base64_encode($body), 76, "\r\n");
            fwrite($fp, $data . "\r\n.\r\n");
            $ok = substr($read(), 0, 3) === '250';
            $cmd('QUIT', ['221']);
            fclose($fp);
            return $ok;
        } catch (\Throwable $e) {
            error_log('[mailer] SMTP hatası: ' . $e->getMessage());
            @fclose($fp);
            return false;
        }
    }

    function prolyn_send_mail(string $to, string $subject, string $body, bool $isHtml = false, array $extraHeaders = []): bool
    {
        $fromEmail = prolynweb_env('MAIL_FROM', 'noreply@prolyn.net');
        $fromName  = prolynweb_env('MAIL_FROM_NAME', 'Prolyn');
        $host      = prolynweb_env('MAIL_HOST', '');
        $port      = (int)prolynweb_env('MAIL_PORT', 587);
        $secure    = prolynweb_env('MAIL_SECURE', 'tls');
        $user      = prolynweb_env('MAIL_USER', '');
        $pass      = prolynweb_env('MAIL_PASS', '');

        // 1) PHPMailer varsa onu kullan (önerilen)
        $phpmailerClass = 'PHPMailer\\PHPMailer\\PHPMailer';
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (is_readable($autoload)) {
            require_once $autoload;
        }

        if ($host !== '' && class_exists($phpmailerClass)) {
            try {
                $mail = new $phpmailerClass(true);
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->Port       = $port;
                $mail->SMTPAuth   = ($user !== '');
                if ($user !== '') {
                    $mail->Username = $user;
                    $mail->Password = $pass;
                }
                if ($secure === 'ssl') {
                    $mail->SMTPSecure = 'ssl';
                } elseif ($secure === 'tls') {
                    $mail->SMTPSecure = 'tls';
                }
                $mail->CharSet = 'UTF-8';
                foreach ($extraHeaders as $hk => $hv) {
                    if (preg_match('/^[A-Za-z0-9-]+$/', (string)$hk)) { $mail->addCustomHeader((string)$hk, preg_replace('/[\r\n]+/', ' ', (string)$hv)); }
                }
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                if ($isHtml) {
                    $mail->isHTML(true);
                    $mail->Body = $body;
                    $mail->AltBody = strip_tags($body);
                } else {
                    $mail->Body = $body;
                }
                return $mail->send();
            } catch (\Throwable $e) {
                error_log('[mailer] PHPMailer hatası: ' . $e->getMessage());
                // mail() fallback'a düş
            }
        }

        // 1b) PHPMailer yoksa yerleşik SMTP istemcisi
        if ($host !== '' && !class_exists($phpmailerClass)) {
            if (prolyn_smtp_send($host, $port, (string)$secure, (string)$user, (string)$pass,
                                 $fromEmail, $fromName, $to, $subject, $body, $isHtml, $extraHeaders)) {
                return true;
            }
            // SMTP başarısızsa mail() yedeğine düş
        }

        // 2) Yedek: PHP mail()
        // Header enjeksiyonuna karşı: konu/alıcıdaki satır sonlarını temizle
        // (mail() başlıkları CRLF ile ayırır; içeride CRLF olursa ek başlık
        // enjekte edilebilir - örn. gizli Bcc:).
        $to = preg_replace('/[\r\n]+/', ' ', $to);
        $subject = preg_replace('/[\r\n]+/', ' ', $subject);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log('[mailer] Geçersiz alıcı adresi: ' . $to);
            return false;
        }

        $headers = [];
        $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
        foreach ($extraHeaders as $hk => $hv) {
            if (preg_match('/^[A-Za-z0-9-]+$/', (string)$hk)) { $headers[] = $hk . ': ' . preg_replace('/[\r\n]+/', ' ', (string)$hv); }
        }
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8';
        $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
        if (!$ok) {
            error_log('[mailer] mail() gönderimi başarısız: ' . $to);
        }
        return $ok;
    }
}
