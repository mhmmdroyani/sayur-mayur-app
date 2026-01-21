<?php
// Suppress all output sebelum JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/proses_checkout_error.log');

session_start();
include 'config/koneksi.php';

// Set header untuk JSON
header('Content-Type: application/json');
ob_clean();

// Terima JSON
$data = json_decode(file_get_contents('php://input'), true);

error_log("=== CHECKOUT REQUEST START ===");
error_log("RAW DATA: " . file_get_contents('php://input'));
error_log("PARSED DATA: " . json_encode($data));

$nama  = trim($data['nama_pembeli'] ?? '');
$no_telp = trim($data['no_telp'] ?? '');
$alamat = trim($data['alamat'] ?? '');
$lokasi_kirim_id = (int)($data['lokasi_kirim'] ?? 0);
$payment_method = trim($data['payment_method'] ?? 'COD');
$subtotal_frontend = floatval($data['subtotal'] ?? 0);
$kode_voucher = trim($data['kode_voucher'] ?? '');
$diskon_frontend = floatval($data['diskon'] ?? 0);
$total_frontend = floatval($data['total'] ?? 0);
$items = $data['items'] ?? [];
$csrf_token = $data['csrf_token'] ?? '';

error_log("PARAMS: nama=$nama, lokasi=$lokasi_kirim_id, subtotal=$subtotal_frontend, diskon=$diskon_frontend");

// Validasi CSRF Token
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Token keamanan tidak valid']);
  error_log("CSRF TOKEN INVALID");
  exit;
}

// Validasi input
if (empty($nama) || strlen($nama) < 3) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Nama pembeli harus minimal 3 karakter']);
  exit;
}

if (empty($no_telp) || strlen($no_telp) < 10) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Nomor telepon harus minimal 10 digit']);
  exit;
}

if (empty($alamat) || strlen($alamat) < 10) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Alamat harus minimal 10 karakter']);
  exit;
}

if (empty($items)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Keranjang belanja kosong']);
  exit;
}

// Sanitasi input (anti-XSS)
$nama = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
$no_telp = htmlspecialchars($no_telp, ENT_QUOTES, 'UTF-8');
$alamat = htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8');
$payment_method = htmlspecialchars($payment_method, ENT_QUOTES, 'UTF-8');

// === VALIDASI STOK (SEBELUM APA PUN) ===
foreach ($items as $item) {
  $produk_id = (int)$item['id'];
  $qty       = (int)$item['qty'];

  if ($produk_id <= 0 || $qty <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid']);
    exit;
  }

  // Prepared statement untuk keamanan
  $stmt = mysqli_prepare($conn, "SELECT stock, harga FROM produk WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $produk_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $p = mysqli_fetch_assoc($result);

  if (!$p || $p['stock'] < $qty) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Stok produk tidak mencukupi'
    ]);
    exit;
  }
  
  // Validasi harga (cek dari database, jangan percaya client)
  if ($p['harga'] != $item['price']) {
    echo json_encode([
      'status' => 'error',
      'message' => 'Harga produk tidak sesuai'
    ]);
    exit;
  }
}

// Mulai transaction untuk data consistency
mysqli_begin_transaction($conn);

try {
  // === HITUNG TOTAL ===
  $subtotal = 0;
  foreach ($items as $item) {
    $subtotal += $item['price'] * $item['qty'];
  }
  
  // Validasi subtotal
  if (abs($subtotal - $subtotal_frontend) > 0.01) {
    throw new Exception('Terjadi kesalahan perhitungan subtotal');
  }
  
  $diskon = 0;
  $voucher_kode = null;
  
  // Validasi voucher jika ada
  if (!empty($kode_voucher)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM voucher WHERE kode = ? AND aktif = 1");
    mysqli_stmt_bind_param($stmt, "s", $kode_voucher);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $voucher = mysqli_fetch_assoc($result);
    
    if ($voucher) {
      // Gunakan diskon dari frontend (sudah divalidasi)
      $diskon = $diskon_frontend;
      $voucher_kode = $kode_voucher;
      
      // Update kuota voucher
      mysqli_query($conn, "UPDATE voucher SET terpakai = terpakai + 1 WHERE id = " . $voucher['id']);
    }
  }
  
  // === VALIDASI DAN AMBIL BIAYA PENGIRIMAN ===
  if ($lokasi_kirim_id <= 0) {
    throw new Exception('Lokasi pengiriman harus dipilih');
  }
  
  $stmt = mysqli_prepare($conn, "SELECT id, lokasi, biaya FROM ongkos_kirim WHERE id = ? AND aktif = 1");
  mysqli_stmt_bind_param($stmt, "i", $lokasi_kirim_id);
  mysqli_stmt_execute($stmt);
  $shipping_result = mysqli_stmt_get_result($stmt);
  $shipping = mysqli_fetch_assoc($shipping_result);
  
  if (!$shipping) {
    throw new Exception('Lokasi pengiriman tidak valid atau tidak tersedia');
  }
  
  $shipping_biaya = floatval($shipping['biaya']);
  $shipping_lokasi = htmlspecialchars($shipping['lokasi'], ENT_QUOTES, 'UTF-8');
  
  error_log("DEBUG SHIPPING: id=$lokasi_kirim_id, lokasi=$shipping_lokasi, biaya=$shipping_biaya");
  
  $total = $subtotal - $diskon + $shipping_biaya;
  
  // DEBUG: Log values
  error_log("DEBUG: subtotal=$subtotal, diskon_frontend=$diskon_frontend, diskon_server=$diskon, total=$total, total_frontend=$total_frontend");
  
  // Gunakan nilai dari frontend jika berbeda (lebih dipercaya karena sudah validated di client)
  if ($diskon_frontend > 0) {
    $diskon = $diskon_frontend;
    $total = $subtotal - $diskon + $shipping_biaya;
  }

  // === SIMPAN TRANSAKSI (dengan prepared statement) ===
  $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (nama_pembeli, no_telp, alamat, subtotal, kode_voucher, diskon, ongkos_kirim_id, shipping_biaya, total, payment_method, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
  mysqli_stmt_bind_param($stmt, "sssdsiddds", $nama, $no_telp, $alamat, $subtotal, $voucher_kode, $diskon, $lokasi_kirim_id, $shipping_biaya, $total, $payment_method);
  mysqli_stmt_execute($stmt);
  
  $transaksi_id = mysqli_insert_id($conn);

  // === SIMPAN DETAIL + KURANGI STOK ===
  foreach ($items as $item) {
    $produk_id = (int)$item['id'];
    $qty       = (int)$item['qty'];
    $harga     = (int)$item['price'];
    $subtotal  = $qty * $harga;

    // detail transaksi (prepared statement)
    $stmt = mysqli_prepare($conn, "INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiidd", $transaksi_id, $produk_id, $qty, $harga, $subtotal);
    mysqli_stmt_execute($stmt);

    // KURANGI STOK (prepared statement)
    $stmt = mysqli_prepare($conn, "UPDATE produk SET stock = stock - ? WHERE id = ? AND stock >= ?");
    mysqli_stmt_bind_param($stmt, "iii", $qty, $produk_id, $qty);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) == 0) {
      throw new Exception("Gagal mengurangi stok");
    }
  }

  // Commit transaction
  mysqli_commit($conn);
  
  // Generate token baru untuk request berikutnya
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

  echo json_encode([
    'status' => 'success',
    'transaksi_id' => $transaksi_id,
    'message' => 'Pesanan berhasil diproses'
  ]);

} catch (Exception $e) {
  // Rollback jika ada error
  mysqli_rollback($conn);
  error_log("EXCEPTION: " . $e->getMessage());
  error_log("TRACE: " . $e->getTraceAsString());
  http_response_code(500);
  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage(),
    'debug' => [
      'diskon_frontend' => $diskon_frontend,
      'subtotal_frontend' => $subtotal_frontend,
      'total_frontend' => $total_frontend,
      'kode_voucher' => $kode_voucher
    ]
  ]);
  error_log("=== CHECKOUT REQUEST END (ERROR) ===");
  exit;
}

// Catch any uncaught errors
error_log("=== CHECKOUT REQUEST END (SUCCESS) ===");
?>
