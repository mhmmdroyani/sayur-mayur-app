<?php 
session_start();
include 'config/koneksi.php';
include 'config/functions.php';
include 'includes/header.php'; 

// Ambil produk terbaru
$query = mysqli_query($conn, "SELECT * FROM produk WHERE stock > 0 ORDER BY id DESC LIMIT 8");
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1 class="display-4 fw-bold mb-3">Sayur Segar Setiap Hari</h1>
        <p class="lead mb-4">Dapatkan sayuran segar berkualitas langsung dari kebun ke meja Anda. Belanja mudah, cepat, dan terpercaya.</p>
        <div class="d-flex gap-3">
          <a href="products.php" class="btn btn-success btn-lg">
            <i class="bi bi-shop"></i> Belanja Sekarang
          </a>
          <a href="#featured" class="btn btn-outline-success btn-lg">
            <i class="bi bi-stars"></i> Produk Pilihan
          </a>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600" 
             alt="Sayur Segar" class="img-fluid rounded-4 shadow-lg" 
             style="max-height: 400px; object-fit: cover;">
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-md-4">
        <div class="feature-box p-4">
          <div class="icon-box mb-3">
            <i class="bi bi-truck fs-1 text-success"></i>
          </div>
          <h5 class="fw-bold">Pengiriman Cepat</h5>
          <p class="text-muted">Pesanan Anda akan dikirim dengan cepat dan aman</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-box p-4">
          <div class="icon-box mb-3">
            <i class="bi bi-shield-check fs-1 text-success"></i>
          </div>
          <h5 class="fw-bold">Produk Berkualitas</h5>
          <p class="text-muted">100% sayuran segar dan berkualitas terjamin</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-box p-4">
          <div class="icon-box mb-3">
            <i class="bi bi-credit-card fs-1 text-success"></i>
          </div>
          <h5 class="fw-bold">Pembayaran Mudah</h5>
          <p class="text-muted">Berbagai metode pembayaran yang aman</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section id="featured" class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Produk Pilihan Kami</h2>
      <p class="text-muted">Sayuran segar dengan kualitas terbaik</p>
    </div>
    
    <div class="row g-4">
      <?php while ($product = mysqli_fetch_assoc($query)): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="product-card card h-100 border-0 shadow-sm">
            <div class="product-image position-relative">
              <img src="assets/img/<?= sanitize($product['gambar']); ?>" 
                   class="card-img-top" 
                   alt="<?= sanitize($product['nama']); ?>"
                   style="height: 200px; object-fit: cover;">
              <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                <span class="badge bg-warning position-absolute top-0 end-0 m-2">Stok Terbatas</span>
              <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column">
              <h6 class="card-title fw-bold"><?= sanitize($product['nama']); ?></h6>
              <p class="text-success fw-bold fs-5 mb-2"><?= formatRupiah($product['harga']); ?></p>
              <div class="text-muted small mb-3">
                <i class="bi bi-box-seam"></i> Stok: <?= $product['stock']; ?>
              </div>
              <div class="mt-auto d-grid gap-2">
                <a href="product_detail.php?id=<?= $product['id']; ?>" class="btn btn-outline-success btn-sm">
                  <i class="bi bi-eye"></i> Detail
                </a>
                <button class="btn btn-success btn-sm" onclick="addToCart({
                  id: <?= $product['id']; ?>,
                  name: '<?= sanitize($product['nama']); ?>',
                  price: <?= $product['harga']; ?>,
                  image: 'assets/img/<?= sanitize($product['gambar']); ?>'
                })">
                  <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <div class="text-center mt-5">
      <a href="products.php" class="btn btn-success btn-lg">
        Lihat Semua Produk <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-success text-white">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Mulai Belanja Sayur Segar Hari Ini!</h2>
    <p class="lead mb-4">Daftar sekarang dan dapatkan penawaran spesial untuk pelanggan baru</p>
    <a href="products.php" class="btn btn-light btn-lg">Belanja Sekarang</a>
  </div>
</section>

<style>
.hero-section {
  padding: 80px 0;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.feature-box {
  transition: transform 0.3s ease;
}

.feature-box:hover {
  transform: translateY(-10px);
}

.product-card {
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}

.cta-section {
  background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}
</style>

<?php include 'includes/footer.php'; ?>
