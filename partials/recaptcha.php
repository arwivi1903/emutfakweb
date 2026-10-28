<?php
/**
 * Google reCAPTCHA yardımcıları — iki tür desteklenir:
 *  - classic    : reCAPTCHA v3 (site anahtarı + gizli anahtar, siteverify)
 *  - enterprise : reCAPTCHA Enterprise (site anahtarı + Google Cloud proje ID + API anahtarı, assessments)
 * Ayarlar: Vitrin Yönetimi > Form Güvenliği. Gizli değerler sistem_ayarlari'nda
 * category='security', is_public=0 olarak durur; sayfaya hiç basılmaz.
 */

function recaptcha_ayarlar(PDO $pdo): array
{
    $c = ['enabled' => false, 'mode' => 'classic', 'site_key' => '', 'secret_key' => '', 'project_id' => '', 'api_key' => '', 'min_score' => 0.5];
    try {
        $st = $pdo->query("SELECT setting_key, setting_value FROM sistem_ayarlari WHERE setting_key LIKE 'recaptcha\\_%'");
        foreach ($st->fetchAll(PDO::FETCH_KEY_PAIR) as $k => $v) {
            $v = trim((string)$v);
            switch ($k) {
                case 'recaptcha_enabled': $c['enabled'] = $v === '1'; break;
                case 'recaptcha_mode': $c['mode'] = $v === 'enterprise' ? 'enterprise' : 'classic'; break;
                case 'recaptcha_site_key': $c['site_key'] = $v; break;
                case 'recaptcha_secret_key': $c['secret_key'] = $v; break;
                case 'recaptcha_project_id': $c['project_id'] = $v; break;
                case 'recaptcha_api_key': $c['api_key'] = $v; break;
                case 'recaptcha_min_score': if (is_numeric($v)) { $c['min_score'] = max(0.1, min(0.9, (float)$v)); } break;
            }
        }
    } catch (Throwable $e) {
        error_log('[recaptcha] ayarlar okunamadı: ' . $e->getMessage());
    }
    return $c;
}

function recaptcha_aktif(array $c): bool
{
    if (!$c['enabled'] || $c['site_key'] === '') { return false; }
    return $c['mode'] === 'enterprise'
        ? ($c['project_id'] !== '' && $c['api_key'] !== '')
        : $c['secret_key'] !== '';
}

function recaptcha_http_post(string $url, string $govde, string $tip): string|false
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $govde, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_HTTPHEADER => ['Content-Type: ' . $tip]]);
        $y = curl_exec($ch);
        return $y;
    }
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: {$tip}\r\n", 'content' => $govde, 'timeout' => 6, 'ignore_errors' => true]]);
    return @file_get_contents($url, false, $ctx);
}

/** @return array{0:bool,1:string} [geçti mi, neden] */
function recaptcha_dogrula(array $c, string $token, string $beklenenAction): array
{
    if ($token === '' || strlen($token) > 4000) {
        return [false, 'token yok'];
    }

    if ($c['mode'] === 'enterprise') {
        $url = 'https://recaptchaenterprise.googleapis.com/v1/projects/' . rawurlencode($c['project_id']) . '/assessments?key=' . rawurlencode($c['api_key']);
        $govde = json_encode(['event' => [
            'token' => $token, 'siteKey' => $c['site_key'], 'expectedAction' => $beklenenAction,
            'userIpAddress' => $_SERVER['REMOTE_ADDR'] ?? '', 'userAgent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
        ]]);
        $y = recaptcha_http_post($url, (string)$govde, 'application/json');
        if ($y === false) { return [false, 'doğrulama sunucusuna ulaşılamadı']; }
        $j = json_decode((string)$y, true);
        if (!is_array($j)) { return [false, 'geçersiz yanıt']; }
        if (isset($j['error'])) { return [false, 'API hatası: ' . ($j['error']['status'] ?? '') . ' ' . mb_substr((string)($j['error']['message'] ?? ''), 0, 120)]; }
        $tp = $j['tokenProperties'] ?? [];
        if (empty($tp['valid'])) { return [false, 'geçersiz token (' . ($tp['invalidReason'] ?? '?') . ')']; }
        if (($tp['action'] ?? '') !== $beklenenAction) { return [false, 'action uyuşmuyor']; }
        $puan = (float)($j['riskAnalysis']['score'] ?? 0);
        return $puan >= $c['min_score'] ? [true, 'puan ' . $puan] : [false, 'düşük puan ' . $puan];
    }

    $y = recaptcha_http_post('https://www.google.com/recaptcha/api/siteverify',
        http_build_query(['secret' => $c['secret_key'], 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '']),
        'application/x-www-form-urlencoded');
    if ($y === false) { return [false, 'doğrulama sunucusuna ulaşılamadı']; }
    $j = json_decode((string)$y, true);
    if (!is_array($j) || empty($j['success'])) {
        return [false, 'geçersiz token (' . implode(',', (array)($j['error-codes'] ?? [])) . ')'];
    }
    if (($j['action'] ?? '') !== $beklenenAction) { return [false, 'action uyuşmuyor']; }
    $puan = (float)($j['score'] ?? 0);
    return $puan >= $c['min_score'] ? [true, 'puan ' . $puan] : [false, 'düşük puan ' . $puan];
}

/** Formu gönderirken token alan istemci kodu (türüne göre klasik veya Enterprise). */
function recaptcha_istemci_kodu(array $c, string $formId, string $action, string $bekleyenMetin): string
{
    $ent = $c['mode'] === 'enterprise';
    $src = ($ent ? 'https://www.google.com/recaptcha/enterprise.js?render=' : 'https://www.google.com/recaptcha/api.js?render=') . rawurlencode($c['site_key']);
    $nesne = $ent ? 'grecaptcha.enterprise' : 'grecaptcha';
    $j = static fn($v) => json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    return '<style>.grecaptcha-badge{visibility:hidden}</style>'
        . '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" async defer></script>'
        . '<script>(function(){var f=document.getElementById(' . $j($formId) . '),k=' . $j($c['site_key']) . ',a=' . $j($action) . ',m=' . $j($bekleyenMetin) . ',g=false;'
        . 'if(!f)return;f.addEventListener("submit",function(ev){if(g)return;ev.preventDefault();if(!f.reportValidity())return;'
        . 'var b=f.querySelector("button[type=submit]");if(b){b.disabled=true;b.textContent=m;}'
        . 'var s=function(t){f.g_recaptcha_token.value=t||"";g=true;f.submit();};'
        . 'var R=window.grecaptcha' . ($ent ? '&&window.grecaptcha.enterprise' : '') . ';if(!R||!R.ready){s("");return;}'
        . 'R.ready(function(){R.execute(k,{action:a}).then(s,function(){s("");});});});})();</script>';
}
