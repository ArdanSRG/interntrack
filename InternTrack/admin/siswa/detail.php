<?php
// admin/siswa/detail.php - Detail Lengkap Siswa PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);

// Ambil info siswa
$stmt = $pdo->prepare("SELECT s.*, u.username FROM siswa s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    set_flash('danger', 'Data siswa tidak ditemukan.');
    header('Location: ' . base_url('admin/siswa/index.php'));
    exit;
}

$page_title = 'Detail Siswa - ' . $siswa['nama'];

// Ambil riwayat penempatan
$stmtPen = $pdo->prepare("
    SELECT pen.*, p.nama_perusahaan, p.bidang_usaha, p.alamat as alamat_perusahaan, p.kontak,
           pem.nama as nama_pembimbing, pem.nip, pem.no_hp as hp_pembimbing
    FROM penempatan pen
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE pen.siswa_id = ?
    ORDER BY pen.id DESC
");
$stmtPen->execute([$id]);
$penempatan_list = $stmtPen->fetchAll();

// Hitung rekap absensi
$stmtAbs = $pdo->prepare("
    SELECT 
        COUNT(*) as total_hari,
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
        SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
    FROM absensi
    WHERE siswa_id = ?
");
$stmtAbs->execute([$id]);
$rekap = $stmtAbs->fetch();

$total_hari  = (int)($rekap['total_hari'] ?? 0);
$total_hadir = (int)($rekap['total_hadir'] ?? 0);
$total_izin  = (int)($rekap['total_izin'] ?? 0);
$total_sakit = (int)($rekap['total_sakit'] ?? 0);
$total_alpa  = (int)($rekap['total_alpa'] ?? 0);

$persentase = hitungPersentaseKehadiran($total_hadir, $total_hari);
$status_kehadiran = hitungStatusKehadiran($persentase);

// Ambil jurnal siswa
$stmtJurnal = $pdo->prepare("SELECT * FROM jurnal WHERE siswa_id = ? ORDER BY tanggal DESC LIMIT 10");
$stmtJurnal->execute([$id]);
$jurnal_list = $stmtJurnal->fetchAll();

// Ambil monitoring siswa
$stmtMon = $pdo->prepare("
    SELECT m.*, pem.nama as nama_pembimbing
    FROM monitoring m
    JOIN pembimbing pem ON pem.id = m.pembimbing_id
    WHERE m.siswa_id = ?
    ORDER BY m.tanggal_monitoring DESC
");
$stmtMon->execute([$id]);
$monitoring_list = $stmtMon->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Breadcrumb -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                        <a href="<?= base_url('admin/siswa/index.php') ?>" class="hover:text-blue-600">Data Siswa</a>
                        <span>/</span>
                        <span class="text-slate-600 font-medium">Detail Siswa</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800"><?= htmlspecialchars($siswa['nama']) ?></h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('admin/siswa/edit.php?id=' . $id) ?>" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="<?= base_url('admin/siswa/index.php') ?>" class="px-3.5 py-2 border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-xl text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Profile & Stats Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Info Siswa Card -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-2xl font-black shadow-md">
                            <?= strtoupper(substr($siswa['nama'], 0, 2)) ?>
                        </div>
                        <div>
                            <span class="text-xs font-bold font-mono px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">NIS: <?= htmlspecialchars($siswa['nis']) ?></span>
                            <h3 class="text-lg font-bold text-slate-900 mt-1"><?= htmlspecialchars($siswa['nama']) ?></h3>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($siswa['kelas']) ?> • <?= htmlspecialchars($siswa['jurusan']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Nomor WhatsApp:</span>
                            <span class="font-semibold text-slate-800"><?= htmlspecialchars($siswa['no_hp']) ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Username Login:</span>
                            <span class="font-mono font-semibold text-blue-600"><?= htmlspecialchars($siswa['username'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Alamat:</span>
                            <span class="font-medium text-slate-700 text-right max-w-[60%]"><?= htmlspecialchars($siswa['alamat'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-500">Terdaftar Sistem:</span>
                            <span class="font-medium text-slate-700"><?= format_tanggal_id($siswa['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Rekap Kehadiran Siswa (Algoritma 3) -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Rekapitulasi Kehadiran & Status PKL</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Penetapan status berdasarkan persentase kehadiran (Algoritma 3)</p>
                        </div>
                        <div>
                            <?= badgeStatusKehadiran($status_kehadiran) ?>
                        </div>
                    </div>

                    <!-- Progress Bar & Big Percent -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-4">
                        <div>
                            <span class="text-xs font-semibold text-slate-500">Persentase Kehadiran:</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                <span class="text-3xl font-black <?= $persentase >= 90 ? 'text-emerald-600' : ($persentase >= 75 ? 'text-blue-600' : ($persentase >= 60 ? 'text-amber-600' : 'text-rose-600')) ?>">
                                    <?= $persentase ?>%
                                </span>
                                <span class="text-xs font-bold text-slate-500">(<?= $total_hadir ?> dari <?= $total_hari ?> hari kerja)</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block font-mono">Formula:</span>
                            <code class="text-xs font-mono font-bold text-slate-700 bg-white px-2 py-1 rounded border border-slate-200">
                                (<?= $total_hadir ?> / <?= $total_hari ?>) &times; 100%
                            </code>
                        </div>
                    </div>

                    <!-- 4 Box Status -->
                    <div class="grid grid-cols-4 gap-3 text-center">
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                            <span class="text-[11px] font-bold text-emerald-700 uppercase">Hadir</span>
                            <p class="text-xl font-black text-emerald-800 mt-0.5"><?= $total_hadir ?></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-100">
                            <span class="text-[11px] font-bold text-blue-700 uppercase">Izin</span>
                            <p class="text-xl font-black text-blue-800 mt-0.5"><?= $total_izin ?></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                            <span class="text-[11px] font-bold text-amber-700 uppercase">Sakit</span>
                            <p class="text-xl font-black text-amber-800 mt-0.5"><?= $total_sakit ?></p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-100">
                            <span class="text-[11px] font-bold text-rose-700 uppercase">Alpa</span>
                            <p class="text-xl font-black text-rose-800 mt-0.5"><?= $total_alpa ?></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Penempatan PKL -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Informasi Penempatan PKL</h3>
                <?php if (empty($penempatan_list)): ?>
                    <p class="text-xs text-slate-400 italic">Siswa ini belum memiliki data penempatan PKL.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($penempatan_list as $pen): ?>
                            <div class="p-4 rounded-xl border border-slate-200 hover:border-blue-300 transition-colors bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-900"><?= htmlspecialchars($pen['nama_perusahaan']) ?></h4>
                                        <?= badgeStatusPenempatan($pen['status']) ?>
                                    </div>
                                    <p class="text-xs text-slate-500"><?= htmlspecialchars($pen['bidang_usaha']) ?> • <?= htmlspecialchars($pen['alamat_perusahaan']) ?></p>
                                    <p class="text-xs text-slate-600">
                                        <span class="font-semibold">Guru Pembimbing:</span> <?= htmlspecialchars($pen['nama_pembimbing']) ?> (<?= htmlspecialchars($pen['nip']) ?> - <?= htmlspecialchars($pen['hp_pembimbing']) ?>)
                                    </p>
                                </div>
                                <div class="text-xs text-slate-500 font-medium shrink-0 md:text-right">
                                    <span class="block text-slate-400">Periode Magang:</span>
                                    <span class="font-bold text-slate-800"><?= format_tanggal_id($pen['tanggal_mulai']) ?> s.d <?= format_tanggal_id($pen['tanggal_selesai']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Riwayat Jurnal & Monitoring -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Jurnal Terakhir -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Jurnal Kegiatan Siswa</h3>
                        <span class="text-xs text-slate-400"><?= count($jurnal_list) ?> Catatan</span>
                    </div>
                    <?php if (empty($jurnal_list)): ?>
                        <p class="text-xs text-slate-400 italic py-4">Belum ada jurnal kegiatan yang dicatat.</p>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto pr-1">
                            <?php foreach ($jurnal_list as $j): ?>
                                <div class="py-3">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-slate-700"><?= format_tanggal_id($j['tanggal']) ?></span>
                                        <?= badgeValidasiJurnal($j['status_validasi']) ?>
                                    </div>
                                    <p class="text-xs font-semibold text-blue-700"><?= htmlspecialchars($j['kegiatan']) ?></p>
                                    <p class="text-xs text-slate-600 mt-0.5">Hasil: <?= htmlspecialchars($j['hasil']) ?></p>
                                    <?php if ($j['kendala']): ?>
                                        <p class="text-xs text-amber-700 mt-0.5">Kendala: <?= htmlspecialchars($j['kendala']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($j['catatan_pembimbing']): ?>
                                        <div class="mt-1 p-2 rounded bg-blue-50 border border-blue-100 text-[11px] text-blue-900">
                                            <strong>Catatan Pembimbing:</strong> <?= htmlspecialchars($j['catatan_pembimbing']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hasil Monitoring -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Hasil Monitoring Guru</h3>
                        <span class="text-xs text-slate-400"><?= count($monitoring_list) ?> Evaluasi</span>
                    </div>
                    <?php if (empty($monitoring_list)): ?>
                        <p class="text-xs text-slate-400 italic py-4">Belum ada catatan monitoring dari guru pembimbing.</p>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto pr-1">
                            <?php foreach ($monitoring_list as $m): ?>
                                <div class="py-3 space-y-1 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800"><?= format_tanggal_id($m['tanggal_monitoring']) ?></span>
                                        <span class="px-2 py-0.5 rounded-full font-black text-xs <?= $m['nilai'] >= 85 ? 'bg-emerald-100 text-emerald-800' : ($m['nilai'] >= 75 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') ?>">
                                            Nilai: <?= $m['nilai'] ?>
                                        </span>
                                    </div>
                                    <p class="text-slate-600"><strong>Catatan:</strong> <?= htmlspecialchars($m['catatan']) ?></p>
                                    <p class="text-blue-700"><strong>Rekomendasi:</strong> <?= htmlspecialchars($m['rekomendasi']) ?></p>
                                    <p class="text-[11px] text-slate-400 italic">Oleh: <?= htmlspecialchars($m['nama_pembimbing']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
