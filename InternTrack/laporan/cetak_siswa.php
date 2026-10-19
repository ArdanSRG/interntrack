<?php
// laporan/cetak_siswa.php - Laporan Resmi Data Siswa PKL
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role(['admin', 'pembimbing']);

$filter_kelas = clean_input($_GET['kelas'] ?? '');
$filter_status = clean_input($_GET['status'] ?? '');

$sql = "
    SELECT 
        s.*, 
        pen.status as status_penempatan,
        pen.tanggal_mulai,
        pen.tanggal_selesai,
        p.nama_perusahaan,
        pem.nama as nama_pembimbing
    FROM siswa s
    LEFT JOIN penempatan pen ON pen.siswa_id = s.id AND pen.status = 'Aktif'
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    LEFT JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE 1=1
";
$params = [];

if (!empty($filter_kelas)) {
    $sql .= " AND s.kelas = ?";
    $params[] = $filter_kelas;
}

if (!empty($filter_status)) {
    $sql .= " AND pen.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY s.kelas ASC, s.nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Siswa PKL - InternTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-slate-100 p-4 sm:p-8">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= base_url('laporan/index.php') ?>" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-colors">
            &larr; Kembali ke Pusat Laporan
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Dokumen / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Paper Container -->
    <div class="max-w-5xl mx-auto bg-white p-8 sm:p-12 shadow-lg rounded-xl print-container">
        
        <!-- Kop Surat Resmi -->
        <div class="kop-surat flex items-center gap-6 pb-4 border-b-4 border-double border-slate-900 mb-6">
            <div class="w-20 h-20 bg-slate-900 rounded-xl flex items-center justify-center text-white text-3xl font-bold shrink-0">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="text-center flex-1">
                <h3 class="text-xs uppercase tracking-widest font-bold text-slate-600">Pemerintah Provinsi Jawa Tengah &bull; Dinas Pendidikan</h3>
                <h1 class="text-xl sm:text-2xl font-black uppercase text-slate-900">SMK NEGERI REKAYASA PERANGKAT LUNAK</h1>
                <p class="text-xs text-slate-600 mt-0.5">Jalan Pendidikan Kejuruan No. 124, Kota Semarang, Jawa Tengah &bull; Telp: (024) 8412345</p>
                <p class="text-[11px] text-blue-700 font-medium">Website: smkrpl.sch.id &bull; Email: hubin@smkrpl.sch.id &bull; Sistem Monitoring InternTrack</p>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-6">
            <h2 class="text-base sm:text-lg font-bold uppercase tracking-wide text-slate-900 underline underline-offset-4">
                LAPORAN DATA SISWA PRAKTIK KERJA LAPANGAN (PKL)
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Tahun Ajaran 2026/2027 &bull; <?= $filter_kelas ? 'Kelas: ' . htmlspecialchars($filter_kelas) : 'Semua Kelas' ?>
            </p>
        </div>

        <!-- Tabel Cetak -->
        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
                <tr>
                    <th class="border border-slate-300 p-2 text-center w-10">No</th>
                    <th class="border border-slate-300 p-2">NIS</th>
                    <th class="border border-slate-300 p-2">Nama Siswa</th>
                    <th class="border border-slate-300 p-2">Kelas & Jurusan</th>
                    <th class="border border-slate-300 p-2">Perusahaan Tempat PKL</th>
                    <th class="border border-slate-300 p-2">Guru Pembimbing</th>
                    <th class="border border-slate-300 p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswa_list)): ?>
                    <tr>
                        <td colspan="7" class="border border-slate-300 p-4 text-center text-slate-500">Tidak ada data siswa yang sesuai.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswa_list as $idx => $s): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border border-slate-300 p-2 text-center font-mono"><?= $idx + 1 ?></td>
                            <td class="border border-slate-300 p-2 font-mono font-bold"><?= htmlspecialchars($s['nis']) ?></td>
                            <td class="border border-slate-300 p-2 font-bold"><?= htmlspecialchars($s['nama']) ?></td>
                            <td class="border border-slate-300 p-2">
                                <?= htmlspecialchars($s['kelas']) ?> - <?= htmlspecialchars($s['jurusan']) ?>
                            </td>
                            <td class="border border-slate-300 p-2">
                                <?= htmlspecialchars($s['nama_perusahaan'] ?? 'Belum Ditempatkan') ?>
                            </td>
                            <td class="border border-slate-300 p-2">
                                <?= htmlspecialchars($s['nama_pembimbing'] ?? '-') ?>
                            </td>
                            <td class="border border-slate-300 p-2 text-center font-semibold">
                                <?= htmlspecialchars($s['status_penempatan'] ?? 'Belum Mulai') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Total Rekap Footer -->
        <div class="mt-4 text-xs text-slate-600 flex justify-between font-medium">
            <span>Total Siswa: <strong><?= count($siswa_list) ?> Orang</strong></span>
            <span>Dicetak Pada: <?= date('d/m/Y H:i') ?> WIB</span>
        </div>

        <!-- Kolom Tanda Tangan -->
        <div class="tanda-tangan mt-12 grid grid-cols-2 text-xs">
            <div class="text-center">
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala SMK Negeri RPL</p>
                <div class="h-20"></div>
                <p class="font-bold underline">Dr. H. Bambang Hartono, M.Pd</p>
                <p class="text-slate-500 font-mono">NIP. 197005121995031002</p>
            </div>
            <div class="text-center">
                <p>Semarang, <?= format_tanggal_id(date('Y-m-d')) ?></p>
                <p class="font-bold">Koordinator Pokja Hubin & PKL</p>
                <div class="h-20"></div>
                <p class="font-bold underline"><?= htmlspecialchars($current_user['nama_lengkap']) ?></p>
                <p class="text-slate-500 font-mono">Koordinator Pelaksana</p>
            </div>
        </div>

    </div>

</body>
</html>
