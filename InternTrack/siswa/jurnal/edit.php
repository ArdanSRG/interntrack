<?php
// siswa/jurnal/edit.php - Edit Jurnal Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role('siswa');

$user = auth_user();
$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'] ?? 0;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM jurnal WHERE id = ? AND siswa_id = ?");
$stmt->execute([$id, $siswa_id]);
$jurnal = $stmt->fetch();

if (!$jurnal) {
    set_flash('danger', 'Jurnal tidak ditemukan atau Anda tidak memiliki akses untuk mengubahnya.');
    header('Location: ' . base_url('siswa/jurnal/index.php'));
    exit;
}

// Hanya boleh diedit jika belum disetujui (Menunggu atau Revisi)
if ($jurnal['status_validasi'] === 'Disetujui') {
    set_flash('warning', 'Jurnal yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
    header('Location: ' . base_url('siswa/jurnal/index.php'));
    exit;
}

$page_title = 'Edit Jurnal Kegiatan';
$errors = [];

$tanggal = $jurnal['tanggal'];
$kegiatan = $jurnal['kegiatan'];
$hasil = $jurnal['hasil'];
$kendala = $jurnal['kendala'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = clean_input($_POST['tanggal'] ?? '');
    $kegiatan = clean_input($_POST['kegiatan'] ?? '');
    $hasil = clean_input($_POST['hasil'] ?? '');
    $kendala = clean_input($_POST['kendala'] ?? '');

    if (empty($tanggal)) $errors['tanggal'] = 'Tanggal wajib diisi.';
    if (empty($kegiatan)) $errors['kegiatan'] = 'Kegiatan wajib diisi.';
    if (empty($hasil)) $errors['hasil'] = 'Hasil wajib diisi.';

    if (empty($errors)) {
        try {
            $stmtUp = $pdo->prepare("
                UPDATE jurnal 
                SET tanggal = ?, kegiatan = ?, hasil = ?, kendala = ?, status_validasi = 'Menunggu'
                WHERE id = ? AND siswa_id = ?
            ");
            $stmtUp->execute([$tanggal, $kegiatan, $hasil, $kendala, $id, $siswa_id]);

            set_flash('success', 'Perubahan jurnal berhasil disimpan dan diajukan kembali ke pembimbing.');
            header('Location: ' . base_url('siswa/jurnal/index.php'));
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
                        <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="hover:text-blue-600">Jurnal Saya</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Jurnal</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Jurnal Kegiatan</h2>
                </div>
                <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <?php if (!empty($jurnal['catatan_pembimbing'])): ?>
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                    <strong class="flex items-center gap-1.5 font-bold"><i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Catatan Pembimbing Sebelumnya:</strong>
                    <p class="mt-1"><?= htmlspecialchars($jurnal['catatan_pembimbing']) ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('siswa/jurnal/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Kegiatan <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="kegiatan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Aktivitas / Kegiatan <span class="text-rose-500">*</span></label>
                        <input type="text" name="kegiatan" id="kegiatan" value="<?= htmlspecialchars($kegiatan) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="hasil" class="block text-xs font-bold text-slate-700 uppercase mb-1">Hasil yang Dicapai <span class="text-rose-500">*</span></label>
                        <textarea name="hasil" id="hasil" rows="3" required
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($hasil) ?></textarea>
                    </div>

                    <div>
                        <label for="kendala" class="block text-xs font-bold text-slate-700 uppercase mb-1">Kendala yang Dihadapi (Opsional)</label>
                        <textarea name="kendala" id="kendala" rows="2"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($kendala) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
