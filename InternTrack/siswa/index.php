<?php
// siswa/index.php - Dashboard Siswa Peserta PKL
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role('siswa');

$page_title = 'Dashboard Siswa';
$user = auth_user();

// Cari profil siswa
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Data profil siswa belum terhubung dengan akun ini. Hubungi Administrator.");
}
$siswa_id = $siswa['id'];

// Ambil penempatan aktif siswa
$stmtPen = $pdo->prepare("
    SELECT pen.*, p.nama_perusahaan, p.bidang_usaha, p.alamat as alamat_perusahaan, p.kontak,
           pem.nama as nama_pembimbing, pem.nip, pem.no_hp as hp_pembimbing
    FROM penempatan pen
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE pen.siswa_id = ?
    ORDER BY pen.id DESC LIMIT 1
");
$stmtPen->execute([$siswa_id]);
$penempatan = $stmtPen->fetch();

// Hitung rekapitulasi absensi (Algoritma 3)
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
$stmtAbs->execute([$siswa_id]);
$rekap = $stmtAbs->fetch();

$total_hari  = (int)($rekap['total_hari'] ?? 0);
$total_hadir = (int)($rekap['total_hadir'] ?? 0);
$total_izin  = (int)($rekap['total_izin'] ?? 0);
$total_sakit = (int)($rekap['total_sakit'] ?? 0);
$total_alpa  = (int)($rekap['total_alpa'] ?? 0);

$persentase = hitungPersentaseKehadiran($total_hadir, $total_hari);
$status_kehadiran = hitungStatusKehadiran($persentase);

// Cek apakah hari ini sudah absen
$today = date('Y-m-d');
$stmtToday = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ? LIMIT 1");
$stmtToday->execute([$siswa_id, $today]);
$absen_hari_ini = $stmtToday->fetch();

// Ambil 5 jurnal terbaru siswa
$stmtJurnal = $pdo->prepare("SELECT * FROM jurnal WHERE siswa_id = ? ORDER BY tanggal DESC LIMIT 5");
$stmtJurnal->execute([$siswa_id]);
$jurnal_terbaru = $stmtJurnal->fetchAll();

// Ambil monitoring terbaru siswa
$stmtMon = $pdo->prepare("
    SELECT m.*, pem.nama as nama_pembimbing
    FROM monitoring m
    JOIN pembimbing pem ON pem.id = m.pembimbing_id
    WHERE m.siswa_id = ?
    ORDER BY m.tanggal_monitoring DESC LIMIT 3
");
$stmtMon->execute([$siswa_id]);
$monitoring_list = $stmtMon->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Banner Sambutan Siswa -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-blue-100 backdrop-blur-sm mb-2">
                            <i class="fa-solid fa-user-graduate"></i> Portal Siswa PKL
                        </span>
                        <h2 class="text-2xl font-black">Halo, <?= htmlspecialchars($siswa['nama']) ?>!</h2>
                        <p class="text-xs text-blue-100 mt-1">
                            NIS: <span class="font-mono font-bold"><?= htmlspecialchars($siswa['nis']) ?></span> • Kelas: <?= htmlspecialchars($siswa['kelas']) ?> (<?= htmlspecialchars($siswa['jurusan']) ?>)
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2.5">
                        <a href="<?= base_url('siswa/jurnal/tambah.php') ?>" class="px-4 py-2.5 bg-white text-blue-900 hover:bg-blue-50 rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-pen"></i> Tulis Jurnal Hari Ini
                        </a>
                        <a href="<?= base_url('siswa/absensi/index.php') ?>" class="px-4 py-2.5 bg-blue-600/70 hover:bg-blue-600 text-white rounded-xl text-xs font-bold transition-all border border-blue-400/30 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check"></i> Absensi Kehadiran
                        </a>
                    </div>
                </div>
            </div>

            <!-- Grid 3 Kolom: Status Kehadiran, Tempat PKL, Status Absen Hari Ini -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Status Kehadiran & Predikat PKL (Algoritma 3) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase">Perkembangan PKL</span>
                        <?= badgeStatusKehadiran($status_kehadiran) ?>
                    </div>
                    
                    <div class="flex items-baseline justify-between">
                        <div>
                            <span class="text-3xl font-black <?= $persentase >= 90 ? 'text-emerald-600' : ($persentase >= 75 ? 'text-blue-600' : ($persentase >= 60 ? 'text-amber-600' : 'text-rose-600')) ?>">
                                <?= $persentase ?>%
                            </span>
                            <span class="text-xs text-slate-400 block font-medium">Persentase Kehadiran</span>
                        </div>
                        <div class="text-right text-xs">
                            <span class="font-bold text-slate-800"><?= $total_hadir ?> / <?= $total_hari ?> Hari</span>
                            <span class="text-slate-400 block font-mono text-[10px]">Hadir &divide; Total &times; 100</span>
                        </div>
                    </div>

                    <!-- Mini Breakdown -->
                    <div class="grid grid-cols-4 gap-1.5 pt-2 text-center text-xs">
                        <div class="bg-emerald-50 text-emerald-800 p-1.5 rounded-lg">
                            <span class="block text-[10px] text-emerald-600">Hadir</span>
                            <span class="font-bold"><?= $total_hadir ?></span>
                        </div>
                        <div class="bg-blue-50 text-blue-800 p-1.5 rounded-lg">
                            <span class="block text-[10px] text-blue-600">Izin</span>
                            <span class="font-bold"><?= $total_izin ?></span>
                        </div>
                        <div class="bg-amber-50 text-amber-800 p-1.5 rounded-lg">
                            <span class="block text-[10px] text-amber-600">Sakit</span>
                            <span class="font-bold"><?= $total_sakit ?></span>
                        </div>
                        <div class="bg-rose-50 text-rose-800 p-1.5 rounded-lg">
                            <span class="block text-[10px] text-rose-600">Alpa</span>
                            <span class="font-bold"><?= $total_alpa ?></span>
                        </div>
                    </div>
                </div>

                <!-- Info Tempat PKL -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase">Tempat Magang PKL</span>
                        <?= badgeStatusPenempatan($penempatan['status'] ?? 'Belum Mulai') ?>
                    </div>
                    <?php if ($penempatan): ?>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800"><?= htmlspecialchars($penempatan['nama_perusahaan']) ?></h3>
                            <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($penempatan['bidang_usaha']) ?></p>
                        </div>
                        <div class="text-xs text-slate-600 space-y-1 pt-1 border-t border-slate-100">
                            <p><span class="text-slate-400">Pembimbing:</span> <span class="font-semibold text-slate-800"><?= htmlspecialchars($penempatan['nama_pembimbing']) ?></span></p>
                            <p><span class="text-slate-400">Periode:</span> <?= format_tanggal_id($penempatan['tanggal_mulai']) ?> s.d <?= format_tanggal_id($penempatan['tanggal_selesai']) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 italic py-3">Anda belum memiliki penempatan PKL aktif.</p>
                    <?php endif; ?>
                </div>

                <!-- Status Presensi Hari Ini -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase">Presensi Hari Ini</span>
                        <span class="text-xs font-mono text-slate-400"><?= format_tanggal_id($today) ?></span>
                    </div>

                    <?php if ($absen_hari_ini): ?>
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                            <div class="flex items-center gap-2 font-bold text-emerald-700">
                                <i class="fa-solid fa-circle-check text-base"></i>
                                <span>Sudah Melakukan Absensi</span>
                            </div>
                            <p class="mt-1.5 font-medium">Status: <span class="font-bold uppercase"><?= htmlspecialchars($absen_hari_ini['status']) ?></span></p>
                            <?php if ($absen_hari_ini['keterangan']): ?>
                                <p class="text-[11px] text-emerald-600 mt-0.5">Ket: <?= htmlspecialchars($absen_hari_ini['keterangan']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                            <div class="flex items-center gap-2 font-bold text-amber-700">
                                <i class="fa-solid fa-clock text-base"></i>
                                <span>Belum Mengisi Absensi Hari Ini</span>
                            </div>
                            <p class="mt-1 text-slate-600">Jangan lupa untuk mengisi kehadiran tepat waktu.</p>
                            <a href="<?= base_url('siswa/absensi/index.php') ?>" class="inline-block mt-2.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-xs transition-colors">
                                Isi Presensi Sekarang &rarr;
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Jurnal Terbaru & Evaluasi Pembimbing -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Jurnal Terbaru Siswa -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-book-journal-whills text-blue-600"></i>
                            <span>Jurnal Kegiatan Terbaru</span>
                        </h3>
                        <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Lihat Semua &rarr;
                        </a>
                    </div>

                    <?php if (empty($jurnal_terbaru)): ?>
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <p>Belum ada jurnal yang Anda tulis.</p>
                            <a href="<?= base_url('siswa/jurnal/tambah.php') ?>" class="mt-2 inline-block font-bold text-blue-600">+ Buat Jurnal Baru</a>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach ($jurnal_terbaru as $j): ?>
                                <div class="py-3">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-slate-800"><?= format_tanggal_id($j['tanggal']) ?></span>
                                        <?= badgeValidasiJurnal($j['status_validasi']) ?>
                                    </div>
                                    <p class="text-xs font-bold text-blue-700"><?= htmlspecialchars($j['kegiatan']) ?></p>
                                    <p class="text-xs text-slate-600 mt-0.5">Hasil: <?= htmlspecialchars($j['hasil']) ?></p>
                                    <?php if ($j['catatan_pembimbing']): ?>
                                        <div class="mt-1.5 p-2 bg-blue-50/70 border border-blue-200 rounded-lg text-[11px] text-blue-900">
                                            <strong>Catatan Pembimbing:</strong> <?= htmlspecialchars($j['catatan_pembimbing']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hasil Monitoring Pembimbing -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-2">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Hasil Monitoring Pembimbing</span>
                        </h3>
                        <a href="<?= base_url('siswa/monitoring/index.php') ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            Semua Catatan &rarr;
                        </a>
                    </div>

                    <?php if (empty($monitoring_list)): ?>
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <p>Belum ada evaluasi monitoring dari guru pembimbing.</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100">
                            <?php foreach ($monitoring_list as $m): ?>
                                <div class="py-3.5 space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800"><?= format_tanggal_id($m['tanggal_monitoring']) ?></span>
                                        <span class="px-2.5 py-0.5 rounded-full font-black text-xs font-mono <?= $m['nilai'] >= 85 ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' ?>">
                                            Nilai: <?= $m['nilai'] ?>
                                        </span>
                                    </div>
                                    <p class="text-slate-700"><strong>Catatan:</strong> <?= htmlspecialchars($m['catatan']) ?></p>
                                    <p class="text-blue-700"><strong>Rekomendasi:</strong> <?= htmlspecialchars($m['rekomendasi']) ?></p>
                                    <p class="text-[11px] text-slate-400 italic">Oleh: <?= htmlspecialchars($m['nama_pembimbing']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
