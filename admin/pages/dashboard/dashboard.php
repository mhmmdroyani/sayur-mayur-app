<?php 
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
require_once '../../../helpers/RouteHelper.php';

// Get all statistics in single queries
$stats = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT 
    (SELECT COUNT(*) FROM produk) as produk_count,
    (SELECT COUNT(*) FROM transaksi) as transaksi_count,
    (SELECT COUNT(*) FROM produk WHERE stock < 10) as low_stock,
    (SELECT COALESCE(SUM(total), 0) FROM transaksi) as total_revenue
"));

$produk_count = $stats['produk_count'];
$transaksi_count = $stats['transaksi_count'];
$total_revenue = $stats['total_revenue'] ?? 0;
$produk_low_stock = $stats['low_stock'];

// Get recent transactions  
$recent_transactions = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin - SAYUR MAYUR</title>
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

    /* Statistics Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 24px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 24px;
      display: flex;
      align-items: center;
      gap: 20px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      border-left: 4px solid;
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .stat-card-green {
      border-left-color: #22c55e;
    }

    .stat-card-blue {
      border-left-color: #3b82f6;
    }

    .stat-card-orange {
      border-left-color: #f59e0b;
    }

    .stat-card-red {
      border-left-color: #ef4444;
    }

    .stat-icon {
      width: 64px;
      height: 64px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      flex-shrink: 0;
    }

    .stat-card-green .stat-icon {
      background: linear-gradient(135deg, #dcfce7, #bbf7d0);
      color: #16a34a;
    }

    .stat-card-blue .stat-icon {
      background: linear-gradient(135deg, #dbeafe, #bfdbfe);
      color: #2563eb;
    }

    .stat-card-orange .stat-icon {
      background: linear-gradient(135deg, #fed7aa, #fdba74);
      color: #d97706;
    }

    .stat-card-red .stat-icon {
      background: linear-gradient(135deg, #fecaca, #fca5a5);
      color: #dc2626;
    }

    .stat-content {
      flex: 1;
    }

    .stat-label {
      color: #64748b;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 900;
      color: #1e293b;
      margin: 0 0 4px 0;
    }

    .stat-trend {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.8rem;
      color: #64748b;
      font-weight: 500;
    }

    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    .content-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .card-header {
      padding: 20px 24px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-header h3 {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-link {
      color: #22c55e;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
    }

    .card-link:hover {
      text-decoration: underline;
    }

    .card-body {
      padding: 24px;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      gap: 12px;
    }

    .quick-action-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 16px 20px;
      background: linear-gradient(135deg, #f0fdf4, #dcfce7);
      border-radius: 12px;
      text-decoration: none;
      color: #16a34a;
      font-weight: 700;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .quick-action-btn:hover {
      background: linear-gradient(135deg, #dcfce7, #bbf7d0);
      border-color: #22c55e;
      transform: translateX(4px);
    }

    .quick-action-btn i {
      font-size: 1.5rem;
    }

    /* Transaction List */
    .transaction-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .transaction-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      background: #f8f9fa;
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .transaction-item:hover {
      background: #f1f5f9;
      transform: translateX(4px);
    }

    .transaction-customer {
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 4px 0;
    }

    .transaction-date {
      font-size: 0.85rem;
      color: #64748b;
      margin: 0;
    }

    .transaction-amount .amount {
      font-weight: 800;
      color: #22c55e;
      font-size: 1.1rem;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #94a3b8;
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 12px;
      display: block;
    }

    .empty-state p {
      margin: 0;
      font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
      .content-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .admin-sidebar {
        transform: translateX(-100%);
      }

      .admin-main {
        margin-left: 0;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .top-bar {
        padding: 16px;
      }

      .admin-content {
        padding: 20px;
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
          <h1 class="page-title">Dashboard Admin</h1>
          <p class="page-breadcrumb">
            <i class="bi bi-house-door"></i> Home / Dashboard
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
        <!-- Statistics Cards -->
        <div class="stats-grid">
          <div class="stat-card stat-card-green">
            <div class="stat-icon">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Total Produk</p>
              <h3 class="stat-value"><?= $produk_count; ?></h3>
              <span class="stat-trend">
                <i class="bi bi-arrow-up"></i> Aktif
              </span>
            </div>
          </div>

          <div class="stat-card stat-card-blue">
            <div class="stat-icon">
              <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Total Transaksi</p>
              <h3 class="stat-value"><?= $transaksi_count; ?></h3>
              <span class="stat-trend">
                <i class="bi bi-graph-up"></i> Orders
              </span>
            </div>
          </div>

          <div class="stat-card stat-card-orange">
            <div class="stat-icon">
              <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Total Pendapatan</p>
              <h3 class="stat-value">Rp <?= number_format($total_revenue, 0, ',', '.'); ?></h3>
              <span class="stat-trend">
                <i class="bi bi-currency-dollar"></i> Revenue
              </span>
            </div>
          </div>

          <div class="stat-card stat-card-red">
            <div class="stat-icon">
              <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Stok Menipis</p>
              <h3 class="stat-value"><?= $produk_low_stock; ?></h3>
              <span class="stat-trend">
                <i class="bi bi-box"></i> Alert
              </span>
            </div>
          </div>
        </div>

        <!-- Action Cards Grid -->
        <div class="content-grid">
          <!-- Quick Actions -->
          <div class="content-card">
            <div class="card-header">
              <h3><i class="bi bi-lightning-charge"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
              <div class="quick-actions">
                <a href="<?= route('products.create') ?>" class="quick-action-btn">
                  <i class="bi bi-plus-circle"></i>
                  <span>Tambah Produk Baru</span>
                </a>
                <a href="<?= route('products.index') ?>" class="quick-action-btn">
                  <i class="bi bi-list-ul"></i>
                  <span>Lihat Semua Produk</span>
                </a>
                <a href="<?= route('transactions.index') ?>" class="quick-action-btn">
                  <i class="bi bi-receipt"></i>
                  <span>Kelola Transaksi</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div class="content-card">
            <div class="card-header">
              <h3><i class="bi bi-clock-history"></i> Transaksi Terbaru</h3>
              <a href="<?= route('transactions.index') ?>" class="card-link">Lihat Semua →</a>
            </div>
            <div class="card-body">
              <div class="transaction-list">
                <?php if (mysqli_num_rows($recent_transactions) > 0): ?>
                  <?php while ($trans = mysqli_fetch_assoc($recent_transactions)): ?>
                    <div class="transaction-item">
                      <div class="transaction-info">
                        <p class="transaction-customer"><?= htmlspecialchars($trans['nama_pembeli']); ?></p>
                        <p class="transaction-date">
                          <i class="bi bi-calendar3"></i> 
                          <?= date('d M Y, H:i', strtotime($trans['tanggal'])); ?>
                        </p>
                      </div>
                      <div class="transaction-amount">
                        <span class="amount">Rp <?= number_format($trans['total'], 0, ',', '.'); ?></span>
                      </div>
                    </div>
                  <?php endwhile; ?>
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
      </div>
    </div>
  </div>
</body>
</html>
