<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
if (!empty($success_msg)) unset($_SESSION['success_msg']);
if (!empty($error_msg)) unset($_SESSION['error_msg']);

// Get filter parameters
$search = $_GET['search'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';
$stock_filter = $_GET['stock'] ?? '';
$sort_by = $_GET['sort'] ?? 'terbaru';

// Build query
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(p.nama LIKE ? OR k.nama LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($kategori_filter)) {
    $where[] = "p.kategori = ?";
    $params[] = $kategori_filter;
    $types .= 'i';
}

if (!empty($stock_filter)) {
    if ($stock_filter == 'habis') {
        $where[] = "p.stock = 0";
    } elseif ($stock_filter == 'rendah') {
        $where[] = "p.stock BETWEEN 1 AND 10";
    } elseif ($stock_filter == 'tersedia') {
        $where[] = "p.stock > 10";
    }
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sort
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'nama_asc':
        $order_by .= "p.nama ASC";
        break;
    case 'nama_desc':
        $order_by .= "p.nama DESC";
        break;
    case 'harga_asc':
        $order_by .= "p.harga ASC";
        break;
    case 'harga_desc':
        $order_by .= "p.harga DESC";
        break;
    case 'stock_asc':
        $order_by .= "p.stock ASC";
        break;
    case 'stock_desc':
        $order_by .= "p.stock DESC";
        break;
    default:
        $order_by .= "p.id DESC";
}

$query = "SELECT p.id, p.nama, p.harga, p.stock, k.nama as kategori, p.kategori as kategori_id, p.gambar FROM produk p LEFT JOIN kategori k ON p.kategori = k.id {$where_clause} {$order_by}";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $product_list = mysqli_stmt_get_result($stmt);
} else {
    $product_list = mysqli_query($conn, $query);
}

$total_produk = mysqli_num_rows($product_list);
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE stock BETWEEN 1 AND 10"))['count'];
$out_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM produk WHERE stock = 0"))['count'];

// Get all categories for filter
$kategori_list = mysqli_query($conn, "SELECT id, nama FROM kategori ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk - Admin SAYUR MAYUR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { margin-left: 280px; flex: 1; min-height: 100vh; }
        .top-bar { background: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }
        .admin-content { padding: 32px; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); display: flex; align-items: center; gap: 16px; }
        .stat-icon { font-size: 2.5rem; }
        .stat-icon-total { color: #22c55e; }
        .stat-icon-low { color: #f59e0b; }
        .stat-icon-out { color: #ef4444; }
        .stat-info h3 { font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0; }
        .stat-info p { font-size: 0.85rem; color: #64748b; margin: 4px 0 0 0; }
        .btn-add { background: #22c55e; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
        .btn-add:hover { background: #16a34a; color: white; }
        .alert-message { border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .alert-success { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac; color: #15803d; }
        .data-table-wrapper { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-bottom: 2px solid #22c55e; }
        .data-table th { padding: 16px; font-weight: 700; color: #16a34a; text-align: left; font-size: 0.85rem; text-transform: uppercase; }
        .data-table td { padding: 16px; color: #1e293b; }
        .data-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .product-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f1f5f9; }
        .badge-stock { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; }
        .badge-in-stock { background: #dcfce7; color: #15803d; }
        .badge-low-stock { background: #fef3c7; color: #b45309; }
        .badge-out-stock { background: #fee2e2; color: #dc2626; }
        .actions { display: flex; gap: 8px; }
        .btn-action { padding: 8px 14px; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-edit { background: #bfdbfe; color: #1e40af; }
        .btn-edit:hover { background: #93c5fd; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
        
        /* Filter & Search Section */
        .filter-section { background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
        .filter-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-label { font-weight: 600; color: #64748b; margin-bottom: 8px; font-size: 0.85rem; }
        .filter-input, .filter-select { padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .filter-input:focus, .filter-select:focus { border-color: #22c55e; outline: none; }
        .btn-filter { padding: 10px 20px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-filter:hover { background: #16a34a; }
        .btn-reset { padding: 10px 16px; background: #f3f4f6; color: #64748b; border: 2px solid #e5e7eb; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-reset:hover { background: #e5e7eb; color: #475569; }
        .active-filters { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .filter-badge { background: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
        .filter-badge i { cursor: pointer; }
        @media (max-width: 1200px) { .filter-row { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include '../../../includes/sidebar.php'; ?>
        <div class="admin-main">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Produk</h1>
                    <p class="page-breadcrumb" style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;"><i class="bi bi-house-door"></i> Home / Produk</p>
                </div>
            </div>
            <div class="admin-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert-message alert-success"><i class="bi bi-check-circle-fill"></i> <?= $success_msg; ?></div>
                <?php endif; ?>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-total"><i class="bi bi-box"></i></div>
                        <div class="stat-info">
                            <h3><?= $total_produk; ?></h3>
                            <p>Total Produk</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-low"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-info">
                            <h3><?= $low_stock; ?></h3>
                            <p>Stok Rendah (1-10)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-out"><i class="bi bi-x-circle"></i></div>
                        <div class="stat-info">
                            <h3><?= $out_stock; ?></h3>
                            <p>Stok Habis</p>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search Section -->
                <div class="filter-section">
                    <form method="GET" action="index.php" id="filterForm">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label"><i class="bi bi-search"></i> Cari Produk</label>
                                <input type="text" name="search" class="filter-input" placeholder="Cari nama produk atau kategori..." value="<?= htmlspecialchars($search); ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label"><i class="bi bi-tag"></i> Kategori</label>
                                <select name="kategori" class="filter-select">
                                    <option value="">Semua Kategori</option>
                                    <?php 
                                    mysqli_data_seek($kategori_list, 0);
                                    while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                        <option value="<?= $kat['id']; ?>" <?= $kategori_filter == $kat['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($kat['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label"><i class="bi bi-box-seam"></i> Status Stok</label>
                                <select name="stock" class="filter-select">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia" <?= $stock_filter == 'tersedia' ? 'selected' : ''; ?>>Tersedia (>10)</option>
                                    <option value="rendah" <?= $stock_filter == 'rendah' ? 'selected' : ''; ?>>Stok Rendah (1-10)</option>
                                    <option value="habis" <?= $stock_filter == 'habis' ? 'selected' : ''; ?>>Habis (0)</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label"><i class="bi bi-sort-down"></i> Urutkan</label>
                                <select name="sort" class="filter-select">
                                    <option value="terbaru" <?= $sort_by == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                                    <option value="nama_asc" <?= $sort_by == 'nama_asc' ? 'selected' : ''; ?>>Nama (A-Z)</option>
                                    <option value="nama_desc" <?= $sort_by == 'nama_desc' ? 'selected' : ''; ?>>Nama (Z-A)</option>
                                    <option value="harga_asc" <?= $sort_by == 'harga_asc' ? 'selected' : ''; ?>>Harga (Rendah-Tinggi)</option>
                                    <option value="harga_desc" <?= $sort_by == 'harga_desc' ? 'selected' : ''; ?>>Harga (Tinggi-Rendah)</option>
                                    <option value="stock_asc" <?= $sort_by == 'stock_asc' ? 'selected' : ''; ?>>Stok (Sedikit-Banyak)</option>
                                    <option value="stock_desc" <?= $sort_by == 'stock_desc' ? 'selected' : ''; ?>>Stok (Banyak-Sedikit)</option>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 8px; align-items: flex-end;">
                                <button type="submit" class="btn-filter">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                                <a href="index.php" class="btn-reset" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($search) || !empty($kategori_filter) || !empty($stock_filter) || $sort_by != 'terbaru'): ?>
                            <div class="active-filters">
                                <small style="color: #64748b; font-weight: 600;">Filter Aktif:</small>
                                <?php if (!empty($search)): ?>
                                    <span class="filter-badge">
                                        <i class="bi bi-search"></i> "<?= htmlspecialchars($search); ?>"
                                        <i class="bi bi-x" onclick="removeFilter('search')"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($kategori_filter)): 
                                    $kat_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM kategori WHERE id = {$kategori_filter}"))['nama'] ?? '';
                                ?>
                                    <span class="filter-badge">
                                        <i class="bi bi-tag"></i> <?= htmlspecialchars($kat_name); ?>
                                        <i class="bi bi-x" onclick="removeFilter('kategori')"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($stock_filter)): ?>
                                    <span class="filter-badge">
                                        <i class="bi bi-box-seam"></i> Stok: <?= ucfirst($stock_filter); ?>
                                        <i class="bi bi-x" onclick="removeFilter('stock')"></i>
                                    </span>
                                <?php endif; ?>
                                <?php if ($sort_by != 'terbaru'): ?>
                                    <span class="filter-badge">
                                        <i class="bi bi-sort-down"></i> Sort: <?= str_replace('_', ' ', ucfirst($sort_by)); ?>
                                        <i class="bi bi-x" onclick="removeFilter('sort')"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <a href="<?= route('products.create') ?>" class="btn-add"><i class="bi bi-plus-circle"></i> Tambah Produk</a>
                    <a href="<?= route('products.reviews') ?>" class="btn-add" style="background: #3b82f6; margin-left: auto;"><i class="bi bi-chat-left"></i> Kelola Review</a>
                </div>

                <div class="data-table-wrapper">
                    <?php if ($total_produk > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th style="width: 180px;">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($product_list, 0);
                                while ($item = mysqli_fetch_assoc($product_list)): 
                                    $stock_status = $item['stock'] == 0 ? 'out-stock' : ($item['stock'] <= 10 ? 'low-stock' : 'in-stock');
                                ?>
                                    <tr>
                                        <td><img src="/sayur_mayur_app/assets/img/<?= htmlspecialchars($item['gambar'] ?? 'placeholder.png'); ?>" alt="<?= htmlspecialchars($item['nama']); ?>" class="product-img"></td>
                                        <td><strong><?= htmlspecialchars($item['nama']); ?></strong></td>
                                        <td><?= htmlspecialchars($item['kategori'] ?? '-'); ?></td>
                                        <td><?= formatRupiah($item['harga']); ?></td>
                                        <td><span class="badge-stock badge-<?= $stock_status; ?>"><?= $item['stock']; ?> Unit</span></td>
                                        <td class="actions">
                                            <a href="<?= route('products.edit', ['id' => $item['id']]) ?>" class="btn-action btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                                            <a href="<?= route('products.delete', ['id' => $item['id']]) ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus?');"><i class="bi bi-trash"></i> Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p><?= !empty($search) || !empty($kategori_filter) || !empty($stock_filter) ? 'Tidak ada produk yang sesuai dengan filter' : 'Belum ada data produk'; ?></p>
                            <?php if (!empty($search) || !empty($kategori_filter) || !empty($stock_filter)): ?>
                                <a href="index.php" class="btn-add" style="margin-top: 16px;">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeFilter(filterName) {
            const form = document.getElementById('filterForm');
            const url = new URL(window.location.href);
            url.searchParams.delete(filterName);
            window.location.href = url.toString();
        }
        
        // Auto-submit on select change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>
