<?php
header('Content-Type: application/json');
include '../config/koneksi.php';

$query = "SELECT id, lokasi, biaya, estimasi_hari FROM ongkos_kirim WHERE aktif = 1 ORDER BY lokasi ASC";
$result = mysqli_query($conn, $query);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);
?>
