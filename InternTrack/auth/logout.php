<?php
// auth/logout.php - Proses Keluar Sistem
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

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

session_destroy();

// Mulai session baru hanya untuk menampung pesan flash
session_start();
set_flash('success', 'Anda telah berhasil keluar dari sistem InternTrack.');

header('Location: ' . base_url('auth/login.php'));
exit;
