<?php
/**
 * Veritabanı yapılandırması.
 * Değerler kaynak koddan çıkarıldı; artık kök dizindeki .env dosyasından okunur.
 * (Güvenlik: şifreler git'e girmez.)
 */
require_once __DIR__ . '/../../env.php';

return [
    'host'      => prolynweb_env('DB_HOST', '127.0.0.1'),
    'port'      => prolynweb_env('DB_PORT', '3306'),
    'user'      => prolynweb_env('DB_USER', ''),
    'pass'      => prolynweb_env('DB_PASS', ''),
    'db'        => prolynweb_env('DB_NAME', 'prolyn_master'),
    'charset'   => prolynweb_env('DB_CHARSET', 'utf8mb4'),
    'collation' => prolynweb_env('DB_COLLATION', 'utf8mb4_unicode_ci'),
];
