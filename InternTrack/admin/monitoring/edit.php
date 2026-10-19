<?php
// admin/monitoring/edit.php - Edit Monitoring PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT m.*, s.nama as nama_siswa, s.nis, s.kelas, p.nama_perusahaan
    FROM monitoring m
    JOIN siswa s ON s.id = m.siswa_id
    LEFT JOIN penempatan pen ON pen.id = m.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$monitoring = $stmt->fetch();

if (!$monitoring) {
    set_flash('danger', 'Data monitoring tidak ditemukan.');
    header('Location: ' . base_url('admin/monitoring/index.php'));
    exit;
}

$page_title = 'Edit Monitoring - ' . $monitoring['nama_siswa'];
$errors = [];

$tanggal_monitoring = $monitoring['tanggal_monitoring'];
$nilai = $monitoring['nilai'];
$catatan = $monitoring['catatan'];
$rekomendasi = $monitoring['rekomendasi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal_monitoring = clean_input($_POST['tanggal_monitoring'] ?? '');
    $nilai = clean_input($_POST['nilai'] ?? '');
    $catatan = clean_input($_POST['catatan'] ?? '');
    $rekomendasi = clean_input($_POST['rekomendasi'] ?? '');

    if (empty($tanggal_monitoring)) $errors['tanggal_monitoring'] = 'Tanggal wajib diisi.';
    if ($nilai === '' || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $errors['nilai'] = 'Nilai harus antara 0 - 100.';
    }
    if (empty($catatan)) $errors['catatan'] = 'Catatan wajib diisi.';
    if (empty($rekomendasi)) $errors['rekomendasi'] = 'Rekomendasi wajib diisi.';

    if (empty($errors)) {
        try {
            $stmtUp = $pdo->prepare("
                UPDATE monitoring 
                SET tanggal_monitoring = ?, nilai = ?, catatan = ?, rekomendasi = ?
                WHERE id = ?
            ");
            $stmtUp->execute([$tanggal_monitoring, (int)$nilai, $catatan, $rekomendasi, $id]);

            set_flash('success', 'Data evaluasi monitoring berhasil diperbarui.');
            header('Location: ' . base_url('admin/monitoring/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan: ' . $e->getMessage();
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
                        <a href="<?= base_url('admin/monitoring/index.php') ?>" class="hover:text-blue-600">Monitoring PKL</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Evaluasi</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Monitoring: <?= htmlspecialchars($monitoring['nama_siswa']) ?></h2>
                </div>
                <a href="<?= base_url('admin/monitoring/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 text-xs text-slate-600">
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($monitoring['nama_siswa']) ?> (<?= htmlspecialchars($monitoring['kelas']) ?>)</span> 
                    • Ditempatkan di: <span class="font-semibold text-blue-700"><?= htmlspecialchars($monitoring['nama_perusahaan'] ?? '-') ?></span>
                </div>

                <form action="<?= base_url('admin/monitoring/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="tanggal_monitoring" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Monitoring <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_monitoring" id="tanggal_monitoring" value="<?= htmlspecialchars($tanggal_monitoring) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="nilai" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nilai Evaluasi (0 - 100) <span class="text-rose-500">*</span></label>
                            <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?= htmlspecialchars($nilai) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nilai']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Monitoring <span class="text-rose-500">*</span></label>
                        <textarea name="catatan" id="catatan" rows="3" required
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($catatan) ?></textarea>
                    </div>

                    <div>
                        <label for="rekomendasi" class="block text-xs font-bold text-slate-700 uppercase mb-1">Rekomendasi / Tindak Lanjut <span class="text-rose-500">*</span></label>
                        <textarea name="rekomendasi" id="rekomendasi" rows="2" required
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($rekomendasi) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/monitoring/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
