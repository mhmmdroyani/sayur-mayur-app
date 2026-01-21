// ====== PRODUCT DETAIL PAGE ======

function renderProductDetail() {
  const detailWrap = document.getElementById("productDetail");
  if (!detailWrap) return;

  // Get product ID from URL query parameter
  const urlParams = new URLSearchParams(window.location.search);
  const productId = Number(urlParams.get("id"));

  if (!productId || !PRODUCTS.length) {
    detailWrap.innerHTML = '<p>Produk tidak ditemukan.</p>';
    return;
  }

  const product = PRODUCTS.find((p) => p.id === productId);

  if (!product) {
    detailWrap.innerHTML = '<p>Produk tidak ditemukan. <a href="products.html">Kembali ke produk</a></p>';
    return;
  }

  const inStock = product.stock > 0;

  detailWrap.innerHTML = `
    <div class="detail-container">
      <div class="detail-image">
        <img src="${product.img}" alt="${product.name}" />
      </div>
      <div class="detail-info">
        <h1>${product.name}</h1>
        <div class="detail-category">
          <span class="badge">${product.category}</span>
          <span class="stock ${inStock ? 'in-stock' : 'out-stock'}">
            ${inStock ? `Stok: ${product.stock}` : 'Stok Habis'}
          </span>
        </div>
        <div class="detail-price">${formatRp(product.price)}</div>
        <div class="detail-description">
          <h3>Deskripsi Produk</h3>
          <p>${product.description || 'Tidak ada deskripsi produk.'}</p>
        </div>
        <div class="detail-specs">
          <h3>Spesifikasi</h3>
          <div class="spec-row">
            <span>ID Produk:</span>
            <strong>#${product.id}</strong>
          </div>
          <div class="spec-row">
            <span>Kategori:</span>
            <strong>${product.category}</strong>
          </div>
          <div class="spec-row">
            <span>Ketersediaan Stok:</span>
            <strong>${product.stock} unit</strong>
          </div>
          <div class="spec-row">
            <span>Harga:</span>
            <strong>${formatRp(product.price)}</strong>
          </div>
        </div>
        <div class="detail-actions">
          <div class="qty-selector">
            <button class="qty-btn qty-down" id="qtyDown">−</button>
            <input type="number" id="qtyInput" class="qty-input" value="1" min="1" readonly />
            <button class="qty-btn qty-up" id="qtyUp">+</button>
          </div>
          <button class="btn add-to-cart-detail" data-id="${product.id}" ${!inStock ? "disabled" : ""}>
            ${inStock ? "🛒 Tambah ke Keranjang" : "Stok Habis"}
          </button>
          <a href="products.html" class="btn outline">Kembali ke Produk</a>
        </div>
      </div>
    </div>
  `;

  // Add to cart event listener with quantity
  const addBtn = detailWrap.querySelector(".add-to-cart-detail");
  const qtyInput = detailWrap.querySelector("#qtyInput");
  const qtyUpBtn = detailWrap.querySelector("#qtyUp");
  const qtyDownBtn = detailWrap.querySelector("#qtyDown");

  if (qtyUpBtn) {
    qtyUpBtn.addEventListener("click", () => {
      let qty = Number(qtyInput.value);
      if (qty < product.stock) {
        qtyInput.value = qty + 1;
      }
    });
  }

  if (qtyDownBtn) {
    qtyDownBtn.addEventListener("click", () => {
      let qty = Number(qtyInput.value);
      if (qty > 1) {
        qtyInput.value = qty - 1;
      }
    });
  }

  if (addBtn && inStock) {
    addBtn.addEventListener("click", () => {
      const qty = Number(qtyInput.value);
      for (let i = 0; i < qty; i++) {
        addToCart(productId);
      }
      openCart();
    });
  }
}

function renderRelatedProducts() {
  const relatedWrap = document.getElementById("relatedProducts");
  if (!relatedWrap) return;

  const urlParams = new URLSearchParams(window.location.search);
  const productId = Number(urlParams.get("id"));
  const currentProduct = PRODUCTS.find((p) => p.id === productId);

  if (!currentProduct) return;

  // Get products from same category, excluding current product
  const related = PRODUCTS.filter(
    (p) => p.category === currentProduct.category && p.id !== productId
  ).slice(0, 4);

  if (!related.length) {
    relatedWrap.innerHTML = "<p>Tidak ada produk lain dalam kategori ini.</p>";
    return;
  }

  relatedWrap.innerHTML = related
    .map(
      (p) => `
    <article class="card">
      <img src="${p.img}" alt="${p.name}" />
      <div class="meta">
        <h4>${p.name}</h4>
        <div class="small">${p.category} • Stok: ${p.stock}</div>
        <div class="price">${formatRp(p.price)}</div>
        <p class="card-desc">${p.description || ""}</p>
        <div class="actions">
          <button class="btn-detail" data-id="${p.id}">Lihat Detail</button>
          <button class="btn add-to-cart" data-id="${p.id}" ${p.stock <= 0 ? "disabled" : ""}>
            <span class="icon">${p.stock <= 0 ? '❌' : '🛒'}</span>
            <span class="label">${p.stock <= 0 ? 'Stok Habis' : '+ Keranjang'}</span>
          </button>
        </div>
      </div>
    </article>
  `
    )
    .join("");

  // Add event listeners for related products
  relatedWrap.querySelectorAll(".add-to-cart").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = Number(btn.dataset.id);
      addToCart(id);
      renderCart();
      openCart();
    });
  });

  relatedWrap.querySelectorAll(".btn-detail").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = Number(btn.dataset.id);
      window.location.href = `product_detail.html?id=${id}`;
    });
  });
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  // Year in footer
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Load products from localStorage
  loadProducts();

  // Highlight nav
  highlightNav();

  // Render detail and related products
  renderProductDetail();
  renderRelatedProducts();

  // Render cart
  renderCart();
  updateBadge();

  // Cart drawer buttons
  const openBtn = document.getElementById("openCart");
  const closeBtn = document.getElementById("closeCart");
  const backdrop = document.getElementById("backdrop");
  const clearBtn = document.getElementById("clearCart");
  const goCheckoutBtn = document.getElementById("goCheckout");

  if (openBtn) openBtn.addEventListener("click", openCart);
  if (closeBtn) closeBtn.addEventListener("click", closeCart);
  if (backdrop) backdrop.addEventListener("click", closeCart);
  if (clearBtn) clearBtn.addEventListener("click", clearCart);
  if (goCheckoutBtn)
    goCheckoutBtn.addEventListener("click", () => {
      window.location.href = "checkout.html";
    });
});
