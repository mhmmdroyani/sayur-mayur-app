<?php 
session_start();
include 'config/koneksi.php';
include 'config/functions.php';
include 'includes/header.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $telepon = sanitize($_POST['telepon'] ?? '');
    $subjek = sanitize($_POST['subjek']);
    $pesan = sanitize($_POST['pesan']);

    // Validation
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $error = 'Semua field wajib diisi (kecuali telepon)';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid';
    } elseif (strlen($pesan) < 10) {
        $error = 'Pesan minimal 10 karakter';
    } else {
        // Insert to database
        $stmt = mysqli_prepare($conn, "INSERT INTO pesan (nama, email, telepon, subjek, pesan) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $nama, $email, $telepon, $subjek, $pesan);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Terima kasih! Pesan Anda telah dikirim. Kami akan menghubungi Anda segera.';
            // Clear form
            $_POST = array();
        } else {
            $error = 'Gagal mengirim pesan. Silakan coba lagi.';
        }
    }
}
?>

<div class="container py-5">
  <!-- Page Header -->
  <div class="text-center mb-5">
    <h1 class="fw-bold mb-2">Hubungi Kami</h1>
    <p class="text-muted">Ada pertanyaan? Kami siap membantu Anda</p>
  </div>

  <div class="row g-4">
    <!-- Contact Info -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4">
            <i class="bi bi-info-circle text-success"></i> Informasi Kontak
          </h5>
          
          <div class="contact-item mb-4">
            <div class="d-flex align-items-start">
              <i class="bi bi-geo-alt-fill text-success fs-4 me-3"></i>
              <div>
                <h6 class="fw-bold">Alamat</h6>
                <p class="text-muted mb-0">
                  Jl. Sayur Segar No. 123<br>
                  Jakarta Selatan, 12345
                </p>
              </div>
            </div>
          </div>

          <div class="contact-item mb-4">
            <div class="d-flex align-items-start">
              <i class="bi bi-telephone-fill text-success fs-4 me-3"></i>
              <div>
                <h6 class="fw-bold">Telepon</h6>
                <p class="text-muted mb-0">
                  <a href="tel:+628123456789" class="text-decoration-none">
                    +62 812-3456-7890
                  </a>
                </p>
              </div>
            </div>
          </div>

          <div class="contact-item mb-4">
            <div class="d-flex align-items-start">
              <i class="bi bi-whatsapp text-success fs-4 me-3"></i>
              <div>
                <h6 class="fw-bold">WhatsApp</h6>
                <p class="text-muted mb-0">
                  <a href="https://wa.me/628123456789" target="_blank" class="text-decoration-none">
                    +62 812-3456-7890
                  </a>
                </p>
              </div>
            </div>
          </div>

          <div class="contact-item mb-4">
            <div class="d-flex align-items-start">
              <i class="bi bi-envelope-fill text-success fs-4 me-3"></i>
              <div>
                <h6 class="fw-bold">Email</h6>
                <p class="text-muted mb-0">
                  <a href="mailto:info@sayurmayur.com" class="text-decoration-none">
                    info@sayurmayur.com
                  </a>
                </p>
              </div>
            </div>
          </div>

          <div class="contact-item">
            <div class="d-flex align-items-start">
              <i class="bi bi-instagram text-success fs-4 me-3"></i>
              <div>
                <h6 class="fw-bold">Instagram</h6>
                <p class="text-muted mb-0">
                  <a href="https://instagram.com/sayurmayur" target="_blank" class="text-decoration-none">
                    @sayurmayur
                  </a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-4">
            <i class="bi bi-chat-dots text-success"></i> Kirim Pesan
          </h5>

          <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
              <i class="bi bi-check-circle"></i> <?= $success; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
              <i class="bi bi-exclamation-triangle"></i> <?= $error; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form method="POST" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">
                  Nama Lengkap <span class="text-danger">*</span>
                </label>
                <input type="text" name="nama" class="form-control" placeholder="Nama Anda" 
                       required minlength="3" value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>">
                <div class="invalid-feedback">Nama minimal 3 karakter</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">
                  Email <span class="text-danger">*</span>
                </label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                       required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
                <div class="invalid-feedback">Email tidak valid</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">
                  Nomor Telepon
                </label>
                <input type="tel" name="telepon" class="form-control" placeholder="08xxxxxxxxxx" 
                       pattern="[0-9]{10,13}" value="<?= htmlspecialchars($_POST['telepon'] ?? ''); ?>">
                <div class="invalid-feedback">Nomor telepon tidak valid</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">
                  Subjek <span class="text-danger">*</span>
                </label>
                <select name="subjek" class="form-select" required>
                  <option value="">Pilih subjek</option>
                  <option value="Pertanyaan Pesanan" <?= (isset($_POST['subjek']) && $_POST['subjek'] == 'Pertanyaan Pesanan') ? 'selected' : ''; ?>>Pertanyaan Pesanan</option>
                  <option value="Informasi Produk" <?= (isset($_POST['subjek']) && $_POST['subjek'] == 'Informasi Produk') ? 'selected' : ''; ?>>Informasi Produk</option>
                  <option value="Pengiriman" <?= (isset($_POST['subjek']) && $_POST['subjek'] == 'Pengiriman') ? 'selected' : ''; ?>>Pengiriman</option>
                  <option value="Keluhan" <?= (isset($_POST['subjek']) && $_POST['subjek'] == 'Keluhan') ? 'selected' : ''; ?>>Keluhan</option>
                  <option value="Lainnya" <?= (isset($_POST['subjek']) && $_POST['subjek'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                </select>
                <div class="invalid-feedback">Pilih subjek pesan</div>
              </div>

              <div class="col-12">
                <label class="form-label fw-bold">
                  Pesan <span class="text-danger">*</span>
                </label>
                <textarea name="pesan" class="form-control" rows="6" 
                          placeholder="Tulis pesan Anda di sini..." 
                          required minlength="10"><?= htmlspecialchars($_POST['pesan'] ?? ''); ?></textarea>
                <div class="invalid-feedback">Pesan minimal 10 karakter</div>
              </div>

              <div class="col-12">
                <button type="submit" name="submit_contact" class="btn btn-success btn-lg px-5">
                  <i class="bi bi-send"></i> Kirim Pesan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Operating Hours -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-3">
            <i class="bi bi-clock text-success"></i> Jam Operasional
          </h5>
          <div class="row">
            <div class="col-md-6">
              <p class="mb-2"><strong>Senin - Jumat:</strong> 08:00 - 20:00</p>
              <p class="mb-2"><strong>Sabtu:</strong> 08:00 - 18:00</p>
              <p class="mb-0"><strong>Minggu:</strong> 09:00 - 15:00</p>
            </div>
            <div class="col-md-6">
              <div class="alert alert-success mb-0">
                <i class="bi bi-info-circle"></i>
                <small>Kami siap melayani pesanan Anda setiap hari!</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Form validation
document.getElementById('contactForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  if (!this.checkValidity()) {
    this.classList.add('was-validated');
    return;
  }
  
  // Simulate form submission
  const resultDiv = document.getElementById('formResult');
  resultDiv.innerHTML = `
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle"></i> 
      Terima kasih! Pesan Anda telah dikirim. Kami akan menghubungi Anda segera.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
  
  this.reset();
  this.classList.remove('was-validated');
  
  // Scroll to result
  resultDiv.scrollIntoView({ behavior: 'smooth' });
});
</script>

<style>
.contact-item {
  padding-bottom: 1rem;
  border-bottom: 1px solid #e9ecef;
}

.contact-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.contact-item a {
  color: #16a34a;
}

.contact-item a:hover {
  color: #22c55e;
}
</style>

<?php include 'includes/footer.php'; ?>
