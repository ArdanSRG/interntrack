<?php
// includes/sidebar.php - Sidebar Navigation
$role = $current_user['role'] ?? '';
$current_page = $_SERVER['REQUEST_URI'] ?? '';
?>

<!-- Desktop Sidebar -->
<aside class="hidden lg:flex lg:flex-col w-64 bg-slate-900 text-slate-300 border-r border-slate-800 shrink-0 select-none">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-blue-500/20">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <h1 class="text-base font-bold text-white tracking-wide">InternTrack</h1>
            <p class="text-[10px] text-blue-400 font-medium uppercase tracking-wider">Monitoring PKL</p>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
        <?php if ($role === 'admin' || $role === 'pembimbing'): ?>
            <!-- Menu Admin & Pembimbing -->
            <div>
                <a href="<?= base_url('admin/index.php') ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/index.php') !== false ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Master Data</p>
                <div class="space-y-1">
                    <a href="<?= base_url('admin/siswa/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/siswa/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Data Siswa</span>
                    </a>
                    <a href="<?= base_url('admin/perusahaan/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/perusahaan/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-building w-5 text-center"></i>
                        <span>Data Perusahaan</span>
                    </a>
                    <a href="<?= base_url('admin/pembimbing/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/pembimbing/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-user-tie w-5 text-center"></i>
                        <span>Data Pembimbing</span>
                    </a>
                    <a href="<?= base_url('admin/penempatan/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/penempatan/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-route w-5 text-center"></i>
                        <span>Penempatan PKL</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Aktivitas Siswa</p>
                <div class="space-y-1">
                    <a href="<?= base_url('admin/jurnal/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/jurnal/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-book-journal-whills w-5 text-center"></i>
                        <span>Jurnal Kegiatan</span>
                    </a>
                    <a href="<?= base_url('admin/absensi/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/absensi/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                        <span>Data Absensi</span>
                    </a>
                    <a href="<?= base_url('admin/monitoring/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/admin/monitoring/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span>Monitoring Pembimbing</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Laporan</p>
                <div class="space-y-1">
                    <a href="<?= base_url('laporan/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/laporan/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-print w-5 text-center"></i>
                        <span>Pusat Cetak Laporan</span>
                    </a>
                </div>
            </div>

        <?php elseif ($role === 'siswa'): ?>
            <!-- Menu Siswa -->
            <div>
                <a href="<?= base_url('siswa/index.php') ?>" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= (strpos($current_page, '/siswa/index.php') !== false || substr($current_page, -6) === '/siswa') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-slate-800 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                    <span>Dashboard Saya</span>
                </a>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Informasi PKL</p>
                <div class="space-y-1">
                    <a href="<?= base_url('siswa/profil.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, 'profil.php') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-id-card w-5 text-center"></i>
                        <span>Profil & Tempat PKL</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Aktivitas Harian</p>
                <div class="space-y-1">
                    <a href="<?= base_url('siswa/jurnal/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/siswa/jurnal/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-book-journal-whills w-5 text-center"></i>
                        <span>Jurnal Kegiatan</span>
                    </a>
                    <a href="<?= base_url('siswa/absensi/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/siswa/absensi/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                        <span>Absensi Kehadiran</span>
                    </a>
                    <a href="<?= base_url('siswa/monitoring/index.php') ?>" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all <?= strpos($current_page, '/siswa/monitoring/') !== false ? 'bg-blue-600/20 text-blue-400 font-semibold' : 'hover:bg-slate-800 hover:text-white' ?>">
                        <i class="fa-solid fa-star w-5 text-center"></i>
                        <span>Hasil Monitoring</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- User Mini Profile Footer -->
    <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                <?= strtoupper(substr($current_user['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="truncate">
                <p class="text-xs font-semibold text-white truncate"><?= htmlspecialchars($current_user['nama_lengkap'] ?? 'User') ?></p>
                <p class="text-[11px] text-slate-400 capitalize"><?= htmlspecialchars($current_user['role'] ?? 'Guest') ?></p>
            </div>
        </div>
        <a href="<?= base_url('auth/logout.php') ?>" title="Logout" class="text-slate-400 hover:text-rose-400 transition-colors p-1.5 rounded hover:bg-slate-800">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</aside>

<!-- Mobile Overlay & Sidebar Drawer -->
<div id="mobile-overlay" class="fixed inset-0 bg-slate-900/60 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>
<div id="mobile-sidebar" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden flex flex-col">
    <!-- Brand Header Mobile -->
    <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <span class="text-base font-bold text-white tracking-wide">InternTrack</span>
        </div>
        <button id="close-mobile-sidebar" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full'); document.getElementById('mobile-overlay').classList.add('hidden');" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>
    
    <!-- Mobile Navigation Content identical to desktop -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
        <?php if ($role === 'admin' || $role === 'pembimbing'): ?>
            <a href="<?= base_url('admin/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= base_url('admin/siswa/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-users w-5 text-center"></i>
                <span>Data Siswa</span>
            </a>
            <a href="<?= base_url('admin/perusahaan/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-building w-5 text-center"></i>
                <span>Data Perusahaan</span>
            </a>
            <a href="<?= base_url('admin/pembimbing/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-user-tie w-5 text-center"></i>
                <span>Data Pembimbing</span>
            </a>
            <a href="<?= base_url('admin/penempatan/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-route w-5 text-center"></i>
                <span>Penempatan PKL</span>
            </a>
            <a href="<?= base_url('admin/jurnal/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-book-journal-whills w-5 text-center"></i>
                <span>Jurnal Kegiatan</span>
            </a>
            <a href="<?= base_url('admin/absensi/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                <span>Data Absensi</span>
            </a>
            <a href="<?= base_url('admin/monitoring/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-chart-line w-5 text-center"></i>
                <span>Monitoring Pembimbing</span>
            </a>
            <a href="<?= base_url('laporan/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-print w-5 text-center"></i>
                <span>Laporan</span>
            </a>
        <?php else: ?>
            <a href="<?= base_url('siswa/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                <span>Dashboard Saya</span>
            </a>
            <a href="<?= base_url('siswa/profil.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-id-card w-5 text-center"></i>
                <span>Profil & Tempat PKL</span>
            </a>
            <a href="<?= base_url('siswa/jurnal/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-book-journal-whills w-5 text-center"></i>
                <span>Jurnal Kegiatan</span>
            </a>
            <a href="<?= base_url('siswa/absensi/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                <span>Absensi Kehadiran</span>
            </a>
            <a href="<?= base_url('siswa/monitoring/index.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                <i class="fa-solid fa-star w-5 text-center"></i>
                <span>Hasil Monitoring</span>
            </a>
        <?php endif; ?>
        
        <div class="pt-4 border-t border-slate-800">
            <a href="<?= base_url('auth/logout.php') ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-rose-400 hover:bg-rose-500/10">
                <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
                <span>Keluar (Logout)</span>
            </a>
        </div>
    </div>
</div>
