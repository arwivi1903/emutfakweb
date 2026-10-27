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

    /**
     * Panel (p/) taban URL'i. BASE_URL'e göre hesaplanır, ortama göre değişir
     * (yerelde .../prolynweb/p, üretimde https://prolyn.net/p gibi).
     * Kodda "/prolynweb/p/" gibi sabit yol YAZMAYIN — bu fonksiyonu kullanın.
     */
    function prolynweb_panel_url(string $path = ''): string
    {
        $base = rtrim((string) prolynweb_env('BASE_URL', ''), '/') . '/p';
        if ($path === '') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('prolynweb_master_db')) {
    /** Merkez (master) veritabanı adı: .env DB_NAME (üretimde teknikse_prolyn_master). */
    function prolynweb_master_db(): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', (string)prolynweb_env('DB_NAME', 'prolyn_master'));
    }
    /** Şablon (default) veritabanı adı: .env TENANT_TEMPLATE_DB. */
    function prolynweb_template_db(): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', (string)prolynweb_env('TENANT_TEMPLATE_DB', 'prolyn_default'));
    }
}
