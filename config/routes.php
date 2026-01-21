<?php
/**
 * Application Routes Configuration
 * 
 * Centralized route definitions for easy navigation and maintenance
 * Format: 'route.name' => 'relative/path/to/page.php'
 */

return [
    // Core & Auth
    'dashboard' => 'admin/pages/dashboard/dashboard.php',
    'login' => 'admin/login.php',
    'logout' => 'admin/logout.php',
    
    // Kategori
    'kategori.index' => 'admin/pages/kategori/index.php',
    'kategori.create' => 'admin/pages/kategori/create.php',
    'kategori.edit' => 'admin/pages/kategori/edit.php',
    'kategori.delete' => 'admin/pages/kategori/delete.php',
    
    // Ongkos Kirim
    'ongkos-kirim.index' => 'admin/pages/ongkos-kirim/index.php',
    'ongkos-kirim.create' => 'admin/pages/ongkos-kirim/create.php',
    'ongkos-kirim.edit' => 'admin/pages/ongkos-kirim/edit.php',
    'ongkos-kirim.delete' => 'admin/pages/ongkos-kirim/delete.php',
    
    // Voucher
    'voucher.index' => 'admin/pages/voucher/index.php',
    'voucher.create' => 'admin/pages/voucher/create.php',
    'voucher.edit' => 'admin/pages/voucher/edit.php',
    'voucher.delete' => 'admin/pages/voucher/delete.php',
    
    // Produk
    'products.index' => 'admin/pages/products/index.php',
    'products.create' => 'admin/pages/products/create.php',
    'products.edit' => 'admin/pages/products/edit.php',
    'products.delete' => 'admin/pages/products/delete.php',
    'products.reviews' => 'admin/pages/products/reviews.php',
    
    // Transaksi
    'transactions.index' => 'admin/pages/transactions/index.php',
    'transactions.detail' => 'admin/pages/transactions/detail.php',
    'transactions.invoice' => 'admin/pages/transactions/invoice.php',
    
    // Review
    'review.index' => 'admin/messages/index.php',
];
