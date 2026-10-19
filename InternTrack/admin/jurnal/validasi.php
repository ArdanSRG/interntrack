<?php
// admin/jurnal/validasi.php - Validasi & Catatan Pembimbing untuk Jurnal
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT j.*, s.nama as nama_siswa, s.nis, s.kelas, p.nama_perusahaan
    FROM jurnal j
    JOIN siswa s ON s.id = j.siswa_id
    LEFT JOIN penempatan pen ON pen.id = j.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    WHERE j.id = ?
");
$stmt->execute([$id]);
$jurnal = $stmt->fetch();

if (!$jurnal) {
    set_flash('danger', 'Jurnal tidak ditemukan.');
    header('Location: ' . base_url('admin/jurnal/index.php'));
    exit;
}

$page_title = 'Validasi Jurnal - ' . $jurnal['nama_siswa'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_validasi = clean_input($_POST['status_validasi'] ?? 'Disetujui');
    $catatan_pembimbing = clean_input($_POST['catatan_pembimbing'] ?? '');

    try {
        $stmtUpdate = $pdo->prepare("
            UPDATE jurnal 
            SET status_validasi = ?, catatan_pembimbing = ?
            WHERE id = ?
        ");
        $stmtUpdate->execute([$status_validasi, $catatan_pembimbing, $id]);

        set_flash('success', 'Validasi dan catatan pembimbing berhasil disimpan.');
        header('Location: ' . base_url('admin/jurnal/index.php'));
        exit;
    } catch (Exception $e) {
        $errors['general'] = 'Gagal menyimpan validasi: ' . $e->getMessage();
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
                        <a href="<?= base_url('admin/jurnal/index.php') ?>" class="hover:text-blue-600">Jurnal Kegiatan</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Validasi Jurnal</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Validasi Aktivitas Siswa</h2>
                </div>
                <a href="<?= base_url('admin/jurnal/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <!-- Card Rincian Jurnal Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl p-6 space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                        <?= format_tanggal_id($jurnal['tanggal']) ?>
                    </span>
                    <h3 class="text-base font-bold text-slate-900 mt-2"><?= htmlspecialchars($jurnal['nama_siswa']) ?> (<?= htmlspecialchars($jurnal['nis']) ?> - <?= htmlspecialchars($jurnal['kelas']) ?>)</h3>
                    <p class="text-xs text-slate-500">Perusahaan: <?= htmlspecialchars($jurnal['nama_perusahaan'] ?? '-') ?></p>
                </div>

                <div class="space-y-2 text-xs text-slate-700">
                    <p><strong class="text-slate-900">Kegiatan:</strong> <?= htmlspecialchars($jurnal['kegiatan']) ?></p>
                    <p><strong class="text-slate-900">Hasil:</strong> <?= htmlspecialchars($jurnal['hasil']) ?></p>
                    <?php if ($jurnal['kendala']): ?>
                        <p><strong class="text-amber-800">Kendala:</strong> <?= htmlspecialchars($jurnal['kendala']) ?></p>
                    <?php endif; ?>
                </div>

                <form action="<?= base_url('admin/jurnal/validasi.php?id=' . $id) ?>" method="POST" class="pt-4 border-t border-slate-100 space-y-4">
                    <div>
                        <label for="status_validasi" class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Validasi <span class="text-rose-500">*</span></label>
                        <select name="status_validasi" id="status_validasi" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Menunggu" <?= $jurnal['status_validasi'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu Review</option>
                            <option value="Disetujui" <?= $jurnal['status_validasi'] === 'Disetujui' ? 'selected' : '' ?>>Disetujui (ACC)</option>
                            <option value="Revisi" <?= $jurnal['status_validasi'] === 'Revisi' ? 'selected' : '' ?>>Revisi (Perlu Perbaikan)</option>
                        </select>
                    </div>

                    <div>
                        <label for="catatan_pembimbing" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan / Bimbingan Guru</label>
                        <textarea name="catatan_pembimbing" id="catatan_pembimbing" rows="4" placeholder="Tuliskan arahan, masukan, atau alasan revisi jika ada..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($jurnal['catatan_pembimbing'] ?? '') ?></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="<?= base_url('admin/jurnal/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Validasi
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
