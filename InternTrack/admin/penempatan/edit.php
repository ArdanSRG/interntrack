<?php
// admin/penempatan/edit.php - Edit Penempatan PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM penempatan WHERE id = ?");
$stmt->execute([$id]);
$penempatan = $stmt->fetch();

if (!$penempatan) {
    set_flash('danger', 'Data penempatan tidak ditemukan.');
    header('Location: ' . base_url('admin/penempatan/index.php'));
    exit;
}

$page_title = 'Edit Penempatan PKL';
$errors = [];

$siswa_id = $penempatan['siswa_id'];
$perusahaan_id = $penempatan['perusahaan_id'];
$pembimbing_id = $penempatan['pembimbing_id'];
$tanggal_mulai = $penempatan['tanggal_mulai'];
$tanggal_selesai = $penempatan['tanggal_selesai'];
$status = $penempatan['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $perusahaan_id = (int)($_POST['perusahaan_id'] ?? 0);
    $pembimbing_id = (int)($_POST['pembimbing_id'] ?? 0);
    $tanggal_mulai = clean_input($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = clean_input($_POST['tanggal_selesai'] ?? '');
    $status = clean_input($_POST['status'] ?? 'Aktif');

    if ($siswa_id <= 0) $errors['siswa_id'] = 'Pilih siswa.';
    if ($perusahaan_id <= 0) $errors['perusahaan_id'] = 'Pilih perusahaan.';
    if ($pembimbing_id <= 0) $errors['pembimbing_id'] = 'Pilih guru pembimbing.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE penempatan 
                SET siswa_id = ?, perusahaan_id = ?, pembimbing_id = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$siswa_id, $perusahaan_id, $pembimbing_id, $tanggal_mulai, $tanggal_selesai, $status, $id]);

            set_flash('success', 'Data penempatan berhasil diperbarui.');
            header('Location: ' . base_url('admin/penempatan/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

$siswa_list = $pdo->query("SELECT id, nis, nama, kelas FROM siswa ORDER BY nama ASC")->fetchAll();
$perusahaan_list = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan ASC")->fetchAll();
$pembimbing_list = $pdo->query("SELECT id, nama, nip FROM pembimbing ORDER BY nama ASC")->fetchAll();

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
                        <a href="<?= base_url('admin/penempatan/index.php') ?>" class="hover:text-blue-600">Penempatan PKL</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Penempatan</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Alokasi Penempatan</h2>
                </div>
                <a href="<?= base_url('admin/penempatan/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('admin/penempatan/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div>
                        <label for="siswa_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Siswa Peserta <span class="text-rose-500">*</span></label>
                        <select name="siswa_id" id="siswa_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($siswa_list as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $siswa_id == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nama']) ?> (NIS: <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['kelas']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="perusahaan_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Perusahaan Mitra <span class="text-rose-500">*</span></label>
                        <select name="perusahaan_id" id="perusahaan_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($perusahaan_list as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $perusahaan_id == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_perusahaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="pembimbing_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Guru Pembimbing <span class="text-rose-500">*</span></label>
                        <select name="pembimbing_id" id="pembimbing_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($pembimbing_list as $pem): ?>
                                <option value="<?= $pem['id'] ?>" <?= $pembimbing_id == $pem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pem['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="tanggal_mulai" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="tanggal_selesai" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Penempatan</label>
                        <select name="status" id="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Belum Mulai" <?= $status === 'Belum Mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                            <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/penempatan/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
