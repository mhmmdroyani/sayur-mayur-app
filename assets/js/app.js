/*************************************************
 * KONFIGURASI
 *************************************************/
const CART_KEY = "sayur_mayur.cart";
const WISHLIST_KEY = "sayur_mayur.wishlist";

/*************************************************
 * UTIL
 *************************************************/
function formatRp(n) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(n);
}

function showToast(message, type = 'success') {
  const toastEl = document.getElementById('cartToast');
  if (!toastEl) return;
  
  const toastBody = toastEl.querySelector('.toast-body');
  toastBody.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
  
  toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;
  
  const toast = new bootstrap.Toast(toastEl);
  toast.show();
}

// Expose immediately
if (typeof window !== 'undefined') {
  window.showToast = showToast;
}

/*************************************************
 * CART (LOCALSTORAGE)
 *************************************************/
function readCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  } catch {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}

function addToCart(product) {
  let cart = readCart();
  const idx = cart.findIndex((item) => item.id === product.id);

  if (idx !== -1) {
    cart[idx].qty += 1;
    showToast(`${product.name} ditambahkan ke keranjang (${cart[idx].qty}x)`, 'success');
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      image: product.image,
      qty: 1,
    });
    showToast(`${product.name} ditambahkan ke keranjang`, 'success');
  }

  saveCart(cart);
  renderCartDrawer();
  
  // Auto open cart drawer (optional)
  setTimeout(() => openCart(), 300);
}

function removeFromCart(id) {
  let cart = readCart().filter((item) => item.id !== id);
  saveCart(cart);
  renderCartDrawer();
  showToast('Produk dihapus dari keranjang', 'warning');
}

function changeQty(id, delta) {
  let cart = readCart();
  const idx = cart.findIndex((item) => item.id === id);
  if (idx === -1) return;

  cart[idx].qty += delta;
  if (cart[idx].qty <= 0) {
    cart.splice(idx, 1);
    showToast('Produk dihapus dari keranjang', 'warning');
  }

  saveCart(cart);
  renderCartDrawer();
}

function clearCart() {
  if (confirm('Yakin ingin mengosongkan keranjang?')) {
    saveCart([]);
    renderCartDrawer();
    showToast('Keranjang dikosongkan', 'info');
  }
}

/*************************************************
 * CART BADGE
 *************************************************/
function updateCartBadge() {
  const badge = document.getElementById("cartCount");
  if (!badge) return;

  const totalQty = readCart().reduce((sum, item) => sum + item.qty, 0);
  badge.textContent = totalQty;
  
  if (totalQty > 0) {
    badge.classList.add('pulse-animation');
  } else {
    badge.classList.remove('pulse-animation');
  }
}

/*************************************************
 * WISHLIST BADGE
 *************************************************/
function updateWishlistBadge() {
  const badge = document.getElementById("wishlistCount");
  if (!badge) return;

  try {
    const wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY) || '[]');
    const count = Array.isArray(wishlist) ? wishlist.length : 0;
    
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  } catch (error) {
    console.error('Error updating wishlist badge:', error);
    badge.style.display = 'none';
  }
}

// Expose immediately
if (typeof window !== 'undefined') {
  window.updateWishlistBadge = updateWishlistBadge;
}

/*************************************************
 * WISHLIST TOGGLE (from product list)
 *************************************************/
function toggleProductWishlist(btn, productId, productName, productPrice, productImage, productStock) {
  console.log('toggleProductWishlist called', {productId, productName, productPrice});
  
  const iconEl = btn.querySelector('i');
  
  // Ensure productId is a number
  productId = parseInt(productId);
  productPrice = parseInt(productPrice);
  productStock = parseInt(productStock);
  
  // Decode HTML entities if any
  productName = String(productName);
  productImage = String(productImage);
  
  try {
    let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY) || '[]');
    
    // Ensure it's an array
    if (!Array.isArray(wishlist)) {
      wishlist = [];
    }
    
    const index = wishlist.findIndex(item => parseInt(item.id) === productId);
    
    if (index > -1) {
      // Remove from wishlist
      wishlist.splice(index, 1);
      iconEl.className = 'bi bi-heart';
      btn.style.background = 'rgba(255,255,255,0.9)';
      btn.style.color = '#ef4444';
      showToast('Produk dihapus dari wishlist', 'info');
      console.log('Removed from wishlist:', productId);
    } else {
      // Add to wishlist
      wishlist.push({
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage,
        stock: productStock
      });
      iconEl.className = 'bi bi-heart-fill';
      btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
      btn.style.color = 'white';
      showToast('Produk disimpan ke wishlist!', 'success');
      console.log('Added to wishlist:', productId);
    }
    
    localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist));
    console.log('Wishlist saved:', wishlist);
    updateWishlistBadge();
  } catch (error) {
    console.error('Error toggling wishlist:', error);
    showToast('Terjadi kesalahan saat menyimpan wishlist', 'danger');
  }
}

// Expose immediately for inline onclick
if (typeof window !== 'undefined') {
  window.toggleProductWishlist = toggleProductWishlist;
  console.log('toggleProductWishlist exposed to window');
}

/*************************************************
 * CHECK WISHLIST STATUS ON LOAD
 *************************************************/
function checkWishlistStatusInProducts() {
  try {
    const wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY) || '[]');
    
    if (!Array.isArray(wishlist)) {
      return;
    }
    
    // Update all wishlist buttons in product list
    document.querySelectorAll('[id^="heart-icon-"]').forEach(iconEl => {
      const btn = iconEl.closest('.btn-wishlist');
      if (!btn) return;
      
      const productIdStr = iconEl.id.replace('heart-icon-', '');
      const productId = parseInt(productIdStr);
      
      const isInWishlist = wishlist.some(item => parseInt(item.id) === productId);
      
      if (isInWishlist) {
        iconEl.className = 'bi bi-heart-fill';
        btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
        btn.style.color = 'white';
      }
    });
  } catch (error) {
    console.error('Error checking wishlist status:', error);
  }
}

/*************************************************
 * CART DRAWER
 *************************************************/
function renderCartDrawer() {
  const wrap = document.getElementById("cartItems");
  const totalEl = document.getElementById("cartTotal");

  if (!wrap || !totalEl) return;

  const cart = readCart();

  if (cart.length === 0) {
    wrap.innerHTML = `
      <div class="text-center text-muted py-4">
        <i class="bi bi-cart-x fs-1"></i>
        <p class="mt-2">Keranjang masih kosong</p>
      </div>`;
    totalEl.textContent = "Rp 0";
    return;
  }

  let total = 0;

  wrap.innerHTML = cart
    .map((item) => {
      const subtotal = item.price * item.qty;
      total += subtotal;

      return `
        <div class="cart-item">
          <div class="d-flex align-items-start">
            <img src="${item.image}" width="60" height="60" class="me-3 rounded" style="object-fit: cover;">
            <div class="flex-grow-1">
              <h6 class="mb-1">${item.name}</h6>
              <p class="text-muted small mb-2">${formatRp(item.price)}</p>
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary" onclick="changeQty(${item.id}, -1)">
                  <i class="bi bi-dash"></i>
                </button>
                <button class="btn btn-outline-secondary" disabled>
                  ${item.qty}
                </button>
                <button class="btn btn-outline-secondary" onclick="changeQty(${item.id}, 1)">
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>
            <div class="text-end">
              <p class="fw-bold mb-2">${formatRp(subtotal)}</p>
              <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    })
    .join("");

  totalEl.textContent = formatRp(total);
}

function openCart() {
  const drawer = document.getElementById("cartDrawer");
  const backdrop = document.getElementById("backdrop");
  updateWishlistBadge();
  if (!drawer || !backdrop) return;

  drawer.classList.add("open");
  backdrop.hidden = false;
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  const drawer = document.getElementById("cartDrawer");
  const backdrop = document.getElementById("backdrop");
  if (!drawer || !backdrop) return;

  drawer.classList.remove("open");
  backdrop.hidden = true;
  document.body.style.overflow = '';
}

/*************************************************
 * INIT
 *************************************************/
document.addEventListener("DOMContentLoaded", () => {
  updateCartBadge();
  updateWishlistBadge();
  checkWishlistStatusInProducts();
  renderCartDrawer();

  // Event listeners
  document.getElementById("openCart")?.addEventListener("click", openCart);
  document.getElementById("closeCart")?.addEventListener("click", closeCart);
  document.getElementById("backdrop")?.addEventListener("click", closeCart);
  document.getElementById("clearCart")?.addEventListener("click", clearCart);
  document.getElementById("goCheckout")?.addEventListener("click", () => {
    const cart = readCart();
    if (cart.length === 0) {
      showToast('Keranjang masih kosong', 'warning');
      return;
    }
    window.location.href = "checkout.php";
  });
  
  // Close cart with ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeCart();
    }
  });
});

/*************************************************
 * EXPOSE GLOBAL
 *************************************************/
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.changeQty = changeQty;
window.clearCart = clearCart;
window.updateWishlistBadge = updateWishlistBadge;
window.showToast = showToast;
window.toggleProductWishlist = toggleProductWishlist;
