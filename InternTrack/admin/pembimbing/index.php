<?php
// admin/pembimbing/index.php - Data Guru Pembimbing PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Data Guru Pembimbing PKL';

$search = clean_input($_GET['search'] ?? '');

$sql = "
    SELECT 
        pem.*, 
        COUNT(pen.id) as total_bimbingan,
        SUM(CASE WHEN pen.status = 'Aktif' THEN 1 ELSE 0 END) as bimbingan_aktif
    FROM pembimbing pem
    LEFT JOIN penempatan pen ON pen.pembimbing_id = pem.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (pem.nip LIKE ? OR pem.nama LIKE ? OR pem.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY pem.id ORDER BY pem.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pembimbing_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Data Guru Pembimbing PKL</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar tenaga pendidik yang bertugas memonitor dan membimbing siswa PKL</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('admin/pembimbing/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Pembimbing</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/pembimbing/index.php') ?>" method="GET" class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari berdasarkan NIP, Nama Pembimbing, atau Email..." 
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                        Cari
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-colors flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total: <?= count($pembimbing_list) ?> Guru Pembimbing</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">NIP</th>
                                <th class="py-3 px-4">Nama Lengkap & Gelar</th>
                                <th class="py-3 px-4">Kontak / WhatsApp</th>
                                <th class="py-3 px-4">Email Dinas</th>
                                <th class="py-3 px-4 text-center">Siswa Bimbingan</th>
                                <th class="py-3 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($pembimbing_list)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                        Tidak ada data guru pembimbing yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pembimbing_list as $index => $row): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $index + 1 ?></td>
                                        <td class="py-3 px-4 font-mono font-semibold text-slate-700"><?= htmlspecialchars($row['nip']) ?></td>
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/pembimbing/detail.php?id=' . $row['id']) ?>" class="font-bold text-slate-800 hover:text-blue-600 block">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($row['no_hp']) ?></td>
                                        <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $row['bimbingan_aktif'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' ?>">
                                                <?= (int)$row['bimbingan_aktif'] ?> Siswa Aktif
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="<?= base_url('admin/pembimbing/detail.php?id=' . $row['id']) ?>" title="Detail" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('admin/pembimbing/edit.php?id=' . $row['id']) ?>" title="Edit" class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="confirmAction('Hapus data pembimbing <?= addslashes(htmlspecialchars($row['nama'])) ?>?', '<?= base_url('admin/pembimbing/hapus.php?id=' . $row['id']) ?>')" title="Hapus" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg">
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
