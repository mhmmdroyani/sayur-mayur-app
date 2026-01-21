<?php
// Load routes configuration
require_once dirname(__DIR__) . '/helpers/RouteHelper.php';

// Menentukan halaman active berdasarkan current page
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$current_path = dirname($_SERVER['PHP_SELF']);
$unread = isset($unread) ? $unread : 0;

// Determine current route
$currentRoute = '';
if (strpos($current_path, 'pages/kategori') !== false) {
    $currentRoute = 'kategori.' . ($current_page === 'index.php' ? 'index' : str_replace('.php', '', $current_page));
} elseif (strpos($current_path, 'pages/ongkos-kirim') !== false) {
    $currentRoute = 'ongkos-kirim.' . ($current_page === 'index.php' ? 'index' : str_replace('.php', '', $current_page));
} elseif (strpos($current_path, 'pages/voucher') !== false) {
    $currentRoute = 'voucher.' . ($current_page === 'index.php' ? 'index' : str_replace('.php', '', $current_page));
} elseif (strpos($current_path, 'pages/products') !== false) {
    $currentRoute = 'products.' . ($current_page === 'index.php' ? 'index' : str_replace('.php', '', $current_page));
} elseif (strpos($current_path, 'pages/dashboard') !== false) {
    $currentRoute = 'dashboard';
} elseif (strpos($current_path, 'pages/transactions') !== false) {
    $currentRoute = 'transactions.' . ($current_page === 'index.php' ? 'index' : 'detail');
} elseif (strpos($current_path, 'messages') !== false) {
    $currentRoute = 'review.index';
}
?>

<style>
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

  @media (max-width: 768px) {
    .admin-sidebar {
      width: 0;
      left: -280px;
    }

    .admin-main {
      margin-left: 0;
    }
  }
</style>

<!-- Sidebar -->
<div class="admin-sidebar">
  <div class="sidebar-header">
    <div class="brand-logo">
      <i class="bi bi-basket2-fill"></i>
      <span>SAYUR MAYUR</span>
    </div>
    <p class="sidebar-subtitle">Admin Panel</p>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= route('dashboard') ?>" class="nav-item <?php echo ($currentRoute === 'dashboard') ? 'active' : ''; ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>
    <a href="<?= route('products.index') ?>" class="nav-item <?php echo (strpos($currentRoute, 'products') === 0 && $currentRoute !== 'products.reviews') ? 'active' : ''; ?>">
      <i class="bi bi-box-seam"></i>
      <span>Kelola Produk</span>
    </a>
    <a href="<?= route('kategori.index') ?>" class="nav-item <?php echo (strpos($currentRoute, 'kategori') === 0) ? 'active' : ''; ?>">
      <i class="bi bi-tags"></i>
      <span>Kategori</span>
    </a>
    <a href="<?= route('ongkos-kirim.index') ?>" class="nav-item <?php echo (strpos($currentRoute, 'ongkos-kirim') === 0) ? 'active' : ''; ?>">
      <i class="bi bi-truck"></i>
      <span>Ongkos Kirim</span>
    </a>
    <a href="<?= route('voucher.index') ?>" class="nav-item <?php echo (strpos($currentRoute, 'voucher') === 0) ? 'active' : ''; ?>">
      <i class="bi bi-ticket"></i>
      <span>Voucher</span>
    </a>
    <a href="<?= route('transactions.index') ?>" class="nav-item <?php echo (strpos($currentRoute, 'transactions') === 0) ? 'active' : ''; ?>">
      <i class="bi bi-receipt"></i>
      <span>Transaksi</span>
    </a>
    <a href="<?= route('review.index') ?>" class="nav-item <?php echo ($currentRoute === 'review.index') ? 'active' : ''; ?>">
      <i class="bi bi-chat-dots"></i>
      <span>Pesan</span>
      <?php if ($unread > 0 && $currentRoute === 'review.index'): ?>
        <span class="badge bg-warning ms-auto"><?= $unread ?? 0; ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= route('products.reviews') ?>" class="nav-item <?php echo ($currentRoute === 'products.reviews') ? 'active' : ''; ?>">
      <i class="bi bi-star"></i>
      <span>Review</span>
    </a>
    <div class="nav-divider"></div>
    <a href="<?= route('logout') ?>" class="nav-item nav-logout">
      <i class="bi bi-box-arrow-right"></i>
      <span>Logout</span>
    </a>
  </nav>
</div>
