<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: " . route('products.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT gambar FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($product && !empty($product['gambar'])) {
    $gambar_path = '../../../../assets/img/' . $product['gambar'];
    if (file_exists($gambar_path)) {
        @unlink($gambar_path);
    }
}

$stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_msg'] = "Produk berhasil dihapus!";
} else {
    $_SESSION['error_msg'] = "Gagal menghapus produk!";
}

header("Location: " . route('products.index'));
exit;
