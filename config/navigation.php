<?php
/**
 * Navigation Configuration
 * 
 * Centralized navigation structure for sidebar, breadcrumbs, and menus
 */

return [
    'sidebar' => [
        [
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'route' => 'dashboard',
            'active_when' => ['dashboard']
        ],
        [
            'label' => 'Kelola Produk',
            'icon' => 'bi-box-seam',
            'route' => 'products.index',
            'active_when' => ['products.index', 'products.create', 'products.edit', 'products.reviews']
        ],
        [
            'label' => 'Kategori',
            'icon' => 'bi-tag',
            'route' => 'kategori.index',
            'active_when' => ['kategori.index', 'kategori.create', 'kategori.edit']
        ],
        [
            'label' => 'Ongkos Kirim',
            'icon' => 'bi-truck',
            'route' => 'ongkos-kirim.index',
            'active_when' => ['ongkos-kirim.index', 'ongkos-kirim.create', 'ongkos-kirim.edit']
        ],
        [
            'label' => 'Voucher',
            'icon' => 'bi-ticket',
            'route' => 'voucher.index',
            'active_when' => ['voucher.index', 'voucher.create', 'voucher.edit']
        ],
        [
            'label' => 'Transaksi',
            'icon' => 'bi-receipt',
            'route' => 'transactions.index',
            'active_when' => ['transactions.index', 'transactions.detail']
        ],
        [
            'label' => 'Pesan',
            'icon' => 'bi-chat-dots',
            'route' => 'review.index',
            'active_when' => ['review.index']
        ],
    ],
    
    'breadcrumbs' => [
        // Kategori
        'kategori.index' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'current' => ['label' => 'Kategori']
        ],
        'kategori.create' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'kategori' => ['label' => 'Kategori', 'route' => 'kategori.index'],
            'current' => ['label' => 'Tambah Kategori']
        ],
        'kategori.edit' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'kategori' => ['label' => 'Kategori', 'route' => 'kategori.index'],
            'current' => ['label' => 'Edit Kategori']
        ],
        
        // Ongkos Kirim
        'ongkos-kirim.index' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'current' => ['label' => 'Ongkos Kirim']
        ],
        'ongkos-kirim.create' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'ongkos' => ['label' => 'Ongkos Kirim', 'route' => 'ongkos-kirim.index'],
            'current' => ['label' => 'Tambah Ongkos Kirim']
        ],
        'ongkos-kirim.edit' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'ongkos' => ['label' => 'Ongkos Kirim', 'route' => 'ongkos-kirim.index'],
            'current' => ['label' => 'Edit Ongkos Kirim']
        ],
        
        // Voucher
        'voucher.index' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'current' => ['label' => 'Voucher']
        ],
        'voucher.create' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'voucher' => ['label' => 'Voucher', 'route' => 'voucher.index'],
            'current' => ['label' => 'Tambah Voucher']
        ],
        'voucher.edit' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'voucher' => ['label' => 'Voucher', 'route' => 'voucher.index'],
            'current' => ['label' => 'Edit Voucher']
        ],
        
        // Produk
        'products.index' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'current' => ['label' => 'Kelola Produk']
        ],
        'products.create' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'products' => ['label' => 'Produk', 'route' => 'products.index'],
            'current' => ['label' => 'Tambah Produk']
        ],
        'products.edit' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'products' => ['label' => 'Produk', 'route' => 'products.index'],
            'current' => ['label' => 'Edit Produk']
        ],
        
        // Transaksi
        'transactions.index' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'current' => ['label' => 'Transaksi']
        ],
        'transactions.detail' => [
            'home' => ['label' => 'Home', 'route' => 'dashboard'],
            'transactions' => ['label' => 'Transaksi', 'route' => 'transactions.index'],
            'current' => ['label' => 'Detail Transaksi']
        ],
    ]
];
