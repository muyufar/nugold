<?php
/**
 * Halaman Hapus Emas
 */

require_once __DIR__ . '/../models/EmasModel.php';

$userId = getUserId();
$emasModel = new EmasModel();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $result = $emasModel->delete($id, $userId);
    if ($result) {
        $_SESSION['success_message'] = 'Emas berhasil dihapus!';
    } else {
        $_SESSION['error_message'] = 'Gagal menghapus emas.';
    }
}

redirect(BASE_URL . 'index.php?action=dashboard');

