<?php

require_once __DIR__ . '/../classes/allClass.php';
require_once __DIR__ . '/../functions/combine.php';

start_secure_session();

// Auth başlat
$db = new Database();
// $db->setDebugMode(true); // Gerekirse açılabilir
$auth = new Auth($db);

$loginUrl = '../login.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    xlogSimple('POST olmayan istek', 'POST olmayan isteği islem.php\'ye erişim denemesi');
    header("Location: {$loginUrl}?islem=bos", true, 302);
    exit;
}

// Form verilerini al
$identifier = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Login işlemi
$result = $auth->login($identifier, $password);

if ($result['success']) {
    // Başarılı giriş -> Dashboard
    header('Location: ../', true, 302);
} else {
    // Hatalı giriş -> Login
    // Redirect params (örn: islem=hata) auth class'tan gelir
    $redirectParams = $result['redirect_params'] ?? 'islem=hata';
    header("Location: {$loginUrl}?{$redirectParams}", true, 302);
}
exit;
?>
?>