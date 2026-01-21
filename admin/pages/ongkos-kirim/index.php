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

$ongkos_list = mysqli_query($conn, "SELECT * FROM ongkos_kirim ORDER BY lokasi ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ongkos Kirim - Admin SAYUR MAYUR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { margin-left: 280px; flex: 1; min-height: 100vh; }
        .top-bar { background: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); position: sticky; top: 0; z-index: 100; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }
        .page-breadcrumb { color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0; }
        .admin-content { padding: 32px; }
        .btn-add { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; transition: all 0.3s ease; }
        .btn-add:hover { background: #16a34a; color: white; text-decoration: none; }
        .alert-message { border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .alert-success { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac; color: #15803d; }
        .alert-danger { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #fca5a5; color: #dc2626; }
        .data-table-wrapper { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-bottom: 2px solid #22c55e; }
        .data-table th { padding: 16px; font-weight: 700; color: #16a34a; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        .data-table td { padding: 16px; color: #1e293b; }
        .data-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .data-table tbody tr:hover { background: #f8fafc; }
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
                    <h1 class="page-title">Ongkos Kirim</h1>
                    <p class="page-breadcrumb"><i class="bi bi-house-door"></i> Home / Ongkos Kirim</p>
                </div>
            </div>
            <div class="admin-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert-message alert-success"><i class="bi bi-check-circle-fill"></i> <?= $success_msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert-message alert-danger"><i class="bi bi-exclamation-circle-fill"></i> <?= $error_msg; ?></div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <a href="<?= route('ongkos-kirim.create') ?>" class="btn-add"><i class="bi bi-plus-circle"></i> Tambah Ongkos Kirim</a>
                </div>
                <div class="data-table-wrapper">
                    <?php if (mysqli_num_rows($ongkos_list) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Lokasi</th><th>Biaya Kirim</th><th>Estimasi</th><th>Deskripsi</th><th style="width: 180px;">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($ongkos_list)): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['lokasi']); ?></strong></td>
                                        <td><?= formatRupiah($item['biaya']); ?></td>
                                        <td><?= $item['estimasi_hari']; ?> hari</td>
                                        <td><?= htmlspecialchars($item['deskripsi'] ?? '-'); ?></td>
                                        <td class="actions">
                                            <a href="<?= route('ongkos-kirim.edit', ['id' => $item['id']]) ?>" class="btn-action btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                                            <a href="<?= route('ongkos-kirim.delete', ['id' => $item['id']]) ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus?');"><i class="bi bi-trash"></i> Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada data ongkos kirim</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
