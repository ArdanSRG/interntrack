<?php
// admin/absensi/index.php - Rekapitulasi & Log Absensi Kehadiran Siswa PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Rekap & Data Absensi Kehadiran';

$tab = clean_input($_GET['tab'] ?? 'rekap'); // 'rekap' atau 'log'
$search = clean_input($_GET['search'] ?? '');
$filter_status_kehadiran = clean_input($_GET['status_kehadiran'] ?? '');

// 1. DATA REKAP PERSENTASE KEHADIRAN (ALGORITMA 3)
$sqlRekap = "
    SELECT 
        s.id as siswa_id,
        s.nis,
        s.nama,
        s.kelas,
        s.jurusan,
        p.nama_perusahaan,
        pen.status as status_penempatan,
        COUNT(a.id) as total_hari,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
        SUM(CASE WHEN a.status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
    FROM siswa s
    LEFT JOIN penempatan pen ON pen.siswa_id = s.id AND pen.status = 'Aktif'
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    LEFT JOIN absensi a ON a.siswa_id = s.id
    WHERE 1=1
";
$paramsRekap = [];

if (!empty($search)) {
    $sqlRekap .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
    $paramsRekap[] = "%$search%";
    $paramsRekap[] = "%$search%";
}

$sqlRekap .= " GROUP BY s.id, s.nis, s.nama, s.kelas, s.jurusan, p.nama_perusahaan, pen.status ORDER BY s.nama ASC";

$stmtRekap = $pdo->prepare($sqlRekap);
$stmtRekap->execute($paramsRekap);
$rawRekap = $stmtRekap->fetchAll();

$rekap_data = [];
foreach ($rawRekap as $row) {
    $persen = hitungPersentaseKehadiran((int)$row['total_hadir'], (int)$row['total_hari']);
    $status_pkl = hitungStatusKehadiran($persen);
    $row['persentase'] = $persen;
    $row['status_kehadiran'] = $status_pkl;

    // Filter berdasarkan status kehadiran kualitatif jika dipilih
    if (!empty($filter_status_kehadiran)) {
        if ($status_pkl !== $filter_status_kehadiran) {
            continue;
        }
    }
    $rekap_data[] = $row;
}

// 2. DATA LOG ABSENSI HARIAN
$filter_tgl = clean_input($_GET['tanggal'] ?? '');
$filter_abs_stat = clean_input($_GET['status_absensi'] ?? '');

$sqlLog = "
    SELECT a.*, s.nama as nama_siswa, s.nis, s.kelas, p.nama_perusahaan
    FROM absensi a
    JOIN siswa s ON s.id = a.siswa_id
    LEFT JOIN penempatan pen ON pen.id = a.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    WHERE 1=1
";
$paramsLog = [];

if (!empty($search)) {
    $sqlLog .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
    $paramsLog[] = "%$search%";
    $paramsLog[] = "%$search%";
}

if (!empty($filter_tgl)) {
    $sqlLog .= " AND a.tanggal = ?";
    $paramsLog[] = $filter_tgl;
}

if (!empty($filter_abs_stat)) {
    $sqlLog .= " AND a.status = ?";
    $paramsLog[] = $filter_abs_stat;
}

$sqlLog .= " ORDER BY a.tanggal DESC, a.id DESC LIMIT 100";

$stmtLog = $pdo->prepare($sqlLog);
$stmtLog->execute($paramsLog);
$log_absensi = $stmtLog->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Manajemen Absensi & Rekapitulasi</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Perhitungan otomatis persentase kehadiran dan status perkembangan PKL (Algoritma 3)</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/absensi/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Input Absensi</span>
                    </a>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b border-slate-200 gap-4">
                <a href="<?= base_url('admin/absensi/index.php?tab=rekap') ?>" 
                   class="pb-3 px-2 text-sm font-bold border-b-2 transition-colors flex items-center gap-2 <?= $tab === 'rekap' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Rekap Persentase & Status PKL</span>
                </a>
                <a href="<?= base_url('admin/absensi/index.php?tab=log') ?>" 
                   class="pb-3 px-2 text-sm font-bold border-b-2 transition-colors flex items-center gap-2 <?= $tab === 'log' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Log Riwayat Absensi Harian</span>
                </a>
            </div>

            <?php if ($tab === 'rekap'): ?>
                <!-- TAB 1: REKAP PERSENTASE SISWA -->

                <!-- Keterangan Algoritma 3 Box -->
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-4 sm:p-5 text-white shadow-md">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider">Algoritma 3: Penentuan Status PKL Berdasarkan Persentase Kehadiran</span>
                            <p class="text-xs text-slate-300 mt-1">
                                Formula: <code class="bg-slate-950 px-2 py-0.5 rounded text-cyan-300 font-mono">Persentase = (Hadir &divide; Total Hari) &times; 100%</code>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] font-bold">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">&ge; 90% : Sangat Baik</span>
                            <span class="px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30">75% - 89% : Baik</span>
                            <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">60% - 74% : Perlu Perhatian</span>
                            <span class="px-2.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">&lt; 60% : Perlu Pembinaan</span>
                        </div>
                    </div>
                </div>

                <!-- Filter Rekap -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <form action="<?= base_url('admin/absensi/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <input type="hidden" name="tab" value="rekap">
                        
                        <div class="sm:col-span-7">
                            <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Cari Siswa</label>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama siswa atau NIS..."
                                   class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="sm:col-span-4">
                            <label for="status_kehadiran" class="block text-xs font-bold text-slate-600 uppercase mb-1">Status PKL (Kualitatif)</label>
                            <select name="status_kehadiran" id="status_kehadiran" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Semua Status --</option>
                                <option value="Sangat Baik" <?= $filter_status_kehadiran === 'Sangat Baik' ? 'selected' : '' ?>>Sangat Baik (&ge; 90%)</option>
                                <option value="Baik" <?= $filter_status_kehadiran === 'Baik' ? 'selected' : '' ?>>Baik (75% - 89%)</option>
                                <option value="Perlu Perhatian" <?= $filter_status_kehadiran === 'Perlu Perhatian' ? 'selected' : '' ?>>Perlu Perhatian (60% - 74%)</option>
                                <option value="Perlu Pembinaan" <?= $filter_status_kehadiran === 'Perlu Pembinaan' ? 'selected' : '' ?>>Perlu Pembinaan (&lt; 60%)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-1">
                            <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Rekapitulasi -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 text-center w-12">No</th>
                                    <th class="py-3 px-4">Nama Siswa / NIS</th>
                                    <th class="py-3 px-4">Perusahaan PKL</th>
                                    <th class="py-3 px-3 text-center">Hadir</th>
                                    <th class="py-3 px-3 text-center">Izin</th>
                                    <th class="py-3 px-3 text-center">Sakit</th>
                                    <th class="py-3 px-3 text-center">Alpa</th>
                                    <th class="py-3 px-3 text-center">Total Hari</th>
                                    <th class="py-3 px-4 text-center">% Kehadiran</th>
                                    <th class="py-3 px-4 text-center">Status PKL</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($rekap_data)): ?>
                                    <tr>
                                        <td colspan="10" class="py-8 text-center text-slate-400 text-xs">Tidak ada data rekapitulasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rekap_data as $idx => $r): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                            <td class="py-3 px-4">
                                                <a href="<?= base_url('admin/siswa/detail.php?id=' . $r['siswa_id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                    <?= htmlspecialchars($r['nama']) ?>
                                                </a>
                                                <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($r['nis']) ?> • <?= htmlspecialchars($r['kelas']) ?></span>
                                            </td>
                                            <td class="py-3 px-4 text-slate-700">
                                                <?= htmlspecialchars($r['nama_perusahaan'] ?? 'Belum Ditempatkan') ?>
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-emerald-600"><?= $r['total_hadir'] ?></td>
                                            <td class="py-3 px-3 text-center font-semibold text-blue-600"><?= $r['total_izin'] ?></td>
                                            <td class="py-3 px-3 text-center font-semibold text-amber-600"><?= $r['total_sakit'] ?></td>
                                            <td class="py-3 px-3 text-center font-bold text-rose-600"><?= $r['total_alpa'] ?></td>
                                            <td class="py-3 px-3 text-center font-mono font-bold text-slate-800"><?= $r['total_hari'] ?></td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="font-black font-mono text-sm <?= $r['persentase'] >= 90 ? 'text-emerald-600' : ($r['persentase'] >= 75 ? 'text-blue-600' : ($r['persentase'] >= 60 ? 'text-amber-600' : 'text-rose-600')) ?>">
                                                    <?= $r['persentase'] ?>%
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <?= badgeStatusKehadiran($r['status_kehadiran']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- TAB 2: LOG HARIAN ABSENSI -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <form action="<?= base_url('admin/absensi/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <input type="hidden" name="tab" value="log">
                        
                        <div class="sm:col-span-5">
                            <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Cari Siswa</label>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama siswa..."
                                   class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="sm:col-span-3">
                            <label for="tanggal" class="block text-xs font-bold text-slate-600 uppercase mb-1">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($filter_tgl) ?>"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="sm:col-span-3">
                            <label for="status_absensi" class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Kehadiran</label>
                            <select name="status_absensi" id="status_absensi" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Semua --</option>
                                <option value="Hadir" <?= $filter_abs_stat === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                <option value="Izin" <?= $filter_abs_stat === 'Izin' ? 'selected' : '' ?>>Izin</option>
                                <option value="Sakit" <?= $filter_abs_stat === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                <option value="Alpa" <?= $filter_abs_stat === 'Alpa' ? 'selected' : '' ?>>Alpa</option>
                            </select>
                        </div>

                        <div class="sm:col-span-1">
                            <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4">No</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Nama Siswa</th>
                                    <th class="py-3 px-4">Perusahaan PKL</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($log_absensi)): ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Tidak ada log absensi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($log_absensi as $idx => $a): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 px-4 text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                            <td class="py-3 px-4 font-bold text-slate-800"><?= format_tanggal_id($a['tanggal']) ?></td>
                                            <td class="py-3 px-4">
                                                <span class="font-bold text-slate-800"><?= htmlspecialchars($a['nama_siswa']) ?></span>
                                                <p class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($a['nis']) ?> - <?= htmlspecialchars($a['kelas']) ?></p>
                                            </td>
                                            <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($a['nama_perusahaan'] ?? '-') ?></td>
                                            <td class="py-3 px-4 text-center"><?= badgeAbsensi($a['status']) ?></td>
                                            <td class="py-3 px-4 text-slate-600 italic"><?= htmlspecialchars($a['keterangan'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
