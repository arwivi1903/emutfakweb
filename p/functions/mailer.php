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

    function prolyn_send_mail(string $to, string $subject, string $body, bool $isHtml = false): bool
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

        // 2) Yedek: PHP mail()
        $headers = [];
        $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . ($isHtml ? 'text/html' : 'text/plain') . '; charset=UTF-8';
        $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
        if (!$ok) {
            error_log('[mailer] mail() gönderimi başarısız: ' . $to);
        }
        return $ok;
    }
}
