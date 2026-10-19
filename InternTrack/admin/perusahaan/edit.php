<?php
// admin/perusahaan/edit.php - Edit Data Perusahaan
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
$stmt->execute([$id]);
$perusahaan = $stmt->fetch();

if (!$perusahaan) {
    set_flash('danger', 'Perusahaan tidak ditemukan.');
    header('Location: ' . base_url('admin/perusahaan/index.php'));
    exit;
}

$page_title = 'Edit Perusahaan - ' . $perusahaan['nama_perusahaan'];
$errors = [];

$nama_perusahaan  = $perusahaan['nama_perusahaan'];
$bidang_usaha     = $perusahaan['bidang_usaha'];
$alamat           = $perusahaan['alamat'];
$kontak           = $perusahaan['kontak'];
$email            = $perusahaan['email'];
$penanggung_jawab = $perusahaan['penanggung_jawab'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_perusahaan  = clean_input($_POST['nama_perusahaan'] ?? '');
    $bidang_usaha     = clean_input($_POST['bidang_usaha'] ?? '');
    $alamat           = clean_input($_POST['alamat'] ?? '');
    $kontak           = clean_input($_POST['kontak'] ?? '');
    $email            = clean_input($_POST['email'] ?? '');
    $penanggung_jawab = clean_input($_POST['penanggung_jawab'] ?? '');

    if (empty($nama_perusahaan)) {
        $errors['nama_perusahaan'] = 'Nama perusahaan wajib diisi.';
    }
    if (empty($bidang_usaha)) {
        $errors['bidang_usaha'] = 'Bidang usaha wajib diisi.';
    }
    if (empty($alamat)) {
        $errors['alamat'] = 'Alamat wajib diisi.';
    }
    if (empty($kontak)) {
        $errors['kontak'] = 'Kontak wajib diisi.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE perusahaan 
                SET nama_perusahaan = ?, bidang_usaha = ?, alamat = ?, kontak = ?, email = ?, penanggung_jawab = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama_perusahaan, $bidang_usaha, $alamat, $kontak, $email, $penanggung_jawab, $id]);

            set_flash('success', "Data perusahaan {$nama_perusahaan} berhasil diperbarui.");
            header('Location: ' . base_url('admin/perusahaan/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal memperbarui: ' . $e->getMessage();
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
                        <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="hover:text-blue-600">Data Perusahaan</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Perusahaan</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Data Perusahaan</h2>
                </div>
                <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('admin/perusahaan/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    <div>
                        <label for="nama_perusahaan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_perusahaan" id="nama_perusahaan" value="<?= htmlspecialchars($nama_perusahaan) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="bidang_usaha" class="block text-xs font-bold text-slate-700 uppercase mb-1">Bidang Usaha <span class="text-rose-500">*</span></label>
                            <input type="text" name="bidang_usaha" id="bidang_usaha" value="<?= htmlspecialchars($bidang_usaha) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="kontak" class="block text-xs font-bold text-slate-700 uppercase mb-1">Kontak <span class="text-rose-500">*</span></label>
                            <input type="text" name="kontak" id="kontak" value="<?= htmlspecialchars($kontak) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Perusahaan</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="penanggung_jawab" class="block text-xs font-bold text-slate-700 uppercase mb-1">Penanggung Jawab / PIC</label>
                            <input type="text" name="penanggung_jawab" id="penanggung_jawab" value="<?= htmlspecialchars($penanggung_jawab) ?>"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="alamat" class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Kantor <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" id="alamat" rows="3" required
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($alamat) ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
