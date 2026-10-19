<?php
// admin/pembimbing/edit.php - Edit Pembimbing
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pembimbing WHERE id = ?");
$stmt->execute([$id]);
$pembimbing = $stmt->fetch();

if (!$pembimbing) {
    set_flash('danger', 'Pembimbing tidak ditemukan.');
    header('Location: ' . base_url('admin/pembimbing/index.php'));
    exit;
}

$page_title = 'Edit Pembimbing - ' . $pembimbing['nama'];
$errors = [];

$nip = $pembimbing['nip'];
$nama = $pembimbing['nama'];
$no_hp = $pembimbing['no_hp'];
$email = $pembimbing['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip   = clean_input($_POST['nip'] ?? '');
    $nama  = clean_input($_POST['nama'] ?? '');
    $no_hp = clean_input($_POST['no_hp'] ?? '');
    $email = clean_input($_POST['email'] ?? '');

    if (empty($nip)) $errors['nip'] = 'NIP wajib diisi.';
    if (empty($nama)) $errors['nama'] = 'Nama wajib diisi.';
    if (empty($no_hp)) $errors['no_hp'] = 'No HP wajib diisi.';

    if (!empty($nip)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM pembimbing WHERE nip = ? AND id != ? LIMIT 1");
        $stmtCheck->execute([$nip, $id]);
        if ($stmtCheck->fetch()) {
            $errors['nip'] = 'NIP sudah dipakai guru lain.';
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE pembimbing SET nip = ?, nama = ?, no_hp = ?, email = ? WHERE id = ?");
            $stmt->execute([$nip, $nama, $no_hp, $email, $id]);

            if (!empty($pembimbing['user_id'])) {
                $stmtU = $pdo->prepare("UPDATE users SET nama_lengkap = ? WHERE id = ?");
                $stmtU->execute([$nama, $pembimbing['user_id']]);
            }

            set_flash('success', "Data pembimbing {$nama} berhasil diperbarui.");
            header('Location: ' . base_url('admin/pembimbing/index.php'));
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
                        <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="hover:text-blue-600">Data Pembimbing</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Edit Pembimbing</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Edit Data Pembimbing</h2>
                </div>
                <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm max-w-2xl overflow-hidden">
                <form action="<?= base_url('admin/pembimbing/edit.php?id=' . $id) ?>" method="POST" class="p-6 space-y-5" novalidate>
                    <div>
                        <label for="nip" class="block text-xs font-bold text-slate-700 uppercase mb-1">NIP <span class="text-rose-500">*</span></label>
                        <input type="text" name="nip" id="nip" value="<?= htmlspecialchars($nip) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="nama" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($nama) ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="no_hp" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Handphone <span class="text-rose-500">*</span></label>
                            <input type="text" name="no_hp" id="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Sekolah</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-4 py-2.5 border border-slate-300 rounded-xl text-slate-600 hover:bg-slate-50 text-xs font-bold">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
