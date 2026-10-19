<?php
// admin/pembimbing/tambah.php - Tambah Data Pembimbing
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$page_title = 'Tambah Guru Pembimbing';
$errors = [];

$nip = '';
$nama = '';
$no_hp = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip   = clean_input($_POST['nip'] ?? '');
    $nama  = clean_input($_POST['nama'] ?? '');
    $no_hp = clean_input($_POST['no_hp'] ?? '');
    $email = clean_input($_POST['email'] ?? '');

    if (empty($nip)) {
        $errors['nip'] = 'NIP wajib diisi.';
    }
    if (empty($nama)) {
        $errors['nama'] = 'Nama lengkap pembimbing wajib diisi.';
    }
    if (empty($no_hp)) {
        $errors['no_hp'] = 'Nomor handphone wajib diisi.';
    }

    if (!empty($nip)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM pembimbing WHERE nip = ? LIMIT 1");
        $stmtCheck->execute([$nip]);
        if ($stmtCheck->fetch()) {
            $errors['nip'] = 'NIP sudah terdaftar pada guru lain.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Buat akun user pembimbing
            $username = 'pembimbing_' . substr($nip, -4);
            $default_pass = password_hash('password123', PASSWORD_BCRYPT);
            
            $stmtUser = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'pembimbing')");
            $stmtUser->execute([$username, $default_pass, $nama]);
            $userId = $pdo->lastInsertId();

            $stmtPem = $pdo->prepare("INSERT INTO pembimbing (user_id, nip, nama, no_hp, email) VALUES (?, ?, ?, ?, ?)");
            $stmtPem->execute([$userId, $nip, $nama, $no_hp, $email]);

            $pdo->commit();
            set_flash('success', "Guru pembimbing {$nama} berhasil ditambahkan dengan username {$username}.");
            header('Location: ' . base_url('admin/pembimbing/index.php'));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                        <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="hover:text-blue-600">Data Pembimbing</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Tambah Baru</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Tambah Guru Pembimbing</h2>
                </div>
                <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('admin/pembimbing/tambah.php') ?>" method="POST" class="p-6 space-y-5" novalidate>
                    <div>
                        <label for="nip" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nip" id="nip" value="<?= htmlspecialchars($nip) ?>" required
                               placeholder="Contoh: 198501152010011001"
                               class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nip']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['nip'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['nip'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="nama" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($nama) ?>" required
                               placeholder="Contoh: Drs. Budi Santoso, M.Kom"
                               class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nama']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['nama'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['nama'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="no_hp" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Handphone / WA <span class="text-rose-500">*</span></label>
                            <input type="text" name="no_hp" id="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required
                                   placeholder="08123456789"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Sekolah</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>"
                                   placeholder="guru@smk.sch.id"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Pembimbing
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
