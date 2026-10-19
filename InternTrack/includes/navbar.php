<?php
// includes/navbar.php - Top Navigation Bar
$flash = get_flash();
?>
<header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 shrink-0 shadow-sm">
    <div class="flex items-center gap-3">
        <!-- Mobile Sidebar Button -->
        <button id="sidebar-toggle" type="button" class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        
        <div>
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">SMK Rekayasa Perangkat Lunak</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">
                <?= isset($page_title) ? htmlspecialchars($page_title) : 'Sistem Monitoring PKL' ?>
            </h2>
        </div>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Badge Periode PKL -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200">
            <i class="fa-regular fa-calendar text-blue-600"></i>
            <span>Tahun Ajaran 2026/2027</span>
        </div>

        <!-- User Info Pill -->
        <div class="flex items-center gap-2.5 pl-2">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                <?= strtoupper(substr($current_user['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="hidden sm:block text-left leading-tight">
                <span class="block text-xs font-bold text-slate-800"><?= htmlspecialchars($current_user['nama_lengkap'] ?? 'User') ?></span>
                <span class="inline-block text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded capitalize border border-blue-200"><?= htmlspecialchars($current_user['role'] ?? 'Guest') ?></span>
            </div>
        </div>

        <!-- Logout Action -->
        <a href="<?= base_url('auth/logout.php') ?>" 
           onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
           title="Keluar dari sistem" 
           class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors border border-transparent hover:border-rose-200">
            <i class="fa-solid fa-right-from-bracket text-base"></i>
        </a>
    </div>
</header>

<!-- Flash Notification Area -->
<?php if ($flash): ?>
    <div id="flash-alert" class="px-4 sm:px-6 pt-4">
        <div class="flex items-center justify-between p-4 rounded-xl border shadow-sm text-sm font-medium
            <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : '' ?>
            <?= $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border-rose-200' : '' ?>
            <?= $flash['type'] === 'warning' ? 'bg-amber-50 text-amber-800 border-amber-200' : '' ?>
            <?= $flash['type'] === 'info' ? 'bg-blue-50 text-blue-800 border-blue-200' : '' ?>
        ">
            <div class="flex items-center gap-3">
                <i class="fa-solid 
                    <?= $flash['type'] === 'success' ? 'fa-circle-check text-emerald-600' : '' ?>
                    <?= $flash['type'] === 'danger' ? 'fa-circle-xmark text-rose-600' : '' ?>
                    <?= $flash['type'] === 'warning' ? 'fa-triangle-exclamation text-amber-600' : '' ?>
                    <?= $flash['type'] === 'info' ? 'fa-circle-info text-blue-600' : '' ?>
                    text-base"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
            <button onclick="document.getElementById('flash-alert').remove()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
<?php endif; ?>
