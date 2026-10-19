<?php
// siswa/absensi/index.php - Absensi Kehadiran Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role('siswa');

$page_title = 'Absensi Kehadiran Saya';
$user = auth_user();

$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'] ?? 0;

// Ambil penempatan aktif
$stmtPen = $pdo->prepare("SELECT id FROM penempatan WHERE siswa_id = ? AND status = 'Aktif' LIMIT 1");
$stmtPen->execute([$siswa_id]);
$penRow = $stmtPen->fetch();
$penempatan_id = $penRow ? $penRow['id'] : null;

$today = date('Y-m-d');
$errors = [];

// Cek apakah sudah absen hari ini
$stmtToday = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? AND tanggal = ? LIMIT 1");
$stmtToday->execute([$siswa_id, $today]);
$absen_hari_ini = $stmtToday->fetch();

// Proses input absensi hari ini jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = clean_input($_POST['status'] ?? '');
    $keterangan = clean_input($_POST['keterangan'] ?? '');

    if ($absen_hari_ini) {
        $errors['general'] = 'Anda sudah mengisi presensi kehadiran hari ini.';
    } elseif (empty($status) || !in_array($status, ['Hadir', 'Izin', 'Sakit', 'Alpa'])) {
        $errors['status'] = 'Pilih salah satu status kehadiran.';
    } else {
        try {
            $stmtIns = $pdo->prepare("
                INSERT INTO absensi (siswa_id, penempatan_id, tanggal, status, keterangan)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtIns->execute([$siswa_id, $penempatan_id, $today, $status, $keterangan]);

            set_flash('success', 'Presensi kehadiran hari ini berhasil dicatat.');
            header('Location: ' . base_url('siswa/absensi/index.php'));
            exit;
        } catch (Exception $e) {
            $errors['general'] = 'Gagal menyimpan absensi: ' . $e->getMessage();
        }
    }
}

// Rekap Kehadiran Siswa (Algoritma 3)
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

// Riwayat Absensi
$stmtLog = $pdo->prepare("SELECT * FROM absensi WHERE siswa_id = ? ORDER BY tanggal DESC");
$stmtLog->execute([$siswa_id]);
$riwayat_absensi = $stmtLog->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Absensi Kehadiran PKL</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Isi daftar hadir harian dan pantau persentase serta status perkembangan PKL</p>
                </div>
            </div>

            <!-- Rekapitulasi & Formula Box (Algoritma 3) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-mono font-bold text-slate-400 uppercase">Status Perkembangan PKL Siswa</span>
                        <h3 class="text-lg font-bold text-slate-900 mt-0.5">Evaluasi Persentase Kehadiran</h3>
                    </div>
                    <div>
                        <?= badgeStatusKehadiran($status_kehadiran) ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <div class="md:col-span-5 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <span class="text-xs font-semibold text-slate-500">Persentase Kehadiran Otomatis:</span>
                        <div class="flex items-baseline gap-3 mt-1">
                            <span class="text-4xl font-black <?= $persentase >= 90 ? 'text-emerald-600' : ($persentase >= 75 ? 'text-blue-600' : ($persentase >= 60 ? 'text-amber-600' : 'text-rose-600')) ?>">
                                <?= $persentase ?>%
                            </span>
                            <span class="text-xs font-bold text-slate-500 font-mono">
                                (<?= $total_hadir ?> / <?= $total_hari ?>) &times; 100%
                            </span>
                        </div>
                    </div>

                    <div class="md:col-span-7 grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="bg-emerald-50 text-emerald-800 p-3 rounded-xl border border-emerald-100">
                            <span class="block text-[11px] font-bold text-emerald-600 uppercase">Hadir</span>
                            <span class="text-xl font-black"><?= $total_hadir ?></span>
                        </div>
                        <div class="bg-blue-50 text-blue-800 p-3 rounded-xl border border-blue-100">
                            <span class="block text-[11px] font-bold text-blue-600 uppercase">Izin</span>
                            <span class="text-xl font-black"><?= $total_izin ?></span>
                        </div>
                        <div class="bg-amber-50 text-amber-800 p-3 rounded-xl border border-amber-100">
                            <span class="block text-[11px] font-bold text-amber-600 uppercase">Sakit</span>
                            <span class="text-xl font-black"><?= $total_sakit ?></span>
                        </div>
                        <div class="bg-rose-50 text-rose-800 p-3 rounded-xl border border-rose-100">
                            <span class="block text-[11px] font-bold text-rose-600 uppercase">Alpa</span>
                            <span class="text-xl font-black"><?= $total_alpa ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Isi Absensi Hari Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Presensi Hari Ini: <?= format_tanggal_id($today) ?></h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih status kehadiran Anda pada hari kerja ini.</p>
                    </div>
                </div>

                <div class="p-6">
                    <?php if ($absen_hari_ini): ?>
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-2xl text-emerald-600"></i>
                                <div>
                                    <p class="text-sm font-bold">Anda telah mengisi absensi hari ini dengan status: <span class="uppercase underline"><?= htmlspecialchars($absen_hari_ini['status']) ?></span></p>
                                    <?php if ($absen_hari_ini['keterangan']): ?>
                                        <p class="text-xs text-emerald-700 mt-0.5">Keterangan: <?= htmlspecialchars($absen_hari_ini['keterangan']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-semibold text-emerald-600"><?= date('H:i:s', strtotime($absen_hari_ini['created_at'])) ?> WIB</span>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors['general'])): ?>
                            <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700">
                                <?= htmlspecialchars($errors['general']) ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('siswa/absensi/index.php') ?>" method="POST" class="space-y-4" novalidate>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilih Status Hadir / Tidak Hadir <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-2 p-3.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 transition-colors">
                                        <input type="radio" name="status" value="Hadir" required class="text-emerald-600 focus:ring-emerald-500">
                                        <span class="text-xs font-bold text-emerald-800">Hadir</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 transition-colors">
                                        <input type="radio" name="status" value="Izin" required class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs font-bold text-blue-800">Izin</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-amber-50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 transition-colors">
                                        <input type="radio" name="status" value="Sakit" required class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-xs font-bold text-amber-800">Sakit</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-rose-50 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 transition-colors">
                                        <input type="radio" name="status" value="Alpa" required class="text-rose-600 focus:ring-rose-500">
                                        <span class="text-xs font-bold text-rose-800">Alpa</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="keterangan" class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Alasan (Opsional)</label>
                                <input type="text" name="keterangan" id="keterangan" placeholder="Tuliskan alasan jika Izin atau Sakit..."
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 flex items-center gap-2">
                                <i class="fa-solid fa-check"></i>
                                <span>Kirim Presensi Hari Ini</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Riwayat Kehadiran -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Riwayat Absensi Kehadiran</h3>
                    <span class="text-xs text-slate-400"><?= count($riwayat_absensi) ?> Hari Tercatat</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4 text-center">Status Kehadiran</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4 text-center">Waktu Input</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($riwayat_absensi)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada riwayat absensi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat_absensi as $idx => $a): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-800"><?= format_tanggal_id($a['tanggal']) ?></td>
                                        <td class="py-3 px-4 text-center"><?= badgeAbsensi($a['status']) ?></td>
                                        <td class="py-3 px-4 text-slate-600 italic"><?= htmlspecialchars($a['keterangan'] ?? '-') ?></td>
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono text-xs"><?= date('H:i', strtotime($a['created_at'])) ?> WIB</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
