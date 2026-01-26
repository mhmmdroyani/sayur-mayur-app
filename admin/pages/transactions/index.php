<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment'] ?? '';
$date_filter = $_GET['date'] ?? '';
$sort_by = $_GET['sort'] ?? 'terbaru';

// Build query
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
  // Cari berdasarkan ID transaksi (as invoice badge), nama pembeli, atau nomor telepon
  $where[] = "(CAST(id AS CHAR) LIKE ? OR nama_pembeli LIKE ? OR no_telp LIKE ?)";
  $search_param = "%{$search}%";
  $params[] = $search_param;
  $params[] = $search_param;
  $params[] = $search_param;
  $types .= 'sss';
}

if (!empty($status_filter)) {
  $where[] = "status = ?";
  $params[] = $status_filter;
  $types .= 's';
}

if (!empty($payment_filter)) {
  $where[] = "payment_method = ?";
  $params[] = $payment_filter;
  $types .= 's';
}

if (!empty($date_filter)) {
  switch ($date_filter) {
    case 'today':
      $where[] = "DATE(tanggal) = CURDATE()";
      break;
    case 'week':
      $where[] = "YEARWEEK(tanggal) = YEARWEEK(NOW())";
      break;
    case 'month':
      $where[] = "YEAR(tanggal) = YEAR(NOW()) AND MONTH(tanggal) = MONTH(NOW())";
      break;
  }
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sort
$order_by = "ORDER BY ";
switch ($sort_by) {
  case 'terlama':
    $order_by .= "id ASC";
    break;
  case 'total_asc':
    $order_by .= "total ASC";
    break;
  case 'total_desc':
    $order_by .= "total DESC";
    break;
  default:
    $order_by .= "id DESC";
}

// Get transactions with filters
$query = "SELECT * FROM transaksi {$where_clause} {$order_by}";

if (!empty($params)) {
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
} else {
  $result = mysqli_query($conn, $query);
}

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
    
    /* Filter & Search Section */
    .filter-section { background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
    .filter-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
    .filter-group { display: flex; flex-direction: column; }
    .filter-label { font-weight: 600; color: #64748b; margin-bottom: 8px; font-size: 0.85rem; }
    .filter-input, .filter-select { padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
    .filter-input:focus, .filter-select:focus { border-color: #22c55e; outline: none; }
    .btn-filter { padding: 10px 20px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
    .btn-filter:hover { background: #16a34a; }
    .btn-reset-filter { padding: 10px 16px; background: #f3f4f6; color: #64748b; border: 2px solid #e5e7eb; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-reset-filter:hover { background: #e5e7eb; color: #475569; }
    .active-filters { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
    .filter-badge { background: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
    .filter-badge i { cursor: pointer; }
    @media (max-width: 1200px) { .filter-row { grid-template-columns: 1fr 1fr; } }
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

        <!-- Filter & Search Section -->
        <div class="filter-section">
          <form method="GET" action="index.php" id="filterForm">
            <div class="filter-row">
              <div class="filter-group">
                <label class="filter-label"><i class="bi bi-search"></i> Cari Transaksi</label>
                <input type="text" name="search" class="filter-input" placeholder="ID transaksi, nama pembeli, atau telepon..." value="<?= htmlspecialchars($search); ?>">
              </div>
              
              <div class="filter-group">
                <label class="filter-label"><i class="bi bi-hourglass"></i> Status</label>
                <select name="status" class="filter-select">
                  <option value="">Semua Status</option>
                  <option value="pending" <?= $status_filter == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                  <option value="processing" <?= $status_filter == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                  <option value="shipped" <?= $status_filter == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                  <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                  <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
              </div>
              
              <div class="filter-group">
                <label class="filter-label"><i class="bi bi-credit-card"></i> Pembayaran</label>
                <select name="payment" class="filter-select">
                  <option value="">Semua Metode</option>
                  <option value="COD" <?= $payment_filter == 'COD' ? 'selected' : ''; ?>>COD</option>
                  <option value="Transfer Bank" <?= $payment_filter == 'Transfer Bank' ? 'selected' : ''; ?>>Transfer Bank</option>
                  <option value="E-Wallet" <?= $payment_filter == 'E-Wallet' ? 'selected' : ''; ?>>E-Wallet</option>
                </select>
              </div>
              
              <div class="filter-group">
                <label class="filter-label"><i class="bi bi-calendar"></i> Periode</label>
                <select name="date" class="filter-select">
                  <option value="">Semua Waktu</option>
                  <option value="today" <?= $date_filter == 'today' ? 'selected' : ''; ?>>Hari Ini</option>
                  <option value="week" <?= $date_filter == 'week' ? 'selected' : ''; ?>>Minggu Ini</option>
                  <option value="month" <?= $date_filter == 'month' ? 'selected' : ''; ?>>Bulan Ini</option>
                </select>
              </div>
              
              <div class="filter-group">
                <label class="filter-label"><i class="bi bi-sort-down"></i> Urutkan</label>
                <select name="sort" class="filter-select">
                  <option value="terbaru" <?= $sort_by == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                  <option value="terlama" <?= $sort_by == 'terlama' ? 'selected' : ''; ?>>Terlama</option>
                  <option value="total_desc" <?= $sort_by == 'total_desc' ? 'selected' : ''; ?>>Total (Besar-Kecil)</option>
                  <option value="total_asc" <?= $sort_by == 'total_asc' ? 'selected' : ''; ?>>Total (Kecil-Besar)</option>
                </select>
              </div>
              
              <div style="display: flex; gap: 8px; align-items: flex-end;">
                <button type="submit" class="btn-filter">
                  <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="index.php" class="btn-reset-filter" title="Reset Filter">
                  <i class="bi bi-arrow-counterclockwise"></i>
                </a>
              </div>
            </div>
            
            <?php if (!empty($search) || !empty($status_filter) || !empty($payment_filter) || !empty($date_filter) || $sort_by != 'terbaru'): ?>
              <div class="active-filters">
                <small style="color: #64748b; font-weight: 600;">Filter Aktif:</small>
                <?php if (!empty($search)): ?>
                  <span class="filter-badge">
                    <i class="bi bi-search"></i> "<?= htmlspecialchars($search); ?>"
                    <i class="bi bi-x" onclick="removeFilter('search')"></i>
                  </span>
                <?php endif; ?>
                <?php if (!empty($status_filter)): ?>
                  <span class="filter-badge">
                    <i class="bi bi-hourglass"></i> Status: <?= ucfirst($status_filter); ?>
                    <i class="bi bi-x" onclick="removeFilter('status')"></i>
                  </span>
                <?php endif; ?>
                <?php if (!empty($payment_filter)): ?>
                  <span class="filter-badge">
                    <i class="bi bi-credit-card"></i> <?= $payment_filter; ?>
                    <i class="bi bi-x" onclick="removeFilter('payment')"></i>
                  </span>
                <?php endif; ?>
                <?php if (!empty($date_filter)): ?>
                  <span class="filter-badge">
                    <i class="bi bi-calendar"></i> <?= ucfirst($date_filter); ?>
                    <i class="bi bi-x" onclick="removeFilter('date')"></i>
                  </span>
                <?php endif; ?>
                <?php if ($sort_by != 'terbaru'): ?>
                  <span class="filter-badge">
                    <i class="bi bi-sort-down"></i> <?= str_replace('_', ' ', ucfirst($sort_by)); ?>
                    <i class="bi bi-x" onclick="removeFilter('sort')"></i>
                  </span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </form>
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
                  <th>Metode Pembayaran</th>
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
                    <td><?= htmlspecialchars($t['payment_method'] ?? ''); ?></td>
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
  <script>
    function removeFilter(filterName) {
      const form = document.getElementById('filterForm');
      const url = new URL(window.location.href);
      url.searchParams.delete(filterName);
      window.location.href = url.toString();
    }
    
    // Auto-submit on select change
    document.querySelectorAll('.filter-select').forEach(select => {
      select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
      });
    });
  </script>
</body>
</html>
