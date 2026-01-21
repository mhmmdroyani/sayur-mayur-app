<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int)($_GET['id'] ?? 0);
$error_msg = $success_msg = '';
$item = null;

if ($id <= 0) {
    header("Location: " . route('ongkos-kirim.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM ongkos_kirim WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    header("Location: " . route('ongkos-kirim.index'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasi = sanitize($_POST['lokasi'] ?? '');
    $biaya = (float)($_POST['biaya'] ?? 0);
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $estimasi_hari = (int)($_POST['estimasi_hari'] ?? 1);

    if (empty($lokasi) || $biaya <= 0) {
        $error_msg = "Lokasi dan biaya harus diisi dengan benar!";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE ongkos_kirim SET lokasi = ?, biaya = ?, deskripsi = ?, estimasi_hari = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sdsii", $lokasi, $biaya, $deskripsi, $estimasi_hari, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Ongkos kirim berhasil diperbarui!";
            header("Location: " . route('ongkos-kirim.index'));
            exit;
        } else {
            $error_msg = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Ongkos Kirim - Admin SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); min-height: 100vh; }
    .admin-main { min-height: 100vh; display: flex; flex-direction: column; }
    .admin-content { padding: 30px 40px; display: flex; flex-direction: column; flex: 1; width: 100%; }
    .page-header { background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border-radius: 16px; padding: 40px; margin-bottom: 40px; border: 1px solid #dcfce7; box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1); position: relative; overflow: hidden; }
    .page-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%); border-radius: 50%; }
    .page-header-content { position: relative; z-index: 1; }
    .page-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-header-title { display: flex; align-items: center; gap: 14px; }
    .page-header-title h1 { font-size: 2.5rem; font-weight: 900; color: #16a34a; margin: 0; }
    .page-header-title i { font-size: 3rem; color: #22c55e; }
    .page-breadcrumb { display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.9rem; }
    .page-breadcrumb a { color: #16a34a; text-decoration: none; font-weight: 600; }
    .page-breadcrumb span { color: #cbd5e1; }
    .back-btn-header { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; }
    .form-card { background: white; border-radius: 16px; padding: 50px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb; max-width: 650px; width: 100%; margin: 0 auto; }
    .form-group { margin-bottom: 28px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 28px; }
    .form-label { font-weight: 700; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; }
    .form-label-required { color: #ef4444; font-weight: 900; }
    .form-input, .form-textarea { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; font-family: inherit; background: #fafafa; color: #1e293b; }
    .form-input:focus, .form-textarea:focus { border-color: #22c55e; outline: none; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15); background: white; }
    .form-textarea { resize: vertical; min-height: 110px; line-height: 1.6; }
    .form-actions { display: flex; gap: 16px; margin-top: 45px; padding-top: 35px; border-top: 2px solid #f1f5f9; }
    .btn-submit { flex: 1; padding: 16px 32px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-submit:hover { transform: translateY(-3px); }
    .btn-cancel { flex: 1; padding: 16px 32px; background: #f3f4f6; color: #1e293b; border: 2px solid #e5e7eb; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
    .btn-cancel:hover { background: #e5e7eb; }
    .error-message { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-color: #fca5a5; color: #dc2626; border: 1px solid #fca5a5; border-radius: 12px; padding: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 16px; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="admin-main">
      <div class="admin-content">
        <div class="page-header">
          <div class="page-header-content">
            <div class="page-header-top">
              <div class="page-header-title">
                <i class="bi bi-pencil-square"></i>
                <h1>Edit Ongkos Kirim</h1>
              </div>
              <a href="<?= route('ongkos-kirim.index') ?>" class="back-btn-header">
                <i class="bi bi-arrow-left"></i> Kembali
              </a>
            </div>
            <nav class="page-breadcrumb">
              <a href="<?= route('dashboard') ?>"><i class="bi bi-house-door"></i> Home</a>
              <span>/</span>
              <a href="<?= route('ongkos-kirim.index') ?>">Ongkos Kirim</a>
              <span>/</span>
              <span>Edit</span>
            </nav>
          </div>
        </div>

        <?php if (!empty($error_msg)): ?>
          <div class="error-message">
            <i class="bi bi-exclamation-circle-fill"></i>
            <p><?= htmlspecialchars($error_msg); ?></p>
          </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; width: 100%;">
          <div class="form-card">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Lokasi / Kecamatan <span class="form-label-required">*</span></label>
                <input type="text" name="lokasi" class="form-input" value="<?= htmlspecialchars($item['lokasi']); ?>" required>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Biaya Kirim (Rp) <span class="form-label-required">*</span></label>
                  <input type="number" name="biaya" class="form-input" value="<?= $item['biaya']; ?>" step="1000" min="0" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Estimasi Hari <span class="form-label-required">*</span></label>
                  <input type="number" name="estimasi_hari" class="form-input" value="<?= $item['estimasi_hari']; ?>" min="1" required>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" class="form-textarea"><?= htmlspecialchars($item['deskripsi'] ?? ''); ?></textarea>
              </div>

              <div class="form-actions">
                <a href="<?= route('ongkos-kirim.index') ?>" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit">
                  <i class="bi bi-check-circle"></i> Perbarui Ongkos Kirim
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
