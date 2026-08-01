<?php
/**
 * Prolynweb - Basit .env yükleyici
 * .env dosyasını (bu dosyanın bulunduğu dizinde) okur ve anahtarları döndürür.
 * Sunucuya .env yerine ortam değişkeni (Apache SetEnv / systemd) da kullanılabilir;
 * o durumda getenv() üzerinden okunur.
 */

if (!function_exists('prolynweb_env')) {

    function prolynweb_env_load(): array
    {
        static $vars = null;
        if ($vars !== null) {
            return $vars;
        }

        $vars = [];
        $path = __DIR__ . '/.env';
        if (is_readable($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }
                if (strpos($line, '=') === false) {
                    continue;
                }
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                // Satır sonu yorumlarını değil, sadece çevreleyen tırnakları temizle
                if (strlen($v) >= 2 &&
                    ((substr($v, 0, 1) === '"' && substr($v, -1) === '"') ||
                     (substr($v, 0, 1) === "'" && substr($v, -1) === "'"))) {
                    $v = substr($v, 1, -1);
                }
                if ($k !== '') {
                    $vars[$k] = $v;
                }
            }
        }

        return $vars;
    }

    /**
     * Ortam değişkenini al. Önce .env, sonra getenv(), sonra varsayılan.
     */
    function prolynweb_env(string $key, $default = null)
    {
        $vars = prolynweb_env_load();
        if (array_key_exists($key, $vars)) {
            return $vars[$key];
        }
        $env = getenv($key);
        if ($env !== false) {
            return $env;
        }
        return $default;
    }
}
