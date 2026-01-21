<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

// Get all transactions
$query = "SELECT * FROM transaksi ORDER BY id DESC LIMIT 50";
$result = mysqli_query($conn, $query);

// Get stats - optimized with single query
$status_query = "
  SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_count,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
  FROM transaksi
";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $status_query));
$total_count = $stats['total'];
$pending_count = $stats['pending_count'];
$processing_count = $stats['processing_count'];
$shipped_count = $stats['shipped_count'];
$delivered_count = $stats['delivered_count'];
$cancelled_count = $stats['cancelled_count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Transaksi - Admin SAYUR MAYUR</title>
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

    /* Stats Cards */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      border-left: 4px solid;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .stat-card.total {
      border-left-color: #3b82f6;
    }

    .stat-card.pending {
      border-left-color: #f59e0b;
    }

    .stat-card.processing {
      border-left-color: #06b6d4;
    }

    .stat-card.shipped {
      border-left-color: #8b5cf6;
    }

    .stat-card.delivered {
      border-left-color: #22c55e;
    }

    .stat-card.cancelled {
      border-left-color: #ef4444;
    }

    .stat-card h3 {
      font-size: 2rem;
      font-weight: 900;
      margin: 0;
      color: #1e293b;
    }

    .stat-card p {
      color: #64748b;
      margin: 4px 0 0 0;
      font-size: 0.9rem;
    }

    /* Table */
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

    .invoice-badge {
      display: inline-block;
      padding: 6px 12px;
      background: #dbeafe;
      color: #1e40af;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.85rem;
    }

    .amount-badge {
      display: inline-block;
      padding: 6px 12px;
      background: #fef3c7;
      color: #92400e;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.85rem;
    }

    .actions {
      display: flex;
      gap: 8px;
    }

    .btn-view {
      background: #3b82f6;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      transition: background 0.2s ease;
    }

    .btn-view:hover {
      background: #2563eb;
      color: white;
      text-decoration: none;
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
      <div class="top-bar">
        <div class="top-bar-left">
          <h1 class="page-title">Riwayat Transaksi</h1>
          <p class="page-breadcrumb">
            <i class="bi bi-house-door"></i> Home / Transaksi
          </p>
        </div>
        <div class="top-bar-right">
          <div class="admin-profile">
            <i class="bi bi-person-circle"></i>
            <span>Admin</span>
          </div>
        </div>
      </div>

      <div class="admin-content">
        <!-- Stats -->
        <div class="stats-row">
          <div class="stat-card total">
            <h3><?= $total_count; ?></h3>
            <p>Total Transaksi</p>
          </div>
          <div class="stat-card pending">
            <h3><?= $pending_count; ?></h3>
            <p>Menunggu</p>
          </div>
          <div class="stat-card processing">
            <h3><?= $processing_count; ?></h3>
            <p>Sedang Diproses</p>
          </div>
          <div class="stat-card shipped">
            <h3><?= $shipped_count; ?></h3>
            <p>Dikirim</p>
          </div>
          <div class="stat-card delivered">
            <h3><?= $delivered_count; ?></h3>
            <p>Selesai</p>
          </div>
          <div class="stat-card cancelled">
            <h3><?= $cancelled_count; ?></h3>
            <p>Dibatalkan</p>
          </div>
        </div>

        <!-- Table -->
        <div class="data-table-wrapper">
          <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="data-table">
              <thead>
                <tr>
                  <th>Invoice</th>
                  <th>Nama Pembeli</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Tanggal Order</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($t = mysqli_fetch_assoc($result)): 
                  $date = new DateTime($t['tanggal']);
                  $formatted_date = $date->format('d M Y H:i');
                ?>
                  <tr>
                    <td><span class="invoice-badge">#INV-<?= str_pad($t['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                    <td><?= htmlspecialchars($t['nama_pembeli']); ?></td>
                    <td><span class="amount-badge">Rp <?= number_format($t['total']); ?></span></td>
                    <td><?= getStatusBadge($t['status']); ?></td>
                    <td><i class="bi bi-calendar3"></i> <?= $formatted_date; ?></td>
                    <td class="actions">
                      <a href="detail.php?id=<?= $t['id']; ?>" class="btn-view">
                        <i class="bi bi-eye"></i> Lihat
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="empty-state">
              <i class="bi bi-inbox"></i>
              <p>Belum ada transaksi</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
