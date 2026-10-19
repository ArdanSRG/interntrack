<?php
// index.php - Router / Entry Point Utama InternTrack
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';

if (is_logged_in()) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'siswa') {
        header('Location: ' . base_url('siswa/index.php'));
    } else {
        header('Location: ' . base_url('admin/index.php'));
    }
    exit;
} else {
    header('Location: ' . base_url('auth/login.php'));
    exit;
}
