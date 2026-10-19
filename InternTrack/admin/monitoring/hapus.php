<?php
// admin/monitoring/hapus.php - Hapus Monitoring
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin', 'pembimbing']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM monitoring WHERE id = ?");
$stmt->execute([$id]);
$mon = $stmt->fetch();

if (!$mon) {
    set_flash('danger', 'Data monitoring tidak ditemukan.');
    header('Location: ' . base_url('admin/monitoring/index.php'));
    exit;
}

try {
    $stmtDel = $pdo->prepare("DELETE FROM monitoring WHERE id = ?");
    $stmtDel->execute([$id]);

    set_flash('success', 'Catatan monitoring berhasil dihapus.');
} catch (Exception $e) {
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/monitoring/index.php'));
exit;
