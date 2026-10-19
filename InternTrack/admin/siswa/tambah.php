<?php
// admin/siswa/tambah.php - Tambah Data Siswa Baru
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Tambah Data Siswa';
$errors = [];

$nis = '';
$nama = '';
$kelas = '';
$jurusan = '';
$no_hp = '';
$alamat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis     = clean_input($_POST['nis'] ?? '');
    $nama    = clean_input($_POST['nama'] ?? '');
    $kelas   = clean_input($_POST['kelas'] ?? '');
    $jurusan = clean_input($_POST['jurusan'] ?? '');
    $no_hp   = clean_input($_POST['no_hp'] ?? '');
    $alamat  = clean_input($_POST['alamat'] ?? '');

    // 1. Validasi NIS wajib diisi
    if (empty($nis)) {
        $errors['nis'] = 'NIS wajib diisi.';
    }

    // 2. Validasi Nama wajib diisi
    if (empty($nama)) {
        $errors['nama'] = 'Nama siswa wajib diisi.';
    }

    if (empty($kelas)) {
        $errors['kelas'] = 'Kelas wajib dipilih/diisi.';
    }

    if (empty($jurusan)) {
        $errors['jurusan'] = 'Jurusan wajib diisi.';
    }

    if (empty($no_hp)) {
        $errors['no_hp'] = 'Nomor HP wajib diisi.';
    }

    // 3. Validasi NIS tidak boleh duplikat
    if (!empty($nis)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? LIMIT 1");
        $stmtCheck->execute([$nis]);
        if ($stmtCheck->fetch()) {
            $errors['nis'] = 'NIS ' . htmlspecialchars($nis) . ' sudah terdaftar pada sistem (duplikat).';
        }
    }

    // Jika lolos semua validasi
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Buat akun user untuk siswa (username = NIS, default password = password123)
            $username = 'siswa_' . $nis;
            $default_pass = password_hash('password123', PASSWORD_BCRYPT);
            
            // Cek apakah username sudah ada
            $stmtUCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmtUCheck->execute([$username]);
            if ($stmtUCheck->fetch()) {
                $username = 'siswa_' . $nis . '_' . rand(10, 99);
            }

            $stmtUser = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'siswa')");
            $stmtUser->execute([$username, $default_pass, $nama]);
            $userId = $pdo->lastInsertId();

            // Insert data siswa
            $stmtSiswa = $pdo->prepare("
                INSERT INTO siswa (user_id, nis, nama, kelas, jurusan, no_hp, alamat) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtSiswa->execute([$userId, $nis, $nama, $kelas, $jurusan, $no_hp, $alamat]);

            $pdo->commit();
            set_flash('success', "Data siswa {$nama} (NIS: {$nis}) berhasil ditambahkan beserta akun login.");
            header('Location: ' . base_url('admin/siswa/index.php'));
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

            <!-- Breadcrumb & Title -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                        <a href="<?= base_url('admin/siswa/index.php') ?>" class="hover:text-blue-600">Data Siswa</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Tambah Baru</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Form Tambah Siswa</h2>
                </div>
                <a href="<?= base_url('admin/siswa/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <!-- Error General Box -->
            <?php if (!empty($errors['general'])): ?>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                    <span><?= htmlspecialchars($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-3xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas Calon Peserta PKL</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pastikan NIS unik dan data kontak siswa valid untuk keperluan komunikasi industri.</p>
                </div>

                <form action="<?= base_url('admin/siswa/tambah.php') ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- NIS -->
                        <div>
                            <label for="nis" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nis" id="nis" value="<?= htmlspecialchars($nis) ?>" required
                                   placeholder="Contoh: 102426" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nis']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['nis'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['nis'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="nama" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Nama Lengkap Siswa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($nama) ?>" required
                                   placeholder="Masukkan nama lengkap siswa" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nama']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['nama'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['nama'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Kelas -->
                        <div>
                            <label for="kelas" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kelas" id="kelas" value="<?= htmlspecialchars($kelas) ?>" required
                                   placeholder="Contoh: XII RPL 1" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['kelas']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['kelas'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['kelas'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Jurusan -->
                        <div>
                            <label for="jurusan" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Jurusan / Kompetensi Keahlian <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="jurusan" id="jurusan" value="<?= htmlspecialchars($jurusan) ?>" required
                                   placeholder="Contoh: Rekayasa Perangkat Lunak" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['jurusan']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['jurusan'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['jurusan'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Nomor HP -->
                        <div>
                            <label for="no_hp" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Nomor Handphone / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="no_hp" id="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required
                                   placeholder="Contoh: 0821xxxxxxxx" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['no_hp']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['no_hp'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['no_hp'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Info Otomatis Akun -->
                        <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-200 text-xs text-blue-900">
                            <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-key"></i> Akun Login Otomatis</span>
                            <p class="text-[11px] text-blue-700 mt-1">Username otomatis diset <code class="font-mono bg-blue-100 px-1 rounded">siswa_[NIS]</code> dan password bawaan <code class="font-mono bg-blue-100 px-1 rounded">password123</code>.</p>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label for="alamat" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Alamat Tinggal Siswa
                        </label>
                        <textarea name="alamat" id="alamat" rows="3" placeholder="Masukkan alamat lengkap domisili siswa..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($alamat) ?></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/siswa/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/20 flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Data Siswa</span>
                        </button>
                    </div>

                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
