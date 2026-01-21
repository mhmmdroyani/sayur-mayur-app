<?php 
session_start();
include 'auth.php';

// Redirect to dashboard
header("Location: pages/dashboard/dashboard.php");
exit;
?>

<div class="admin-container">
  <!-- Header -->
  <div class="admin-header">
    <div class="admin-header-content">
      <h1 class="admin-title">Dashboard Admin</h1>
      <p class="admin-subtitle">Kelola dan pantau data produk SAYUR MAYUR</p>
    </div>
    <a href="logout.php" class="btn-logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
        <i class="bi bi-box-seam"></i>
      </div>
      <div class="stat-content">
        <h3 class="stat-value"><?= $produk_count; ?></h3>
        <p class="stat-label">Total Produk</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
        <i class="bi bi-receipt"></i>
      </div>
      <div class="stat-content">
        <h3 class="stat-value"><?= $transaksi_count; ?></h3>
        <p class="stat-label">Total Transaksi</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <i class="bi bi-cash-coin"></i>
      </div>
      <div class="stat-content">
        <h3 class="stat-value">Rp <?= number_format($total_revenue); ?></h3>
        <p class="stat-label">Total Pendapatan</p>
      </div>
    </div>
  </div>

  <!-- Action Cards -->
  <div class="action-grid">
    <div class="action-card">
      <div class="action-icon">
        <i class="bi bi-plus-circle"></i>
      </div>
      <h4>Kelola Produk</h4>
      <p>Tambah, edit, atau hapus produk dari katalog</p>
      <a href="produk.php" class="action-btn">
        Buka <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="action-card">
      <div class="action-icon">
        <i class="bi bi-bag-check"></i>
      </div>
      <h4>Riwayat Transaksi</h4>
      <p>Lihat semua pesanan dan detail pembayaran</p>
      <a href="<?php require_once 'helpers/RouteHelper.php'; echo route('transactions.index'); ?>" class="action-btn">
        Buka <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</div>

<style>
.admin-container {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 20px;
}

.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  padding-bottom: 30px;
  border-bottom: 2px solid #ecf0f1;
}

.admin-header-content h1 {
  font-size: 2.5rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0;
}

.admin-title {
  margin-bottom: 8px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.admin-subtitle {
  color: #64748b;
  font-size: 1.1rem;
  margin: 0;
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 50px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-logout:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
  background: linear-gradient(135deg, #dc2626, #b91c1c);
}

/* Statistics Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 50px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 1px solid #ecf0f1;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 32px;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0;
}

.stat-label {
  color: #64748b;
  font-size: 0.9rem;
  margin: 4px 0 0 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Action Cards */
.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.action-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  border-radius: 16px;
  padding: 32px 24px;
  text-align: center;
  border: 2px solid #e0e0e0;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-card:hover {
  transform: translateY(-6px);
  border-color: #22c55e;
  box-shadow: 0 16px 40px rgba(34, 197, 94, 0.2);
}

.action-icon {
  font-size: 3rem;
  color: #22c55e;
  margin-bottom: 16px;
}

.action-card h4 {
  font-size: 1.3rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 8px 0;
}

.action-card p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0 0 20px 0;
  line-height: 1.5;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  border: none;
  border-radius: 50px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);
}

.action-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  text-decoration: none;
}

@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    gap: 20px;
    align-items: flex-start;
  }

  .admin-title {
    font-size: 2rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .action-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
