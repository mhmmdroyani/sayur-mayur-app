<?php 
session_start();
include '../auth.php';
include '../../config/koneksi.php';

// Handle mark as read
if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE pesan SET status='read' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM pesan WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = "";
if ($filter == 'unread') {
    $where = "WHERE status='unread'";
} elseif ($filter == 'read') {
    $where = "WHERE status='read'";
}

// Get messages
$query = "SELECT * FROM pesan $where ORDER BY created_at DESC";
$messages = mysqli_query($conn, $query);

// Get counts
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan"))['total'];
$unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='unread'"))['total'];
$read_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='read'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesan - Admin SAYUR MAYUR</title>
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

    .nav-divider {
      height: 1px;
      background: rgba(0, 0, 0, 0.08);
      margin: 16px 24px;
    }

    .nav-logout {
      color: #ef4444;
    }

    .nav-logout:hover {
      background: rgba(239, 68, 68, 0.1) !important;
      color: #dc2626 !important;
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

    .stat-card.unread {
      border-left-color: #f59e0b;
    }

    .stat-card.read {
      border-left-color: #22c55e;
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

    /* Filter Tabs */
    .filter-tabs {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
      background: white;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .filter-btn {
      padding: 10px 20px;
      border: 2px solid #e5e7eb;
      background: white;
      border-radius: 8px;
      color: #64748b;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .filter-btn:hover {
      border-color: #22c55e;
      color: #22c55e;
    }

    .filter-btn.active {
      background: #22c55e;
      border-color: #22c55e;
      color: white;
    }

    /* Messages Table */
    .messages-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .messages-table {
      width: 100%;
      border-collapse: collapse;
    }

    .messages-table thead {
      background: linear-gradient(135deg, #f0fdf4, #dcfce7);
      border-bottom: 2px solid #22c55e;
    }

    .messages-table th {
      padding: 16px;
      text-align: left;
      font-weight: 700;
      color: #16a34a;
      font-size: 0.9rem;
      text-transform: uppercase;
    }

    .messages-table tbody tr {
      border-bottom: 1px solid #f1f5f9;
      transition: background 0.2s ease;
    }

    .messages-table tbody tr:hover {
      background: #f8f9fa;
    }

    .messages-table tbody tr.unread {
      background: #fef3c7;
    }

    .messages-table td {
      padding: 16px;
      color: #1e293b;
    }

    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 700;
    }

    .status-badge.unread {
      background: #fed7aa;
      color: #92400e;
    }

    .status-badge.read {
      background: #bbf7d0;
      color: #15803d;
    }

    .action-btn {
      padding: 8px 12px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      margin: 0 4px;
    }

    .btn-view {
      background: #3b82f6;
      color: white;
    }

    .btn-view:hover {
      background: #2563eb;
      color: white;
    }

    .btn-delete {
      background: #ef4444;
      color: white;
    }

    .btn-delete:hover {
      background: #dc2626;
      color: white;
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
  </style>
</head>
<body>
  <div class="admin-wrapper">
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
      <div class="top-bar">
        <div class="top-bar-left">
          <h1 class="page-title">Pesan</h1>
          <p class="page-breadcrumb">
            <i class="bi bi-house-door"></i> Home / Pesan
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
            <h3><?= $total; ?></h3>
            <p>Total Pesan</p>
          </div>
          <div class="stat-card unread">
            <h3><?= $unread; ?></h3>
            <p>Belum Dibaca</p>
          </div>
          <div class="stat-card read">
            <h3><?= $read_count; ?></h3>
            <p>Sudah Dibaca</p>
          </div>
        </div>

        <!-- Filter -->
        <div class="filter-tabs">
          <a href="index.php?filter=all" class="filter-btn <?= ($filter == 'all') ? 'active' : ''; ?>">
            Semua (<?= $total; ?>)
          </a>
          <a href="index.php?filter=unread" class="filter-btn <?= ($filter == 'unread') ? 'active' : ''; ?>">
            Belum Dibaca (<?= $unread; ?>)
          </a>
          <a href="index.php?filter=read" class="filter-btn <?= ($filter == 'read') ? 'active' : ''; ?>">
            Sudah Dibaca (<?= $read_count; ?>)
          </a>
        </div>

        <!-- Messages Table -->
        <div class="messages-card">
          <table class="messages-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email / Telepon</th>
                <th>Subjek</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($messages) > 0): ?>
                <?php $no = 1; while ($msg = mysqli_fetch_assoc($messages)): ?>
                  <tr class="<?= $msg['status'] == 'unread' ? 'unread' : ''; ?>">
                    <td><?= $no++; ?></td>
                    <td><strong><?= htmlspecialchars($msg['nama']); ?></strong></td>
                    <td>
                      <?= htmlspecialchars($msg['email']); ?><br>
                      <small class="text-muted"><?= htmlspecialchars($msg['telepon'] ?: '-'); ?></small>
                    </td>
                    <td><?= htmlspecialchars($msg['subjek']); ?></td>
                    <td>
                      <small><?= date('d M Y', strtotime($msg['created_at'])); ?><br>
                      <?= date('H:i', strtotime($msg['created_at'])); ?></small>
                    </td>
                    <td>
                      <span class="status-badge <?= $msg['status']; ?>">
                        <?= $msg['status'] == 'unread' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?>
                      </span>
                    </td>
                    <td>
                      <button class="action-btn btn-view" onclick="viewMessage(<?= $msg['id']; ?>, '<?= htmlspecialchars(addslashes($msg['nama'])); ?>', '<?= htmlspecialchars(addslashes($msg['email'])); ?>', '<?= htmlspecialchars(addslashes($msg['telepon'])); ?>', '<?= htmlspecialchars(addslashes($msg['subjek'])); ?>', '<?= htmlspecialchars(addslashes($msg['pesan'])); ?>', '<?= date('d M Y H:i', strtotime($msg['created_at'])); ?>')">
                        <i class="bi bi-eye"></i> Lihat
                      </button>
                       <a href="index.php?delete=1&id=<?= $msg['id']; ?>" 
                         class="action-btn btn-delete"
                         onclick="return confirm('Yakin ingin menghapus pesan ini?')">
                        <i class="bi bi-trash"></i> Hapus
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada pesan</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Detail Pesan -->
  <div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Pesan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <strong>Nama:</strong>
            <p id="modalNama"></p>
          </div>
          <div class="mb-3">
            <strong>Email:</strong>
            <p id="modalEmail"></p>
          </div>
          <div class="mb-3">
            <strong>Telepon:</strong>
            <p id="modalTelepon"></p>
          </div>
          <div class="mb-3">
            <strong>Subjek:</strong>
            <p id="modalSubjek"></p>
          </div>
          <div class="mb-3">
            <strong>Tanggal:</strong>
            <p id="modalTanggal"></p>
          </div>
          <div class="mb-3">
            <strong>Pesan:</strong>
            <p id="modalPesan" style="white-space: pre-wrap;"></p>
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" id="markReadBtn" class="btn btn-success">Tandai Sudah Dibaca</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function viewMessage(id, nama, email, telepon, subjek, pesan, tanggal) {
      document.getElementById('modalNama').textContent = nama;
      document.getElementById('modalEmail').textContent = email;
      document.getElementById('modalTelepon').textContent = telepon || '-';
      document.getElementById('modalSubjek').textContent = subjek;
      document.getElementById('modalTanggal').textContent = tanggal;
      document.getElementById('modalPesan').textContent = pesan;
      document.getElementById('markReadBtn').href = 'index.php?mark_read=1&id=' + id;
      
      const modal = new bootstrap.Modal(document.getElementById('messageModal'));
      modal.show();
    }
  </script>
</body>
</html>
