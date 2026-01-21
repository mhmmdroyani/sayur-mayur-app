<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
if (!empty($success_msg)) unset($_SESSION['success_msg']);
if (!empty($error_msg)) unset($_SESSION['error_msg']);

$product_list = mysqli_query($conn, "SELECT p.id, p.nama, p.harga, p.stock, k.nama as kategori, p.gambar FROM produk p LEFT JOIN kategori k ON p.kategori = k.id ORDER BY p.id DESC");

$total_produk = mysqli_num_rows($product_list);
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE stock BETWEEN 1 AND 10"))['count'];
$out_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE stock = 0"))['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk - Admin SAYUR MAYUR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { margin-left: 280px; flex: 1; min-height: 100vh; }
        .top-bar { background: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }
        .admin-content { padding: 32px; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); display: flex; align-items: center; gap: 16px; }
        .stat-icon { font-size: 2.5rem; }
        .stat-icon-total { color: #22c55e; }
        .stat-icon-low { color: #f59e0b; }
        .stat-icon-out { color: #ef4444; }
        .stat-info h3 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
        .stat-info p { font-size: 0.85rem; color: #64748b; margin: 4px 0 0 0; }
        .btn-add { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
        .btn-add:hover { background: #16a34a; color: white; }
        .alert-message { border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .alert-success { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac; color: #15803d; }
        .data-table-wrapper { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-bottom: 2px solid #22c55e; }
        .data-table th { padding: 16px; font-weight: 700; color: #16a34a; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        .data-table td { padding: 16px; color: #1e293b; }
        .data-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .product-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f1f5f9; }
        .badge-stock { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; }
        .badge-in-stock { background: #dcfce7; color: #15803d; }
        .badge-low-stock { background: #fef3c7; color: #b45309; }
        .badge-out-stock { background: #fee2e2; color: #dc2626; }
        .actions { display: flex; gap: 8px; }
        .btn-action { padding: 8px 14px; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-edit { background: #bfdbfe; color: #1e40af; }
        .btn-edit:hover { background: #93c5fd; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include '../../../includes/sidebar.php'; ?>
        <div class="admin-main">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Produk</h1>
                    <p class="page-breadcrumb" style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;"><i class="bi bi-house-door"></i> Home / Produk</p>
                </div>
            </div>
            <div class="admin-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert-message alert-success"><i class="bi bi-check-circle-fill"></i> <?= $success_msg; ?></div>
                <?php endif; ?>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-total"><i class="bi bi-box"></i></div>
                        <div class="stat-info">
                            <h3><?= $total_produk; ?></h3>
                            <p>Total Produk</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-low"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-info">
                            <h3><?= $low_stock; ?></h3>
                            <p>Stok Rendah (1-10)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-out"><i class="bi bi-x-circle"></i></div>
                        <div class="stat-info">
                            <h3><?= $out_stock; ?></h3>
                            <p>Stok Habis</p>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <a href="<?= route('products.create') ?>" class="btn-add"><i class="bi bi-plus-circle"></i> Tambah Produk</a>
                    <a href="<?= route('products.reviews') ?>" class="btn-add" style="background: #3b82f6; margin-left: auto;"><i class="bi bi-chat-left"></i> Kelola Review</a>
                </div>

                <div class="data-table-wrapper">
                    <?php if ($total_produk > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th style="width: 180px;">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($product_list, 0);
                                while ($item = mysqli_fetch_assoc($product_list)): 
                                    $stock_status = $item['stock'] == 0 ? 'out-stock' : ($item['stock'] <= 10 ? 'low-stock' : 'in-stock');
                                ?>
                                    <tr>
                                        <td><img src="../../../../assets/img/<?= htmlspecialchars($item['gambar'] ?? 'placeholder.png'); ?>" alt="<?= htmlspecialchars($item['nama']); ?>" class="product-img"></td>
                                        <td><strong><?= htmlspecialchars($item['nama']); ?></strong></td>
                                        <td><?= htmlspecialchars($item['kategori'] ?? '-'); ?></td>
                                        <td><?= formatRupiah($item['harga']); ?></td>
                                        <td><span class="badge-stock badge-<?= $stock_status; ?>"><?= $item['stock']; ?> Unit</span></td>
                                        <td class="actions">
                                            <a href="<?= route('products.edit', ['id' => $item['id']]) ?>" class="btn-action btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                                            <a href="<?= route('products.delete', ['id' => $item['id']]) ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus?');"><i class="bi bi-trash"></i> Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data produk</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
