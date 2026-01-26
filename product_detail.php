<?php
session_start();
include 'config/koneksi.php';
include 'config/functions.php';
include 'includes/header.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
  echo "<div class='container py-5'><div class='alert alert-danger'>Produk tidak ditemukan</div></div>";
  include 'includes/footer.php';
  exit;
}

// Ambil detail produk
$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
  echo "<div class='container py-5'><div class='alert alert-danger'>Produk tidak ditemukan</div></div>";
  include 'includes/footer.php';
  exit;
}

// Ambil produk lainnya
$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id != ? LIMIT 4");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$related = mysqli_stmt_get_result($stmt);
?>

<div class="container py-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
      <li class="breadcrumb-item"><a href="products.php">Produk</a></li>
      <li class="breadcrumb-item active"><?= sanitize($produk['nama']); ?></li>
    </ol>
  </nav>

  <div class="row g-5">
    <!-- Product Image -->
    <div class="col-lg-6">
      <div class="position-relative">
        <img src="assets/img/<?= sanitize($produk['gambar']); ?>" 
             class="img-fluid rounded-4 shadow-sm w-100" 
             alt="<?= sanitize($produk['nama']); ?>"
             style="max-height: 500px; object-fit: cover;">
        <?php if ($produk['stock'] == 0): ?>
          <span class="badge bg-danger position-absolute top-0 end-0 m-3">Stok Habis</span>
        <?php elseif ($produk['stock'] < 10): ?>
          <span class="badge bg-warning position-absolute top-0 end-0 m-3">Stok Terbatas</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Product Info -->
    <div class="col-lg-6">
      <h1 class="fw-bold mb-2"><?= sanitize($produk['nama']); ?></h1>
      
      <div class="mb-3">
        <div class="rating">
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-fill text-warning"></i>
          <i class="bi bi-star-half text-warning"></i>
          <span class="text-muted">(15 ulasan)</span>
        </div>
      </div>

      <!-- Price -->
      <div class="mb-4">
        <p class="text-success fs-3 fw-bold"><?= formatRupiah($produk['harga']); ?></p>
        <p class="text-muted">
          <i class="bi bi-check-circle text-success"></i> Harga termurah
        </p>
      </div>

      <!-- Stock Info -->
      <div class="alert alert-info mb-4">
        <i class="bi bi-box-seam"></i>
        <strong>Stok Tersedia:</strong> <?= $produk['stock']; ?> unit
        <?php if ($produk['berat']): ?>
          | <strong>Berat:</strong> <?= $produk['berat']; ?>g
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div class="mb-4">
        <h5 class="fw-bold">Deskripsi Produk</h5>
        <p class="text-muted">
          <?= nl2br(sanitize($produk['deskripsi'])); ?>
        </p>
      </div>

      <!-- Quantity Selection -->
      <div class="mb-4">
        <label class="form-label fw-bold">Jumlah</label>
        <div class="quantity-selector">
          <button class="qty-btn qty-minus" type="button" id="decreaseBtn">
            <i class="bi bi-dash-lg"></i>
          </button>
          <input type="number" class="qty-input" id="quantityInput" value="1" min="1" max="<?= $produk['stock']; ?>" readonly>
          <button class="qty-btn qty-plus" type="button" id="increaseBtn">
            <i class="bi bi-plus-lg"></i>
          </button>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="product-actions mb-4">
        <?php if ($produk['stock'] > 0): ?>
          <button class="btn-primary-large" id="addToCartBtn">
            <i class="bi bi-cart-plus"></i>
            <span>Tambah ke Keranjang</span>
          </button>
          <button class="btn-secondary-large" onclick="toggleWishlist()">
            <i class="bi bi-heart"></i>
            <span>Simpan</span>
          </button>
        <?php else: ?>
          <button class="btn-disabled-large" disabled>
            <i class="bi bi-x-circle"></i>
            <span>Stok Habis</span>
          </button>
        <?php endif; ?>
      </div>

      <!-- Sharing & Contact -->
      <div class="product-footer-actions">
        <button class="btn-tertiary" onclick="shareProduct()">
          <i class="bi bi-share"></i> Bagikan
        </button>
        <a href="contact.php" class="btn-tertiary">
          <i class="bi bi-chat-dots"></i> Tanya
        </a>
      </div>
    </div>
  </div>

  <!-- Reviews Section -->
  <hr class="my-5">
  
  <div class="reviews-section">
    <h3 class="fw-bold mb-4" style="padding: 0 20px;">
      <i class="bi bi-chat-dots"></i> Ulasan Pelanggan
    </h3>

    <?php
    // Ambil rating info
    $rating_query = mysqli_query($conn, 
      "SELECT AVG(rating) as avg_rating, COUNT(*) as total 
       FROM review WHERE produk_id = " . $id);
    $rating_data = mysqli_fetch_assoc($rating_query);
    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
    $total_reviews = $rating_data['total'] ?? 0;
    ?>

    <!-- Rating Summary -->
    <div class="row mb-4" style="padding: 0 20px;">
      <div class="col-md-3">
        <div class="card border-0 bg-light">
          <div class="card-body text-center">
            <h2 class="card-title fw-bold text-warning"><?= $avg_rating; ?>/5</h2>
            <div class="mb-2">
              <?php for($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star<?= ($i <= round($avg_rating)) ? '-fill' : ''; ?> text-warning"></i>
              <?php endfor; ?>
            </div>
            <p class="text-muted mb-0"><?= $total_reviews; ?> ulasan</p>
          </div>
        </div>
      </div>

      <div class="col-md-9">
        <!-- Rating Breakdown -->
        <div class="rating-breakdown">
          <?php for($stars = 5; $stars >= 1; $stars--): ?>
            <?php
            $star_count = mysqli_fetch_assoc(mysqli_query($conn, 
              "SELECT COUNT(*) as count FROM review WHERE produk_id = " . $id . " AND rating = " . $stars));
            $count = $star_count['count'] ?? 0;
            $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
            ?>
            <div class="rating-row mb-2">
              <span class="text-muted" style="width: 40px;"><?= $stars; ?> ⭐</span>
              <div class="progress flex-grow-1" style="height: 8px;">
                <div class="progress-bar bg-warning" style="width: <?= $percentage; ?>%"></div>
              </div>
              <span class="text-muted ms-2" style="width: 50px; text-align: right;"><?= $count; ?></span>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <!-- Review Form -->
    <div class="card border-0 bg-light mb-4">
      <div class="card-body">
        <h5 class="card-title fw-bold mb-3">Tulis Ulasan Anda</h5>
        <form id="reviewForm">
          <input type="hidden" name="produk_id" value="<?= $id; ?>">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Anda</label>
            <input type="text" class="form-control" name="nama_reviewer" 
                   placeholder="Nama lengkap Anda" required minlength="3">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Rating</label>
            <div class="rating-input" id="ratingGroup">
              <?php for($i = 1; $i <= 5; $i++): ?>
                <input type="radio" class="btn-check" name="rating" id="rating<?= $i; ?>" 
                       value="<?= $i; ?>" required>
                <label class="btn btn-outline-warning rating-btn" for="rating<?= $i; ?>">
                  <span class="rating-stars"><?= str_repeat('⭐', $i); ?></span>
                </label>
              <?php endfor; ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Ulasan</label>
            <textarea class="form-control" name="komentar" rows="4" 
                      placeholder="Bagikan pengalaman Anda dengan produk ini... (min. 10 karakter)" 
                      required minlength="10" maxlength="500"></textarea>
            <small class="text-muted">Maksimal 500 karakter</small>
          </div>

          <button type="submit" class="btn btn-success">
            <i class="bi bi-send"></i> Kirim Ulasan
          </button>
        </form>
        <div id="reviewAlert" class="alert mt-3" style="display: none;"></div>
      </div>
    </div>

    <!-- Reviews List -->
    <div id="reviewsList">
      <?php
      $reviews = mysqli_query($conn, 
        "SELECT * FROM review WHERE produk_id = " . $id . " 
         ORDER BY created_at DESC LIMIT 20");
      
      if (mysqli_num_rows($reviews) > 0):
        while($review = mysqli_fetch_assoc($reviews)):
      ?>
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="card-title fw-bold mb-0"><?= htmlspecialchars($review['nama_reviewer']); ?></h6>
                <small class="text-muted">
                  <?= date('d M Y H:i', strtotime($review['created_at'])); ?>
                </small>
              </div>
              <div class="text-warning">
                <?php for($i = 0; $i < $review['rating']; $i++): ?>
                  <i class="bi bi-star-fill"></i>
                <?php endfor; ?>
              </div>
            </div>
            <p class="card-text"><?= nl2br(htmlspecialchars($review['komentar'])); ?></p>
          </div>
        </div>
      <?php 
        endwhile;
      else:
        echo '<p class="text-muted text-center py-4">Belum ada ulasan. Jadilah yang pertama!</p>';
      endif;
      ?>
    </div>
  </div>

  <!-- Related Products -->
  <hr class="my-5">
  
  <h3 class="fw-bold mb-4">Produk Lainnya</h3>
  <div class="row g-4">
    <?php while ($item = mysqli_fetch_assoc($related)): ?>
      <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="product-card card h-100 border-0 shadow-sm">
          <img src="assets/img/<?= sanitize($item['gambar']); ?>" 
               class="card-img-top" 
               alt="<?= sanitize($item['nama']); ?>"
               style="height: 200px; object-fit: cover;">
          <div class="card-body d-flex flex-column">
            <h6 class="card-title fw-bold text-truncate">
              <?= sanitize($item['nama']); ?>
            </h6>
            <p class="text-success fw-bold fs-5 mb-2">
              <?= formatRupiah($item['harga']); ?>
            </p>
            <div class="mt-auto d-grid gap-2">
              <a href="product_detail.php?id=<?= $item['id']; ?>" 
                 class="btn-view-detail">
                <i class="bi bi-eye"></i> Lihat
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const quantityInput = document.getElementById('quantityInput');
  const increaseBtn = document.getElementById('increaseBtn');
  const decreaseBtn = document.getElementById('decreaseBtn');
  const addToCartBtn = document.getElementById('addToCartBtn');
  const maxStock = parseInt(quantityInput.max);

  increaseBtn.addEventListener('click', function() {
    const current = parseInt(quantityInput.value);
    if (current < maxStock) {
      quantityInput.value = current + 1;
    }
  });

  decreaseBtn.addEventListener('click', function() {
    const current = parseInt(quantityInput.value);
    if (current > 1) {
      quantityInput.value = current - 1;
    }
  });

  addToCartBtn.addEventListener('click', function() {
    const quantity = parseInt(quantityInput.value);
    addToCart({
      id: <?= $produk['id']; ?>,
      name: '<?= addslashes(sanitize($produk['nama'])); ?>',
      price: <?= $produk['harga']; ?>,
      image: 'assets/img/<?= sanitize($produk['gambar']); ?>',
      qty: quantity
    });
  });
});

function shareProduct() {
  const productUrl = window.location.href;
  const productName = '<?= sanitize($produk['nama']); ?>';
  
  if (navigator.share) {
    navigator.share({
      title: productName,
      text: 'Lihat produk ini di SAYUR MAYUR',
      url: productUrl
    }).catch(err => console.log('Error:', err));
  } else {
    navigator.clipboard.writeText(productUrl);
    showToast('Link produk disalin ke clipboard!', 'success');
  }
}

// Wishlist functionality
function toggleWishlist() {
  const productId = <?= $produk['id']; ?>;
  const product = {
    id: productId,
    name: '<?= addslashes(sanitize($produk['nama'])); ?>',
    price: <?= $produk['harga']; ?>,
    image: 'assets/img/<?= sanitize($produk['gambar']); ?>',
    stock: <?= $produk['stock']; ?>
  };
  
  // Get wishlist from localStorage
  let wishlist = JSON.parse(localStorage.getItem('sayur_mayur.wishlist') || '[]');
  
  // Check if product already in wishlist
  const index = wishlist.findIndex(item => item.id === productId);
  
  if (index > -1) {
    // Remove from wishlist
    wishlist.splice(index, 1);
    localStorage.setItem('sayur_mayur.wishlist', JSON.stringify(wishlist));
    updateWishlistUI(false);
    showToast('Produk dihapus dari wishlist', 'info');
  } else {
    // Add to wishlist
    wishlist.push(product);
    localStorage.setItem('sayur_mayur.wishlist', JSON.stringify(wishlist));
    updateWishlistUI(true);
    showToast('Produk disimpan ke wishlist!', 'success');
  }
  
  // Update badge counter
  if (typeof updateWishlistBadge === 'function') {
    updateWishlistBadge();
  }
}

function updateWishlistUI(isInWishlist) {
  const btn = document.querySelector('.btn-secondary-large');
  if (!btn) return;
  
  const icon = btn.querySelector('i');
  const text = btn.querySelector('span');
  
  if (isInWishlist) {
    icon.className = 'bi bi-heart-fill';
    text.textContent = 'Tersimpan';
    btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
    btn.style.color = 'white';
  } else {
    icon.className = 'bi bi-heart';
    text.textContent = 'Simpan';
    btn.style.background = '';
    btn.style.color = '';
  }
}

// Check wishlist status on page load
function checkWishlistStatus() {
  const productId = <?= $produk['id']; ?>;
  const wishlist = JSON.parse(localStorage.getItem('sayur_mayur.wishlist') || '[]');
  const isInWishlist = wishlist.some(item => item.id === productId);
  
  if (isInWishlist) {
    updateWishlistUI(true);
  }
}

// Initialize wishlist status
document.addEventListener('DOMContentLoaded', function() {
  checkWishlistStatus();
});
</script>

<style>
/* Quantity Selector Styling */
.quantity-selector {
  display: flex;
  align-items: center;
  gap: 0;
  width: fit-content;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.qty-btn {
  width: 48px;
  height: 48px;
  border: none;
  background: #22c55e;
  color: white;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.qty-btn:hover {
  background: #16a34a;
  transform: scale(1.05);
}

.qty-btn:active {
  transform: scale(0.95);
}

.qty-btn.qty-minus {
  border-radius: 8px 0 0 8px;
}

.qty-btn.qty-plus {
  border-radius: 0 8px 8px 0;
}

.qty-input {
  width: 80px;
  height: 48px;
  border: none;
  text-align: center;
  font-size: 18px;
  font-weight: bold;
  background: white;
  color: #1e293b;
}

.qty-input:focus {
  outline: none;
  background: #f0fdf4;
}

/* Disable number spinner */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.qty-input[type=number] {
  -moz-appearance: textfield;
}

/* Review Styles */
.reviews-section {
  background: white;
  padding-top: 20px;
  padding-bottom: 20px;
}

.card.border-0.bg-light {
  padding: 12px;
}

.card-body {
  padding: 20px !important;
}

.rating-breakdown {
  padding: 10px 0 10px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.rating-row span:first-child {
  min-width: 40px;
  font-weight: 500;
}

.progress {
  flex-grow: 1;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  height: 10px !important;
}

.progress-bar {
  background: linear-gradient(90deg, #fbbf24, #f59e0b) !important;
  height: 100%;
  border-radius: 4px;
}

.rating-row span:last-child {
  min-width: 50px;
  text-align: right;
  font-weight: 500;
  color: #1f2937;
}


.rating-input {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: -8px;
  margin-bottom: 10px;
  padding: 0;
  background: transparent;
  border-radius: 0;
  border: none;
  align-items: center;
}

.rating-btn {
  padding: 6px 10px !important;
  font-size: 0.9rem !important;
  border: 2px solid #d1d5db !important;
  color: #1f2937 !important;
  background: white !important;
  transition: all 0.2s ease !important;
  cursor: pointer !important;
  border-radius: 6px !important;
  position: relative;
}

.rating-btn:hover {
  border-color: #22c55e !important;
  background: #f0fdf4 !important;
  transform: scale(1.08);
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

.btn-check:checked + .rating-btn {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: white !important;
  border-color: #16a34a !important;
  box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3) !important;
  transform: scale(1.12);
}

</style>

<script>
// Handle review form submission
document.getElementById('reviewForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const alertDiv = document.getElementById('reviewAlert');
  
  try {
    const response = await fetch('submit_review.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      alertDiv.className = 'alert alert-success';
      alertDiv.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message;
      alertDiv.style.display = 'block';
      
      // Reset form
      form.reset();
      
      // Reload reviews after 2 seconds
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      alertDiv.className = 'alert alert-danger';
      alertDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> ' + data.message;
      alertDiv.style.display = 'block';
    }
  } catch (error) {
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = '<i class="bi bi-exclamation-circle"></i> Terjadi kesalahan: ' + error.message;
    alertDiv.style.display = 'block';
  }
});
</script>

<?php include 'includes/footer.php'; ?>
