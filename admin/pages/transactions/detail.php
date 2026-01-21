<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int) ($_GET['id'] ?? 0);

// Handle status update
if (isset($_POST['update_status'])) {
  $new_status = $_POST['status'];
  $allowed_status = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
  
  if (in_array($new_status, $allowed_status)) {
    $stmt = mysqli_prepare($conn, "UPDATE transaksi SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
    if (mysqli_stmt_execute($stmt)) {
      $success_msg = "Status pesanan berhasil diupdate!";
    }
  }
}

// Get transaksi with shipping location
$qT = mysqli_query($conn, "
  SELECT t.*, ok.lokasi as shipping_lokasi 
  FROM transaksi t
  LEFT JOIN ongkos_kirim ok ON t.ongkos_kirim_id = ok.id
  WHERE t.id=$id
");
$transaksi = mysqli_fetch_assoc($qT);

if (!$transaksi) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Transaksi tidak ditemukan</div></div>";
  include '../../../includes/footer.php';
  exit;
}

// Get detail
$qD = mysqli_query($conn, "
  SELECT d.*, p.nama 
  FROM detail_transaksi d
  JOIN produk p ON d.produk_id = p.id
  WHERE d.transaksi_id = $id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detail Transaksi - Admin SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
      min-height: 100vh;
    }

    .admin-wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* Main Content */
    .admin-main {
      margin-left: 0;
      flex: 1;
    }

    .admin-content {
      padding: 40px 50px;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
      border-radius: 16px;
      padding: 40px;
      margin-bottom: 40px;
      border: 1px solid #dcfce7;
      box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
      position: relative;
      overflow: hidden;
    }

    .page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .page-header-content {
      position: relative;
      z-index: 1;
    }

    .page-header-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .page-header-title {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .page-header-title h1 {
      font-size: 2.5rem;
      font-weight: 900;
      color: #16a34a;
      margin: 0;
      letter-spacing: -0.5px;
    }

    .page-header-title i {
      font-size: 3rem;
      color: #22c55e;
    }

    .back-btn-header {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      border: 2px solid #22c55e;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    .back-btn-header:hover {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      border-color: #15803d;
      color: white;
      transform: translateX(-4px);
      box-shadow: 0 6px 16px rgba(34, 197, 94, 0.35);
      text-decoration: none;
    }

    .page-breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #64748b;
      font-size: 0.9rem;
    }

    .page-breadcrumb a {
      color: #1e40af;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .page-breadcrumb a:hover {
      color: #22c55e;
      text-decoration: underline;
    }

    .page-breadcrumb span {
      color: #cbd5e1;
    }

    /* Status Alert */
    .success-alert {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      border: 1px solid #86efac;
      color: #15803d;
      padding: 16px 20px;
      border-radius: 12px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 600;
    }

    .success-alert i {
      font-size: 1.2rem;
    }

    /* Content Cards */
    .detail-section {
      background: white;
      border-radius: 16px;
      padding: 30px;
      margin-bottom: 20px;
      border: 1px solid #e5e7eb;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .section-title {
      font-size: 1.3rem;
      font-weight: 900;
      color: #16a34a;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
      padding-bottom: 12px;
      border-bottom: 2px solid #f1f5f9;
    }

    .section-title i {
      font-size: 1.5rem;
      color: #22c55e;
    }

    /* Detail Grid */
    .detail-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-bottom: 0;
    }

    /* Two Column Section */
    .info-section-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .info-section-wrapper .detail-section {
      margin-bottom: 0;
    }

    /* Action Section Wrapper (Products & Status) */
    .action-section-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .action-section-wrapper .detail-section {
      margin-bottom: 0;
    }

    /* Main Content Wrapper (2 Column Layout) */
    .main-content-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .main-content-wrapper .detail-section {
      margin-bottom: 0;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
    }

    .detail-label {
      font-size: 0.85rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0px;
    }

    .detail-value {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1e293b;
      line-height: 1.6;
    }

    .detail-value a {
      color: #1e40af;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .detail-value a:hover {
      color: #22c55e;
      text-decoration: underline;
    }

    /* Status Badge */
    .status-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.85rem;
      width: fit-content;
    }

    .status-pending {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #92400e;
      border: 1px solid #fcd34d;
    }

    .status-processing {
      background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
      color: #1e40af;
      border: 1px solid #60a5fa;
    }

    .status-shipped {
      background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%);
      color: #3730a3;
      border: 1px solid #818cf8;
    }

    .status-delivered {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      color: #15803d;
      border: 1px solid #86efac;
    }

    .status-cancelled {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #dc2626;
      border: 1px solid #fca5a5;
    }

    /* Badge Style */
    .badge-info {
      background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
      color: #1e40af;
      padding: 4px 10px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.85rem;
      display: inline-block;
      width: fit-content;
    }

    /* Products Table */
    .products-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }

    .products-table thead {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 1px solid #22c55e;
    }

    .products-table th {
      padding: 8px 16px;
      text-align: left;
      font-weight: 700;
      color: #16a34a;
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .products-table td {
      padding: 6px 16px;
      border-bottom: 1px solid #e5e7eb;
      color: #1e293b;
      font-size: 0.95rem;
    }

    .products-table tbody tr:hover {
      background: #f9fafb;
    }

    .products-table tfoot {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 1px solid #22c55e;
      border-top: 2px solid #22c55e;
    }

    .products-table tfoot td {
      padding: 8px 16px;
      font-weight: 700;
      color: #16a34a;
      border: none;
    }

    .text-right {
      text-align: right;
    }

    /* Update Status Form */
    .status-form {
      background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
      border: 1px solid #dcfce7;
      border-radius: 12px;
      padding: 24px;
      margin-top: 8px;
      display: flex;
      gap: 16px;
      align-items: flex-end;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .form-label {
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .form-select {
      padding: 12px 16px;
      border: 2px solid #22c55e;
      border-radius: 10px;
      font-size: 0.95rem;
      font-family: inherit;
      background: white;
      color: #1e293b;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .form-select:focus {
      outline: none;
      border-color: #16a34a;
      box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
    }

    .btn-update {
      padding: 12px 28px;
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    .btn-update:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(34, 197, 94, 0.35);
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      color: white;
      text-decoration: none;
    }

    .btn-update:active {
      transform: translateY(0);
    }

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 32px;
      padding-top: 24px;
      border-top: 1px solid #e5e7eb;
    }

    .btn-secondary {
      padding: 12px 24px;
      background: #f3f4f6;
      color: #1e293b;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-secondary:hover {
      background: #e5e7eb;
      border-color: #d1d5db;
      color: #000;
      text-decoration: none;
    }

    .btn-primary {
      padding: 12px 24px;
      background: white;
      color: #1e40af;
      border: 2px solid #1e40af;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary:hover {
      background: #1e40af;
      color: white;
      text-decoration: none;
    }

    @media (max-width: 768px) {
      .admin-content {
        padding: 20px;
      }

      .page-header {
        padding: 24px;
        margin-bottom: 24px;
      }

      .detail-section {
        padding: 20px;
      }

      .page-header-title h1 {
        font-size: 1.8rem;
      }

      .page-header-title i {
        font-size: 2.2rem;
      }

      .detail-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .info-section-wrapper {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .action-section-wrapper {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .main-content-wrapper {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .status-form {
        flex-direction: column;
        align-items: stretch;
      }

      .form-group {
        width: 100%;
      }

      .btn-update {
        width: 100%;
        justify-content: center;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn-secondary, .btn-primary {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="admin-wrapper">
    <!-- MAIN CONTENT -->
    <div class="admin-main">
      <div class="admin-content">
        <!-- PAGE HEADER -->
        <div class="page-header">
          <div class="page-header-content">
            <div class="page-header-top">
              <div class="page-header-title">
                <i class="bi bi-receipt-cutoff"></i>
                <h1>Detail Transaksi</h1>
              </div>
              <a href="<?= route('transactions.index') ?>" class="back-btn-header">
                <i class="bi bi-arrow-left"></i> Kembali
              </a>
            </div>
            <nav class="page-breadcrumb">
              <a href="<?= route('dashboard') ?>">
                <i class="bi bi-house-door"></i> Home
              </a>
              <span>/</span>
              <a href="<?= route('transactions.index') ?>">Transaksi</a>
              <span>/</span>
              <span>Detail Transaksi #<?= $transaksi['id']; ?></span>
            </nav>
          </div>
        </div>

        <!-- SUCCESS MESSAGE -->
        <?php if (isset($success_msg)): ?>
          <div class="success-alert">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= $success_msg; ?></span>
          </div>
        <?php endif; ?>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="main-content-wrapper">
          <!-- LEFT COLUMN: Customer Info + Products -->
          <div class="detail-section">
            <!-- CUSTOMER INFO -->
            <div class="section-title">
              <i class="bi bi-person-circle"></i>
              Informasi Pembeli
            </div>
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">Nama Pembeli</div>
                <div class="detail-value"><?= htmlspecialchars($transaksi['nama_pembeli']); ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">No. Telepon</div>
                <div class="detail-value"><?= htmlspecialchars($transaksi['no_telp'] ?: '-'); ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Alamat Pengiriman</div>
                <div class="detail-value"><?= nl2br(htmlspecialchars($transaksi['alamat'] ?: '-')); ?></div>
              </div>
            </div>

            <!-- PRODUCTS SECTION -->
            <div style="margin-top: 8px; padding-top: 8px; border-top: 2px solid #f1f5f9;">
              <table class="products-table">
                <thead>
                  <tr>
                    <th>Nama Produk</th>
                    <th style="text-align: center; width: 100px;">Qty</th>
                    <th style="text-align: right; width: 150px;">Harga Satuan</th>
                    <th style="text-align: right; width: 150px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($qD)) { ?>
                    <tr>
                      <td><?= htmlspecialchars($row['nama']); ?></td>
                      <td style="text-align: center;"><?= $row['qty']; ?> kg</td>
                      <td style="text-align: right;">Rp <?= number_format($row['harga']); ?></td>
                      <td style="text-align: right;">Rp <?= number_format($row['subtotal']); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3" class="text-right"><strong>Total Pesanan</strong></td>
                    <td style="text-align: right;"><strong>Rp <?= number_format($transaksi['total']); ?></strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- RIGHT COLUMN: Order Info + Status -->
          <div class="detail-section">
            <!-- ORDER INFO -->
            <div class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
              <div style="display: flex; align-items: center; gap: 12px;">
                <i class="bi bi-box-seam"></i>
                Informasi Pesanan
              </div>
              <a href="<?= route('transactions.invoice', ['id' => $transaksi['id']]) ?>" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); text-decoration: none; border-radius: 8px; flex-shrink: 0; transition: all 0.3s ease;" title="Cetak Invoice">
                <i class="bi bi-printer" style="color: white; font-size: 1.2rem;"></i>
              </a>
            </div>
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">Invoice Number</div>
                <div class="detail-value">#INV-<?= str_pad($transaksi['id'], 6, '0', STR_PAD_LEFT); ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Tanggal Order</div>
                <div class="detail-value"><?= date('d M Y H:i', strtotime($transaksi['tanggal'])); ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Metode Pembayaran</div>
                <div class="badge-info"><?= strtoupper($transaksi['payment_method']); ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Status Pesanan</div>
                <div class="status-badge status-<?= strtolower($transaksi['status']); ?>">
                  <?php
                    $status_text = [
                      'pending' => 'Menunggu',
                      'processing' => 'Diproses',
                      'shipped' => 'Dikirim',
                      'delivered' => 'Selesai',
                      'cancelled' => 'Dibatalkan'
                    ];
                    echo $status_text[$transaksi['status']] ?? ucfirst($transaksi['status']);
                  ?>
                </div>
              </div>
              <?php if (!empty($transaksi['kode_voucher'])): ?>
                <div class="detail-item">
                  <div class="detail-label">Kode Voucher</div>
                  <div class="detail-value"><span class="badge-info"><?= htmlspecialchars($transaksi['kode_voucher']); ?></span></div>
                </div>
              <?php endif; ?>
              <?php if ($transaksi['diskon'] > 0): ?>
                <div class="detail-item">
                  <div class="detail-label">Diskon</div>
                  <div class="detail-value">Rp <?= number_format($transaksi['diskon']); ?></div>
                </div>
              <?php endif; ?>
              <?php if ($transaksi['shipping_biaya'] > 0): ?>
                <div class="detail-item">
                  <div class="detail-label">Ongkos Kirim</div>
                  <div class="detail-value">Rp <?= number_format($transaksi['shipping_biaya']); ?></div>
                </div>
              <?php endif; ?>
              <?php if (!empty($transaksi['shipping_lokasi'])): ?>
                <div class="detail-item">
                  <div class="detail-label">Lokasi Pengiriman</div>
                  <div class="detail-value"><?= htmlspecialchars($transaksi['shipping_lokasi']); ?></div>
                </div>
              <?php endif; ?>
            </div>

            <!-- UPDATE STATUS SECTION -->
            <div style="margin-top: 8px; padding-top: 8px; border-top: 2px solid #f1f5f9;">
              <form method="POST" class="status-form">
                <div class="form-group">
                  <label class="form-label">Status Baru</label>
                  <select name="status" class="form-select" required>
                    <option value="pending" <?= $transaksi['status'] == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="processing" <?= $transaksi['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="shipped" <?= $transaksi['status'] == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="delivered" <?= $transaksi['status'] == 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="cancelled" <?= $transaksi['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                  </select>
                </div>
                <button type="submit" name="update_status" class="btn-update">
                  <i class="bi bi-check-circle"></i> Update Status
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
