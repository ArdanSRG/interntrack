<?php
// siswa/jurnal/index.php - Daftar Jurnal Kegiatan Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role('siswa');

$page_title = 'Jurnal Kegiatan Saya';
$user = auth_user();

$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'] ?? 0;

$search = clean_input($_GET['search'] ?? '');
$filter_status = clean_input($_GET['status'] ?? '');

$sql = "SELECT * FROM jurnal WHERE siswa_id = ?";
$params = [$siswa_id];

if (!empty($search)) {
    $sql .= " AND (kegiatan LIKE ? OR hasil LIKE ? OR kendala LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_status)) {
    $sql .= " AND status_validasi = ?";
    $params[] = $filter_status;
}

$sql .= " ORDER BY tanggal DESC, id DESC";

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
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Jurnal Kegiatan Harian</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Dokumentasi aktivitas, pekerjaan, kendala teknis, dan verifikasi guru pembimbing</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('siswa/jurnal/tambah.php') ?>" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tulis Jurnal Baru</span>
                    </a>
                </div>
            </div>

            <!-- Toolbar Filter & Search -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <form action="<?= base_url('siswa/jurnal/index.php') ?>" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari isi kegiatan atau hasil..."
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="sm:w-48">
                        <select name="status" class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="Disetujui" <?= $filter_status === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="Revisi" <?= $filter_status === 'Revisi' ? 'selected' : '' ?>>Revisi</option>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                        Filter
                    </button>
                </form>
            </div>

            <!-- List Jurnal -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Catatan: <?= count($jurnal_list) ?> Hari</span>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php if (empty($jurnal_list)): ?>
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-book-open text-3xl mb-2 text-slate-300"></i>
                            <p>Belum ada catatan jurnal. Mulailah mendokumentasikan kegiatan PKL Anda.</p>
                            <a href="<?= base_url('siswa/jurnal/tambah.php') ?>" class="mt-2 inline-block font-bold text-blue-600">+ Tambah Jurnal Sekarang</a>
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
                                                <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-comment-dots text-blue-600"></i> Catatan Guru Pembimbing:</span>
                                                <p class="mt-0.5"><?= htmlspecialchars($j['catatan_pembimbing']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($j['status_validasi'] !== 'Disetujui'): ?>
                                        <div class="shrink-0 pt-2 sm:pt-0">
                                            <a href="<?= base_url('siswa/jurnal/edit.php?id=' . $j['id']) ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-amber-100 hover:text-amber-800 text-slate-600 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
