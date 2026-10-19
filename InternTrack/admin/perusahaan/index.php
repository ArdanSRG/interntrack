<?php
// admin/perusahaan/index.php - Data Perusahaan Mitra PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Data Perusahaan Mitra PKL';

$search = clean_input($_GET['search'] ?? '');

$sql = "
    SELECT 
        p.*, 
        COUNT(pen.id) as total_siswa_pkl,
        SUM(CASE WHEN pen.status = 'Aktif' THEN 1 ELSE 0 END) as siswa_aktif
    FROM perusahaan p
    LEFT JOIN penempatan pen ON pen.perusahaan_id = p.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.nama_perusahaan LIKE ? OR p.bidang_usaha LIKE ? OR p.alamat LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY p.id ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$perusahaan_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Data Perusahaan Mitra (DUDI)</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar instansi dunia usaha dan dunia industri tempat pelaksanaan PKL</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/perusahaan/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Perusahaan</span>
                    </a>
                </div>
            </div>

            <!-- Toolbar Pencarian -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/perusahaan/index.php') ?>" method="GET" class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama perusahaan, bidang usaha, atau alamat..." 
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                        Cari
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Data Perusahaan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total: <?= count($perusahaan_list) ?> Perusahaan</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Nama Perusahaan</th>
                                <th class="py-3 px-4">Bidang Usaha</th>
                                <th class="py-3 px-4">Alamat</th>
                                <th class="py-3 px-4">Kontak / PIC</th>
                                <th class="py-3 px-4 text-center">Siswa Aktif</th>
                                <th class="py-3 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($perusahaan_list)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                        Tidak ada data perusahaan yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($perusahaan_list as $index => $row): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $index + 1 ?></td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/perusahaan/detail.php?id=' . $row['id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                <?= htmlspecialchars($row['nama_perusahaan']) ?>
                                            </a>
                                            <?php if ($row['email']): ?>
                                                <span class="text-[11px] text-slate-400"><?= htmlspecialchars($row['email']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-700"><?= htmlspecialchars($row['bidang_usaha']) ?></td>
                                        <td class="py-3 px-4 text-slate-600 max-w-xs truncate"><?= htmlspecialchars($row['alamat']) ?></td>
                                        <td class="py-3 px-4">
                                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($row['kontak']) ?></p>
                                            <p class="text-[11px] text-slate-400">PIC: <?= htmlspecialchars($row['penanggung_jawab'] ?? '-') ?></p>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $row['siswa_aktif'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                                                <?= (int)$row['siswa_aktif'] ?> Siswa
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="<?= base_url('admin/perusahaan/detail.php?id=' . $row['id']) ?>" title="Detail" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/perusahaan/edit.php?id=' . $row['id']) ?>" title="Edit" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Hapus perusahaan <?= addslashes(htmlspecialchars($row['nama_perusahaan'])) ?>? Penempatan terkait akan terhapus.', '<?= base_url('admin/perusahaan/hapus.php?id=' . $row['id']) ?>')" title="Hapus" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
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
