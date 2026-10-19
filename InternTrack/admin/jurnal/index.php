<?php
// admin/jurnal/index.php - Monitoring Jurnal Kegiatan Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Jurnal Kegiatan Siswa PKL';

$search = clean_input($_GET['search'] ?? '');
$filter_status = clean_input($_GET['status'] ?? '');
$filter_tanggal = clean_input($_GET['tanggal'] ?? '');

$sql = "
    SELECT 
        j.*, 
        s.nama as nama_siswa, s.nis, s.kelas,
        p.nama_perusahaan,
        pem.nama as nama_pembimbing
    FROM jurnal j
    JOIN siswa s ON s.id = j.siswa_id
    LEFT JOIN penempatan pen ON pen.id = j.penempatan_id
    LEFT JOIN perusahaan p ON p.id = pen.perusahaan_id
    LEFT JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR j.kegiatan LIKE ? OR j.hasil LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_status)) {
    $sql .= " AND j.status_validasi = ?";
    $params[] = $filter_status;
}

if (!empty($filter_tanggal)) {
    $sql .= " AND j.tanggal = ?";
    $params[] = $filter_tanggal;
}

$sql .= " ORDER BY j.tanggal DESC, j.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jurnal_list = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Jurnal Kegiatan Harian Siswa</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Review aktivitas, hasil pekerjaan, kendala, dan berikan validasi bimbingan</p>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('admin/jurnal/index.php') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    <div class="sm:col-span-5">
                        <label for="search" class="block text-xs font-bold text-slate-600 uppercase mb-1">Cari Kegiatan / Siswa</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama siswa, topik kegiatan, atau hasil..."
                                   class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="tanggal" class="block text-xs font-bold text-slate-600 uppercase mb-1">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>" 
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="sm:col-span-3">
                        <label for="status" class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Validasi</label>
                        <select name="status" id="status" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Semua Status --</option>
                            <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu Review</option>
                            <option value="Disetujui" <?= $filter_status === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="Revisi" <?= $filter_status === 'Revisi' ? 'selected' : '' ?>>Perlu Revisi</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 flex gap-2">
                        <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Jurnal Cards & Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total: <?= count($jurnal_list) ?> Catatan Jurnal</span>
                </div>
                
                <div class="divide-y divide-slate-100">
                    <?php if (empty($jurnal_list)): ?>
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-book-open text-3xl mb-2 text-slate-300"></i>
                            <p>Tidak ada jurnal kegiatan yang sesuai dengan kriteria filter.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jurnal_list as $j): ?>
                            <div class="p-5 hover:bg-slate-50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                                                <i class="fa-regular fa-calendar mr-1"></i><?= format_tanggal_id($j['tanggal']) ?>
                                            </span>
                                            <?= badgeValidasiJurnal($j['status_validasi']) ?>
                                            <span class="text-xs text-slate-400">•</span>
                                            <span class="text-xs font-bold text-slate-800"><?= htmlspecialchars($j['nama_siswa']) ?></span>
                                            <span class="text-xs text-slate-400">(NIS: <?= htmlspecialchars($j['nis']) ?> - <?= htmlspecialchars($j['kelas']) ?>)</span>
                                        </div>

                                        <h4 class="text-sm font-bold text-slate-900 pt-1">
                                            <i class="fa-solid fa-laptop-code text-blue-600 mr-1.5"></i>
                                            <?= htmlspecialchars($j['kegiatan']) ?>
                                        </h4>

                                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1">
                                            <p><span class="font-bold text-slate-700">Hasil:</span> <?= htmlspecialchars($j['hasil']) ?></p>
                                            <?php if ($j['kendala']): ?>
                                                <p><span class="font-bold text-amber-700">Kendala:</span> <?= htmlspecialchars($j['kendala']) ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($j['catatan_pembimbing']): ?>
                                            <div class="p-2.5 bg-blue-50/70 border border-blue-200 rounded-xl text-xs text-blue-900 mt-2">
                                                <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-comment-dots text-blue-600"></i> Catatan Pembimbing:</span>
                                                <p class="mt-0.5"><?= htmlspecialchars($j['catatan_pembimbing']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Tombol Validasi / Beri Catatan -->
                                    <div class="shrink-0 sm:text-right pt-2 sm:pt-0">
                                        <a href="<?= base_url('admin/jurnal/validasi.php?id=' . $j['id']) ?>" class="px-3.5 py-2 bg-slate-900 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-pen-nib"></i> Validasi / Catatan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
