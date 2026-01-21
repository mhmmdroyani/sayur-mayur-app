<?php
/**
 * Admin Password Reset Script
 * Gunakan script ini untuk reset password admin jika lupa
 * 
 * Akses: http://localhost/sayur_mayur_app/admin/reset_password.php
 */

include '../config/koneksi.php';

// Pastikan connection berhasil
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = '';
$error = '';

if (isset($_POST['reset'])) {
    $username = 'admin';
    $new_password = 'admin123'; // Password default
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 10]);

    // Update password
    $stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $username);

    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Password admin berhasil direset!<br>
                   <strong>Username:</strong> admin<br>
                   <strong>Password:</strong> admin123";
    } else {
        $error = "❌ Gagal mereset password: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white text-center py-4">
                        <h4 class="mb-0">🔐 Reset Password Admin</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-success" role="alert">
                                <?= $message; ?>
                            </div>
                            <div class="alert alert-info">
                                <strong>ℹ️ Catatan:</strong><br>
                                Setelah reset, silakan <a href="login.php">login</a> dengan:
                                <ul class="mt-2">
                                    <li>Username: <strong>admin</strong></li>
                                    <li>Password: <strong>admin123</strong></li>
                                </ul>
                            </div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="mt-4">
                            <div class="alert alert-warning">
                                <strong>⚠️ Perhatian:</strong><br>
                                Klik tombol di bawah untuk reset password admin ke default (admin123)
                            </div>
                            
                            <button type="submit" name="reset" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-arrow-clockwise"></i> Reset Password Sekarang
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="alert alert-info">
                            <strong>💡 Tips:</strong><br>
                            <small>
                                - Gunakan script ini jika lupa password admin<br>
                                - Setelah berhasil, gunakan username: <strong>admin</strong> password: <strong>admin123</strong><br>
                                - Kemudian ubah password di dashboard admin untuk keamanan lebih baik
                            </small>
                        </div>

                        <a href="../index.php" class="btn btn-outline-secondary w-100 mt-2">
                            ← Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
