<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: " . route('voucher.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM voucher WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_msg'] = "Voucher berhasil dihapus!";
} else {
    $_SESSION['error_msg'] = "Gagal menghapus voucher!";
}

header("Location: " . route('voucher.index'));
exit;
