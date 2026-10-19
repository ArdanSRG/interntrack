<?php
// admin/pembimbing/hapus.php - Hapus Pembimbing
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helper.php';

require_role(['admin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, u.id as user_account_id FROM pembimbing p LEFT JOIN users u ON u.id = p.user_id WHERE p.id = ?");
$stmt->execute([$id]);
$pembimbing = $stmt->fetch();

if (!$pembimbing) {
    set_flash('danger', 'Guru pembimbing tidak ditemukan.');
    header('Location: ' . base_url('admin/pembimbing/index.php'));
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtDel = $pdo->prepare("DELETE FROM pembimbing WHERE id = ?");
    $stmtDel->execute([$id]);

    if (!empty($pembimbing['user_account_id'])) {
        $stmtDelU = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDelU->execute([$pembimbing['user_account_id']]);
    }

    $pdo->commit();
    set_flash('success', "Guru pembimbing {$pembimbing['nama']} berhasil dihapus.");
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: ' . base_url('admin/pembimbing/index.php'));
exit;
