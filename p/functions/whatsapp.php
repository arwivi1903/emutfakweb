<?php
/**
 * Prolyn - WhatsApp Business Cloud API (Meta) gönderici
 * -----------------------------------------------------
 * Ayarlar: panel > Mail Ayarları > WhatsApp (system_settings):
 *   whatsapp_api_url  : https://graph.facebook.com/v23.0/{phone_id}/messages  ({phone_id} otomatik doldurulur)
 *   whatsapp_phone_id : Meta > WhatsApp > API Setup'taki "Phone number ID"
 *   whatsapp_token    : Kalıcı erişim anahtarı (System User token)
 *
 * Meta kuralı: işletmenin başlattığı mesaj ancak ONAYLI ŞABLON ile gider. Serbest metin (type=text)
 * yalnızca alıcı son 24 saat içinde işletme numarasına yazdıysa teslim edilir.
 * Bu nedenle test düğmesi Meta'nın her hesaba verdiği "hello_world" şablonunu kullanır.
 *
 * Bu dosya functions/combine.php ile otomatik yüklenir; yan etkisi yoktur.
 */

require_once __DIR__ . '/mailer.php';

if (!function_exists('prolyn_whatsapp_gonder')) {

    /**
     * @param string     $numara Uluslararası biçim, yalnız rakam (905xxxxxxxxx)
     * @param string     $metin  Serbest metin (24 saat penceresi içinde teslim edilir)
     * @param array|null $sablon ['ad' => 'hello_world', 'dil' => 'en_US'] verilirse şablon mesajı gönderilir
     * @return array [bool başarı, string hata]
     */
    function prolyn_whatsapp_gonder(string $numara, string $metin = '', ?array $sablon = null): array
    {
        $wa = prolyn_mail_ayarlari()['whatsapp'];
        $numara = preg_replace('/\D/', '', $numara);
        if (strlen($numara) < 10 || strlen($numara) > 15) { return [false, 'Geçersiz WhatsApp numarası']; }
        if ($wa['token'] === '' || $wa['api_url'] === '') { return [false, 'WhatsApp API URL veya erişim anahtarı boş']; }

        $url = str_replace('{phone_id}', rawurlencode($wa['phone_id']), $wa['api_url']);
        // Anahtar yalnızca Meta'ya gider: başka bir alan adına sızmasını engelle (Zero Trust)
        if (!preg_match('~^https://graph\.facebook\.com/v\d+\.\d+/\d+/messages$~', $url)) {
            return [false, 'WhatsApp API URL biçimi hatalı (Phone number ID girildi mi?)'];
        }
        if (!function_exists('curl_init')) { return [false, 'Sunucuda cURL eklentisi yok']; }

        $govde = ['messaging_product' => 'whatsapp', 'to' => $numara];
        if ($sablon) {
            $govde['type'] = 'template';
            $govde['template'] = ['name' => (string)$sablon['ad'], 'language' => ['code' => (string)($sablon['dil'] ?? 'tr')]];
        } else {
            if (trim($metin) === '') { return [false, 'Mesaj metni boş']; }
            $govde['type'] = 'text';
            $govde['text'] = ['body' => mb_substr($metin, 0, 4000), 'preview_url' => false];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $wa['token'], 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($govde, JSON_UNESCAPED_UNICODE),
        ]);
        $yanit = curl_exec($ch);
        $kod = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlHata = curl_error($ch);
        curl_close($ch);

        if ($yanit === false) { return [false, 'Bağlantı hatası: ' . $curlHata]; }
        if ($kod >= 200 && $kod < 300) { return [true, '']; }
        $j = json_decode((string)$yanit, true);
        $mesaj = (string)($j['error']['message'] ?? ('HTTP ' . $kod));
        error_log('[whatsapp] HTTP ' . $kod . ': ' . $mesaj);
        return [false, mb_substr($mesaj, 0, 250)];
    }
}
