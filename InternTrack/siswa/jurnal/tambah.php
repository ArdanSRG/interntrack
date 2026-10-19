<?php
// siswa/jurnal/tambah.php - Input Jurnal Kegiatan Harian Siswa (Algoritma 2)
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role('siswa');

$page_title = 'Tulis Jurnal Kegiatan';
$user = auth_user();

$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'] ?? 0;

// Ambil penempatan aktif
$stmtPen = $pdo->prepare("SELECT id FROM penempatan WHERE siswa_id = ? AND status = 'Aktif' LIMIT 1");
$stmtPen->execute([$siswa_id]);
$penRow = $stmtPen->fetch();
$penempatan_id = $penRow ? $penRow['id'] : null;

$errors = [];
$tanggal = clean_input($_POST['tanggal'] ?? date('Y-m-d'));
$kegiatan = clean_input($_POST['kegiatan'] ?? '');
$hasil = clean_input($_POST['hasil'] ?? '');
$kendala = clean_input($_POST['kendala'] ?? '');

// Algoritma 2: Implementasi Proses Input Jurnal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi: Tanggal wajib diisi
    if (empty($tanggal)) {
        $errors['tanggal'] = 'Tanggal kegiatan wajib diisi.';
    }

    // 2. Validasi: Kegiatan wajib diisi
    if (empty($kegiatan)) {
        $errors['kegiatan'] = 'Deskripsi kegiatan harian wajib diisi.';
    }

    // Validasi Hasil minimal tidak kosong
    if (empty($hasil)) {
        $errors['hasil'] = 'Hasil kegiatan yang dicapai wajib diisi.';
    }

    if (empty($errors)) {
        try {
            $stmtIns = $pdo->prepare("
                INSERT INTO jurnal (siswa_id, penempatan_id, tanggal, kegiatan, hasil, kendala, status_validasi)
                VALUES (?, ?, ?, ?, ?, ?, 'Menunggu')
            ");
            $stmtIns->execute([$siswa_id, $penempatan_id, $tanggal, $kegiatan, $hasil, $kendala]);

            set_flash('success', 'Jurnal kegiatan harian berhasil disimpan dan menunggu review pembimbing.');
            header('Location: ' . base_url('siswa/jurnal/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan jurnal: ' . $e->getMessage();
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
                        <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="hover:text-blue-600">Jurnal Saya</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Tulis Baru</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Tulis Jurnal Kegiatan Harian</h2>
                </div>
                <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <div class="p-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Algoritma 2: Input Jurnal PKL</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catat dengan jelas apa yang Anda pelajari dan kerjakan hari ini di industri.</p>
                    </div>
                </div>

                <form action="<?= base_url('siswa/jurnal/tambah.php') ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Tanggal Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['tanggal']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['tanggal'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['tanggal'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="kegiatan" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Aktivitas / Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="kegiatan" id="kegiatan" value="<?= htmlspecialchars($kegiatan) ?>" required
                               placeholder="Contoh: Membuat Desain Database"
                               class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['kegiatan']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (isset($errors['kegiatan'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['kegiatan'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="hasil" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Hasil yang Dicapai <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="hasil" id="hasil" rows="3" required
                                  placeholder="Contoh: ERD berhasil diselesaikan dan dilakukan normalisasi database"
                                  class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['hasil']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($hasil) ?></textarea>
                        <?php if (isset($errors['hasil'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-circle-xmark mr-1"></i><?= $errors['hasil'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="kendala" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Kendala yang Dihadapi (Opsional)
                        </label>
                        <textarea name="kendala" id="kendala" rows="2"
                                  placeholder="Contoh: Kesulitan menentukan relasi kardinalitas antar tabel (Boleh dikosongkan jika tidak ada)"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($kendala) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Jurnal Kegiatan</span>
                        </button>
                    </div>

                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
