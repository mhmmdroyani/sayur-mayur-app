<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$success_msg = $_SESSION['success_msg'] ?? '';
if (!empty($success_msg)) unset($_SESSION['success_msg']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $review_id = (int)($_POST['review_id'] ?? 0);

    if ($action == 'delete' && $review_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM review WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $review_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Review berhasil dihapus!";
        }
        header("Location: " . route('products.reviews'));
        exit;
    }
}

$filter_rating = (int)($_GET['rating'] ?? 0);
$query = "SELECT r.id, r.produk_id, p.nama, r.rating, r.komentar, r.nama_reviewer, r.created_at FROM review r LEFT JOIN produk p ON r.produk_id = p.id";

if ($filter_rating > 0) {
    $query .= " WHERE r.rating = " . $filter_rating;
}

$query .= " ORDER BY r.created_at DESC";
$review_list = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Review - Admin SAYUR MAYUR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { margin-left: 280px; flex: 1; min-height: 100vh; }
        .top-bar { background: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }
        .page-breadcrumb { color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0; }
        .admin-content { padding: 32px; }
        .filter-section { background: white; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; gap: 16px; align-items: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); }
        .filter-section a { padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 2px solid #e5e7eb; color: #64748b; }
        .filter-section a.active { background: #22c55e; color: white; border-color: #22c55e; }
        .filter-section a:hover { border-color: #22c55e; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #22c55e; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 24px; }
        .back-btn:hover { background: #16a34a; }
        .alert-message { border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 600; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac; color: #15803d; }
        .review-card { background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06); border-left: 4px solid #f59e0b; }
        .review-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; }
        .review-product { font-weight: 700; color: #1e293b; }
        .review-meta { color: #64748b; font-size: 0.85rem; }
        .review-rating { display: flex; gap: 4px; margin: 8px 0; }
        .star { color: #fbbf24; font-size: 1.1rem; }
        .review-text { color: #1e293b; margin: 12px 0; line-height: 1.6; font-style: italic; }
        .review-actions { display: flex; gap: 12px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .btn-delete { padding: 8px 14px; background: #fee2e2; color: #dc2626; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; background: white; border-radius: 12px; }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include '../../../includes/sidebar.php'; ?>
        <div class="admin-main">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Kelola Review Produk</h1>
                    <p class="page-breadcrumb"><i class="bi bi-house-door"></i> Home / Review Produk</p>
                </div>
            </div>
            <div class="admin-content">
                <a href="<?= route('products.index') ?>" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Kembali ke Produk
                </a>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert-message"><i class="bi bi-check-circle-fill"></i> <?= $success_msg; ?></div>
                <?php endif; ?>

                <div class="filter-section">
                    <span style="font-weight: 600; color: #1e293b;">Filter Rating:</span>
                    <a href="<?= route('products.reviews') ?>" class="<?= $filter_rating == 0 ? 'active' : ''; ?>">Semua</a>
                    <a href="<?= route('products.reviews') . '?rating=5'; ?>" class="<?= $filter_rating == 5 ? 'active' : ''; ?>"><i class="bi bi-star-fill"></i> 5 Bintang</a>
                    <a href="<?= route('products.reviews') . '?rating=4'; ?>" class="<?= $filter_rating == 4 ? 'active' : ''; ?>"><i class="bi bi-star-fill"></i> 4 Bintang</a>
                    <a href="<?= route('products.reviews') . '?rating=3'; ?>" class="<?= $filter_rating == 3 ? 'active' : ''; ?>"><i class="bi bi-star-fill"></i> 3 Bintang</a>
                    <a href="<?= route('products.reviews') . '?rating=1'; ?>" class="<?= $filter_rating == 1 ? 'active' : ''; ?>"><i class="bi bi-star-fill"></i> 1-2 Bintang</a>
                </div>

                <?php if (mysqli_num_rows($review_list) > 0): ?>
                    <?php while ($review = mysqli_fetch_assoc($review_list)): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <div class="review-product"><?= htmlspecialchars($review['nama'] ?? 'Produk Dihapus'); ?></div>
                                    <div class="review-meta"><?= htmlspecialchars($review['nama_reviewer']); ?> - <?= date('d M Y, H:i', strtotime($review['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    <span class="star"><i class="bi bi-star-fill"></i></span>
                                <?php endfor; ?>
                            </div>
                            <div class="review-text"><?= htmlspecialchars($review['komentar']); ?></div>
                            <div class="review-actions">
                                <form method="POST" action="<?= route('products.reviews') . '?action=delete'; ?>" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?= $review['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Yakin ingin menghapus review ini?');"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state"><i class="bi bi-chat-left"></i><p>Tidak ada review untuk filter yang dipilih</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
