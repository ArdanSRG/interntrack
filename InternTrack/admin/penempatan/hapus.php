<?php
// admin/penempatan/hapus.php - Hapus Penempatan PKL
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT pen.*, s.nama as nama_siswa FROM penempatan pen JOIN siswa s ON s.id = pen.siswa_id WHERE pen.id = ?");
$stmt->execute([$id]);
$penempatan = $stmt->fetch();

if (!$penempatan) {
    set_flash('danger', 'Data penempatan tidak ditemukan.');
    header('Location: ' . base_url('admin/penempatan/index.php'));
    exit;
}

try {
    $stmtDel = $pdo->prepare("DELETE FROM penempatan WHERE id = ?");
    $stmtDel->execute([$id]);

    set_flash('success', "Penempatan siswa {$penempatan['nama_siswa']} berhasil dihapus.");
} catch (Exception $e) {
    set_flash('danger', 'Gagal menghapus penempatan: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/penempatan/index.php'));
exit;
