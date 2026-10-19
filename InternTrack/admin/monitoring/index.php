<?php
// admin/monitoring/index.php - Data Monitoring PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Monitoring Pembimbing PKL';

$search = clean_input($_GET['search'] ?? '');
$filter_pembimbing = (int)($_GET['pembimbing'] ?? 0);

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

if (!empty($search)) {
    $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR m.catatan LIKE ? OR m.rekomendasi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_pembimbing > 0) {
    $sql .= " AND m.pembimbing_id = ?";
    $params[] = $filter_pembimbing;
}

$sql .= " ORDER BY m.tanggal_monitoring DESC, m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$monitoring_list = $stmt->fetchAll();

$pembimbing_all = $pdo->query("SELECT id, nama FROM pembimbing ORDER BY nama ASC")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Monitoring & Evaluasi PKL</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Catatan observasi lapangan, nilai kinerja industri, dan rekomendasi guru pembimbing</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/monitoring/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Input Monitoring</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/monitoring/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    <div class="sm:col-span-7">
                        <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Cari Siswa / Evaluasi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama siswa, NIS, atau catatan..."
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="pembimbing" class="block text-xs font-bold text-slate-600 uppercase mb-1">Guru Pembimbing</label>
                        <select name="pembimbing" id="pembimbing" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Pembimbing --</option>
                            <?php foreach ($pembimbing_all as $pm): ?>
                                <option value="<?= $pm['id'] ?>" <?= $filter_pembimbing == $pm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pm['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Monitoring -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Hasil Monitoring: <?= count($monitoring_list) ?> Data</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Siswa Peserta</th>
                                <th class="py-3 px-4">Tempat PKL</th>
                                <th class="py-3 px-4 text-center">Nilai</th>
                                <th class="py-3 px-4">Catatan & Rekomendasi</th>
                                <th class="py-3 px-4">Guru Pembimbing</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($monitoring_list)): ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 text-xs">Belum ada catatan monitoring yang tersimpan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($monitoring_list as $idx => $m): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-800 whitespace-nowrap">
                                            <?= format_tanggal_id($m['tanggal_monitoring']) ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/siswa/detail.php?id=' . $m['siswa_id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                <?= htmlspecialchars($m['nama_siswa']) ?>
                                            </a>
                                            <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($m['nis']) ?> - <?= htmlspecialchars($m['kelas']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 font-medium">
                                            <?= htmlspecialchars($m['nama_perusahaan'] ?? '-') ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black font-mono <?= $m['nilai'] >= 85 ? 'bg-emerald-100 text-emerald-800' : ($m['nilai'] >= 75 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') ?>">
                                                <?= $m['nilai'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 max-w-sm">
                                            <p class="text-xs text-slate-700 line-clamp-2"><span class="font-bold">Catatan:</span> <?= htmlspecialchars($m['catatan']) ?></p>
                                            <p class="text-[11px] text-blue-700 font-medium line-clamp-1 mt-0.5"><span class="font-bold">Rekomendasi:</span> <?= htmlspecialchars($m['rekomendasi']) ?></p>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700">
                                            <?= htmlspecialchars($m['nama_pembimbing']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="<?= base_url('admin/monitoring/edit.php?id=' . $m['id']) ?>" title="Edit" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Hapus catatan monitoring ini?', '<?= base_url('admin/monitoring/hapus.php?id=' . $m['id']) ?>')" title="Hapus" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
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
