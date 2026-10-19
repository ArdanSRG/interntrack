<?php
// admin/pembimbing/detail.php - Detail Guru & Siswa Bimbingan
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT pem.*, u.username FROM pembimbing pem LEFT JOIN users u ON u.id = pem.user_id WHERE pem.id = ?");
$stmt->execute([$id]);
$pembimbing = $stmt->fetch();

if (!$pembimbing) {
    set_flash('danger', 'Pembimbing tidak ditemukan.');
    header('Location: ' . base_url('admin/pembimbing/index.php'));
    exit;
}

$page_title = 'Detail Guru Pembimbing - ' . $pembimbing['nama'];

// Daftar siswa bimbingan
$stmtSiswa = $pdo->prepare("
    SELECT pen.*, s.nama as nama_siswa, s.nis, s.kelas, s.jurusan, p.nama_perusahaan
    FROM penempatan pen
    JOIN siswa s ON s.id = pen.siswa_id
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    WHERE pen.pembimbing_id = ?
    ORDER BY pen.status ASC, s.nama ASC
");
$stmtSiswa->execute([$id]);
$bimbingan_list = $stmtSiswa->fetchAll();

// Daftar monitoring yang telah diinput oleh pembimbing ini
$stmtMon = $pdo->prepare("
    SELECT m.*, s.nama as nama_siswa, s.nis
    FROM monitoring m
    JOIN siswa s ON s.id = m.siswa_id
    WHERE m.pembimbing_id = ?
    ORDER BY m.tanggal_monitoring DESC
");
$stmtMon->execute([$id]);
$monitoring_list = $stmtMon->fetchAll();

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
                        <span class="text-slate-600 font-medium">Detail Profil</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800"><?= htmlspecialchars($pembimbing['nama']) ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('admin/pembimbing/edit.php?id=' . $id) ?>" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-colors">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                    </a>
                    <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-3.5 py-2 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-xl text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">NIP: <?= htmlspecialchars($pembimbing['nip']) ?></span>
                            <h3 class="text-base font-bold text-slate-900 mt-1"><?= htmlspecialchars($pembimbing['nama']) ?></h3>
                            <p class="text-xs text-slate-500">Guru Pembimbing Praktik Kerja Lapangan</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-xs border-t md:border-t-0 md:border-l border-slate-100 md:pl-6 pt-3 md:pt-0">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Nomor Telepon/WA:</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($pembimbing['no_hp']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Email Kedinasan:</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($pembimbing['email'] ?? '-') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Username Akun:</span>
                        <span class="font-mono font-bold text-blue-600"><?= htmlspecialchars($pembimbing['username'] ?? '-') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Total Bimbingan:</span>
                        <span class="font-bold text-indigo-600"><?= count($bimbingan_list) ?> Siswa</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Siswa Bimbingan</h3>
                    <span class="text-xs text-slate-400"><?= count($bimbingan_list) ?> Siswa Terdaftar</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Siswa</th>
                                <th class="py-3 px-4">Kelas / Jurusan</th>
                                <th class="py-3 px-4">Perusahaan Tempat PKL</th>
                                <th class="py-3 px-4">Periode</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($bimbingan_list)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs">Belum ada siswa yang ditugaskan kepada pembimbing ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bimbingan_list as $idx => $s): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4 text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-800">
                                            <a href="<?= base_url('admin/siswa/detail.php?id=' . $s['siswa_id']) ?>" class="hover:text-blue-600">
                                                <?= htmlspecialchars($s['nama_siswa']) ?>
                                            </a>
                                            <p class="text-[11px] text-slate-400 font-normal">NIS: <?= htmlspecialchars($s['nis']) ?></p>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700">
                                            <?= htmlspecialchars($s['kelas']) ?>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-800">
                                            <?= htmlspecialchars($s['nama_perusahaan']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-600">
                                            <?= format_tanggal_id($s['tanggal_mulai']) ?> s.d <?= format_tanggal_id($s['tanggal_selesai']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?= badgeStatusPenempatan($s['status']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
