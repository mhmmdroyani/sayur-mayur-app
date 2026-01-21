<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$id = (int)($_GET['id'] ?? 0);
$error_msg = '';
$item = null;

if ($id <= 0) {
    header("Location: " . route('voucher.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM voucher WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    header("Location: " . route('voucher.index'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode = strtoupper(sanitize($_POST['kode'] ?? ''));
    $nama = sanitize($_POST['nama'] ?? '');
    $tipe = sanitize($_POST['tipe'] ?? 'persen');
    $nilai = (float)($_POST['nilai'] ?? 0);
    $min_pembelian = (float)($_POST['min_pembelian'] ?? 0);
    $max_diskon = (float)($_POST['max_diskon'] ?? 0);
    $tanggal_mulai = sanitize($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = sanitize($_POST['tanggal_selesai'] ?? '');
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if (empty($kode) || empty($nama) || $nilai <= 0) {
        $error_msg = "Semua field wajib diisi dengan benar!";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE voucher SET kode = ?, nama = ?, tipe = ?, nilai = ?, min_pembelian = ?, max_diskon = ?, tanggal_mulai = ?, tanggal_selesai = ?, aktif = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssdddssii", $kode, $nama, $tipe, $nilai, $min_pembelian, $max_diskon, $tanggal_mulai, $tanggal_selesai, $aktif, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Voucher berhasil diperbarui!";
            header("Location: " . route('voucher.index'));
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
  <title>Edit Voucher - Admin SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI'; background: linear-gradient(135deg, #f5f3ff 0%, #f8fafc 100%); min-height: 100vh; }
    .admin-main { min-height: 100vh; display: flex; flex-direction: column; }
    .admin-content { padding: 30px 40px; display: flex; flex-direction: column; flex: 1; width: 100%; }
    .page-header { background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%); border-radius: 16px; padding: 40px; margin-bottom: 40px; border: 1px solid #e9d5ff; }
    .page-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-header-title { display: flex; align-items: center; gap: 14px; }
    .page-header-title h1 { font-size: 2.5rem; font-weight: 900; color: #6d28d9; margin: 0; }
    .back-btn-header { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; }
    .form-card { background: white; border-radius: 16px; padding: 50px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); max-width: 750px; width: 100%; margin: 0 auto; }
    .form-group { margin-bottom: 28px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 28px; }
    .form-label { font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .form-label-required { color: #ef4444; }
    .form-input, .form-select { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; background: #fafafa; }
    .form-input:focus, .form-select:focus { border-color: #8b5cf6; outline: none; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15); background: white; }
    .form-group-checkbox { display: flex; align-items: center; gap: 12px; margin-top: 20px; }
    .form-checkbox { width: 20px; height: 20px; accent-color: #8b5cf6; }
    .form-checkbox-label { font-weight: 600; color: #1e293b; cursor: pointer; }
    .form-actions { display: flex; gap: 16px; margin-top: 45px; padding-top: 35px; border-top: 2px solid #f1f5f9; }
    .btn-submit { flex: 1; padding: 16px 32px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-cancel { flex: 1; padding: 16px 32px; background: #f3f4f6; color: #1e293b; border: 2px solid #e5e7eb; border-radius: 12px; font-weight: 700; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
    .error-message { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #fca5a5; border-radius: 12px; padding: 20px; margin-bottom: 30px; color: #dc2626; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="admin-main">
      <div class="admin-content">
        <div class="page-header">
          <div class="page-header-top">
            <div class="page-header-title">
              <i class="bi bi-pencil-square"></i>
              <h1>Edit Voucher</h1>
            </div>
            <a href="<?= route('voucher.index') ?>" class="back-btn-header">
              <i class="bi bi-arrow-left"></i> Kembali
            </a>
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
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Kode Voucher <span class="form-label-required">*</span></label>
                  <input type="text" name="kode" class="form-input" value="<?= htmlspecialchars($item['kode']); ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Nama Voucher <span class="form-label-required">*</span></label>
                  <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($item['nama']); ?>" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Tipe Diskon <span class="form-label-required">*</span></label>
                  <select name="tipe" class="form-select" required>
                    <option value="persen" <?= $item['tipe'] == 'persen' ? 'selected' : ''; ?>>Persen (%)</option>
                    <option value="nominal" <?= $item['tipe'] == 'nominal' ? 'selected' : ''; ?>>Nominal (Rp)</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="form-label">Nilai Diskon <span class="form-label-required">*</span></label>
                  <input type="number" name="nilai" class="form-input" value="<?= $item['nilai']; ?>" step="0.01" min="0" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Min Pembelian (Rp)</label>
                  <input type="number" name="min_pembelian" class="form-input" value="<?= $item['min_pembelian']; ?>" step="1000" min="0">
                </div>

                <div class="form-group">
                  <label class="form-label">Max Diskon (Rp)</label>
                  <input type="number" name="max_diskon" class="form-input" value="<?= $item['max_diskon']; ?>" step="1000" min="0">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Tanggal Mulai <span class="form-label-required">*</span></label>
                  <input type="datetime-local" name="tanggal_mulai" class="form-input" value="<?= str_replace(' ', 'T', $item['tanggal_mulai']); ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Tanggal Selesai <span class="form-label-required">*</span></label>
                  <input type="datetime-local" name="tanggal_selesai" class="form-input" value="<?= str_replace(' ', 'T', $item['tanggal_selesai']); ?>" required>
                </div>
              </div>

              <div class="form-group-checkbox">
                <input type="checkbox" id="aktif" name="aktif" class="form-checkbox" <?= $item['aktif'] == 1 ? 'checked' : ''; ?>>
                <label for="aktif" class="form-checkbox-label">Aktifkan Voucher</label>
              </div>

              <div class="form-actions">
                <a href="<?= route('voucher.index') ?>" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit">
                  <i class="bi bi-check-circle"></i> Perbarui Voucher
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
