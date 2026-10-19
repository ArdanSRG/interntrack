<?php
// admin/siswa/hapus.php - Hapus Data Siswa
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, u.id as user_account_id FROM siswa s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    set_flash('danger', 'Data siswa tidak ditemukan.');
    header('Location: ' . base_url('admin/siswa/index.php'));
    exit;
}

try {
    $pdo->beginTransaction();

    // Hapus siswa (foreign keys cascade akan menghapus penempatan, jurnal, absensi, monitoring)
    $stmtDel = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    $stmtDel->execute([$id]);

    // Hapus user account jika ada
    if (!empty($siswa['user_account_id'])) {
        $stmtDelU = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDelU->execute([$siswa['user_account_id']]);
    }

    $pdo->commit();
    set_flash('success', "Data siswa {$siswa['nama']} (NIS: {$siswa['nis']}) berhasil dihapus beserta akun terkait.");
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('danger', 'Gagal menghapus siswa: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/siswa/index.php'));
exit;
