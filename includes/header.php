<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Sayur Mayur - Belanja sayur segar online dengan harga terbaik">
  <title>SAYUR MAYUR - Sayuran Segar Setiap Hari</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4" href="index.php">
      <i class="bi bi-basket2-fill text-success"></i>
      <span class="text-success">SAYUR</span> MAYUR
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <i class="bi bi-house-door"></i> Beranda
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="products.php">
            <i class="bi bi-grid"></i> Produk
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">
            <i class="bi bi-envelope"></i> Kontak
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/index.php">
            <i class="bi bi-gear"></i> Admin
          </a>
        </li>
      </ul>
      
      <div class="nav-actions">
        <button class="cart-btn position-relative" id="openCart" type="button" title="Keranjang">
          <i class="bi bi-cart3"></i>
          <span class="cart-badge" id="cartCount">0</span>
        </button>
        <a href="checkout.php" class="checkout-btn">
          <i class="bi bi-bag-check"></i>
          <span>Checkout</span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Cart Drawer -->
<div id="cartDrawer" class="cart-drawer">
  <div class="cart-drawer-header">
    <h5 class="mb-0"><i class="bi bi-cart3"></i> Keranjang Belanja</h5>
    <button id="closeCart" class="btn-close"></button>
  </div>
  
  <div class="cart-drawer-body">
    <div id="cartItems"></div>
  </div>
  
  <div class="cart-drawer-footer">
    <div class="d-flex justify-content-between mb-3">
      <strong>Total:</strong>
      <strong class="text-success fs-5" id="cartTotal">Rp 0</strong>
    </div>
    <div class="d-grid gap-2">
      <button class="btn btn-success" id="goCheckout">
        <i class="bi bi-bag-check"></i> Checkout
      </button>
      <button class="btn btn-outline-danger btn-sm" id="clearCart">
        <i class="bi bi-trash"></i> Kosongkan Keranjang
      </button>
    </div>
  </div>
</div>

<!-- Backdrop -->
<div id="backdrop" class="cart-backdrop" hidden></div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle"></i> Produk ditambahkan ke keranjang!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<style>
/* Navigation Actions Styling */
.nav-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cart-btn {
  width: 50px;
  height: 50px;
  border: none;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 22px;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
  position: relative;
}

.cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
}

.cart-btn:active {
  transform: scale(0.95);
}

.cart-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  border: 3px solid white;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

.checkout-btn {
  height: 50px;
  padding: 0 28px;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  border: none;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 15px;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.checkout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
  background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color: white;
  text-decoration: none;
}

.checkout-btn:active {
  transform: scale(0.98);
}

.checkout-btn i {
  font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-actions {
    gap: 8px;
  }
  
  .cart-btn {
    width: 44px;
    height: 44px;
    font-size: 18px;
  }
  
  .checkout-btn {
    height: 44px;
    padding: 0 20px;
    font-size: 14px;
    gap: 6px;
  }
  
  .checkout-btn i {
    font-size: 16px;
  }
}

@media (max-width: 576px) {
  .nav-actions {
    gap: 6px;
  }
  
  .checkout-btn span {
    display: none;
  }
  
  .checkout-btn {
    width: 44px;
    height: 44px;
    padding: 0;
    justify-content: center;
  }
}

/* Cart Drawer Styles */
.cart-drawer {
  position: fixed;
  right: -400px;
  top: 0;
  width: 400px;
  height: 100vh;
  background: white;
  box-shadow: -2px 0 8px rgba(0,0,0,0.1);
  transition: right 0.3s ease;
  z-index: 1050;
  display: flex;
  flex-direction: column;
}

.cart-drawer.open {
  right: 0;
}

.cart-drawer-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-drawer-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.cart-drawer-footer {
  padding: 1.5rem;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
}

.cart-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 1040;
}

.cart-item {
  padding: 1rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: #f8f9fa;
}

.cart-item img {
  border-radius: 4px;
  object-fit: cover;
}

@media (max-width: 576px) {
  .cart-drawer {
    width: 100%;
    right: -100%;
  }
}
</style>