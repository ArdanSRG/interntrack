<?php
// laporan/index.php - Menu Pusat Cetak Laporan PKL
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

require_role(['admin', 'pembimbing']);

$page_title = 'Pusat Laporan PKL';

$kelasList = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
$perusahaanList = $pdo->query("SELECT id, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan ASC")->fetchAll();
$pembimbingList = $pdo->query("SELECT id, nama FROM pembimbing ORDER BY nama ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Pusat Cetak & Unduh Laporan</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Generate laporan resmi kegiatan Praktik Kerja Lapangan siap cetak dan simpan PDF</p>
                </div>
            </div>

            <!-- 3 Kartu Laporan Utama Sesuai Modul R -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- 1. Laporan Data Siswa PKL -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:border-blue-300 transition-colors">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">1. Laporan Data Siswa PKL</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Mencakup rekapitulasi data seluruh siswa peserta PKL, kelas, jurusan, penempatan perusahaan mitra, dan guru pembimbing.
                        </p>
                    </div>

                    <form action="<?= base_url('laporan/cetak_siswa.php') ?>" method="GET" target="_blank" class="space-y-3 pt-3 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Filter Kelas</label>
                            <select name="kelas" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Filter Status PKL</label>
                            <select name="status" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Belum Mulai">Belum Mulai</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Laporan Siswa</span>
                        </button>
                    </form>
                </div>

                <!-- 2. Laporan Kehadiran Siswa -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:border-emerald-300 transition-colors">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">2. Laporan Kehadiran Siswa</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Rekapitulasi persentase kehadiran siswa otomatis sesuai Algoritma 3 (Sangat Baik, Baik, Perlu Perhatian, Perlu Pembinaan).
                        </p>
                    </div>

                    <form action="<?= base_url('laporan/cetak_absensi.php') ?>" method="GET" target="_blank" class="space-y-3 pt-3 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Filter Perusahaan</label>
                            <select name="perusahaan" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                <option value="">Semua Perusahaan</option>
                                <?php foreach ($perusahaanList as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_perusahaan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Kehadiran</label>
                            <select name="status_kehadiran" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="Sangat Baik">Sangat Baik (&ge; 90%)</option>
                                <option value="Baik">Baik (75% - 89%)</option>
                                <option value="Perlu Perhatian">Perlu Perhatian (60% - 74%)</option>
                                <option value="Perlu Pembinaan">Perlu Pembinaan (&lt; 60%)</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Rekap Kehadiran</span>
                        </button>
                    </form>
                </div>

                <!-- 3. Laporan Monitoring Pembimbing -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-4 hover:border-purple-300 transition-colors">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">3. Laporan Monitoring Pembimbing</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Dokumen evaluasi berkala bimbingan PKL, nilai kinerja industri, catatan observasi, dan rekomendasi sekolah.
                        </p>
                    </div>

                    <form action="<?= base_url('laporan/cetak_monitoring.php') ?>" method="GET" target="_blank" class="space-y-3 pt-3 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Guru Pembimbing</label>
                            <select name="pembimbing" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                                <option value="">Semua Guru Pembimbing</option>
                                <?php foreach ($pembimbingList as $pm): ?>
                                    <option value="<?= $pm['id'] ?>"><?= htmlspecialchars($pm['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Rentang Tanggal (Opsional)</label>
                            <input type="date" name="tanggal" class="w-full py-1.5 px-3 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Laporan Monitoring</span>
                        </button>
                    </form>
                </div>

            </div>

        </main>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
