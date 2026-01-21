<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int) ($_GET['id'] ?? 0);

// Get transaksi dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Invoice tidak ditemukan</div></div>";
  exit;
}

// Get detail transaksi + produk
$stmt = mysqli_prepare($conn, "
  SELECT d.*, p.nama
  FROM detail_transaksi d
  JOIN produk p ON d.produk_id = p.id
  WHERE d.transaksi_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$qDetail = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice Admin #<?= $transaksi['id']; ?> - SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  
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
      padding: 20px;
    }

    .invoice-container {
      max-width: 900px;
      margin: 0 auto;
    }

    .invoice-card {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      border: 1px solid #e5e7eb;
    }

    /* Invoice Header */
    .invoice-header {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      padding: 40px;
      position: relative;
      overflow: hidden;
    }

    .invoice-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .invoice-header-content {
      position: relative;
      z-index: 1;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .company-info h1 {
      font-size: 2rem;
      font-weight: 900;
      margin: 0;
      letter-spacing: -0.5px;
    }

    .company-info small {
      display: block;
      opacity: 0.95;
      font-size: 0.9rem;
      margin-top: 4px;
    }

    .invoice-number {
      text-align: right;
    }

    .invoice-number h2 {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0;
    }

    .invoice-number small {
      display: block;
      opacity: 0.95;
      margin-top: 4px;
    }

    /* Invoice Body */
    .invoice-body {
      padding: 40px;
    }

    /* Two Column Section */
    .info-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-bottom: 40px;
      padding-bottom: 40px;
      border-bottom: 2px solid #f1f5f9;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
    }

    .info-block h6 {
      font-size: 0.85rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-block {
      background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 24px;
    }

    .info-block p {
      margin: 0 0 12px 0;
      color: #1e293b;
      font-size: 0.95rem;
      line-height: 1.6;
      word-break: break-word;
    }

    .info-block p:last-child {
      margin-bottom: 0;
    }

    .info-block strong {
      display: block;
      margin-bottom: 0;
      color: #0f172a;
      font-size: 1rem;
    }
    }

    /* Invoice Table */
    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
    }

    .invoice-table thead {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 1px solid #22c55e;
    }

    .invoice-table th {
      padding: 16px;
      text-align: left;
      font-weight: 700;
      color: #16a34a;
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .invoice-table td {
      padding: 16px;
      border-bottom: 1px solid #e5e7eb;
      color: #1e293b;
      font-size: 0.95rem;
    }

    .invoice-table tbody tr:hover {
      background: #f9fafb;
    }

    .invoice-table tfoot {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 1px solid #22c55e;
      border-top: 2px solid #22c55e;
    }

    .invoice-table tfoot td {
      padding: 16px;
      border: none;
      font-weight: 700;
      color: #16a34a;
    }

    .text-right {
      text-align: right;
    }

    .amount {
      font-weight: 600;
      color: #1e293b;
    }

    /* Summary Section */
    .summary-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
    }

    .summary-block {
      background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
      border: 1px solid #dcfce7;
      border-radius: 12px;
      padding: 20px;
    }

    .summary-block h6 {
      font-size: 0.9rem;
      font-weight: 700;
      color: #16a34a;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .summary-block p {
      margin: 0 0 8px 0;
      font-size: 0.9rem;
      color: #475569;
    }

    .summary-block p:last-child {
      margin-bottom: 0;
    }

    .summary-block strong {
      color: #1e293b;
      font-weight: 600;
    }

    /* Invoice Footer */
    .invoice-footer {
      background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
      border-top: 2px solid #dcfce7;
      padding: 30px 40px;
      text-align: center;
      color: #64748b;
      font-size: 0.9rem;
    }

    .invoice-footer p {
      margin: 0;
    }

    /* Status Badge */
    .status-badge {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.85rem;
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

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 30px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-action {
      padding: 12px 28px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .btn-print {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    .btn-print:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(34, 197, 94, 0.35);
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      color: white;
      text-decoration: none;
    }

    .btn-back {
      background: #f3f4f6;
      color: #1e293b;
      border-color: #e5e7eb;
    }

    .btn-back:hover {
      background: #e5e7eb;
      color: #000;
      text-decoration: none;
    }

    /* Print Styles */
    @media print {
      body {
        background: white;
        padding: 0;
      }

      .action-buttons {
        display: none;
      }

      .invoice-card {
        box-shadow: none;
        border: none;
      }

      .invoice-container {
        max-width: 100%;
      }
    }

    @media (max-width: 768px) {
      .invoice-header-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
      }

      .company-info,
      .invoice-number {
        text-align: center;
      }

      .info-section,
      .summary-section {
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .invoice-table {
        font-size: 0.85rem;
      }

      .invoice-table th,
      .invoice-table td {
        padding: 12px;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn-action {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="invoice-container">
    <!-- Invoice Card -->
    <div class="invoice-card">
      <!-- Header -->
      <div class="invoice-header">
        <div class="invoice-header-content">
          <div class="company-info">
            <h1>
              <i class="bi bi-basket2-fill"></i> SAYUR MAYUR
            </h1>
            <small>Sayuran Segar Berkualitas Premium</small>
          </div>
          <div class="invoice-number">
            <h2>#INV-<?= str_pad($transaksi['id'], 6, '0', STR_PAD_LEFT); ?></h2>
            <small><?= date('d M Y, H:i', strtotime($transaksi['tanggal'])); ?></small>
          </div>
        </div>
      </div>

      <!-- Body -->
      <div class="invoice-body">
        <!-- Customer Info -->
        <div class="info-section">
          <div class="info-block">
            <h6>
              <i class="bi bi-person-circle" style="color: #22c55e; font-size: 1.1rem;"></i> Informasi Pembeli
            </h6>
            <strong><?= htmlspecialchars($transaksi['nama_pembeli']); ?></strong>
            <p><i class="bi bi-telephone" style="color: #64748b; margin-right: 4px;"></i><?= htmlspecialchars($transaksi['no_telp'] ?: '-'); ?></p>
            <p><i class="bi bi-geo-alt" style="color: #64748b; margin-right: 4px;"></i><?= nl2br(htmlspecialchars($transaksi['alamat'] ?: '-')); ?></p>
          </div>

          <div class="info-block">
            <h6>
              <i class="bi bi-file-earmark-check" style="color: #22c55e; font-size: 1.1rem;"></i> Informasi Pesanan
            </h6>
            <p>
              <strong style="color: #64748b; font-size: 0.85rem; text-transform: uppercase;">Metode Pembayaran</strong>
              <span class="badge" style="background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%); color: #1e40af; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; display: inline-block; margin-top: 4px;">
                <?= strtoupper($transaksi['payment_method']); ?>
              </span>
            </p>
            <p style="margin-top: 16px;">
              <strong style="color: #64748b; font-size: 0.85rem; text-transform: uppercase;">Status Pesanan</strong>
              <span class="status-badge status-<?= strtolower($transaksi['status']); ?>" style="display: inline-block; margin-top: 4px;">
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
              </span>
            </p>
            <?php if (!empty($transaksi['kode_voucher'])): ?>
              <p style="margin-top: 16px;">
                <strong style="color: #64748b; font-size: 0.85rem; text-transform: uppercase;">Kode Voucher</strong>
                <span class="badge" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #15803d; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; display: inline-block; margin-top: 4px;">
                  <i class="bi bi-tag"></i> <?= htmlspecialchars($transaksi['kode_voucher']); ?>
                </span>
              </p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Products Table -->
        <table class="invoice-table">
          <thead>
            <tr>
              <th style="width: 50%;">Nama Produk</th>
              <th style="width: 15%; text-align: center;">Qty</th>
              <th style="width: 18%; text-align: right;">Harga Satuan</th>
              <th style="width: 17%; text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $totalItems = 0;
            while ($row = mysqli_fetch_assoc($qDetail)): 
              $totalItems += $row['qty'];
            ?>
              <tr>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td style="text-align: center;"><?= $row['qty']; ?> kg</td>
                <td class="text-right amount">Rp <?= number_format($row['harga']); ?></td>
                <td class="text-right amount">Rp <?= number_format($row['subtotal']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
              <td class="text-right"><strong>Rp <?= number_format($transaksi['subtotal'] ?? 0); ?></strong></td>
            </tr>
            <?php if ($transaksi['diskon'] > 0): ?>
              <tr style="background: rgba(34, 197, 94, 0.1); color: #15803d;">
                <td colspan="3" class="text-right"><strong>Diskon <?php if (!empty($transaksi['kode_voucher'])): ?>(<?= htmlspecialchars($transaksi['kode_voucher']); ?>)<?php endif; ?></strong></td>
                <td class="text-right"><strong>-Rp <?= number_format($transaksi['diskon']); ?></strong></td>
              </tr>
            <?php endif; ?>
            <tr>
              <td colspan="3" class="text-right" style="font-size: 1.1rem;"><strong>Total Pesanan</strong></td>
              <td class="text-right" style="font-size: 1.1rem;"><strong>Rp <?= number_format($transaksi['total']); ?></strong></td>
            </tr>
          </tfoot>
        </table>

        <!-- Summary Blocks -->
        <div class="summary-section" style="margin-top: 40px;">
          <div class="summary-block">
            <h6><i class="bi bi-bag-check"></i> Ringkasan Pesanan</h6>
            <p>Total Item: <strong><?= $totalItems; ?></strong></p>
            <p>Total Pembayaran: <strong style="color: #16a34a;">Rp <?= number_format($transaksi['total']); ?></strong></p>
          </div>

          <div class="summary-block">
            <h6><i class="bi bi-info-circle"></i> Catatan Penting</h6>
            <p>Pesanan telah diterima dan sedang dalam proses penyiapan. Estimasi pengiriman 1-2 hari kerja.</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="invoice-footer">
        <p>
          <strong>SAYUR MAYUR - Sayuran Segar Berkualitas Premium</strong><br>
          Terima kasih atas kepercayaan Anda. Untuk pertanyaan, hubungi kami di: <strong>info@sayurmayur.com</strong>
        </p>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="<?= route('transactions.detail', ['id' => $transaksi['id']]) ?>" class="btn-action btn-back">
        <i class="bi bi-arrow-left"></i> Kembali
      </a>
      <button onclick="window.print()" class="btn-action btn-print">
        <i class="bi bi-printer"></i> Cetak Invoice
      </button>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
