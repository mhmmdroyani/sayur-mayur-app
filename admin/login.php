<?php
session_start();
include '../config/koneksi.php';
include '../config/functions.php';

// Redirect jika sudah login
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if (isset($_POST['login'])) {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token keamanan tidak valid';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];

        // Validasi input
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi';
        } else {
            // Prepared statement untuk keamanan
            $stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);

            if ($admin && password_verify($password, $admin['password'])) {
                // Regenerate session ID untuk keamanan
                session_regenerate_id(true);
                
                $_SESSION['admin'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['login_time'] = time();
                
                setFlashMessage('success', 'Login berhasil! Selamat datang, ' . $admin['username']);
                redirect('index.php');
            } else {
                $error = 'Username atau password salah';
                
                // Log failed attempts (optional)
                // You can implement this later
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin - SAYUR MAYUR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  
  <style>
    body {
      background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .login-header {
      background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
      color: white;
      border-radius: 15px 15px 0 0;
      padding: 2rem;
      text-align: center;
    }
    
    .login-header i {
      font-size: 3rem;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card login-card">
        <div class="login-header">
          <i class="bi bi-shield-lock-fill"></i>
          <h4 class="mb-0">Login Admin</h4>
          <p class="mb-0 small">SAYUR MAYUR</p>
        </div>
        
        <div class="card-body p-4">
          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
              <i class="bi bi-exclamation-triangle"></i> <?= $error; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form method="post" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
            
            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="bi bi-person"></i> Username
              </label>
              <input type="text" name="username" class="form-control form-control-lg" 
                     placeholder="Masukkan username" required autofocus>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">
                <i class="bi bi-lock"></i> Password
              </label>
              <div class="input-group">
                <input type="password" name="password" id="password" 
                       class="form-control form-control-lg" 
                       placeholder="Masukkan password" required>
                <button class="btn btn-outline-secondary" type="button" 
                        onclick="togglePassword()">
                  <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
              </div>
            </div>

            <div class="d-grid mb-3">
              <button name="login" class="btn btn-success btn-lg">
                <i class="bi bi-box-arrow-in-right"></i> Login
              </button>
            </div>
            
            <div class="text-center">
              <a href="../index.php" class="text-muted small">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
              </a>
            </div>
          </form>
        </div>
      </div>
      
      <div class="text-center mt-3 text-white">
        <small>&copy; <?= date('Y'); ?> SAYUR MAYUR. All rights reserved.</small>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('toggleIcon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.className = 'bi bi-eye-slash';
  } else {
    passwordInput.type = 'password';
    toggleIcon.className = 'bi bi-eye';
  }
}

// Add form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const username = this.username.value.trim();
  const password = this.password.value;
  
  if (username.length < 3) {
    e.preventDefault();
    alert('Username harus minimal 3 karakter');
    return false;
  }
  
  if (password.length < 4) {
    e.preventDefault();
    alert('Password harus minimal 4 karakter');
    return false;
  }
});
</script>

</body>
</html>
