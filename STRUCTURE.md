# 📁 Struktur Project SAYUR MAYUR

## Organisasi Folder (Updated 2026)

```
sayur_mayur_app/
├── admin/                                # Panel administrator
│   ├── auth.php                         # Authentication check
│   ├── index.php                        # Redirect ke dashboard
│   ├── login.php                        # Halaman login admin
│   ├── logout.php                       # Logout
│   ├── reset_password.php               # Reset password admin
│   │
│   ├── pages/                           # Admin pages (modular structure)
│   │   ├── dashboard/
│   │   │   └── dashboard.php            # Dashboard utama & statistik
│   │   │
│   │   ├── kategori/                    # Manajemen kategori
│   │   │   ├── index.php                # List kategori
│   │   │   ├── create.php               # Tambah kategori
│   │   │   ├── edit.php                 # Edit kategori
│   │   │   └── delete.php               # Delete kategori
│   │   │
│   │   ├── ongkos-kirim/                # Manajemen ongkos kirim
│   │   │   ├── index.php                # List lokasi pengiriman
│   │   │   ├── create.php               # Tambah lokasi
│   │   │   ├── edit.php                 # Edit lokasi
│   │   │   └── delete.php               # Delete lokasi
│   │   │
│   │   ├── voucher/                     # Manajemen voucher
│   │   │   ├── index.php                # List voucher
│   │   │   ├── create.php               # Tambah voucher
│   │   │   ├── edit.php                 # Edit voucher
│   │   │   └── delete.php               # Delete voucher
│   │   │
│   │   ├── products/                    # Manajemen produk
│   │   │   ├── index.php                # List produk
│   │   │   ├── create.php               # Tambah produk
│   │   │   ├── edit.php                 # Edit produk
│   │   │   ├── delete.php               # Delete produk
│   │   │   └── reviews.php              # Kelola review produk
│   │   │
│   │   └── transactions/                # Manajemen transaksi
│   │       ├── index.php                # List transaksi
│   │       ├── detail.php               # Detail transaksi
│   │       └── invoice.php              # Invoice transaksi
│   │
│   ├── messages/                        # Manajemen pesan kontak
│   │   └── index.php                    # List dan reply pesan
│
├── api/                                 # API Endpoints
│   └── get_shipping.php                 # API untuk hitung ongkos kirim
│
├── assets/                              # Static assets
│   ├── css/
│   │   └── style.css                    # Stylesheet utama (26 KB)
│   ├── js/
│   │   ├── app.js                       # Utility JavaScript (7 KB)
│   │   └── product_detail.js            # Script detail produk (7 KB)
│   └── img/                             # Product images (13 gambar)
│
├── config/                              # Konfigurasi
│   ├── koneksi.php                      # Database connection
│   ├── functions.php                    # Helper functions
│   ├── routes.php                       # Centralized routes (NEW)
│   └── navigation.php                   # Navigation config (NEW)
│
├── helpers/                             # Helper functions
│   └── RouteHelper.php                  # Route helper functions (NEW)
│
├── includes/                            # Reusable components
│   ├── header.php                       # Header component
│   ├── footer.php                       # Footer component (dynamic kategoris)
│   └── sidebar.php                      # Admin sidebar component
│
├── docs/                                # Dokumentasi project
│   ├── LOGGING.md                       # Logging system documentation
│   ├── PANDUAN_PROJECT.md               # Panduan lengkap project
│   ├── QUICK_START.md                   # Quick start guide
│   ├── README.md                        # Documentation
│   └── REVIEW_SYSTEM.md                 # Review system documentation
│
├── logs/                                # Log files
│   └── (auto-generated error logs)
│
├── index.php                            # Homepage
├── products.php                         # Halaman daftar produk
├── product_detail.php                   # Detail produk + reviews
├── checkout.php                         # Halaman checkout
├── proses_checkout.php                  # Process checkout (backend)
├── invoice.php                          # Halaman invoice
├── cetak-invoice.php                    # Thermal printer format
├── contact.php                          # Halaman kontak
├── cek_voucher.php                      # Pengecekan voucher
├── submit_review.php                    # Submit review produk
│
├── database.sql                         # Database schema & sample data
├── .gitignore                           # Git ignore rules
├── README.md                            # Main readme
├── STRUCTURE.md                         # File ini - struktur project
└── .git/                                # Git repository
```

## Deskripsi Folder Utama

### 📋 `/admin`
Folder untuk admin panel. Berisi semua halaman manajemen sistem.
- **auth.php** - Session check untuk autentikasi
- **dashboard.php** - Ringkasan statistik dan dashboard
- **produk.php** - CRUD produk
- **kategori.php** - CRUD kategori
- **ongkos_kirim.php** - Manajemen biaya pengiriman
- **transaksi.php** - Laporan transaksi penjualan
- **pesan.php** - Kelola pesan dari halaman kontak

### 🔌 `/api`
API endpoints untuk aplikasi.
- **get_shipping.php** - API untuk mengambil biaya pengiriman berdasarkan lokasi

### 🎨 `/assets`
Semua file statis (CSS, JavaScript, Images).
- **css/style.css** - Stylesheet utama aplikasi
- **js/app.js** - Utility JavaScript dan jQuery
- **js/product_detail.js** - Script khusus untuk halaman detail produk
- **img/** - Gambar produk (13 gambar sayuran/buah)

### ⚙️ `/config`
Konfigurasi database dan fungsi-fungsi reusable.
- **koneksi.php** - MySQLi database connection
- **functions.php** - Helper functions (sanitize, formatRupiah, dll)
- **database.sql** - Database schema dan initial data

### 🔄 `/includes`
Komponen yang dapat digunakan kembali di berbagai halaman.
- **header.php** - Navbar dan head section
- **footer.php** - Footer dan script loading
- **sidebar.php** - Sidebar admin (digunakan di semua admin pages)

### 📚 `/docs`
Dokumentasi lengkap project.
- README, quick start guide, panduan project
- Implementation checklist dan reports
- Shipping system documentation

### 📝 `/logs`
Folder untuk menyimpan log files.
- Checkout errors
- Database errors
- Aplikasi logs

## Path Reference Updates

Semua path sudah diupdate dari struktur lama ke struktur baru:

| Lama | Baru |
|------|------|
| `asset/css/style.css` | `assets/css/style.css` |
| `asset/js/app.js` | `assets/js/app.js` |
| `img/produk.jpg` | `assets/img/produk.jpg` |
| `../img/` (admin) | `../assets/img/` |

## File Permissions

Folder yang perlu write permission:
```bash
# Linux/Mac
chmod 755 logs/
chmod 755 assets/img/

# Windows (usually automatic)
```

## Catatan

- ✅ Sidebar admin sudah dipindahkan ke `includes/sidebar.php` dan di-include ke semua admin pages
- ✅ Semua path references sudah diupdate otomatis
- ✅ Database connection terpusat di `config/koneksi.php`
- ✅ Helper functions terpusat di `config/functions.php`
- ✅ Dokumentasi terpusat di folder `docs/`

## Quick Navigation

| Halaman | File | Lokasi |
|---------|------|--------|
| Homepage | `index.php` | Root |
| Daftar Produk | `products.php` | Root |
| Detail Produk | `product_detail.php` | Root |
| Checkout | `checkout.php` | Root |
| Invoice | `invoice.php` | Root |
| Admin Dashboard | `admin/dashboard.php` | Admin |
| Kelola Produk | `admin/produk.php` | Admin |
| Kategori | `admin/kategori.php` | Admin |
| Ongkos Kirim | `admin/ongkos_kirim.php` | Admin |
| Transaksi | `admin/transaksi.php` | Admin |

---

**Last Updated**: 2026-01-21  
**Version**: 2.0 (Restructured)
