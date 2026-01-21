<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$error_msg = '';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check apakah kategori digunakan oleh produk
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE kategori = (SELECT nama FROM kategori WHERE id = $id)");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['count'] > 0) {
        $_SESSION['error_msg'] = "Tidak bisa menghapus kategori ini karena masih digunakan oleh " . $result['count'] . " produk!";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM kategori WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Kategori berhasil dihapus!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
        }
    }
} else {
    $_SESSION['error_msg'] = "ID kategori tidak ditemukan!";
}

header("Location: " . route('kategori.index'));
exit;
