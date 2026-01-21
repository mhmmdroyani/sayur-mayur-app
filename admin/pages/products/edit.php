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
    header("Location: " . route('products.index'));
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM produk WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$item) {
    header("Location: " . route('products.index'));
    exit;
}

$kategori_list = mysqli_query($conn, "SELECT id, nama FROM kategori ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $kategori = (int)($_POST['kategori'] ?? 0);
    $berat = (float)($_POST['berat'] ?? 0);
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $gambar = $item['gambar'];

    if (empty($nama) || $harga <= 0 || $stock < 0 || $kategori <= 0) {
        $error_msg = "Semua field wajib diisi dengan benar!";
    } else {
        if ($_FILES['gambar']['size'] > 0) {
            $upload_dir = '../../../../assets/img/';
            $file_ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($file_ext), $allowed)) {
                if (!empty($item['gambar']) && file_exists($upload_dir . $item['gambar'])) {
                    @unlink($upload_dir . $item['gambar']);
                }
                $gambar = 'prod_' . time() . '.' . $file_ext;
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar)) {
                    $error_msg = "Gagal upload gambar!";
                }
            } else {
                $error_msg = "Format gambar tidak didukung!";
            }
        }

        if (empty($error_msg)) {
            $stmt = mysqli_prepare($conn, "UPDATE produk SET nama = ?, harga = ?, stock = ?, kategori = ?, berat = ?, gambar = ?, deskripsi = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sdiiidsi", $nama, $harga, $stock, $kategori, $berat, $gambar, $deskripsi, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_msg'] = "Produk berhasil diperbarui!";
                header("Location: " . route('products.index'));
                exit;
            } else {
                $error_msg = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Produk - Admin SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI'; background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); min-height: 100vh; }
    .admin-main { min-height: 100vh; display: flex; flex-direction: column; }
    .admin-content { padding: 30px 40px; display: flex; flex-direction: column; flex: 1; width: 100%; }
    .page-header { background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border-radius: 16px; padding: 40px; margin-bottom: 40px; border: 1px solid #dcfce7; }
    .page-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-header-title { display: flex; align-items: center; gap: 14px; }
    .page-header-title h1 { font-size: 2.5rem; font-weight: 900; color: #16a34a; margin: 0; }
    .back-btn-header { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; }
    .form-card { background: white; border-radius: 16px; padding: 50px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); max-width: 800px; width: 100%; margin: 0 auto; }
    .form-group { margin-bottom: 28px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 28px; }
    .form-label { font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .form-label-required { color: #ef4444; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; background: #fafafa; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #22c55e; outline: none; background: white; }
    .form-textarea { resize: vertical; min-height: 120px; }
    .file-input-wrapper { position: relative; overflow: hidden; }
    .file-input-label { display: block; padding: 16px; border: 2px dashed #cbd5e1; border-radius: 10px; text-align: center; cursor: pointer; background: #f8fafc; }
    .file-input-label:hover { background: #f1f5f9; border-color: #94a3b8; }
    .file-input-label i { font-size: 1.5rem; color: #64748b; display: block; margin-bottom: 8px; }
    .file-input-label p { margin: 0; color: #64748b; font-weight: 600; font-size: 0.9rem; }
    .image-preview { margin-top: 16px; text-align: center; }
    .image-preview img { max-width: 300px; max-height: 300px; border-radius: 10px; border: 2px solid #e5e7eb; }
    .current-image { margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 10px; }
    .current-image-label { font-weight: 600; color: #64748b; margin-bottom: 12px; display: block; }
    .current-image img { max-width: 200px; max-height: 200px; border-radius: 8px; }
    .form-actions { display: flex; gap: 16px; margin-top: 45px; padding-top: 35px; border-top: 2px solid #f1f5f9; }
    .btn-submit { flex: 1; padding: 16px 32px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
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
              <h1>Edit Produk</h1>
            </div>
            <a href="<?= route('products.index') ?>" class="back-btn-header">
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
            <form method="POST" enctype="multipart/form-data">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Nama Produk <span class="form-label-required">*</span></label>
                  <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($item['nama']); ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Kategori <span class="form-label-required">*</span></label>
                  <select name="kategori" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    mysqli_data_seek($kategori_list, 0);
                    while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                      <option value="<?= $kat['id']; ?>" <?= $item['kategori'] == $kat['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($kat['nama']); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Harga (Rp) <span class="form-label-required">*</span></label>
                  <input type="number" name="harga" class="form-input" value="<?= $item['harga']; ?>" step="100" min="0" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Stok <span class="form-label-required">*</span></label>
                  <input type="number" name="stock" class="form-input" value="<?= $item['stock']; ?>" min="0" required>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Berat (kg)</label>
                <input type="number" name="berat" class="form-input" value="<?= $item['berat']; ?>" step="0.1" min="0">
              </div>

              <div class="form-group">
                <label class="form-label">Gambar Produk</label>
                <?php if (!empty($item['gambar'])): ?>
                  <div class="current-image">
                    <span class="current-image-label"><i class="bi bi-image"></i> Gambar Saat Ini:</span>
                    <img src="../../../../assets/img/<?= htmlspecialchars($item['gambar']); ?>" alt="<?= htmlspecialchars($item['nama']); ?>">
                  </div>
                <?php endif; ?>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 12px;">Upload gambar baru untuk mengganti (opsional)</p>
                <div class="file-input-wrapper">
                  <label for="gambar" class="file-input-label">
                    <i class="bi bi-image"></i>
                    <p>Klik untuk upload atau drag file gambar di sini</p>
                    <small style="color: #94a3b8;">JPG, PNG, GIF, WebP (Max 5MB)</small>
                  </label>
                  <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;">
                </div>
                <div class="image-preview" id="imagePreview"></div>
              </div>

              <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-textarea"><?= htmlspecialchars($item['deskripsi'] ?? ''); ?></textarea>
              </div>

              <div class="form-actions">
                <a href="<?= route('products.index') ?>" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit">
                  <i class="bi bi-check-circle"></i> Perbarui Produk
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('gambar').addEventListener('change', function(e) {
      const preview = document.getElementById('imagePreview');
      const file = e.target.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
      } else {
        preview.innerHTML = '';
      }
    });
  </script>
</body>
</html>
