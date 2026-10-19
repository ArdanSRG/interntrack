<?php
// admin/perusahaan/hapus.php - Hapus Perusahaan
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE id = ?");
$stmt->execute([$id]);
$perusahaan = $stmt->fetch();

if (!$perusahaan) {
    set_flash('danger', 'Perusahaan tidak ditemukan.');
    header('Location: ' . base_url('admin/perusahaan/index.php'));
    exit;
}

try {
    $stmtDel = $pdo->prepare("DELETE FROM perusahaan WHERE id = ?");
    $stmtDel->execute([$id]);

    set_flash('success', "Perusahaan {$perusahaan['nama_perusahaan']} berhasil dihapus.");
} catch (Exception $e) {
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/perusahaan/index.php'));
exit;
