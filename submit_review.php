<?php
include 'config/koneksi.php';
include 'config/functions.php';

header('Content-Type: application/json');

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validasi dan sanitasi input
$produk_id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
$nama_reviewer = isset($_POST['nama_reviewer']) ? sanitize($_POST['nama_reviewer']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$komentar = isset($_POST['komentar']) ? sanitize($_POST['komentar']) : '';

// Validasi data
if ($produk_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak valid']);
    exit;
}

if (empty($nama_reviewer) || strlen($nama_reviewer) < 3) {
    echo json_encode(['success' => false, 'message' => 'Nama harus minimal 3 karakter']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
    exit;
}

if (empty($komentar) || strlen($komentar) < 10) {
    echo json_encode(['success' => false, 'message' => 'Komentar harus minimal 10 karakter']);
    exit;
}

// Limit komentar max 500 karakter
if (strlen($komentar) > 500) {
    echo json_encode(['success' => false, 'message' => 'Komentar maksimal 500 karakter']);
    exit;
}

// Cek produk ada atau tidak
$produk_check = mysqli_query($conn, "SELECT id FROM produk WHERE id = $produk_id");
if (mysqli_num_rows($produk_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit;
}

// Cek duplicate review dari nama yang sama untuk produk yang sama (rate limit)
$duplicate_check = mysqli_query($conn, 
    "SELECT id FROM review 
     WHERE produk_id = $produk_id 
     AND LOWER(nama_reviewer) = LOWER('$nama_reviewer')
     AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");

if (mysqli_num_rows($duplicate_check) > 0) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah memberi review untuk produk ini dalam 1 jam terakhir']);
    exit;
}

// Insert review
$stmt = mysqli_prepare($conn, 
    "INSERT INTO review (produk_id, nama_reviewer, rating, komentar) 
     VALUES (?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt, "isss", $produk_id, $nama_reviewer, $rating, $komentar);

if (mysqli_stmt_execute($stmt)) {
    // Ambil review yang baru dibuat
    $review_id = mysqli_insert_id($conn);
    $review = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT * FROM review WHERE id = $review_id"));
    
    echo json_encode([
        'success' => true,
        'message' => 'Review berhasil ditambahkan!',
        'review' => [
            'id' => $review['id'],
            'nama_reviewer' => $review['nama_reviewer'],
            'rating' => $review['rating'],
            'komentar' => $review['komentar'],
            'created_at' => date('d M Y H:i', strtotime($review['created_at']))
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
