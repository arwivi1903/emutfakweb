<?php
/**
 * Admin Paneli Çıkış Sayfası
 * prolynweb/p/logout.php
 * 
 * Güvenli oturum kapatma işlemini gerçekleştirir
 */

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturum tablosundan sil (Sistem Geneli Aktif Oturumlar Takibi)
require_once __DIR__ . '/classes/allClass.php';
try {
    $db = new Database();
    $db->Delete("DELETE FROM oturumlar WHERE session_id = ?", [session_id()]);
} catch (Exception $e) {
    // Hata durumunda yoksay
}

// Session'daki tüm verileri temizle
$_SESSION = [];

// Session cookie'sini sil
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Session'ı yok et
session_destroy();

// Login sayfasına yönlendir
header('Location: /prolynweb/p/login.php', true, 302);
exit;
