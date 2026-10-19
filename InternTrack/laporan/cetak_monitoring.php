<?php
// laporan/cetak_monitoring.php - Laporan Resmi Hasil Monitoring Guru Pembimbing
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role(['admin', 'pembimbing']);

$filter_pembimbing = (int)($_GET['pembimbing'] ?? 0);
$filter_tanggal = clean_input($_GET['tanggal'] ?? '');

$sql = "
    SELECT 
        m.*, 
        s.nama as nama_siswa, s.nis, s.kelas,
        p.nama_perusahaan,
        pem.nama as nama_pembimbing, pem.nip
    FROM monitoring m
    JOIN siswa s ON s.id = m.siswa_id
    JOIN pembimbing pem ON pem.id = m.pembimbing_id
    LEFT JOIN penempatan pen ON pen.id = m.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    WHERE 1=1
";
$params = [];

if ($filter_pembimbing > 0) {
    $sql .= " AND m.pembimbing_id = ?";
    $params[] = $filter_pembimbing;
}

if (!empty($filter_tanggal)) {
    $sql .= " AND m.tanggal_monitoring = ?";
    $params[] = $filter_tanggal;
}

$sql .= " ORDER BY m.tanggal_monitoring DESC, s.nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$monitoring_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Monitoring Pembimbing PKL - InternTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-slate-100 p-4 sm:p-8">

    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= base_url('laporan/index.php') ?>" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-colors">
            &larr; Kembali ke Pusat Laporan
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Cetak Laporan Monitoring / Simpan PDF
        </button>
    </div>

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
                <p class="text-[11px] text-blue-700 font-medium">Laporan Rekapitulasi Monitoring & Evaluasi Kunjungan Industri Siswa PKL</p>
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="text-base sm:text-lg font-bold uppercase tracking-wide text-slate-900 underline underline-offset-4">
                LAPORAN MONITORING DAN EVALUASI PEMBIMBING PKL
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Tahun Ajaran 2026/2027 &bull; Rekap Catatan Supervisi dan Penilaian Kinerja Industri
            </p>
        </div>

        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
                <tr>
                    <th class="border border-slate-300 p-2 text-center w-10">No</th>
                    <th class="border border-slate-300 p-2">Tanggal</th>
                    <th class="border border-slate-300 p-2">Nama Siswa / NIS</th>
                    <th class="border border-slate-300 p-2">Perusahaan PKL</th>
                    <th class="border border-slate-300 p-2 text-center">Nilai</th>
                    <th class="border border-slate-300 p-2">Catatan Lapangan</th>
                    <th class="border border-slate-300 p-2">Rekomendasi</th>
                    <th class="border border-slate-300 p-2">Guru Pembimbing</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($monitoring_list)): ?>
                    <tr>
                        <td colspan="8" class="border border-slate-300 p-4 text-center text-slate-500">Tidak ada data evaluasi monitoring.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($monitoring_list as $idx => $m): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border border-slate-300 p-2 text-center font-mono"><?= $idx + 1 ?></td>
                            <td class="border border-slate-300 p-2 whitespace-nowrap font-medium"><?= format_tanggal_id($m['tanggal_monitoring']) ?></td>
                            <td class="border border-slate-300 p-2 font-bold">
                                <?= htmlspecialchars($m['nama_siswa']) ?>
                                <span class="block text-[10px] text-slate-500 font-normal">NIS: <?= htmlspecialchars($m['nis']) ?></span>
                            </td>
                            <td class="border border-slate-300 p-2 font-medium"><?= htmlspecialchars($m['nama_perusahaan'] ?? '-') ?></td>
                            <td class="border border-slate-300 p-2 text-center font-black font-mono">
                                <?= $m['nilai'] ?>
                            </td>
                            <td class="border border-slate-300 p-2"><?= htmlspecialchars($m['catatan']) ?></td>
                            <td class="border border-slate-300 p-2 text-blue-900 font-medium"><?= htmlspecialchars($m['rekomendasi']) ?></td>
                            <td class="border border-slate-300 p-2"><?= htmlspecialchars($m['nama_pembimbing']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4 text-xs text-slate-600 flex justify-between font-medium">
            <span>Total Kunjungan Monitoring: <strong><?= count($monitoring_list) ?> Catatan</strong></span>
            <span>Rata-Rata Nilai: <strong><?= count($monitoring_list) > 0 ? round(array_sum(array_column($monitoring_list, 'nilai')) / count($monitoring_list), 1) : 0 ?></strong></span>
        </div>

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
