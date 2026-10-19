<?php
// config/helper.php - Fungsi-fungsi Bantuan Global

// Menentukan Base URL aplikasi secara dinamis
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Deteksi direktori script saat ini
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    // Normalisasi pemisah direktori
    $script_dir = str_replace('\\', '/', $script_dir);
    
    // Cari posisi folder /InternTrack/ atau /interntrack/
    $pos = stripos($script_dir, '/interntrack');
    if ($pos !== false) {
        $base = substr($script_dir, 0, $pos + 12); // length of /interntrack is 12
    } else {
        $base = '';
    }
    
    $clean_path = ltrim($path, '/');
    return rtrim($protocol . $host . $base, '/') . ($clean_path ? '/' . $clean_path : '');
}

// Cek apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Ambil data user yang sedang login
function auth_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'           => $_SESSION['user_id'] ?? null,
        'username'     => $_SESSION['username'] ?? '',
        'nama_lengkap' => $_SESSION['nama_lengkap'] ?? '',
        'role'         => $_SESSION['role'] ?? '',
        'siswa_id'     => $_SESSION['siswa_id'] ?? null,
        'pembimbing_id'=> $_SESSION['pembimbing_id'] ?? null,
    ];
}

// Proteksi halaman harus login
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

// Proteksi halaman berdasarkan role
function require_role($allowed_roles = []) {
    require_login();
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    $current_role = $_SESSION['role'] ?? '';
    if (!in_array($current_role, $allowed_roles)) {
        set_flash('danger', 'Akses ditolak. Anda tidak memiliki izin untuk halaman tersebut.');
        if ($current_role === 'siswa') {
            header('Location: ' . base_url('siswa/index.php'));
        } else {
            header('Location: ' . base_url('admin/index.php'));
        }
        exit;
    }
}

// Flash Message System
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Algoritma 3: Perhitungan Persentase Kehadiran
// Formula: Persentase = (Hadir ÷ Total Hari) × 100%
function hitungPersentaseKehadiran($hadir, $total_hari) {
    if ($total_hari <= 0) {
        return 0;
    }
    $persen = ($hadir / $total_hari) * 100;
    return round($persen, 1);
}

// Algoritma 3: Penentuan Status PKL Berdasarkan Persentase Kehadiran
// Persentase Kehadiran   Status
// ≥ 90%                  Sangat Baik
// 75% – 89%              Baik
// 60% – 74%              Perlu Perhatian
// < 60%                  Perlu Pembinaan
function hitungStatusKehadiran($persentase) {
    if ($persentase >= 90) {
        return 'Sangat Baik';
    } elseif ($persentase >= 75) {
        return 'Baik';
    } elseif ($persentase >= 60) {
        return 'Perlu Perhatian';
    } else {
        return 'Perlu Pembinaan';
    }
}

// Helper Badge Tampilan Status Kehadiran
function badgeStatusKehadiran($status) {
    switch ($status) {
        case 'Sangat Baik':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i> Sangat Baik</span>';
        case 'Baik':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300"><i class="fa-solid fa-thumbs-up mr-1 text-blue-600"></i> Baik</span>';
        case 'Perlu Perhatian':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"><i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i> Perlu Perhatian</span>';
        case 'Perlu Pembinaan':
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300"><i class="fa-solid fa-circle-xmark mr-1 text-rose-600"></i> Perlu Pembinaan</span>';
    }
}

// Helper Badge Status Penempatan
function badgeStatusPenempatan($status) {
    switch ($status) {
        case 'Aktif':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Aktif</span>';
        case 'Selesai':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><span class="w-1.5 h-1.5 mr-1.5 bg-gray-500 rounded-full"></span> Selesai</span>';
        case 'Belum Mulai':
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"><span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full"></span> Belum Mulai</span>';
    }
}

// Helper Badge Validasi Jurnal
function badgeValidasiJurnal($status) {
    switch ($status) {
        case 'Disetujui':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><i class="fa-solid fa-check mr-1 text-xs"></i> Disetujui</span>';
        case 'Revisi':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800"><i class="fa-solid fa-rotate-left mr-1 text-xs"></i> Revisi</span>';
        case 'Menunggu':
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"><i class="fa-solid fa-clock mr-1 text-xs"></i> Menunggu</span>';
    }
}

// Helper Badge Absensi Harian
function badgeAbsensi($status) {
    switch ($status) {
        case 'Hadir':
            return '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Hadir</span>';
        case 'Izin':
            return '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">Izin</span>';
        case 'Sakit':
            return '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Sakit</span>';
        case 'Alpa':
        default:
            return '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800">Alpa</span>';
    }
}

// Sanitasi Input Form
function clean_input($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format Tanggal Indonesia
function format_tanggal_id($date) {
    if (!$date || $date == '0000-00-00') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    $d = date('d', $timestamp);
    $m = (int)date('m', $timestamp);
    $y = date('Y', $timestamp);
    return "$d " . ($bulan[$m] ?? '') . " $y";
}
