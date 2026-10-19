<?php
// siswa/monitoring/index.php - Hasil Monitoring Guru Pembimbing
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role('siswa');

$page_title = 'Hasil Monitoring Pembimbing';
$user = auth_user();

$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'] ?? 0;

$stmtMon = $pdo->prepare("
    SELECT m.*, pem.nama as nama_pembimbing, pem.nip
    FROM monitoring m
    JOIN pembimbing pem ON pem.id = m.pembimbing_id
    WHERE m.siswa_id = ?
    ORDER BY m.tanggal_monitoring DESC
");
$stmtMon->execute([$siswa_id]);
$monitoring_list = $stmtMon->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="flex h-screen overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col overflow-y-auto">
        <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
        
        <main class="p-4 sm:p-6 lg:p-8 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Hasil Monitoring Guru Pembimbing</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Catatan evaluasi kunjungan industri, penilaian kinerja, dan arahan pembimbing</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Evaluasi Diterima: <?= count($monitoring_list) ?> Catatan</span>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php if (empty($monitoring_list)): ?>
                        <div class="py-12 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                            <p>Belum ada catatan evaluasi monitoring dari guru pembimbing.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($monitoring_list as $m): ?>
                            <div class="p-6 hover:bg-slate-50 transition-colors space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                                            <i class="fa-regular fa-calendar mr-1"></i><?= format_tanggal_id($m['tanggal_monitoring']) ?>
                                        </span>
                                        <span class="text-xs text-slate-500 font-medium">Pembimbing: <strong class="text-slate-800"><?= htmlspecialchars($m['nama_pembimbing']) ?></strong></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-500">Nilai Evaluasi:</span>
                                        <span class="px-3 py-1 rounded-xl font-black text-sm font-mono <?= $m['nilai'] >= 85 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($m['nilai'] >= 75 ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-rose-100 text-rose-800 border border-rose-300') ?>">
                                            <?= $m['nilai'] ?> / 100
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                    <div>
                                        <strong class="text-slate-900 block mb-0.5"><i class="fa-solid fa-comment-dots text-slate-400 mr-1"></i> Catatan Evaluasi:</strong>
                                        <p class="text-slate-700 leading-relaxed"><?= htmlspecialchars($m['catatan']) ?></p>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/60">
                                        <strong class="text-blue-900 block mb-0.5"><i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i> Rekomendasi & Arahan Pembimbing:</strong>
                                        <p class="text-blue-800 leading-relaxed font-medium"><?= htmlspecialchars($m['rekomendasi']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </main>

        <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
