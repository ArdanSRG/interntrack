<?php
// admin/siswa/index.php - Manajemen Data Siswa (Lihat, Cari, Filter)
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Data Siswa PKL';

// Filter & Pencarian
$search = clean_input($_GET['search'] ?? '');
$filter_kelas = clean_input($_GET['kelas'] ?? '');
$filter_jurusan = clean_input($_GET['jurusan'] ?? '');

$sql = "
    SELECT 
        s.*, 
        pen.status as status_penempatan,
        pen.id as penempatan_id,
        p.nama_perusahaan,
        pem.nama as nama_pembimbing
    FROM siswa s
    LEFT JOIN penempatan pen ON pen.siswa_id = s.id AND pen.status = 'Aktif'
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    LEFT JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.nis LIKE ? OR s.nama LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_kelas)) {
    $sql .= " AND s.kelas = ?";
    $params[] = $filter_kelas;
}

if (!empty($filter_jurusan)) {
    $sql .= " AND s.jurusan = ?";
    $params[] = $filter_jurusan;
}

$sql .= " ORDER BY s.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa_list = $stmt->fetchAll();

// Ambil list unik kelas dan jurusan untuk filter dropdown
$kelasList = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
$jurusanList = $pdo->query("SELECT DISTINCT jurusan FROM siswa ORDER BY jurusan ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Manajemen Data Siswa</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data siswa peserta Praktik Kerja Lapangan (PKL)</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/siswa/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Tambah Siswa</span>
                    </a>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/siswa/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    <!-- Input Pencarian (NIS / Nama) -->
                    <div class="sm:col-span-5">
                        <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Pencarian Siswa</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari berdasarkan NIS atau Nama..." 
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Filter Kelas -->
                    <div class="sm:col-span-3">
                        <label for="kelas" class="block text-xs font-bold text-slate-600 uppercase mb-1">Filter Kelas</label>
                        <select name="kelas" id="kelas" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $filter_kelas === $k ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Jurusan -->
                    <div class="sm:col-span-3">
                        <label for="jurusan" class="block text-xs font-bold text-slate-600 uppercase mb-1">Filter Jurusan</label>
                        <select name="jurusan" id="jurusan" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Semua Jurusan --</option>
                            <?php foreach ($jurusanList as $j): ?>
                                <option value="<?= htmlspecialchars($j) ?>" <?= $filter_jurusan === $j ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tombol Filter & Reset -->
                    <div class="sm:col-span-1 flex gap-2">
                        <button type="submit" title="Filter Data" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                        <?php if (!empty($search) || !empty($filter_kelas) || !empty($filter_jurusan)): ?>
                            <a href="<?= base_url('admin/siswa/index.php') ?>" title="Reset Filter" class="py-2 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Data Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Hasil: <?= count($siswa_list) ?> Siswa</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">NIS</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Kelas & Jurusan</th>
                                <th class="py-3 px-4">No. HP</th>
                                <th class="py-3 px-4">Tempat PKL & Pembimbing</th>
                                <th class="py-3 px-4 text-center">Status PKL</th>
                                <th class="py-3 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($siswa_list)): ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-user-slash text-2xl mb-2 block"></i>
                                        Tidak ada data siswa yang cocok dengan kriteria pencarian/filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($siswa_list as $index => $row): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $index + 1 ?></td>
                                        <td class="py-3 px-4 font-mono font-semibold text-blue-600"><?= htmlspecialchars($row['nis']) ?></td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/siswa/detail.php?id=' . $row['id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 transition-colors">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">
                                            <p class="font-semibold text-slate-700"><?= htmlspecialchars($row['kelas']) ?></p>
                                            <p class="text-[11px] text-slate-400"><?= htmlspecialchars($row['jurusan']) ?></p>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            <i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i>
                                            <?= htmlspecialchars($row['no_hp']) ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <?php if ($row['nama_perusahaan']): ?>
                                                <p class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_perusahaan']) ?></p>
                                                <p class="text-[11px] text-slate-500">Pembimbing: <?= htmlspecialchars($row['nama_pembimbing'] ?? '-') ?></p>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400 italic">Belum Ditempatkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?= badgeStatusPenempatan($row['status_penempatan'] ?? 'Belum Mulai') ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="<?= base_url('admin/siswa/detail.php?id=' . $row['id']) ?>" title="Detail Siswa" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/siswa/edit.php?id=' . $row['id']) ?>" title="Edit Siswa" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Hapus data siswa <?= addslashes(htmlspecialchars($row['nama'])) ?>? Tindakan ini akan menghapus semua riwayat penempatan, absensi, dan jurnal siswa.', '<?= base_url('admin/siswa/hapus.php?id=' . $row['id']) ?>')" title="Hapus Siswa" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
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
