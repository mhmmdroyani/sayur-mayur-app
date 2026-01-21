<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: " . route('ongkos-kirim.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM ongkos_kirim WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_msg'] = "Ongkos kirim berhasil dihapus!";
} else {
    $_SESSION['error_msg'] = "Gagal menghapus ongkos kirim!";
}

header("Location: " . route('ongkos-kirim.index'));
exit;
