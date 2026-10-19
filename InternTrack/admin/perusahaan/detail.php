<?php
// admin/perusahaan/detail.php - Detail Perusahaan & Siswa yang Magang
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

$page_title = 'Detail Perusahaan - ' . $perusahaan['nama_perusahaan'];

// Ambil daftar siswa yang ditempatkan di perusahaan ini
$stmtSiswa = $pdo->prepare("
    SELECT pen.*, s.nama as nama_siswa, s.nis, s.kelas, s.jurusan, s.no_hp as hp_siswa,
           pem.nama as nama_pembimbing, pem.nip
    FROM penempatan pen
    JOIN siswa s ON s.id = pen.siswa_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE pen.perusahaan_id = ?
    ORDER BY pen.status ASC, s.nama ASC
");
$stmtSiswa->execute([$id]);
$siswa_terdaftar = $stmtSiswa->fetchAll();

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
                        <span class="text-slate-600 font-medium">Detail Perusahaan</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800"><?= htmlspecialchars($perusahaan['nama_perusahaan']) ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('admin/perusahaan/edit.php?id=' . $id) ?>" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="px-3.5 py-2 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-xl text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Info Profil Perusahaan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800"><?= htmlspecialchars($perusahaan['nama_perusahaan']) ?></h3>
                            <span class="text-xs font-semibold text-indigo-600"><?= htmlspecialchars($perusahaan['bidang_usaha']) ?></span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 pt-2"><i class="fa-solid fa-location-dot text-slate-400 mr-1.5"></i><?= htmlspecialchars($perusahaan['alamat']) ?></p>
                </div>

                <div class="space-y-2 text-xs border-t md:border-t-0 md:border-l border-slate-100 md:pl-6 pt-3 md:pt-0">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Nomor Telepon:</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($perusahaan['kontak']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Email:</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($perusahaan['email'] ?? '-') ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Penanggung Jawab (PIC):</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($perusahaan['penanggung_jawab'] ?? '-') ?></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Total Siswa Terdaftar:</span>
                        <span class="font-bold text-blue-600"><?= count($siswa_terdaftar) ?> Siswa</span>
                    </div>
                </div>
            </div>

            <!-- Daftar Siswa Magang di Perusahaan Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Siswa Peserta PKL di Instansi Ini</h3>
                    <a href="<?= base_url('admin/penempatan/tambah.php') ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        + Tambah Penempatan
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Siswa</th>
                                <th class="py-3 px-4">Kelas / Jurusan</th>
                                <th class="py-3 px-4">Guru Pembimbing</th>
                                <th class="py-3 px-4">Periode</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($siswa_terdaftar)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs">Belum ada siswa yang ditempatkan di perusahaan ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($siswa_terdaftar as $idx => $s): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4 text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/siswa/detail.php?id=' . $s['siswa_id']) ?>" class="font-bold text-slate-800 hover:text-blue-600">
                                                <?= htmlspecialchars($s['nama_siswa']) ?>
                                            </a>
                                            <p class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($s['nis']) ?></p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <p class="font-semibold text-slate-700"><?= htmlspecialchars($s['kelas']) ?></p>
                                            <p class="text-[11px] text-slate-400"><?= htmlspecialchars($s['jurusan']) ?></p>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700">
                                            <?= htmlspecialchars($s['nama_pembimbing']) ?>
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
