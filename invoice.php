<?php
session_start();
include 'config/koneksi.php';
include 'config/functions.php';
include 'includes/header.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil transaksi dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
  echo "<div class='container py-5'>
    <div class='alert alert-danger text-center'>
      <i class='bi bi-exclamation-triangle fs-1'></i>
      <p class='mt-3'>Invoice tidak ditemukan</p>
      <a href='products.php' class='btn btn-success mt-2'>Kembali Belanja</a>
    </div>
  </div>";
  include 'includes/footer.php';
  exit;
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

<div class="container py-3">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <!-- Success Message (screen only) -->
      <div class="alert alert-success alert-dismissible fade show text-center d-print-none" role="alert">
        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
        <h5 class="fw-bold">Pesanan Berhasil!</h5>
        <p class="mb-0">Terima kasih telah berbelanja di SAYUR MAYUR</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>

      <!-- Invoice Card -->
      <div class="card border-0 shadow-lg" id="invoiceCard">
        <!-- Header -->
        <div class="card-header bg-success text-white">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-0 fw-bold">SAYUR MAYUR</h5>
              <small class="text-white-50">Sayuran Segar Setiap Hari</small>
            </div>
            <div class="text-end">
              <small class="d-block fw-bold">#INV-<?= str_pad($transaksi['id'], 5, '0', STR_PAD_LEFT); ?></small>
              <small class="text-white-50"><?= date('d M Y', strtotime($transaksi['tanggal'])); ?></small>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="card-body">
          <!-- Buyer Info -->
          <div class="mb-2 pb-3 border-bottom">
            <h6 class="fw-bold mb-2">Info Pemesan</h6>
            <p class="mb-1"><strong><?= sanitize($transaksi['nama_pembeli']); ?></strong></p>
            <p class="mb-1 small"><i class="bi bi-telephone"></i> <?= sanitize($transaksi['no_telp']); ?></p>
            <p class="mb-0 small"><i class="bi bi-geo-alt"></i> <?= sanitize($transaksi['alamat']); ?></p>
          </div>

          <!-- Status Pesanan -->
          <div class="mb-2 pb-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
              <h6 class="fw-bold mb-0">Status Pesanan</h6>
            </div>
            <div>
              <?php 
                $status = $transaksi['status'];
                $statusBadge = match($status) {
                  'pending' => '<span class="badge bg-warning">⏳ Menunggu Pembayaran</span>',
                  'processing' => '<span class="badge bg-info">⚙️ Diproses</span>',
                  'shipped' => '<span class="badge bg-primary">📦 Dikirim</span>',
                  'delivered' => '<span class="badge bg-success">✓ Terima</span>',
                  'cancelled' => '<span class="badge bg-danger">✗ Dibatalkan</span>',
                  default => '<span class="badge bg-secondary">?Tidak Diketahui</span>'
                };
                echo $statusBadge;
              ?>
            </div>
          </div>

          <!-- Items Table -->
          <div class="mb-4">
            <h6 class="fw-bold mb-3">Detail Pesanan</h6>
            <table class="table table-sm table-hover">
              <thead class="table-light">
                <tr>
                  <th width="50%">Produk</th>
                  <th width="15%" class="text-center">Qty</th>
                  <th width="20%" class="text-end">Harga</th>
                  <th width="15%" class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $totalItems = 0;
                while ($row = mysqli_fetch_assoc($qDetail)): 
                  $totalItems += $row['qty'];
                ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="assets/img/<?= sanitize($row['gambar']); ?>" 
                             width="50" height="50" 
                             class="rounded me-2" 
                             style="object-fit: cover;"
                             alt="<?= sanitize($row['nama']); ?>">
                        <strong><?= sanitize($row['nama']); ?></strong>
                      </div>
                    </td>
                    <td class="text-center"><?= $row['qty']; ?>x</td>
                    <td class="text-end"><?= formatRupiah($row['harga']); ?></td>
                    <td class="text-end fw-bold"><?= formatRupiah($row['subtotal']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                  <td class="text-end fw-bold text-muted">
                    <?= formatRupiah($transaksi['subtotal']); ?>
                  </td>
                </tr>
                <?php if ($transaksi['diskon'] > 0): ?>
                  <tr class="text-success">
                    <td colspan="3" class="text-end fw-bold">
                      <i class="bi bi-tag" style="color: #000; margin-right: 0.3rem;"></i> Diskon <?php if (!empty($transaksi['kode_voucher'])): ?>(<?= htmlspecialchars($transaksi['kode_voucher']); ?>)<?php else: ?>(Promo)<?php endif; ?>:
                    </td>
                    <td class="text-end fw-bold">
                      -<?= formatRupiah($transaksi['diskon']); ?>
                    </td>
                  </tr>
                <?php endif; ?>
                <?php if ($transaksi['shipping_biaya'] > 0): ?>
                  <tr>
                    <td colspan="3" class="text-end fw-bold">
                      <i class="bi bi-truck" style="color: #16a34a; margin-right: 0.3rem;"></i> Ongkos Kirim:
                    </td>
                    <td class="text-end fw-bold text-muted">
                      <?= formatRupiah($transaksi['shipping_biaya']); ?>
                    </td>
                  </tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #16a34a;">
                  <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                  <td class="text-end fw-bold text-success fs-5" style="color: #16a34a !important; font-size: 1.2rem; white-space: nowrap;">
                    <?= formatRupiah($transaksi['total']); ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Summary -->
          <div class="row">
            <div class="col-md-6">
              <div class="bg-light p-3 rounded">
                <h6 class="mb-2">Ringkasan Pesanan</h6>
                <p class="mb-1 small">Total Item: <strong><?= $totalItems; ?></strong></p>
                <p class="mb-0 small">Metode Pembayaran: <strong><?= htmlspecialchars($transaksi['payment_method']); ?></strong></p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="bg-light p-3 rounded">
                <h6 class="mb-2">Catatan</h6>
                <p class="mb-0 small text-muted">
                  Pesanan Anda akan segera diproses. 
                  Estimasi pengiriman 1-2 hari kerja.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer bg-light text-center py-3">
          <small class="text-muted">
            Terima kasih telah berbelanja di SAYUR MAYUR<br>
            Untuk pertanyaan, hubungi kami di: info@sayurmayur.com
          </small>
        </div>
      </div>

      <!-- Action Buttons (Screen Only) -->
      <div class="d-flex gap-2 mt-4 justify-content-center flex-wrap d-print-none">
        <a href="products.php" class="btn btn-success btn-lg">
          <i class="bi bi-arrow-left"></i> Lanjut Belanja
        </a>
        <a href="cetak-invoice.php?id=<?= $transaksi['id']; ?>" target="_blank" class="btn btn-primary btn-lg">
          <i class="bi bi-printer"></i> Cetak Struk
        </a>
      </div>
    </div>
  </div>
</div>

<style>
  /* Print Styles - Optional untuk cetak invoice jika diperlukan */
  @media print {
    .d-print-none {
      display: none !important;
    }
    
    body {
      font-size: 12px;
    }
  }
</style>

<script>
function shareInvoice() {
  const invoiceUrl = window.location.href;
  const invoiceText = `Invoice #INV-<?= str_pad($transaksi['id'], 5, '0', STR_PAD_LEFT); ?> - SAYUR MAYUR`;
  
  if (navigator.share) {
    navigator.share({
      title: invoiceText,
      text: 'Pesanan saya di SAYUR MAYUR',
      url: invoiceUrl
    }).catch(err => console.log('Error sharing:', err));
  } else {
    // Fallback: copy to clipboard
    navigator.clipboard.writeText(invoiceUrl).then(() => {
      showToast('Link invoice disalin ke clipboard!', 'success');
    });
  }
}
</script>

<?php include 'includes/footer.php'; ?>
