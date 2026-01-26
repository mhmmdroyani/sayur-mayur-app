<?php 
session_start();
include 'config/functions.php';
include 'includes/header.php'; 
?>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-10 mx-auto">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold mb-2">
            <i class="bi bi-heart-fill text-danger"></i> Wishlist Saya
          </h2>
          <p class="text-muted mb-0">Produk yang Anda simpan untuk dibeli nanti</p>
        </div>
        <button class="btn btn-outline-danger" onclick="clearWishlist()">
          <i class="bi bi-trash"></i> Hapus Semua
        </button>
      </div>

      <div id="wishlistContainer">
        <!-- Wishlist items will be loaded here by JavaScript -->
      </div>
    </div>
  </div>
</div>

<script>
// Load wishlist items
function loadWishlist() {
  const container = document.getElementById('wishlistContainer');
  const wishlist = JSON.parse(localStorage.getItem('sayur_mayur.wishlist') || '[]');
  
  if (wishlist.length === 0) {
    container.innerHTML = `
      <div class="text-center py-5">
        <div style="font-size: 5rem; color: #d1d5db; margin-bottom: 1rem;">
          <i class="bi bi-heart"></i>
        </div>
        <h4 class="text-muted mb-3">Wishlist Anda Kosong</h4>
        <p class="text-muted mb-4">Belum ada produk yang disimpan. Mulai belanja sekarang!</p>
        <a href="products.php" class="btn btn-success">
          <i class="bi bi-shop"></i> Lihat Produk
        </a>
      </div>
    `;
    return;
  }
  
  let html = '<div class="row g-4">';
  
  wishlist.forEach((item, index) => {
    const stockBadge = item.stock > 0 
      ? `<span class="badge bg-success">Stok: ${item.stock}</span>`
      : `<span class="badge bg-danger">Stok Habis</span>`;
    
    html += `
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 position-relative">
          <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" 
                  onclick="removeFromWishlist(${item.id})" 
                  style="z-index: 10; border-radius: 50%; width: 36px; height: 36px; padding: 0;">
            <i class="bi bi-x-lg"></i>
          </button>
          
          <img src="${item.image}" 
               class="card-img-top" 
               alt="${item.name}"
               style="height: 200px; object-fit: cover;">
          
          <div class="card-body d-flex flex-column">
            <h6 class="card-title fw-bold mb-2">${item.name}</h6>
            <p class="text-success fw-bold fs-5 mb-2">
              Rp ${item.price.toLocaleString('id-ID')}
            </p>
            <div class="mb-3">${stockBadge}</div>
            
            <div class="mt-auto d-grid gap-2">
              <a href="product_detail.php?id=${item.id}" class="btn btn-outline-success">
                <i class="bi bi-eye"></i> Lihat Detail
              </a>
              ${item.stock > 0 
                ? `<button class="btn btn-success" onclick="addToCartFromWishlist(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}')">
                     <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                   </button>`
                : `<button class="btn btn-secondary" disabled>
                     <i class="bi bi-x-circle"></i> Stok Habis
                   </button>`
              }
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  
  // Add summary
  html += `
    <div class="alert alert-info mt-4 d-flex justify-content-between align-items-center">
      <div>
        <i class="bi bi-info-circle"></i>
        <strong>${wishlist.length}</strong> produk di wishlist Anda
      </div>
      <a href="products.php" class="btn btn-sm btn-success">
        <i class="bi bi-plus-circle"></i> Tambah Produk
      </a>
    </div>
  `;
  
  container.innerHTML = html;
}

// Remove single item from wishlist
function removeFromWishlist(productId) {
  let wishlist = JSON.parse(localStorage.getItem('sayur_mayur.wishlist') || '[]');
  wishlist = wishlist.filter(item => item.id !== productId);
  localStorage.setItem('sayur_mayur.wishlist', JSON.stringify(wishlist));
  
  showToast('Produk dihapus dari wishlist', 'info');
  loadWishlist();
  
  // Update badge counter
  if (typeof updateWishlistBadge === 'function') {
    updateWishlistBadge();
  }
}

// Clear all wishlist
function clearWishlist() {
  if (!confirm('Yakin ingin menghapus semua produk dari wishlist?')) {
    return;
  }
  
  localStorage.setItem('sayur_mayur.wishlist', JSON.stringify([]));
  showToast('Semua produk berhasil dihapus dari wishlist', 'success');
  loadWishlist();
  
  // Update badge counter
  if (typeof updateWishlistBadge === 'function') {
    updateWishlistBadge();
  }
}

// Add to cart from wishlist
function addToCartFromWishlist(id, name, price, image) {
  addToCart({
    id: id,
    name: name,
    price: price,
    image: image,
    qty: 1
  });
}

// Load wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
  loadWishlist();
});
</script>

<style>
.card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
}

.btn-outline-success:hover {
  background: #22c55e;
  border-color: #22c55e;
  color: white;
}
</style>

<?php include 'includes/footer.php'; ?>
