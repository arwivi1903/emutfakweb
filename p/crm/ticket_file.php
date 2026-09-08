<?php
/**
 * Prolyn Admin Panel — Destek Eki Servisi
 * Prolyn/p/crm/ticket_file.php
 *
 * Destek eklerinin durduğu klasör web'den doğrudan okunamaz (.htaccess ile
 * kapalı). Bu betik yönetici oturumunu doğruladıktan sonra dosyayı akıtır.
 * Kiracı tarafındaki karşılığı: prolyn/destekdosya.php
 */

require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';
require_once __DIR__ . '/../functions/auth.php';

start_secure_session();

/* config/header.php burada kullanılamaz: gövde ikili dosya, HTML basılamaz.
   Bu yüzden yetki, ticket_detail.php ile aynı izin adına göre elle bakılır. */
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Yetkisiz erişim.');
}

$ekId = (int) ($_GET['ek'] ?? 0);
if ($ekId <= 0) {
    http_response_code(400);
    exit('Geçersiz istek.');
}

$db = new Database();

if (($_SESSION['role'] ?? '') !== 'superadmin') {
    $izin = $db->getRowAssoc(
        "SELECT COUNT(*) AS c FROM yonetici_rolleri yr
         JOIN rol_izinleri ri ON yr.role_id = ri.role_id
         JOIN izinler i ON ri.permission_id = i.id
         WHERE yr.admin_id = ? AND i.name = 'manage_support'",
        [(int) $_SESSION['admin_id']]
    );
    if (empty($izin['c'])) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Bu içeriğe erişim yetkiniz yok.');
    }
}
$ek = $db->getRowAssoc("SELECT * FROM destek_ekleri WHERE ek_id = ? LIMIT 1", [$ekId]);

if (!$ek) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Dosya bulunamadı.');
}

/** Eklerin fiziksel kökü — kiracı tarafıyla AYNI klasör olmalı. */
function destek_ek_kok(): string
{
    if (function_exists('prolynweb_env')) {
        $ozel = trim((string) prolynweb_env('SUPPORT_UPLOAD_DIR', ''));
        if ($ozel !== '') {
            return rtrim($ozel, '/');
        }
    }
    // .../prolynweb/p/crm → .../prolynweb/uploads/destek
    return dirname(dirname(__DIR__)) . '/uploads/destek';
}

$kok = realpath(destek_ek_kok());
$yol = realpath(destek_ek_kok() . '/' . (string) $ek['dosya_yol']);

if ($kok === false || $yol === false || strpos($yol, $kok . DIRECTORY_SEPARATOR) !== 0 || !is_file($yol)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Dosya bulunamadı.');
}

$mime    = (string) ($ek['mime_tipi'] ?? 'application/octet-stream');
$ad      = (string) $ek['dosya_ad'];
$dagitim = (strpos($mime, 'image/') === 0 || $mime === 'application/pdf') ? 'inline' : 'attachment';

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($yol));
header('Content-Disposition: ' . $dagitim . '; filename="' . rawurlencode($ad) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=600');
readfile($yol);
exit;
