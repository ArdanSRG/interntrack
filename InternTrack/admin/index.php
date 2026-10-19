<?php
// admin/index.php - Dashboard Administrator dan Pembimbing
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Dashboard Utama';

// 1. Total Siswa PKL
$stmt = $pdo->query("SELECT COUNT(*) FROM siswa");
$total_siswa = (int)$stmt->fetchColumn();

// 2. Total Perusahaan
$stmt = $pdo->query("SELECT COUNT(*) FROM perusahaan");
$total_perusahaan = (int)$stmt->fetchColumn();

// 3. Total Pembimbing
$stmt = $pdo->query("SELECT COUNT(*) FROM pembimbing");
$total_pembimbing = (int)$stmt->fetchColumn();

// 4. PKL Aktif
$stmt = $pdo->query("SELECT COUNT(*) FROM penempatan WHERE status = 'Aktif'");
$pkl_aktif = (int)$stmt->fetchColumn();

// 5. PKL Selesai
$stmt = $pdo->query("SELECT COUNT(*) FROM penempatan WHERE status = 'Selesai'");
$pkl_selesai = (int)$stmt->fetchColumn();

// 6. Jurnal Hari Ini (Dihitung berdasarkan tanggal hari ini secara dinamis)
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jurnal WHERE tanggal = ?");
$stmt->execute([$today]);
$jurnal_hari_ini = (int)$stmt->fetchColumn();

// 7. Siswa Perlu Perhatian (< 75% kehadiran atau berstatus 'Perlu Perhatian' / 'Perlu Pembinaan')
// Dihitung dinamis dari agregasi absensi per siswa
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.nama,
        s.nis,
        s.kelas,
        p.nama_perusahaan,
        COUNT(a.id) as total_hari,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
        SUM(CASE WHEN a.status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
    FROM siswa s
    LEFT JOIN penempatan pen ON pen.siswa_id = s.id AND pen.status = 'Aktif'
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    INNER JOIN absensi a ON a.siswa_id = s.id
    GROUP BY s.id, s.nama, s.nis, s.kelas, p.nama_perusahaan
    HAVING total_hari > 0
");
$rekap_absensi_siswa = $stmt->fetchAll();

$siswa_perlu_perhatian_list = [];
foreach ($rekap_absensi_siswa as $row) {
    $persen = hitungPersentaseKehadiran($row['total_hadir'], $row['total_hari']);
    $status = hitungStatusKehadiran($persen);
    if ($persen < 75) {
        $row['persentase'] = $persen;
        $row['status_kehadiran'] = $status;
        $siswa_perlu_perhatian_list[] = $row;
    }
}
$siswa_perlu_perhatian = count($siswa_perlu_perhatian_list);

// Ambil 5 Jurnal Terbaru
$stmt = $pdo->query("
    SELECT j.*, s.nama as nama_siswa, s.nis, s.kelas, p.nama_perusahaan 
    FROM jurnal j
    JOIN siswa s ON s.id = j.siswa_id
    LEFT JOIN penempatan pen ON pen.id = j.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    ORDER BY j.tanggal DESC, j.id DESC
    LIMIT 5
");
$jurnal_terbaru = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Banner Sambutan & Format Wajib Dashboard (Sesuai Ketentuan Modul I) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Kolom Kiri: Ringkasan Kartu Modern -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-blue-100 backdrop-blur-sm mb-3">
                                <i class="fa-solid fa-sparkles text-amber-300"></i> Sistem Monitoring Terpadu
                            </span>
                            <h2 class="text-2xl font-black tracking-tight">Selamat Datang, <?= htmlspecialchars($current_user['nama_lengkap']) ?>!</h2>
                            <p class="text-blue-100 text-sm mt-1 max-w-xl">
                                Pantau aktivitas harian, rekapitulasi kehadiran, dan evaluasi hasil bimbingan praktik kerja lapangan siswa SMK secara real-time.
                            </p>
                            
                            <div class="flex flex-wrap gap-3 mt-5">
                                <a href="<?= base_url('admin/penempatan/tambah.php') ?>" class="px-4 py-2 bg-white text-blue-800 hover:bg-blue-50 rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Penempatan Baru
                                </a>
                                <a href="<?= base_url('admin/monitoring/tambah.php') ?>" class="px-4 py-2 bg-blue-600/60 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all border border-blue-400/30 flex items-center gap-2">
                                    <i class="fa-solid fa-clipboard-check"></i> Input Monitoring
                                </a>
                                <a href="<?= base_url('laporan/index.php') ?>" class="px-4 py-2 bg-slate-900/40 hover:bg-slate-900/60 text-white rounded-xl text-xs font-bold transition-all border border-white/20 flex items-center gap-2">
                                    <i class="fa-solid fa-print"></i> Cetak Laporan
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Kartu Metrik Utama -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 uppercase">Siswa PKL</span>
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-black text-slate-900 mt-2"><?= $total_siswa ?></p>
                            <span class="text-[11px] text-slate-400">Total terdaftar</span>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 uppercase">Perusahaan</span>
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-black text-slate-900 mt-2"><?= $total_perusahaan ?></p>
                            <span class="text-[11px] text-slate-400">Mitra industri (DUDI)</span>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 uppercase">PKL Aktif</span>
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-circle-play"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-black text-emerald-600 mt-2"><?= $pkl_aktif ?></p>
                            <span class="text-[11px] text-slate-400">Sedang magang</span>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 uppercase">Perlu Perhatian</span>
                                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-black text-rose-600 mt-2"><?= $siswa_perlu_perhatian ?></p>
                            <span class="text-[11px] text-rose-500 font-medium">Kehadiran &lt; 75%</span>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Format Informasi Wajib Modul I (Terminal Block Style) -->
                <div class="lg:col-span-5">
                    <div class="bg-slate-950 rounded-2xl p-6 border border-slate-800 shadow-xl">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                <span class="text-xs font-mono text-slate-400 ml-2">interntrack-terminal</span>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800">DINAMIS DARI DATABASE</span>
                        </div>

                        <!-- Blok Teks Monospace Persis Sesuai Modul I -->
                        <div class="ascii-box">
<pre class="font-mono text-xs sm:text-sm text-cyan-300 whitespace-pre">
========================================
              INTERNTRACK
========================================

Total Siswa PKL       : <?= str_pad($total_siswa, 2, ' ', STR_PAD_LEFT) ?>

Perusahaan            : <?= str_pad($total_perusahaan, 2, ' ', STR_PAD_LEFT) ?>

Pembimbing            : <?= str_pad($total_pembimbing, 2, ' ', STR_PAD_LEFT) ?>

PKL Aktif             : <?= str_pad($pkl_aktif, 2, ' ', STR_PAD_LEFT) ?>

PKL Selesai           : <?= str_pad($pkl_selesai, 2, ' ', STR_PAD_LEFT) ?>


----------------------------------------

Jurnal Hari Ini       : <?= str_pad($jurnal_hari_ini, 2, ' ', STR_PAD_LEFT) ?>

Siswa Perlu Perhatian : <?= str_pad($siswa_perlu_perhatian, 2, ' ', STR_PAD_LEFT) ?>


========================================</pre>
                        </div>
                        <p class="text-[11px] font-mono text-slate-400 text-center mt-3">
                            Format Output Sesuai Spesifikasi Soal Modul I
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bagian 2 Kolom: Siswa Perlu Perhatian & Jurnal Terkini -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Tabel Siswa Perlu Perhatian (<75% kehadiran) -->
                <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                <span>Siswa Memerlukan Perhatian / Pembinaan</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Siswa dengan persentase kehadiran di bawah 75%</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                            <?= $siswa_perlu_perhatian ?> Siswa
                        </span>
                    </div>

                    <?php if (empty($siswa_perlu_perhatian_list)): ?>
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2"></i>
                            <p>Semua siswa memiliki rekapitulasi kehadiran baik (≥ 75%).</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto mt-4">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                        <th class="py-2.5 px-3">Siswa</th>
                                        <th class="py-2.5 px-3">Perusahaan</th>
                                        <th class="py-2.5 px-3 text-center">Hadir/Total</th>
                                        <th class="py-2.5 px-3 text-center">% Kehadiran</th>
                                        <th class="py-2.5 px-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($siswa_perlu_perhatian_list as $row): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-3">
                                                <a href="<?= base_url('admin/siswa/detail.php?id=' . $row['id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                    <?= htmlspecialchars($row['nama']) ?>
                                                </a>
                                                <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($row['nis']) ?> • <?= htmlspecialchars($row['kelas']) ?></span>
                                            </td>
                                            <td class="py-3 px-3 text-slate-600">
                                                <?= htmlspecialchars($row['nama_perusahaan'] ?? 'Belum Ditempatkan') ?>
                                            </td>
                                            <td class="py-3 px-3 text-center font-mono">
                                                <span class="text-emerald-600 font-bold"><?= $row['total_hadir'] ?></span> / <?= $row['total_hari'] ?>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="font-bold text-xs <?= $row['persentase'] < 60 ? 'text-rose-600' : 'text-amber-600' ?>">
                                                    <?= $row['persentase'] ?>%
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <?= badgeStatusKehadiran($row['status_kehadiran']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Jurnal Terkini -->
                <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-book-journal-whills text-blue-600"></i>
                                <span>Jurnal Kegiatan Terbaru</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Aktivitas dan log harian yang diisi oleh siswa</p>
                        </div>
                        <a href="<?= base_url('admin/jurnal/index.php') ?>" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        <?php foreach ($jurnal_terbaru as $j): ?>
                            <div class="py-3.5 hover:bg-slate-50/80 rounded-xl px-2 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold text-slate-800"><?= htmlspecialchars($j['kegiatan']) ?></p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            <span class="font-semibold text-slate-700"><?= htmlspecialchars($j['nama_siswa']) ?></span> (<?= htmlspecialchars($j['kelas']) ?>) 
                                            • <i class="fa-regular fa-calendar-days text-[10px]"></i> <?= format_tanggal_id($j['tanggal']) ?>
                                        </p>
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-1 italic">
                                            Hasil: <?= htmlspecialchars($j['hasil']) ?>
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <?= badgeValidasiJurnal($j['status_validasi']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
