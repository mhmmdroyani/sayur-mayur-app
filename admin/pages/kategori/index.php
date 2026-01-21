<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

// Get messages from session
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';

// Clear session messages after displaying
if (!empty($success_msg)) unset($_SESSION['success_msg']);
if (!empty($error_msg)) unset($_SESSION['error_msg']);

// Query all categories
$kategoris = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Kategori - Admin SAYUR MAYUR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 30px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 8px;
        }

        .brand-logo i {
            font-size: 2rem;
            color: #22c55e;
        }

        .sidebar-subtitle {
            color: #94a3b8;
            font-size: 0.85rem;
            margin: 0;
        }

        .sidebar-nav {
            padding: 24px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-left: 3px solid #22c55e;
        }

        .nav-item.active {
            background: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            border-left: 3px solid #22c55e;
        }

        .nav-item i {
            font-size: 1.2rem;
            width: 24px;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 16px 24px;
        }

        .nav-logout {
            color: #f87171;
        }

        .nav-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            border-left-color: #ef4444;
        }

        /* Main Content */
        .admin-main {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 20px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-bar-left {
            flex: 1;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .page-breadcrumb {
            color: #64748b;
            font-size: 0.9rem;
            margin: 4px 0 0 0;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: #f1f5f9;
            border-radius: 50px;
            font-weight: 600;
            color: #1e293b;
        }

        .admin-profile i {
            font-size: 1.5rem;
            color: #22c55e;
        }

        .admin-content {
            padding: 32px;
        }

        .btn-add {
            background: #22c55e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background: #16a34a;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .alert-message {
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .alert-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border: 1px solid #86efac;
            color: #15803d;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 1px solid #fca5a5;
            color: #dc2626;
        }

        .data-table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .data-table thead {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-bottom: 2px solid #22c55e;
        }

        .data-table th {
            padding: 16px;
            font-weight: 700;
            color: #16a34a;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .data-table td {
            padding: 16px;
            color: #1e293b;
        }

        .icon-display {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #16a34a;
            font-size: 1.3rem;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #bfdbfe;
            color: #1e40af;
        }

        .btn-edit:hover {
            background: #93c5fd;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fecaca;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 16px;
            display: block;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 0;
                left: -280px;
            }

            .admin-main {
                margin-left: 0;
            }

            .top-bar {
                flex-direction: column;
                gap: 12px;
            }

            .admin-content {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include '../../../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h1 class="page-title">Manage Kategori</h1>
                    <p class="page-breadcrumb">
                        <i class="bi bi-house-door"></i> Home / Kategori
                    </p>
                </div>
                <div class="top-bar-right">
                    <div class="admin-profile">
                        <i class="bi bi-person-circle"></i>
                        <span>Admin</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="admin-content">
                <!-- Messages -->
                <?php if (!empty($success_msg)): ?>
                    <div class="alert-message alert-success">
                        <i class="bi bi-check-circle-fill"></i> <?= $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert-message alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i> <?= $error_msg; ?>
                    </div>
                <?php endif; ?>

                <!-- Action Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div style="display: flex; gap: 12px;">
                        <a href="<?= route('kategori.create') ?>" class="btn-add">
                            <i class="bi bi-plus-circle"></i> Tambah Kategori
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="data-table-wrapper">
                    <?php if (mysqli_num_rows($kategoris) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Icon</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($kat = mysqli_fetch_assoc($kategoris)): ?>
                                    <tr>
                                        <td>
                                            <div class="icon-display">
                                                <i class="bi <?= htmlspecialchars($kat['icon'] ?? 'bi-folder'); ?>"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($kat['nama']); ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($kat['deskripsi'] ?? '-'); ?>
                                        </td>
                                        <td class="actions">
                                            <a href="<?= route('kategori.edit', ['id' => $kat['id']]) ?>" class="btn-action btn-edit">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="<?= route('kategori.delete', ['id' => $kat['id']]) ?>" class="btn-action btn-delete" 
                                               onclick="return confirm('Yakin ingin menghapus kategori ini?');">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Belum ada kategori</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
