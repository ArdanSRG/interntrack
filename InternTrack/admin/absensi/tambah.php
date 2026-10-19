<?php
// admin/absensi/tambah.php - Input Absensi Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Input Absensi Kehadiran';
$errors = [];

$siswa_id = (int)($_POST['siswa_id'] ?? 0);
$tanggal = clean_input($_POST['tanggal'] ?? date('Y-m-d'));
$status = clean_input($_POST['status'] ?? '');
$keterangan = clean_input($_POST['keterangan'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Siswa wajib dipilih
    if ($siswa_id <= 0) {
        $errors['siswa_id'] = 'Siswa wajib dipilih.';
    }

    // 2. Status wajib dipilih
    if (empty($status) || !in_array($status, ['Hadir', 'Izin', 'Sakit', 'Alpa'])) {
        $errors['status'] = 'Status kehadiran wajib dipilih (Hadir/Izin/Sakit/Alpa).';
    }

    if (empty($tanggal)) {
        $errors['tanggal'] = 'Tanggal absensi wajib diisi.';
    }

    // Cek duplikasi absensi siswa pada tanggal yang sama
    if ($siswa_id > 0 && !empty($tanggal)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ? LIMIT 1");
        $stmtCheck->execute([$siswa_id, $tanggal]);
        if ($stmtCheck->fetch()) {
            $errors['general'] = 'Data absensi untuk siswa ini pada tanggal tersebut sudah tercatat.';
        }
    }

    if (empty($errors)) {
        try {
            // Ambil penempatan aktif jika ada
            $stmtPen = $pdo->prepare("SELECT id FROM penempatan WHERE siswa_id = ? AND status = 'Aktif' LIMIT 1");
            $stmtPen->execute([$siswa_id]);
            $penRow = $stmtPen->fetch();
            $penempatan_id = $penRow ? $penRow['id'] : null;

            $stmtIns = $pdo->prepare("
                INSERT INTO absensi (siswa_id, penempatan_id, tanggal, status, keterangan)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtIns->execute([$siswa_id, $penempatan_id, $tanggal, $status, $keterangan]);

            set_flash('success', 'Absensi kehadiran berhasil disimpan.');
            header('Location: ' . base_url('admin/absensi/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan absensi: ' . $e->getMessage();
        }
    }
}

$siswa_list = $pdo->query("SELECT id, nis, nama, kelas FROM siswa ORDER BY nama ASC")->fetchAll();

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
                        <a href="<?= base_url('admin/absensi/index.php') ?>" class="hover:text-blue-600">Data Absensi</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Input Manual</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Input Data Kehadiran</h2>
                </div>
                <a href="<?= base_url('admin/absensi/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                    <span><?= htmlspecialchars($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-xl overflow-hidden">
                <form action="<?= base_url('admin/absensi/tambah.php') ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div>
                        <label for="siswa_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Siswa <span class="text-rose-500">*</span></label>
                        <select name="siswa_id" id="siswa_id" required class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['siswa_id']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($siswa_list as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $siswa_id == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nama']) ?> (NIS: <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['kelas']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['siswa_id'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['siswa_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Absensi <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Status Kehadiran <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-emerald-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/70">
                                <input type="radio" name="status" value="Hadir" <?= $status === 'Hadir' ? 'checked' : '' ?> class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-emerald-800">Hadir</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-blue-50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/70">
                                <input type="radio" name="status" value="Izin" <?= $status === 'Izin' ? 'checked' : '' ?> class="text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-blue-800">Izin</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-amber-50 transition-colors has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/70">
                                <input type="radio" name="status" value="Sakit" <?= $status === 'Sakit' ? 'checked' : '' ?> class="text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-bold text-amber-800">Sakit</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-rose-50 transition-colors has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/70">
                                <input type="radio" name="status" value="Alpa" <?= $status === 'Alpa' ? 'checked' : '' ?> class="text-rose-600 focus:ring-rose-500">
                                <span class="text-xs font-bold text-rose-800">Alpa</span>
                            </label>
                        </div>
                        <?php if (isset($errors['status'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['status'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Alasan (Opsional)</label>
                        <input type="text" name="keterangan" id="keterangan" value="<?= htmlspecialchars($keterangan) ?>" placeholder="Contoh: Sakit demam ada surat dokter / Izin kepentingan keluarga"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/absensi/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Absensi
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
