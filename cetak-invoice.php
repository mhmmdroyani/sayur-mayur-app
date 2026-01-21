<?php
session_start();
include 'config/koneksi.php';
include 'config/functions.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil transaksi dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
  die("Invoice tidak ditemukan");
}

// Ambil detail transaksi + produk
$stmt = mysqli_prepare($conn, "
  SELECT d.*, p.nama, p.gambar
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
  <title>Cetak Struk #INV-<?= str_pad($id, 5, '0', STR_PAD_LEFT); ?></title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Courier New', monospace;
      font-size: 12px;
      width: 80mm;
      margin: 0 auto;
      background: #f5f5f5;
      padding: 10px;
    }
    
    .struk {
      background: white;
      padding: 10px;
      page-break-after: always;
    }
    
    /* Header */
    .header {
      text-align: center;
      border-bottom: 1px dashed #000;
      padding-bottom: 8px;
      margin-bottom: 8px;
    }
    
    .header h1 {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 2px;
    }
    
    .header p {
      font-size: 10px;
      color: #666;
      margin: 1px 0;
    }
    
    .invoice-no {
      font-size: 11px;
      font-weight: bold;
      margin: 4px 0;
    }
    
    /* Customer Info */
    .customer {
      font-size: 11px;
      border-bottom: 1px dashed #000;
      padding-bottom: 6px;
      margin-bottom: 6px;
    }
    
    .customer-name {
      font-weight: bold;
      margin-bottom: 2px;
    }
    
    .customer-detail {
      font-size: 10px;
      color: #666;
    }
    
    /* Items Table */
    .items {
      margin-bottom: 8px;
      border-bottom: 1px dashed #000;
      padding-bottom: 6px;
    }
    
    .item-header {
      display: flex;
      justify-content: space-between;
      font-size: 10px;
      font-weight: bold;
      margin-bottom: 3px;
      border-bottom: 1px solid #000;
      padding-bottom: 2px;
    }
    
    .item-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 2px;
      line-height: 1.3;
    }
    
    .item-name {
      flex: 1;
      word-wrap: break-word;
      word-break: break-all;
    }
    
    .item-qty {
      width: 30px;
      text-align: center;
      margin: 0 4px;
    }
    
    .item-price {
      width: 50px;
      text-align: right;
    }
    
    /* Summary */
    .summary {
      margin-bottom: 8px;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 3px;
    }
    
    .summary-label {
      flex: 1;
    }
    
    .summary-value {
      width: 70px;
      text-align: right;
      padding-left: 5px;
    }
    
    .summary-row.total {
      font-weight: bold;
      font-size: 12px;
      border-top: 1px dashed #000;
      border-bottom: 1px solid #000;
      padding: 3px 0;
      margin: 6px 0;
    }
    
    .summary-row.diskon {
      color: #27ae60;
    }
    
    .summary-row.shipping {
      color: #e74c3c;
    }
    
    /* Footer */
    .footer {
      text-align: center;
      font-size: 10px;
      color: #666;
      padding-top: 6px;
    }
    
    .payment-method {
      font-weight: bold;
      margin-bottom: 4px;
    }
    
    .thank-you {
      margin-top: 6px;
      font-size: 11px;
    }
    
    /* Spacer untuk thermal printer */
    .spacer {
      height: 40mm;
      border: none;
    }
    
    /* Print Styles */
    @media print {
      body {
        background: white;
        width: 80mm;
        margin: 0;
        padding: 0;
      }
      
      .struk {
        page-break-after: auto;
        padding: 0;
        box-shadow: none;
      }
      
      .spacer {
        page-break-after: always;
      }
    }
  </style>
</head>
<body>

<div class="struk">
  <!-- Header -->
  <div class="header">
    <h1>SAYUR MAYUR</h1>
    <p>Sayuran Segar Setiap Hari</p>
    <div class="invoice-no">
      INV-<?= str_pad($transaksi['id'], 5, '0', STR_PAD_LEFT); ?>
    </div>
    <p><?= date('d/m/Y H:i', strtotime($transaksi['tanggal'])); ?></p>
  </div>

  <!-- Customer Info -->
  <div class="customer">
    <div class="customer-name"><?= sanitize($transaksi['nama_pembeli']); ?></div>
    <div class="customer-detail"><?= sanitize($transaksi['no_telp']); ?></div>
    <div class="customer-detail"><?= sanitize($transaksi['alamat']); ?></div>
  </div>

  <!-- Items -->
  <div class="items">
    <div class="item-header">
      <div class="item-name">Produk</div>
      <div class="item-qty">Qty</div>
      <div class="item-price">Harga</div>
    </div>
    
    <?php while ($row = mysqli_fetch_assoc($qDetail)): ?>
      <div class="item-row">
        <div class="item-name"><?= sanitize($row['nama']); ?></div>
        <div class="item-qty"><?= $row['qty']; ?>x</div>
        <div class="item-price"><?= formatRupiah($row['subtotal']); ?></div>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- Summary -->
  <div class="summary">
    <div class="summary-row">
      <div class="summary-label">Subtotal</div>
      <div class="summary-value"><span style="font-family: Arial, sans-serif; font-size: 10px;">Rp</span> <?= number_format($transaksi['subtotal'], 0, ',', '.'); ?></div>
    </div>
    
    <?php if ($transaksi['diskon'] > 0): ?>
      <div class="summary-row diskon">
        <div class="summary-label">Diskon</div>
        <div class="summary-value"><span style="font-family: Arial, sans-serif; font-size: 10px;">-Rp</span> <?= number_format($transaksi['diskon'], 0, ',', '.'); ?></div>
      </div>
    <?php endif; ?>
    
    <?php if ($transaksi['shipping_biaya'] > 0): ?>
      <div class="summary-row shipping">
        <div class="summary-label">Ongkos Kirim</div>
        <div class="summary-value"><span style="font-family: Arial, sans-serif; font-size: 10px;">Rp</span> <?= number_format($transaksi['shipping_biaya'], 0, ',', '.'); ?></div>
      </div>
    <?php endif; ?>
    
    <div class="summary-row total">
      <div class="summary-label">TOTAL</div>
      <div class="summary-value"><span style="font-family: Arial, sans-serif; font-size: 10px;">Rp</span> <?= number_format($transaksi['total'], 0, ',', '.'); ?></div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="payment-method">Metode: <?= strtoupper($transaksi['payment_method']); ?></div>
    <div class="thank-you">Terima kasih telah berbelanja</div>
    <div class="thank-you" style="margin-top: 8px; font-size: 9px;">
      www.sayurmayur.com
    </div>
  </div>

  <!-- Spacer untuk thermal printer -->
  <div class="spacer"></div>
</div>

<script>
  // Auto print saat halaman dimuat
  window.addEventListener('load', function() {
    window.print();
  });
</script>

</body>
</html>
