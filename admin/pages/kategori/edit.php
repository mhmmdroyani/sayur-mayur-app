<?php
session_start();
include '../../auth.php';
include '../../../config/koneksi.php';
include '../../../config/functions.php';
require_once '../../../helpers/RouteHelper.php';

$error_msg = '';
$edit_data = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM kategori WHERE id = $id");
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: " . route('kategori.index'));
        exit;
    }
    
    $edit_data = mysqli_fetch_assoc($result);
} else {
    header("Location: " . route('kategori.index'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $nama = sanitize($_POST['nama'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $icon = sanitize($_POST['icon'] ?? '');

    if (empty($nama)) {
        $error_msg = "Nama kategori harus diisi!";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM kategori WHERE nama = '$nama' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $error_msg = "Nama kategori '$nama' sudah digunakan!";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE kategori SET nama = ?, deskripsi = ?, icon = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $deskripsi, $icon, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_msg'] = "Kategori berhasil diupdate!";
                header("Location: " . route('kategori.index'));
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
  <title>Edit Kategori - Admin SAYUR MAYUR</title>
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
    }

    .admin-main {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .admin-content {
      padding: 30px 40px;
      display: flex;
      flex-direction: column;
      flex: 1;
      width: 100%;
    }

    .page-header {
      background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
      border-radius: 16px;
      padding: 40px;
      margin-bottom: 40px;
      border: 1px solid #dcfce7;
      box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
      position: relative;
      overflow: hidden;
    }

    .page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .page-header-content {
      position: relative;
      z-index: 1;
    }

    .page-header-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .page-header-title {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .page-header-title h1 {
      font-size: 2.5rem;
      font-weight: 900;
      color: #16a34a;
      margin: 0;
      letter-spacing: -0.5px;
    }

    .page-header-title i {
      font-size: 3rem;
      color: #22c55e;
    }

    .page-breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #64748b;
      font-size: 0.9rem;
    }

    .page-breadcrumb a {
      color: #16a34a;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .page-breadcrumb a:hover {
      color: #22c55e;
      text-decoration: underline;
    }

    .page-breadcrumb span {
      color: #cbd5e1;
    }

    .back-btn-header {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      border: 2px solid #22c55e;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      box-shadow: 0 4px 12px rgba(34, 197, 94, 0.25);
    }

    .back-btn-header:hover {
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
      border-color: #15803d;
      color: white;
      transform: translateX(-4px);
      box-shadow: 0 6px 16px rgba(34, 197, 94, 0.35);
    }

    .form-card {
      background: white;
      border-radius: 16px;
      padding: 50px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      border: 1px solid #e5e7eb;
      max-width: 600px;
      width: 100%;
      margin: 0 auto;
    }

    .form-group {
      margin-bottom: 28px;
    }

    .form-label {
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.95rem;
      letter-spacing: 0.3px;
    }

    .form-label-required {
      color: #ef4444;
      font-weight: 900;
    }

    .form-input, .form-textarea, .form-select {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 0.95rem;
      font-family: inherit;
      background: #fafafa;
      color: #1e293b;
      transition: all 0.3s ease;
    }

    .form-input::placeholder, .form-textarea::placeholder {
      color: #94a3b8;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
      border-color: #22c55e;
      outline: none;
      box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
      background: white;
    }

    .form-textarea {
      resize: vertical;
      min-height: 120px;
      line-height: 1.6;
    }

    .form-helper-text {
      font-size: 0.85rem;
      color: #64748b;
      margin-top: 6px;
    }

    .form-actions {
      display: flex;
      gap: 16px;
      margin-top: 45px;
      padding-top: 35px;
      border-top: 2px solid #f1f5f9;
    }

    .btn-submit {
      flex: 1;
      padding: 16px 32px;
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 8px 20px rgba(34, 197, 94, 0.35);
    }

    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(34, 197, 94, 0.45);
      background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }

    .btn-submit:active {
      transform: translateY(-1px);
    }

    .btn-cancel {
      flex: 1;
      padding: 16px 32px;
      background: #f3f4f6;
      color: #1e293b;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      background: #e5e7eb;
      border-color: #d1d5db;
      text-decoration: none;
      color: #0f172a;
    }

    .info-message {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 1px solid #bbf7d0;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 16px;
      color: #166534;
    }

    .info-message i {
      font-size: 1.4rem;
      color: #22c55e;
      flex-shrink: 0;
    }

    .info-message p {
      margin: 0;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    .error-message {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      border-color: #fca5a5;
      color: #dc2626;
    }

    .error-message i {
      color: #ef4444;
    }

    @media (max-width: 768px) {
      .admin-content {
        padding: 20px;
      }

      .page-header {
        padding: 24px;
        margin-bottom: 24px;
      }

      .page-header-title h1 {
        font-size: 1.8rem;
      }

      .page-header-title i {
        font-size: 2.2rem;
      }

      .form-card {
        padding: 24px;
      }

      .form-actions {
        flex-direction: column;
      }

      .btn-submit, .btn-cancel {
        width: 100%;
      }
    }
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
                <h1>Edit Kategori</h1>
              </div>
              <a href="<?= route('kategori.index') ?>" class="back-btn-header">
                <i class="bi bi-arrow-left"></i> Kembali
              </a>
            </div>
            <nav class="page-breadcrumb">
              <a href="<?= route('dashboard') ?>">
                <i class="bi bi-house-door"></i> Home
              </a>
              <span>/</span>
              <span>Master Data</span>
              <span>/</span>
              <a href="<?= route('kategori.index') ?>">Kategori</a>
              <span>/</span>
              <span>Edit Kategori</span>
            </nav>
          </div>
        </div>

        <div class="info-message">
          <i class="bi bi-info-circle-fill"></i>
          <p>Perbarui informasi kategori dan simpan perubahan.</p>
        </div>

        <?php if (!empty($error_msg)): ?>
          <div class="info-message error-message">
            <i class="bi bi-exclamation-circle-fill"></i>
            <p><?= htmlspecialchars($error_msg); ?></p>
          </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; width: 100%;">
          <div class="form-card">
            <form method="POST" class="category-form">
              <input type="hidden" name="action" value="edit">

              <div class="form-group">
                <label class="form-label">Nama Kategori <span class="form-label-required">*</span></label>
                <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($edit_data['nama']); ?>" placeholder="Contoh: Sayuran" required>
              </div>

              <div class="form-group">
                <label class="form-label">Icon Bootstrap</label>
                <input type="text" name="icon" class="form-input" value="<?= htmlspecialchars($edit_data['icon'] ?? ''); ?>" placeholder="Contoh: bi-leaf">
                <div class="form-helper-text">
                  Lihat ikon di <a href="https://icons.getbootstrap.com" target="_blank" style="color: #22c55e; font-weight: 600;">Bootstrap Icons</a>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-textarea" placeholder="Jelaskan kategori ini..."><?= htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
              </div>

              <div class="form-actions">
                <a href="<?= route('kategori.index') ?>" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-submit">
                  <i class="bi bi-check-circle"></i> Update Kategori
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
