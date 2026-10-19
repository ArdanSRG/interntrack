<?php
// admin/penempatan/index.php - Manajemen Penempatan PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Penempatan PKL Siswa';

$search = clean_input($_GET['search'] ?? '');
$filter_perusahaan = (int)($_GET['perusahaan'] ?? 0);
$filter_pembimbing = (int)($_GET['pembimbing'] ?? 0);
$filter_status = clean_input($_GET['status'] ?? '');

$sql = "
    SELECT 
        pen.*, 
        s.nama as nama_siswa, s.nis, s.kelas, s.jurusan,
        p.nama_perusahaan,
        pem.nama as nama_pembimbing
    FROM penempatan pen
    JOIN siswa s ON s.id = pen.siswa_id
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_perusahaan > 0) {
    $sql .= " AND pen.perusahaan_id = ?";
    $params[] = $filter_perusahaan;
}

if ($filter_pembimbing > 0) {
    $sql .= " AND pen.pembimbing_id = ?";
    $params[] = $filter_pembimbing;
}

if (!empty($filter_status)) {
    $sql .= " AND pen.status = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY pen.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$penempatan_list = $stmt->fetchAll();

// List untuk filter dropdown
$perusahaan_all = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan ASC")->fetchAll();
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
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Penempatan PKL Siswa</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Alokasi siswa ke instansi mitra industri dan penugasan guru pembimbing</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/penempatan/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Penempatan</span>
                    </a>
                </div>
            </div>

            <!-- Toolbar Filter & Pencarian -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/penempatan/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    <div class="sm:col-span-4">
                        <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Cari Siswa</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama siswa atau NIS..."
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="perusahaan" class="block text-xs font-bold text-slate-600 uppercase mb-1">Perusahaan</label>
                        <select name="perusahaan" id="perusahaan" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Perusahaan --</option>
                            <?php foreach ($perusahaan_all as $pr): ?>
                                <option value="<?= $pr['id'] ?>" <?= $filter_perusahaan == $pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['nama_perusahaan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="pembimbing" class="block text-xs font-bold text-slate-600 uppercase mb-1">Guru Pembimbing</label>
                        <select name="pembimbing" id="pembimbing" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Pembimbing --</option>
                            <?php foreach ($pembimbing_all as $pm): ?>
                                <option value="<?= $pm['id'] ?>" <?= $filter_pembimbing == $pm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pm['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex gap-2">
                        <div class="flex-1">
                            <label for="status" class="block text-xs font-bold text-slate-600 uppercase mb-1">Status</label>
                            <select name="status" id="status" class="w-full py-2 px-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua</option>
                                <option value="Belum Mulai" <?= $filter_status === 'Belum Mulai' ? 'selected' : '' ?>>Belum Mulai</option>
                                <option value="Aktif" <?= $filter_status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Selesai" <?= $filter_status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <button type="submit" class="py-2 px-3 self-end bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Penempatan PKL -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Penempatan: <?= count($penempatan_list) ?> Data</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Siswa Peserta</th>
                                <th class="py-3 px-4">Perusahaan (DUDI)</th>
                                <th class="py-3 px-4">Guru Pembimbing</th>
                                <th class="py-3 px-4">Periode PKL</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($penempatan_list)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                        Tidak ada data penempatan yang sesuai filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($penempatan_list as $index => $row): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $index + 1 ?></td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/siswa/detail.php?id=' . $row['siswa_id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                <?= htmlspecialchars($row['nama_siswa']) ?>
                                            </a>
                                            <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($row['nis']) ?> • <?= htmlspecialchars($row['kelas']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-slate-800">
                                            <?= htmlspecialchars($row['nama_perusahaan']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-slate-700">
                                            <?= htmlspecialchars($row['nama_pembimbing']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 text-xs">
                                            <span class="block font-medium"><?= format_tanggal_id($row['tanggal_mulai']) ?></span>
                                            <span class="text-slate-400">s.d <?= format_tanggal_id($row['tanggal_selesai']) ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?= badgeStatusPenempatan($row['status']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="<?= base_url('admin/penempatan/edit.php?id=' . $row['id']) ?>" title="Edit Status / Data" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Hapus penempatan ini?', '<?= base_url('admin/penempatan/hapus.php?id=' . $row['id']) ?>')" title="Hapus" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
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
