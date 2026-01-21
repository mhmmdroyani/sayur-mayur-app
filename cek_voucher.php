<?php
session_start();
include 'config/koneksi.php';

header('Content-Type: application/json');

$kode = strtoupper(trim($_POST['kode'] ?? ''));
$subtotal = floatval($_POST['subtotal'] ?? 0);

if (empty($kode)) {
  echo json_encode(['status' => 'error', 'message' => 'Kode voucher tidak boleh kosong']);
  exit;
}

// Cek voucher
$stmt = mysqli_prepare($conn, "SELECT * FROM voucher WHERE kode = ? AND aktif = 1");
mysqli_stmt_bind_param($stmt, "s", $kode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$voucher = mysqli_fetch_assoc($result);

if (!$voucher) {
  echo json_encode(['status' => 'error', 'message' => 'Kode voucher tidak valid atau sudah tidak aktif']);
  exit;
}

// Cek tanggal
$today = date('Y-m-d');
if ($today < $voucher['tanggal_mulai'] || $today > $voucher['tanggal_selesai']) {
  echo json_encode(['status' => 'error', 'message' => 'Voucher sudah kadaluarsa atau belum aktif']);
  exit;
}

// Cek kuota
if ($voucher['kuota'] !== null && $voucher['terpakai'] >= $voucher['kuota']) {
  echo json_encode(['status' => 'error', 'message' => 'Kuota voucher sudah habis']);
  exit;
}

// Cek minimum pembelian
if ($subtotal < $voucher['min_pembelian']) {
  echo json_encode([
    'status' => 'error', 
    'message' => 'Minimum pembelian Rp ' . number_format($voucher['min_pembelian'], 0, ',', '.')
  ]);
  exit;
}

// Hitung diskon
$diskon = 0;
if ($voucher['tipe'] == 'persen') {
  $diskon = ($subtotal * $voucher['nilai']) / 100;
  // Cek max diskon
  if ($voucher['max_diskon'] !== null && $diskon > $voucher['max_diskon']) {
    $diskon = $voucher['max_diskon'];
  }
} else {
  $diskon = $voucher['nilai'];
}

// Diskon tidak boleh lebih dari subtotal
if ($diskon > $subtotal) {
  $diskon = $subtotal;
}

$total = $subtotal - $diskon;

echo json_encode([
  'status' => 'success',
  'message' => 'Voucher berhasil digunakan!',
  'voucher' => [
    'kode' => $voucher['kode'],
    'nama' => $voucher['nama'],
    'tipe' => $voucher['tipe'],
    'nilai' => $voucher['nilai']
  ],
  'diskon' => $diskon,
  'total' => $total
]);
?>
