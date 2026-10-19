<?php
// admin/siswa/edit.php - Ubah Data Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    set_flash('danger', 'Data siswa tidak ditemukan.');
    header('Location: ' . base_url('admin/siswa/index.php'));
    exit;
}

$page_title = 'Edit Siswa - ' . $siswa['nama'];
$errors = [];

$nis = $siswa['nis'];
$nama = $siswa['nama'];
$kelas = $siswa['kelas'];
$jurusan = $siswa['jurusan'];
$no_hp = $siswa['no_hp'];
$alamat = $siswa['alamat'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis     = clean_input($_POST['nis'] ?? '');
    $nama    = clean_input($_POST['nama'] ?? '');
    $kelas   = clean_input($_POST['kelas'] ?? '');
    $jurusan = clean_input($_POST['jurusan'] ?? '');
    $no_hp   = clean_input($_POST['no_hp'] ?? '');
    $alamat  = clean_input($_POST['alamat'] ?? '');

    if (empty($nis)) {
        $errors['nis'] = 'NIS wajib diisi.';
    }

    if (empty($nama)) {
        $errors['nama'] = 'Nama siswa wajib diisi.';
    }

    if (empty($kelas)) {
        $errors['kelas'] = 'Kelas wajib diisi.';
    }

    if (empty($jurusan)) {
        $errors['jurusan'] = 'Jurusan wajib diisi.';
    }

    if (empty($no_hp)) {
        $errors['no_hp'] = 'Nomor HP wajib diisi.';
    }

    // Cek duplikasi NIS kecuali record saat ini
    if (!empty($nis)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM siswa WHERE nis = ? AND id != ? LIMIT 1");
        $stmtCheck->execute([$nis, $id]);
        if ($stmtCheck->fetch()) {
            $errors['nis'] = 'NIS ' . htmlspecialchars($nis) . ' sudah terdaftar pada siswa lain.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmtUpdate = $pdo->prepare("
                UPDATE siswa 
                SET nis = ?, nama = ?, kelas = ?, jurusan = ?, no_hp = ?, alamat = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nis, $nama, $kelas, $jurusan, $no_hp, $alamat, $id]);

            // Jika user_id ada, update juga nama lengkap di tabel users
            if (!empty($siswa['user_id'])) {
                $stmtU = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
                $stmtU->execute([$nama, $siswa['user_id']]);
            }

            $pdo->commit();
            set_flash('success', "Data siswa {$nama} berhasil diperbarui.");
            header('Location: ' . base_url('admin/siswa/index.php'));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Gagal memperbarui data: ' . $e->getMessage();
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
                        <a href="<?= base_url('admin/siswa/index.php') ?>" class="hover:text-blue-600">Data Siswa</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Siswa</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Data Siswa</h2>
                </div>
                <a href="<?= base_url('admin/siswa/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                    <span><?= htmlspecialchars($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-3xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Perbarui Informasi Siswa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">ID Siswa: #<?= $siswa['id'] ?> • Terdaftar Sejak: <?= date('d M Y', strtotime($siswa['created_at'])) ?></p>
                </div>

                <form action="<?= base_url('admin/siswa/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="nis" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Nomor Induk Siswa (NIS) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nis" id="nis" value="<?= htmlspecialchars($nis) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nis']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['nis'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['nis'] ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="nama" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Nama Lengkap Siswa <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($nama) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nama']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['nama'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['nama'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="kelas" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Kelas <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="kelas" id="kelas" value="<?= htmlspecialchars($kelas) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['kelas']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['kelas'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['kelas'] ?></p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <label for="jurusan" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Jurusan / Kompetensi Keahlian <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="jurusan" id="jurusan" value="<?= htmlspecialchars($jurusan) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['jurusan']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if (isset($errors['jurusan'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['jurusan'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nomor Handphone / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="no_hp" id="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['no_hp']) ? 'border-rose-400 bg-rose-50/30' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php if (isset($errors['no_hp'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['no_hp'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="alamat" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Alamat Tinggal Siswa
                        </label>
                        <textarea name="alamat" id="alamat" rows="3"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($alamat) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/siswa/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/20 flex items-center gap-2">
                            <i class="fa-solid fa-check"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>

                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
