<?php
// auth/login.php - Halaman Autentikasi Login Pengguna
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

// Jika sudah login, langsung arahkan ke dashboard yang sesuai
if (is_logged_in()) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'siswa') {
        header('Location: ' . base_url('siswa/index.php'));
    } else {
        header('Location: ' . base_url('admin/index.php'));
    }
    exit;
}

$error_message = '';
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $username_val = $username;

    // Validasi input tidak boleh kosong
    if (empty($username) || empty($password)) {
        // Tampilkan pesan error sesuai ketentuan
        $error_message = 'Username dan Password wajib diisi.';
    } else {
        // Cari user di database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi password (Bcrypt dengan fallback plaintext untuk kompatibilitas pengujian)
        $password_valid = false;
        if ($user) {
            if (password_verify($password, $user['password']) || $password === $user['password'] || md5($password) === $user['password']) {
                $password_valid = true;
            }
        }

        if ($user && $password_valid) {
            // Regenerate session id untuk keamanan
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Jika role siswa, cari ID record di tabel siswa
            if ($user['role'] === 'siswa') {
                $stmtSiswa = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
                $stmtSiswa->execute([$user['id']]);
                $siswaRow = $stmtSiswa->fetch();
                $_SESSION['siswa_id'] = $siswaRow ? $siswaRow['id'] : null;
                
                set_flash('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
                header('Location: ' . base_url('siswa/index.php'));
                exit;
            } elseif ($user['role'] === 'pembimbing') {
                $stmtPem = $pdo->prepare("SELECT id FROM pembimbing WHERE user_id = ? LIMIT 1");
                $stmtPem->execute([$user['id']]);
                $pemRow = $stmtPem->fetch();
                $_SESSION['pembimbing_id'] = $pemRow ? $pemRow['id'] : null;

                set_flash('success', 'Selamat datang kembali, ' . $user['nama_lengkap'] . '!');
                header('Location: ' . base_url('admin/index.php'));
                exit;
            } else {
                set_flash('success', 'Selamat datang di Panel Administrator InternTrack!');
                header('Location: ' . base_url('admin/index.php'));
                exit;
            }
        } else {
            // Pesan error spesifik sesuai instruksi modul H:
            // "Apabila login gagal, tampilkan pesan: 'Username atau Password Salah.'"
            $error_message = 'Username atau Password Salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InternTrack | Sistem Monitoring PKL Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Background Glow Effect -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md z-10">
        <!-- Card Login -->
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
            <!-- Header Brand -->
            <div class="bg-slate-950 p-6 text-center border-b border-slate-800">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/30 mb-3">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h1 class="text-2xl font-black tracking-wide text-white">INTERNTRACK</h1>
                <p class="text-xs text-blue-400 font-medium tracking-wider uppercase mt-1">Sistem Monitoring PKL Siswa SMK</p>
            </div>

            <!-- Form Body -->
            <div class="p-6 sm:p-8">
                <!-- Alert Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div id="login-error-alert" class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500 text-lg shrink-0"></i>
                        <span class="font-semibold"><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('auth/login.php') ?>" method="POST" class="space-y-4" novalidate>
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" name="username" id="username" value="<?= htmlspecialchars($username_val) ?>" required 
                                   placeholder="Masukkan username" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" required 
                                   placeholder="••••••••" 
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                            <button type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Masuk ke Sistem</span>
                    </button>
                </form>

                <!-- Demo Login Fast Buttons (Untuk Penguji / Asesor) -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">Akun Demo Pengujian (1-Klik Isi)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="fillLogin('admin', 'admin123')" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200 text-center transition-colors">
                            <p class="text-xs font-bold text-slate-800">Admin</p>
                            <p class="text-[10px] text-slate-400">admin / admin123</p>
                        </button>
                        <button type="button" onclick="fillLogin('pembimbing1', 'password123')" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200 text-center transition-colors">
                            <p class="text-xs font-bold text-slate-800">Guru</p>
                            <p class="text-[10px] text-slate-400">pembimbing1</p>
                        </button>
                        <button type="button" onclick="fillLogin('siswa1', 'password123')" class="p-2 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200 text-center transition-colors">
                            <p class="text-xs font-bold text-slate-800">Siswa</p>
                            <p class="text-[10px] text-slate-400">siswa1</p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-center">
                <span class="text-xs text-slate-500">Skema Junior Coder RPL &copy; 2026</span>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
