
<!-- Footer -->
<footer class="bg-dark text-white mt-5">
  <div class="container py-5">
    <div class="row g-4">
      <!-- About -->
      <div class="col-lg-4 col-md-6">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-basket2-fill text-success"></i> SAYUR MAYUR
        </h5>
        <p class="text-light-emphasis">
          Platform e-commerce terpercaya untuk sayuran segar berkualitas. 
          Kami menyediakan produk pilihan langsung dari petani lokal.
        </p>
        <div class="d-flex gap-2">
          <a href="#" class="btn btn-outline-light btn-sm">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#" class="btn btn-outline-light btn-sm">
            <i class="bi bi-instagram"></i>
          </a>
          <a href="#" class="btn btn-outline-light btn-sm">
            <i class="bi bi-twitter"></i>
          </a>
          <a href="#" class="btn btn-outline-light btn-sm">
            <i class="bi bi-whatsapp"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-6">
        <h6 class="fw-bold mb-3">Menu</h6>
        <ul class="list-unstyled">
          <li class="mb-2">
            <a href="index.php" class="text-light-emphasis text-decoration-none">
              <i class="bi bi-chevron-right"></i> Beranda
            </a>
          </li>
          <li class="mb-2">
            <a href="products.php" class="text-light-emphasis text-decoration-none">
              <i class="bi bi-chevron-right"></i> Produk
            </a>
          </li>
          <li class="mb-2">
            <a href="contact.php" class="text-light-emphasis text-decoration-none">
              <i class="bi bi-chevron-right"></i> Kontak
            </a>
          </li>
          <li class="mb-2">
            <a href="admin/index.php" class="text-light-emphasis text-decoration-none">
              <i class="bi bi-chevron-right"></i> Admin
            </a>
          </li>
        </ul>
      </div>

      <!-- Categories -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold mb-3">Kategori</h6>
        <ul class="list-unstyled">
          <?php
            // Load categories from database
            if (!isset($conn)) {
              require_once dirname(__DIR__) . '/config/koneksi.php';
            }
            $result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama ASC");
            if ($result && mysqli_num_rows($result) > 0) {
              while ($cat = mysqli_fetch_assoc($result)) {
                echo '<li class="mb-2">';
                echo '<a href="products.php?kategori=' . urlencode($cat['nama']) . '" class="text-light-emphasis text-decoration-none">';
                echo '<i class="bi bi-chevron-right"></i> ' . htmlspecialchars($cat['nama']);
                echo '</a></li>';
              }
            }
          ?>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold mb-3">Hubungi Kami</h6>
        <ul class="list-unstyled">
          <li class="mb-2">
            <i class="bi bi-geo-alt-fill text-success"></i>
            <small>Jl. Sayur Segar No. 123, Jakarta</small>
          </li>
          <li class="mb-2">
            <i class="bi bi-telephone-fill text-success"></i>
            <small>+62 812-3456-7890</small>
          </li>
          <li class="mb-2">
            <i class="bi bi-envelope-fill text-success"></i>
            <small>info@sayurmayur.com</small>
          </li>
          <li class="mb-2">
            <i class="bi bi-clock-fill text-success"></i>
            <small>Sen-Sab: 08:00 - 20:00</small>
          </li>
        </ul>
      </div>
    </div>

    <hr class="my-4 border-secondary">

    <!-- Copyright -->
    <div class="row">
      <div class="col-md-6 text-center text-md-start">
        <small>
          &copy; <?= date('Y'); ?> SAYUR MAYUR. All rights reserved.
        </small>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <small>
          Made with <i class="bi bi-heart-fill text-danger"></i> for E-Commerce Course
        </small>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/app.js"></script>

</body>
</html>
