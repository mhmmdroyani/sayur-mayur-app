<?php
session_start();
include 'config/koneksi.php';
include 'config/functions.php';
include 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Search & Filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND nama LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if (!empty($kategori)) {
    $whereClause .= " AND kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

// Get all categories for filter UI
$kategoris = [];
$kategoriResult = mysqli_query($conn, "SELECT nama FROM kategori ORDER BY nama ASC");
while ($k = mysqli_fetch_assoc($kategoriResult)) {
    if (!empty($k['nama'])) {
        $kategoris[] = $k['nama'];
    }
}

// Count total
$countQuery = "SELECT COUNT(*) as total FROM produk $whereClause";
$stmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$totalResult = mysqli_stmt_get_result($stmt);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalItems = $totalRow['total'];
$totalPages = ceil($totalItems / $perPage);

// Order by
$orderBy = "ORDER BY ";
switch ($sortBy) {
    case 'price_low':
        $orderBy .= "harga ASC";
        break;
    case 'price_high':
        $orderBy .= "harga DESC";
        break;
    case 'name':
        $orderBy .= "nama ASC";
        break;
    default:
        $orderBy .= "id DESC";
}

// Main query
$query = "SELECT * FROM produk $whereClause $orderBy LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container py-5">
  <!-- Page Header -->
  <div class="text-center mb-5">
    <h1 class="fw-bold mb-2">Katalog Produk</h1>
    <p class="text-muted">Temukan sayuran segar pilihan terbaik</p>
  </div>

  <!-- Search & Filter -->
  <div class="search-filter-section mb-5">
    <div class="row g-3">
      <div class="col-lg-8">
        <form method="get" class="search-form">
          <div class="search-wrapper">
            <input type="text" name="search" class="search-input" placeholder="Cari produk..." 
                   value="<?= htmlspecialchars($search); ?>">
            <button type="submit" class="search-btn">
              <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($search)): ?>
              <a href="products.php" class="reset-btn">
                <i class="bi bi-x"></i>
              </a>
            <?php endif; ?>
          </div>
        </form>
      </div>
      <div class="col-lg-4">
        <select class="form-select sort-select" onchange="window.location.href='products.php?sort='+this.value+'<?= !empty($search) ? '&search='.urlencode($search) : ''; ?>'">
          <option value="newest" <?= $sortBy == 'newest' ? 'selected' : ''; ?>>📅 Terbaru</option>
          <option value="price_low" <?= $sortBy == 'price_low' ? 'selected' : ''; ?>>💰 Harga Terendah</option>
          <option value="price_high" <?= $sortBy == 'price_high' ? 'selected' : ''; ?>>💲 Harga Tertinggi</option>
          <option value="name" <?= $sortBy == 'name' ? 'selected' : ''; ?>>🔤 Nama A-Z</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Category Filter -->
  <?php if (!empty($kategoris)): ?>
    <div class="mb-4" style="padding: 0 20px;">
      <div class="d-flex flex-wrap gap-2" style="row-gap: 12px;">
        <a href="products.php<?= !empty($search) ? '?search='.urlencode($search) : ''; ?>" 
           class="btn btn-sm <?= empty($kategori) ? 'btn-success' : 'btn-outline-success'; ?>" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
          <i class="bi bi-list"></i> Semua
        </a>
        <?php foreach ($kategoris as $k): ?>
          <a href="products.php?kategori=<?= urlencode($k); ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?>&sort=<?= urlencode($sortBy); ?>" 
             class="btn btn-sm <?= $kategori == $k ? 'btn-success' : 'btn-outline-success'; ?>" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
            <?= htmlspecialchars($k); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Products Grid -->
  <div class="row g-4 mb-4">
    <?php if (mysqli_num_rows($result) == 0): ?>
      <div class="col-12">
        <div class="alert alert-info text-center">
          <i class="bi bi-info-circle fs-4"></i>
          <p class="mb-0 mt-2">Tidak ada produk yang ditemukan.</p>
        </div>
      </div>
    <?php else: ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="product-card card h-100 border-0 shadow-sm">
            <div class="product-image position-relative">
              <img src="assets/img/<?= sanitize($row['gambar']); ?>" 
                   class="card-img-top" 
                   alt="<?= sanitize($row['nama']); ?>"
                   style="height: 200px; object-fit: cover;">
              
              <?php if ($row['stock'] == 0): ?>
                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Habis</span>
              <?php elseif ($row['stock'] < 10): ?>
                <span class="badge bg-warning position-absolute top-0 end-0 m-2">Stok Terbatas</span>
              <?php endif; ?>
            </div>

            <div class="card-body d-flex flex-column">
              <h6 class="card-title fw-bold text-truncate" title="<?= sanitize($row['nama']); ?>">
                <?= sanitize($row['nama']); ?>
              </h6>
              
              <p class="text-success fw-bold fs-5 mb-2"><?= formatRupiah($row['harga']); ?></p>
              
              <div class="text-muted small mb-3">
                <i class="bi bi-box-seam"></i> Stok: <?= $row['stock']; ?>
              </div>

              <div class="mt-auto d-grid gap-2">
                <a href="product_detail.php?id=<?= $row['id']; ?>" 
                   class="btn btn-outline-success btn-sm">
                  <i class="bi bi-eye"></i> Detail
                </a>
                
                <?php if ($row['stock'] > 0): ?>
                  <button class="btn btn-success btn-sm add-to-cart-btn" 
                          onclick="addToCart({
                            id: <?= $row['id']; ?>,
                            name: '<?= addslashes(sanitize($row['nama'])); ?>',
                            price: <?= $row['harga']; ?>,
                            image: 'assets/img/<?= sanitize($row['gambar']); ?>'
                          })">
                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                  </button>
                <?php else: ?>
                  <button class="btn btn-secondary btn-sm" disabled>
                    <i class="bi bi-x-circle"></i> Stok Habis
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <nav aria-label="Product pagination">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?= $page - 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= $sortBy != 'newest' ? '&sort='.$sortBy : ''; ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
            <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
              <a class="page-link" href="?page=<?= $i; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= $sortBy != 'newest' ? '&sort='.$sortBy : ''; ?>">
                <?= $i; ?>
              </a>
            </li>
          <?php elseif (abs($i - $page) == 3): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
        <?php endfor; ?>
        
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?= $page + 1; ?><?= !empty($search) ? '&search='.urlencode($search) : ''; ?><?= $sortBy != 'newest' ? '&sort='.$sortBy : ''; ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<style>
.product-card {
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}

.add-to-cart-btn {
  transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
  transform: scale(1.05);
}

/* Search Bar Styling */
.search-filter-section {
  padding: 2rem;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border-radius: 12px;
}

.search-form {
  width: 100%;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 50px;
  padding: 4px 20px;
  box-shadow: 0 4px 16px rgba(34, 197, 94, 0.15);
  transition: all 0.3s ease;
}

.search-wrapper:focus-within {
  box-shadow: 0 6px 24px rgba(34, 197, 94, 0.25);
  transform: translateY(-2px);
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 16px;
  padding: 12px 0;
  color: #1e293b;
  outline: none;
}

.search-input::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

.search-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #22c55e;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-size: 18px;
  margin-left: 8px;
  flex-shrink: 0;
}

.search-btn:hover {
  background: #16a34a;
  transform: scale(1.1);
}

.search-btn:active {
  transform: scale(0.95);
}

.reset-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 16px;
  margin-left: 4px;
  flex-shrink: 0;
}

.reset-btn:hover {
  background: #e5e7eb;
  color: #1f2937;
}

.sort-select {
  border-radius: 50px;
  padding: 12px 20px;
  border: 2px solid #22c55e;
  font-size: 15px;
  background: white;
  color: #1e293b;
  transition: all 0.3s ease;
  height: auto;
  min-height: 50px;
}

.sort-select:focus {
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.sort-select:hover {
  border-color: #16a34a;
}

/* Responsive */
@media (max-width: 768px) {
  .search-filter-section {
    padding: 1.5rem;
  }
  
  .search-wrapper {
    padding: 4px 16px;
  }
  
  .search-input {
    font-size: 14px;
  }
  
  .search-btn {
    width: 36px;
    height: 36px;
    font-size: 16px;
  }
  
  .sort-select {
    padding: 10px 16px;
    min-height: 44px;
    font-size: 14px;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
