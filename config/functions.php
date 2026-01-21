<?php
/**
 * Helper Functions untuk Sayur Mayur App
 * Fungsi-fungsi utility untuk keamanan dan kemudahan development
 */

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitasi input untuk mencegah XSS
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validasi email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format rupiah
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Get flash message
 */
function getFlashMessage($key) {
    if (isset($_SESSION['flash_' . $key])) {
        $msg = $_SESSION['flash_' . $key];
        unset($_SESSION['flash_' . $key]);
        return $msg;
    }
    return null;
}

/**
 * Set flash message
 */
function setFlashMessage($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}

/**
 * Upload gambar dengan validasi
 */
function uploadImage($file, $targetDir = 'assets/img/') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Validasi error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error upload file'];
    }
    
    // Validasi tipe file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, WEBP'];
    }
    
    // Validasi ukuran
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB'];
    }
    
    // Generate nama file unik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal upload file'];
}

/**
 * Hapus file gambar
 */
function deleteImage($filename, $dir = 'assets/img/') {
    $filepath = $dir . $filename;
    if (file_exists($filepath) && $filename !== 'default.jpg') {
        return unlink($filepath);
    }
    return false;
}

/**
 * Pagination helper
 */
function getPagination($currentPage, $totalItems, $itemsPerPage = 12) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'limit' => $itemsPerPage,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Cek apakah user sudah login (admin)
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin']);
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('../admin/login.php');
    }
}

/**
 * Get status badge HTML dengan styling konsisten
 */
function getStatusBadge($status) {
    $statusMap = [
        'pending' => [
            'badge' => 'warning',
            'text' => 'dark',
            'label' => 'Menunggu',
            'icon' => 'hourglass-split'
        ],
        'processing' => [
            'badge' => 'info',
            'text' => 'dark',
            'label' => 'Sedang Diproses',
            'icon' => 'arrow-repeat'
        ],
        'shipped' => [
            'badge' => 'primary',
            'text' => 'dark',
            'label' => 'Dikirim',
            'icon' => 'truck'
        ],
        'delivered' => [
            'badge' => 'success',
            'text' => 'dark',
            'label' => 'Terima',
            'icon' => 'check-circle'
        ],
        'cancelled' => [
            'badge' => 'danger',
            'text' => 'dark',
            'label' => 'Dibatalkan',
            'icon' => 'x-circle'
        ]
    ];
    
    $info = $statusMap[$status] ?? $statusMap['pending'];
    
    return sprintf(
        '<span class="badge bg-%s text-%s fs-6" style="padding: 0.5rem 0.75rem;">
            <i class="bi bi-%s" style="color: #000; margin-right: 0.5rem;"></i> %s
        </span>',
        $info['badge'],
        $info['text'],
        $info['icon'],
        $info['label']
    );
}
