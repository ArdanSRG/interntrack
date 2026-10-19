<?php
// siswa/profil.php - Profil Siswa & Informasi Tempat PKL
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role('siswa');

$page_title = 'Profil Siswa & Tempat PKL';
$user = auth_user();

$stmt = $pdo->prepare("SELECT * FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Profil tidak ditemukan.");
}

$stmtPen = $pdo->prepare("
    SELECT pen.*, p.nama_perusahaan, p.bidang_usaha, p.alamat as alamat_perusahaan, p.kontak as kontak_perusahaan, p.email as email_perusahaan, p.penanggung_jawab,
           pem.nama as nama_pembimbing, pem.nip, pem.no_hp as hp_pembimbing, pem.email as email_pembimbing
    FROM penempatan pen
    JOIN perusahaan p ON p.id = pen.perusahaan_id
    JOIN pembimbing pem ON pem.id = pen.pembimbing_id
    WHERE pen.siswa_id = ?
    ORDER BY pen.id DESC LIMIT 1
");
$stmtPen->execute([$siswa['id']]);
$penempatan = $stmtPen->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Profil Saya & Informasi Penempatan PKL</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Data identitas diri, tempat magang industri, dan kontak pembimbing</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Profil Siswa -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                    <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white text-2xl font-black shadow-md">
                            <?= strtoupper(substr($siswa['nama'], 0, 2)) ?>
                        </div>
                        <div>
                            <span class="text-xs font-bold font-mono px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">NIS: <?= htmlspecialchars($siswa['nis']) ?></span>
                            <h3 class="text-lg font-bold text-slate-900 mt-1"><?= htmlspecialchars($siswa['nama']) ?></h3>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($siswa['kelas']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Kompetensi Keahlian:</span>
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($siswa['jurusan']) ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Nomor WhatsApp:</span>
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($siswa['no_hp']) ?></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Username Login:</span>
                            <span class="font-mono font-semibold text-blue-600"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="py-1.5">
                            <span class="text-slate-500 block mb-1">Alamat Tinggal:</span>
                            <span class="font-medium text-slate-700"><?= htmlspecialchars($siswa['alamat'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Tempat PKL & Guru Pembimbing -->
                <div class="lg:col-span-7 space-y-6">
                    <?php if ($penempatan): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-building text-blue-600"></i>
                                    <span>Instansi Tempat PKL</span>
                                </h3>
                                <?= badgeStatusPenempatan($penempatan['status']) ?>
                            </div>

                            <div class="space-y-2 text-xs">
                                <h4 class="text-base font-bold text-slate-900"><?= htmlspecialchars($penempatan['nama_perusahaan']) ?></h4>
                                <p class="text-slate-500 font-medium"><?= htmlspecialchars($penempatan['bidang_usaha']) ?></p>
                                <p class="text-slate-600 pt-1"><i class="fa-solid fa-location-dot text-slate-400 mr-1.5"></i><?= htmlspecialchars($penempatan['alamat_perusahaan']) ?></p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 mt-2 border-t border-slate-100">
                                    <div>
                                        <span class="text-slate-400 block">Kontak Kantor:</span>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($penempatan['kontak_perusahaan']) ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Penanggung Jawab Industri:</span>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($penempatan['penanggung_jawab'] ?? '-') ?></span>
                                    </div>
                                </div>

                                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 text-blue-900 mt-2">
                                    <span class="font-bold block">Periode Pelaksanaan PKL:</span>
                                    <span class="font-semibold text-xs"><?= format_tanggal_id($penempatan['tanggal_mulai']) ?> s.d <?= format_tanggal_id($penempatan['tanggal_selesai']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-user-tie text-indigo-600"></i>
                                <span>Guru Pembimbing Sekolah</span>
                            </h3>
                            <div class="space-y-2 text-xs">
                                <h4 class="text-base font-bold text-slate-900"><?= htmlspecialchars($penempatan['nama_pembimbing']) ?></h4>
                                <p class="text-slate-500 font-mono">NIP: <?= htmlspecialchars($penempatan['nip']) ?></p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <span class="text-slate-400 block">Nomor HP / WhatsApp:</span>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($penempatan['hp_pembimbing']) ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Email Sekolah:</span>
                                        <span class="font-medium text-slate-800"><?= htmlspecialchars($penempatan['email_pembimbing'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-building-circle-exclamation text-3xl mb-2 text-slate-300"></i>
                            <p>Data penempatan PKL Anda belum dimasukkan oleh Administrator.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
