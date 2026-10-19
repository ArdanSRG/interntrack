<?php
// laporan/cetak_absensi.php - Laporan Resmi Rekapitulasi Kehadiran Siswa PKL (Algoritma 3)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role(['admin', 'pembimbing']);

$filter_perusahaan = (int)($_GET['perusahaan'] ?? 0);
$filter_status_kehadiran = clean_input($_GET['status_kehadiran'] ?? '');

$sql = "
    SELECT 
        s.id as siswa_id,
        s.nis,
        s.nama,
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
    LEFT JOIN absensi a ON a.siswa_id = s.id
    WHERE 1=1
";
$params = [];

if ($filter_perusahaan > 0) {
    $sql .= " AND pen.perusahaan_id = ?";
    $params[] = $filter_perusahaan;
}

$sql .= " GROUP BY s.id, s.nis, s.nama, s.kelas, p.nama_perusahaan ORDER BY s.kelas ASC, s.nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$raw = $stmt->fetchAll();

$rekap_list = [];
foreach ($raw as $row) {
    $persen = hitungPersentaseKehadiran((int)$row['total_hadir'], (int)$row['total_hari']);
    $status_pkl = hitungStatusKehadiran($persen);
    $row['persentase'] = $persen;
    $row['status_kehadiran'] = $status_pkl;

    if (!empty($filter_status_kehadiran) && $status_pkl !== $filter_status_kehadiran) {
        continue;
    }
    $rekap_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Kehadiran Siswa PKL - InternTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-slate-100 p-4 sm:p-8">

    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= base_url('laporan/index.php') ?>" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-colors">
            &larr; Kembali ke Pusat Laporan
        </a>
        <button onclick="window.print()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Cetak Rekap Kehadiran / Simpan PDF
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
                <p class="text-[11px] text-blue-700 font-medium">Laporan Rekapitulasi Kehadiran & Status Perkembangan Siswa PKL</p>
            </div>
        </div>

        <div class="text-center mb-6">
            <h2 class="text-base sm:text-lg font-bold uppercase tracking-wide text-slate-900 underline underline-offset-4">
                LAPORAN REKAPITULASI KEHADIRAN SISWA PKL
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Kriteria Algoritma 3: Sangat Baik (&ge;90%), Baik (75%-89%), Perlu Perhatian (60%-74%), Perlu Pembinaan (&lt;60%)
            </p>
        </div>

        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
                <tr>
                    <th class="border border-slate-300 p-2 text-center w-10">No</th>
                    <th class="border border-slate-300 p-2">NIS</th>
                    <th class="border border-slate-300 p-2">Nama Siswa</th>
                    <th class="border border-slate-300 p-2">Kelas</th>
                    <th class="border border-slate-300 p-2">Perusahaan PKL</th>
                    <th class="border border-slate-300 p-2 text-center">Hadir</th>
                    <th class="border border-slate-300 p-2 text-center">Izin</th>
                    <th class="border border-slate-300 p-2 text-center">Sakit</th>
                    <th class="border border-slate-300 p-2 text-center">Alpa</th>
                    <th class="border border-slate-300 p-2 text-center">Total</th>
                    <th class="border border-slate-300 p-2 text-center">% Kehadiran</th>
                    <th class="border border-slate-300 p-2 text-center">Status PKL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekap_list)): ?>
                    <tr>
                        <td colspan="12" class="border border-slate-300 p-4 text-center text-slate-500">Tidak ada data rekapitulasi kehadiran.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekap_list as $idx => $r): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border border-slate-300 p-2 text-center font-mono"><?= $idx + 1 ?></td>
                            <td class="border border-slate-300 p-2 font-mono font-bold"><?= htmlspecialchars($r['nis']) ?></td>
                            <td class="border border-slate-300 p-2 font-bold"><?= htmlspecialchars($r['nama']) ?></td>
                            <td class="border border-slate-300 p-2"><?= htmlspecialchars($r['kelas']) ?></td>
                            <td class="border border-slate-300 p-2"><?= htmlspecialchars($r['nama_perusahaan'] ?? 'Belum Ditempatkan') ?></td>
                            <td class="border border-slate-300 p-2 text-center font-bold text-emerald-800"><?= $r['total_hadir'] ?></td>
                            <td class="border border-slate-300 p-2 text-center"><?= $r['total_izin'] ?></td>
                            <td class="border border-slate-300 p-2 text-center"><?= $r['total_sakit'] ?></td>
                            <td class="border border-slate-300 p-2 text-center font-bold text-rose-800"><?= $r['total_alpa'] ?></td>
                            <td class="border border-slate-300 p-2 text-center font-bold"><?= $r['total_hari'] ?></td>
                            <td class="border border-slate-300 p-2 text-center font-mono font-bold">
                                <?= $r['persentase'] ?>%
                            </td>
                            <td class="border border-slate-300 p-2 text-center font-bold">
                                <?= htmlspecialchars($r['status_kehadiran']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4 text-xs text-slate-600 flex justify-between font-medium">
            <span>Total Siswa: <strong><?= count($rekap_list) ?> Orang</strong></span>
            <span>Formula: Persentase = (Hadir &divide; Total Hari) &times; 100%</span>
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
