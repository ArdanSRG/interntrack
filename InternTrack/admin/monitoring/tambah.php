<?php
// admin/monitoring/tambah.php - Input Hasil Monitoring PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Input Monitoring PKL';
$errors = [];

$penempatan_id = (int)($_POST['penempatan_id'] ?? 0);
$tanggal_monitoring = clean_input($_POST['tanggal_monitoring'] ?? date('Y-m-d'));
$nilai = clean_input($_POST['nilai'] ?? '');
$catatan = clean_input($_POST['catatan'] ?? '');
$rekomendasi = clean_input($_POST['rekomendasi'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($penempatan_id <= 0) $errors['penempatan_id'] = 'Pilih siswa penempatan PKL.';
    if (empty($tanggal_monitoring)) $errors['tanggal_monitoring'] = 'Tanggal monitoring wajib diisi.';
    if ($nilai === '' || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $errors['nilai'] = 'Nilai harus berupa angka antara 0 hingga 100.';
    }
    if (empty($catatan)) $errors['catatan'] = 'Catatan monitoring wajib diisi.';
    if (empty($rekomendasi)) $errors['rekomendasi'] = 'Rekomendasi wajib diisi.';

    if (empty($errors)) {
        try {
            // Dapatkan siswa_id dan pembimbing_id dari penempatan
            $stmtPen = $pdo->prepare("SELECT siswa_id, pembimbing_id FROM penempatan WHERE id = ?");
            $stmtPen->execute([$penempatan_id]);
            $pen = $stmtPen->fetch();

            if (!$pen) {
                $errors['penempatan_id'] = 'Data penempatan tidak valid.';
            } else {
                $stmtIns = $pdo->prepare("
                    INSERT INTO monitoring (penempatan_id, pembimbing_id, siswa_id, tanggal_monitoring, catatan, nilai, rekomendasi)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtIns->execute([
                    $penempatan_id, 
                    $pen['pembimbing_id'], 
                    $pen['siswa_id'], 
                    $tanggal_monitoring, 
                    $catatan, 
                    (int)$nilai, 
                    $rekomendasi
                ]);

                set_flash('success', 'Catatan hasil monitoring berhasil disimpan.');
                header('Location: ' . base_url('admin/monitoring/index.php'));
                exit;
            }
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan monitoring: ' . $e->getMessage();
        }
    }
}

// Ambil list penempatan aktif
$penempatan_list = $pdo->query("
    SELECT pen.id, s.nama as nama_siswa, s.nis, s.kelas, p.nama_perusahaan, pem.nama as nama_pembimbing
    FROM penempatan pen
    JOIN siswa s ON s.id = pen.siswa_id
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    ORDER BY s.nama ASC
")->fetchAll();

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
                        <span class="text-slate-600 font-medium">Input Baru</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Input Hasil Monitoring Siswa</h2>
                </div>
                <a href="<?= base_url('admin/monitoring/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('admin/monitoring/tambah.php') ?>" method="POST" class="p-6 space-y-5" novalidate>
                    
                    <div>
                        <label for="penempatan_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Siswa & Tempat PKL <span class="text-rose-500">*</span></label>
                        <select name="penempatan_id" id="penempatan_id" required class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['penempatan_id']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Siswa PKL --</option>
                            <?php foreach ($penempatan_list as $pen): ?>
                                <option value="<?= $pen['id'] ?>" <?= $penempatan_id == $pen['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pen['nama_siswa']) ?> (<?= htmlspecialchars($pen['kelas']) ?>) &rarr; <?= htmlspecialchars($pen['nama_perusahaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['penempatan_id'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['penempatan_id'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="tanggal_monitoring" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Kunjungan / Monitoring <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_monitoring" id="tanggal_monitoring" value="<?= htmlspecialchars($tanggal_monitoring) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="nilai" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nilai Evaluasi (0 - 100) <span class="text-rose-500">*</span></label>
                            <input type="number" name="nilai" id="nilai" min="0" max="100" value="<?= htmlspecialchars($nilai) ?>" required
                                   placeholder="Contoh: 85"
                                   class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['nilai']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php if (isset($errors['nilai'])): ?>
                                <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['nilai'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Monitoring Lapangan <span class="text-rose-500">*</span></label>
                        <textarea name="catatan" id="catatan" rows="3" required placeholder="Contoh: Siswa mampu berkomunikasi dengan baik dan menyelesaikan tugas yang diberikan oleh industri dengan tepat waktu."
                                  class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['catatan']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($catatan) ?></textarea>
                        <?php if (isset($errors['catatan'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['catatan'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="rekomendasi" class="block text-xs font-bold text-slate-700 uppercase mb-1">Rekomendasi / Tindak Lanjut <span class="text-rose-500">*</span></label>
                        <textarea name="rekomendasi" id="rekomendasi" rows="2" required placeholder="Contoh: Pertahankan kedisiplinan dan tingkatkan eksplorasi backend architecture."
                                  class="w-full px-3.5 py-2.5 rounded-xl border <?= isset($errors['rekomendasi']) ? 'border-rose-400' : 'border-slate-300' ?> text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($rekomendasi) ?></textarea>
                        <?php if (isset($errors['rekomendasi'])): ?>
                            <p class="text-xs text-rose-600 font-semibold mt-1"><?= $errors['rekomendasi'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/monitoring/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Monitoring
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
