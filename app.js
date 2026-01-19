// ====== KONFIGURASI & DATA DASAR ======
const PRODUCTS_KEY = "tokoku.products";
const CART_KEY = "tokoku.cart";
const LAST_ORDER_KEY = "tokoku.lastOrder";
const ADMIN_LOGIN_KEY = "tokoku.adminLogged";

// Produk default (jika localStorage masih kosong)
const DEFAULT_PRODUCTS = [
  { id: 1, name: "Kaos Polos", price: 75000, img: "img/produk1.png", category: "Fashion", stock: 10, description: "Kaos polos berkualitas premium dengan bahan katun 100% yang nyaman dipakai sepanjang hari." },
  { id: 2, name: "Topi Kain", price: 50000, img: "img/produk2.png", category: "Aksesoris", stock: 5, description: "Topi kain yang stylish dan fungsional, cocok untuk melindungi dari sinar matahari." },
  { id: 3, name: "Totebag", price: 45000, img: "img/produk3.png", category: "Tas", stock: 8, description: "Tas tote yang praktis dan ramah lingkungan, sempurna untuk belanja atau bekerja." },
  { id: 4, name: "Gelas Mug", price: 35000, img: "img/produk4.png", category: "Perlengkapan", stock: 12, description: "Gelas mug premium dengan desain eksklusif, ideal untuk minuman panas atau dingin." },
  { id: 5, name: "Celana Jeans", price: 95000, img: "img/produk5.png", category: "Fashion", stock: 7, description: "Celana jeans berkualitas tinggi dengan potongan modern dan nyaman untuk sehari-hari." },
  { id: 6, name: "Sneaker Casual", price: 120000, img: "img/produk6.png", category: "Sepatu", stock: 6, description: "Sepatu sneaker casual dengan desain minimalis yang cocok untuk berbagai gaya fashion." },
  { id: 7, name: "Dompet Kulit", price: 85000, img: "img/produk7.png", category: "Aksesoris", stock: 9, description: "Dompet kulit asli dengan desain elegan, tahan lama dan fungsional untuk penyimpanan kartu dan uang." },
];

let PRODUCTS = [];

// Utilitas singkat
const $ = (sel) => document.querySelector(sel);
const formatRp = (n) =>
  new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);

// ====== PRODUK & LOCALSTORAGE ======
function loadProducts() {
  try {
    const raw = localStorage.getItem(PRODUCTS_KEY);
    if (!raw) {
      PRODUCTS = [...DEFAULT_PRODUCTS];
      saveProducts();
    } else {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length) {
        PRODUCTS = parsed;
      } else {
        PRODUCTS = [...DEFAULT_PRODUCTS];
        saveProducts();
      }
    }
  } catch {
    PRODUCTS = [...DEFAULT_PRODUCTS];
    saveProducts();
  }
}

function saveProducts() {
  localStorage.setItem(PRODUCTS_KEY, JSON.stringify(PRODUCTS));
}

// ====== RENDER PRODUK ======
function renderProducts(list = PRODUCTS) {
  const grid = document.getElementById("productGrid");
  if (!grid) return;

  if (!list.length) {
    grid.innerHTML = "<p>Tidak ada produk.</p>";
    return;
  }

  grid.innerHTML = list
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
          <button class="btn add-to-cart" data-id="${p.id}" title="Tambah ke keranjang" ${p.stock <= 0 ? "disabled" : ""}>
            <span class="icon">${p.stock <= 0 ? '❌' : '🛒'}</span>
            <span class="label">${p.stock <= 0 ? 'Stok Habis' : '+ Keranjang'}</span>
          </button>
        </div>
      </div>
    </article>
  `
    )
    .join("");

  grid.querySelectorAll(".add-to-cart").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = Number(btn.dataset.id);
      addToCart(id);
    });
  });

  // Detail button navigation
  grid.querySelectorAll(".btn-detail").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = Number(btn.dataset.id);
      window.location.href = `product_detail.html?id=${id}`;
    });
  });
}

// Pencarian produk di halaman products
function setupSearch() {
  const input = document.getElementById("searchBox");
  if (!input) return;

  input.addEventListener("input", () => {
    const keyword = input.value.toLowerCase();
    const filtered = PRODUCTS.filter(
      (p) =>
        p.name.toLowerCase().includes(keyword) ||
        p.category.toLowerCase().includes(keyword)
    );
    renderProducts(filtered);
  });
}

// ====== KERANJANG (LOCALSTORAGE) ======
function readCart() {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY)) || [];
  } catch {
    return [];
  }
}
function saveCart(items) {
  localStorage.setItem(CART_KEY, JSON.stringify(items));
  updateBadge();
}

function addToCart(id) {
  const items = readCart();
  const idx = items.findIndex((it) => it.id === id);
  const prod = PRODUCTS.find((p) => p.id === id);
  if (!prod) return;

  if (idx > -1) {
    items[idx].qty += 1;
  } else {
    items.push({
      id: prod.id,
      name: prod.name,
      price: prod.price,
      img: prod.img,
      qty: 1,
    });
  }
  saveCart(items);
  renderCart();
  openCart();
}

function updateBadge() {
  const badge = document.getElementById("cartCount");
  if (!badge) return;
  const totalQty = readCart().reduce((acc, it) => acc + it.qty, 0);
  badge.textContent = totalQty;
}

function changeQty(id, delta) {
  const items = readCart();
  const idx = items.findIndex((it) => it.id === id);
  if (idx === -1) return;
  items[idx].qty += delta;
  if (items[idx].qty <= 0) items.splice(idx, 1);
  saveCart(items);
  renderCart();
}

function removeFromCart(id) {
  const items = readCart().filter((it) => it.id !== id);
  saveCart(items);
  renderCart();
}

function clearCart() {
  saveCart([]);
  renderCart();
}

function renderCart() {
  const wrap = document.getElementById("cartItems");
  const totalEl = document.getElementById("cartTotal");
  if (!wrap || !totalEl) return;

  const items = readCart();
  if (!items.length) {
    wrap.innerHTML = "<p>Keranjang masih kosong.</p>";
    totalEl.textContent = "Rp 0";
    return;
  }

  wrap.innerHTML = items
    .map(
      (it) => `
      <div class="cart-item">
        <img src="${it.img}" alt="${it.name}" />
        <div>
          <div><strong>${it.name}</strong></div>
          <div class="small">${formatRp(it.price)} / item</div>
          <div class="qty-row">
            <button onclick="changeQty(${it.id}, -1)">-</button>
            <span>${it.qty}</span>
            <button onclick="changeQty(${it.id}, 1)">+</button>
            <button style="margin-left:8px" onclick="removeFromCart(${it.id})">Hapus</button>
          </div>
        </div>
        <div><strong>${formatRp(it.price * it.qty)}</strong></div>
      </div>
    `
    )
    .join("");

  const total = items.reduce((sum, it) => sum + it.price * it.qty, 0);
  totalEl.textContent = formatRp(total);
}

// ====== CART DRAWER UI ======
function openCart() {
  const drawer = document.getElementById("cartDrawer");
  const backdrop = document.getElementById("backdrop");
  if (!drawer || !backdrop) return;
  drawer.classList.add("open");
  drawer.setAttribute("aria-hidden", "false");
  backdrop.hidden = false;
}
function closeCart() {
  const drawer = document.getElementById("cartDrawer");
  const backdrop = document.getElementById("backdrop");
  if (!drawer || !backdrop) return;
  drawer.classList.remove("open");
  drawer.setAttribute("aria-hidden", "true");
  backdrop.hidden = true;
}

// ====== CHECKOUT PAGE LOGIC ======
function renderCheckoutPage() {
  const listEl = document.getElementById("checkoutItems");
  const subtotalEl = document.getElementById("checkoutSubtotal");
  const shippingEl = document.getElementById("checkoutShipping");
  const totalEl = document.getElementById("checkoutTotal");
  const form = document.getElementById("checkoutForm");
  const metodeKirim = document.getElementById("metodeKirim");
  const metodeBayar = document.getElementById("metodeBayar");
  const successMessage = document.getElementById("successMessage");

  if (!listEl || !subtotalEl || !shippingEl || !totalEl || !form || !metodeKirim) return;

  function updateTotals() {
    const items = readCart();
    if (!items.length) {
      listEl.innerHTML = "<p>Keranjang kosong. Silakan kembali ke halaman produk.</p>";
      subtotalEl.textContent = "Rp 0";
      shippingEl.textContent = "Rp 0";
      totalEl.textContent = "Rp 0";
      return;
    }

    listEl.innerHTML = items
      .map(
        (it) => `
      <div class="checkout-row">
        <span>${it.name} × ${it.qty}</span>
        <span>${formatRp(it.price * it.qty)}</span>
      </div>
    `
      )
      .join("");

    const subtotal = items.reduce((sum, it) => sum + it.price * it.qty, 0);
    const shipping = Number(metodeKirim.value || 0);
    const total = subtotal + shipping;
    subtotalEl.textContent = formatRp(subtotal);
    shippingEl.textContent = formatRp(shipping);
    totalEl.textContent = formatRp(total);
  }

  metodeKirim.addEventListener("change", updateTotals);
  updateTotals();

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const items = readCart();
    if (!items.length) return;

    const order = {
      id: "INV-" + Date.now(),
      date: new Date().toLocaleString("id-ID"),
      customer: {
        nama: document.getElementById("nama").value,
        alamat: document.getElementById("alamat").value,
        kota: document.getElementById("kota").value,
        hp: document.getElementById("hp").value,
      },
      shippingMethod: metodeKirim.options[metodeKirim.selectedIndex].text,
      shippingCost: Number(metodeKirim.value || 0),
      paymentMethod: metodeBayar ? metodeBayar.value : "",
      items: items.map((it) => ({
        name: it.name,
        qty: it.qty,
        price: it.price,
        subtotal: it.price * it.qty,
      })),
    };

    order.subtotal = order.items.reduce((sum, it) => sum + it.subtotal, 0);
    order.total = order.subtotal + order.shippingCost;

    localStorage.setItem(LAST_ORDER_KEY, JSON.stringify(order));
    clearCart();
    if (successMessage) successMessage.hidden = false;

    // Redirect ke invoice
    window.location.href = "invoice.html";
  });
}

// ====== INVOICE PAGE LOGIC ======
function renderInvoicePage() {
  const wrap = document.getElementById("invoiceContent");
  if (!wrap) return;

  const raw = localStorage.getItem(LAST_ORDER_KEY);
  if (!raw) {
    wrap.innerHTML = "<p>Belum ada pesanan yang bisa ditampilkan.</p>";
    return;
  }

  const order = JSON.parse(raw);

  const itemsHtml = order.items
    .map(
      (it) => `
    <div class="invoice-row">
      <span>${it.name} × ${it.qty}</span>
      <span>${formatRp(it.subtotal)}</span>
    </div>
  `
    )
    .join("");

  wrap.innerHTML = `
    <div class="invoice-header">
      <div>
        <h3>TokoKu</h3>
        <div class="small muted">Invoice Pesanan</div>
      </div>
      <div class="small">
        <div><strong>${order.id}</strong></div>
        <div>${order.date}</div>
      </div>
    </div>

    <div class="small" style="margin-bottom:8px;">
      <div><strong>Nama:</strong> ${order.customer.nama}</div>
      <div><strong>Alamat:</strong> ${order.customer.alamat}, ${order.customer.kota}</div>
      <div><strong>No. HP:</strong> ${order.customer.hp}</div>
      <div><strong>Pengiriman:</strong> ${order.shippingMethod}</div>
      <div><strong>Pembayaran:</strong> ${order.paymentMethod}</div>
    </div>

    <div class="invoice-items">
      ${itemsHtml}
    </div>

    <div class="invoice-footer">
      <div class="invoice-row">
        <span>Subtotal</span>
        <span>${formatRp(order.subtotal)}</span>
      </div>
      <div class="invoice-row">
        <span>Ongkos Kirim</span>
        <span>${formatRp(order.shippingCost)}</span>
      </div>
      <div class="invoice-row" style="border-top:1px solid #e0e0ea; padding-top:6px; margin-top:4px;">
        <strong>Total Bayar</strong>
        <strong>${formatRp(order.total)}</strong>
      </div>
    </div>
  `;
}

// ====== ADMIN PANEL ======
function setupAdminPage() {
  const loginSection = document.getElementById("adminLoginSection");
  const panelSection = document.getElementById("adminPanel");
  const loginForm = document.getElementById("adminLoginForm");
  const logoutBtn = document.getElementById("adminLogout");
  const productTableBody = document.querySelector("#productTable tbody");
  const productForm = document.getElementById("productForm");

  if (!loginSection || !panelSection || !loginForm || !productTableBody || !productForm) return;

  const logged = localStorage.getItem(ADMIN_LOGIN_KEY) === "true";

  function renderAdminTable() {
    productTableBody.innerHTML = PRODUCTS.map(
      (p) => `
      <tr>
        <td>${p.id}</td>
        <td>${p.name}</td>
        <td>${p.category}</td>
        <td>${formatRp(p.price)}</td>
        <td>${p.stock}</td>
        <td>
          <button type="button" onclick="editProduct(${p.id})">Edit</button>
          <button type="button" onclick="deleteProduct(${p.id})">Hapus</button>
        </td>
      </tr>
    `
    ).join("");
  }

  function showPanel() {
    loginSection.hidden = true;
    panelSection.hidden = false;
    renderAdminTable();
  }

  function showLogin() {
    loginSection.hidden = false;
    panelSection.hidden = true;
  }

  if (logged) {
    showPanel();
  } else {
    showLogin();
  }

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const user = document.getElementById("adminUser").value;
    const pass = document.getElementById("adminPass").value;
    // Kredensial contoh
    if (user === "admin" && pass === "admin123") {
      localStorage.setItem(ADMIN_LOGIN_KEY, "true");
      showPanel();
    } else {
      alert("Username atau password salah (coba admin / admin123).");
    }
  });

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      localStorage.removeItem(ADMIN_LOGIN_KEY);
      showLogin();
    });
  }

  productForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const idField = document.getElementById("prodId");
    const nameField = document.getElementById("prodName");
    const catField = document.getElementById("prodCat");
    const priceField = document.getElementById("prodPrice");
    const stockField = document.getElementById("prodStock");
    const imgField = document.getElementById("prodImg");
    const descField = document.getElementById("prodDesc");

    const idVal = idField.value ? Number(idField.value) : null;
    const data = {
      id: idVal || (PRODUCTS.length ? Math.max(...PRODUCTS.map((p) => p.id)) + 1 : 1),
      name: nameField.value,
      category: catField.value,
      price: Number(priceField.value),
      stock: Number(stockField.value),
      img: imgField.value || "img/produk1.png",
      description: descField.value || "Produk berkualitas tinggi.",
    };

    if (idVal) {
      const idx = PRODUCTS.findIndex((p) => p.id === idVal);
      if (idx > -1) {
        PRODUCTS[idx] = data;
      }
    } else {
      PRODUCTS.push(data);
    }

    saveProducts();
    renderAdminTable();
    renderProducts(); // update jika halaman punya grid
    productForm.reset();
    idField.value = "";
  });

  // expose function untuk tombol edit/hapus
  window.editProduct = function (id) {
    const p = PRODUCTS.find((x) => x.id === id);
    if (!p) return;
    document.getElementById("prodId").value = p.id;
    document.getElementById("prodName").value = p.name;
    document.getElementById("prodCat").value = p.category;
    document.getElementById("prodPrice").value = p.price;
    document.getElementById("prodStock").value = p.stock;
    document.getElementById("prodImg").value = p.img;
    document.getElementById("prodDesc").value = p.description || "";
  };

  window.deleteProduct = function (id) {
    if (!confirm("Yakin ingin menghapus produk ini?")) return;
    PRODUCTS = PRODUCTS.filter((p) => p.id !== id);
    saveProducts();
    renderAdminTable();
    renderProducts();
  };
}

// ====== NAV ACTIVE STATE ======
function highlightNav() {
  const page = document.body.getAttribute("data-page");
  document.querySelectorAll(".nav a[data-nav]").forEach((a) => {
    if (a.dataset.nav === page) a.classList.add("is-active");
    else a.classList.remove("is-active");
  });
}

// ====== MOBILE NAV (HAMBURGER) ======
function setupMobileNav() {
  const header = document.querySelector('header.site-header');
  if (!header) return;
  // avoid duplicate toggle
  if (header.querySelector('.mobile-nav-toggle')) return;

  const toggle = document.createElement('button');
  toggle.className = 'mobile-nav-toggle';
  toggle.setAttribute('aria-expanded', 'false');
  toggle.setAttribute('aria-label', 'Buka menu');
  toggle.innerHTML = '☰';

  const nav = header.querySelector('.nav');
  const headerInner = header.querySelector('.header-inner');
  const brand = headerInner ? headerInner.querySelector('.brand') : null;
  // insert toggle after brand (left side)
  if (brand && brand.nextSibling) {
    brand.parentNode.insertBefore(toggle, brand.nextSibling);
  } else if (headerInner) {
    headerInner.insertBefore(toggle, headerInner.children[1] || null);
  } else {
    header.appendChild(toggle);
  }

  function closeNav() {
    header.classList.remove('nav-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  function openNav() {
    header.classList.add('nav-open');
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', () => {
    if (header.classList.contains('nav-open')) closeNav();
    else openNav();
  });

  // close menu when clicking a nav link
  if (nav) {
    nav.addEventListener('click', (e) => {
      if (e.target && e.target.matches('a')) closeNav();
    });
  }

  // close when resizing up
  window.addEventListener('resize', () => {
    if (window.innerWidth > 800) closeNav();
  });
}

// ====== INISIALISASI SAAT LOAD ======
document.addEventListener("DOMContentLoaded", () => {
  // Tahun footer
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // Load produk dari localStorage
  loadProducts();

  // Nav active
  highlightNav();

  // Mobile nav toggle
  setupMobileNav();

  // Render produk jika ada grid di halaman
  renderProducts();
  setupSearch();

  // Render keranjang awal
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

  // Halaman checkout & invoice
  renderCheckoutPage();
  renderInvoicePage();

  // Admin
  setupAdminPage();
});

// Expose some functions globally
window.changeQty = changeQty;
window.removeFromCart = removeFromCart;
