# 🥬 SAYUR MAYUR - E-Commerce Sayuran Segar

![Version](https://img.shields.io/badge/version-2.0.0-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

Website e-commerce untuk penjualan sayuran segar online dengan fitur lengkap dan desain modern.

## 📋 Deskripsi Project

SAYUR MAYUR adalah platform e-commerce yang dirancang khusus untuk memudahkan transaksi jual beli sayuran segar secara online. Website ini dibangun menggunakan teknologi modern dengan fokus pada keamanan, user experience, dan performa.

**Project ini dibuat untuk:** Final Project Mata Kuliah E-Commerce

## ✨ Fitur Utama

### 🛒 Fitur Customer
- ✅ **Katalog Produk** dengan search dan filter
- ✅ **Shopping Cart** dengan local storage
- ✅ **Checkout System** yang aman dengan CSRF protection
- ✅ **Invoice** digital yang dapat dicetak
- ✅ **Responsive Design** - Mobile friendly
- ✅ **Real-time Cart Update** dengan toast notifications
- ✅ **Product Pagination** untuk performa optimal
- ✅ **Contact Form** untuk komunikasi

### 👨‍💼 Fitur Admin
- ✅ **Dashboard** dengan ringkasan data
- ✅ **Manajemen Produk** (CRUD)
- ✅ **Riwayat Transaksi** lengkap
- ✅ **Upload Gambar** dengan validasi
- ✅ **Secure Login** dengan session management
- ✅ **Protected Routes** dengan authentication

### 🔒 Keamanan
- ✅ **SQL Injection Prevention** dengan Prepared Statements
- ✅ **XSS Protection** dengan input sanitization
- ✅ **CSRF Token** untuk form submissions
- ✅ **Password Hashing** dengan bcrypt
- ✅ **Session Security** dengan timeout
- ✅ **Input Validation** di client & server side

## 🚀 Teknologi yang Digunakan

### Backend
- **PHP 7.4+** - Server-side programming
- **MySQL 5.7+** - Database management
- **mysqli** - Database connectivity dengan prepared statements

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling dengan custom properties
- **Bootstrap 5.3** - Responsive framework
- **JavaScript ES6+** - Interactive features
- **Bootstrap Icons** - Icon library

### Tools & Libraries
- **LocalStorage API** - Shopping cart persistence
- **Fetch API** - AJAX requests
- **FormData** - File uploads

## 📦 Instalasi

### Prerequisites
- XAMPP / Laragon / WAMP (Apache + MySQL + PHP)
- PHP >= 7.4
- MySQL >= 5.7
- Web Browser modern

### Langkah Instalasi

1. **Clone atau Download Project**
   ```bash
   git clone https://github.com/mhmmdroyani/sayur-mayur-app.git
   cd sayur-mayur-app
   ```

2. **Import Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru bernama `sayur_mayur_app`
   - Import file `database.sql` yang ada di root folder

3. **Konfigurasi Database**
   - Buka file `config/koneksi.php`
   - Sesuaikan kredensial database jika perlu:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "sayur_mayur_app";
   ```

4. **Setup Folder Upload**
   - Pastikan folder `img/` memiliki permission write (777)
   ```bash
   chmod 777 img/
   ```

5. **Akses Website**
   - Frontend: `http://localhost/sayur_mayur_app/`
   - Admin: `http://localhost/sayur_mayur_app/admin/`

## 🔐 Default Login Admin

```
Username: admin
Password: admin123
```

**⚠️ PENTING:** Segera ganti password default setelah login pertama kali!

## 📁 Struktur Folder

```
sayur_mayur_app/
├── admin/              # Panel admin
│   ├── auth.php        # Authentication middleware
│   ├── index.php       # Dashboard admin
│   ├── login.php       # Login page
│   ├── produk.php      # Manage products
│   ├── tambah_produk.php
│   ├── edit_produk.php
│   ├── hapus_produk.php
│   └── transaksi.php   # Transaction history
├── asset/
│   ├── css/
│   │   └── style.css   # Custom styles
│   └── js/
│       └── app.js      # Cart & interactions
├── config/
│   ├── koneksi.php     # Database connection
│   └── functions.php   # Helper functions
├── img/                # Product images
├── includes/
│   ├── header.php      # Global header
│   └── footer.php      # Global footer
├── index.php           # Homepage
├── products.php        # Product catalog
├── product_detail.php  # Product detail page
├── checkout.php        # Checkout page
├── proses_checkout.php # Checkout processing
├── invoice.php         # Invoice page
├── contact.php         # Contact page
├── database.sql        # Database schema
└── README.md           # Documentation
```

## 💻 Cara Penggunaan

### Untuk Customer

1. **Belanja Produk**
   - Browse katalog produk di halaman "Produk"
   - Gunakan search & filter untuk menemukan produk
   - Klik "Tambah ke Keranjang" pada produk yang diinginkan

2. **Checkout**
   - Klik icon keranjang untuk melihat items
   - Klik "Checkout" untuk melanjutkan
   - Isi form data pembeli
   - Submit pesanan

3. **Invoice**
   - Setelah checkout sukses, invoice akan ditampilkan
   - Cetak invoice untuk bukti pemesanan

### Untuk Admin

1. **Login**
   - Akses `/admin/login.php`
   - Masukkan username dan password

2. **Kelola Produk**
   - Tambah produk baru dengan upload gambar
   - Edit informasi produk
   - Hapus produk yang tidak diperlukan
   - Kelola stok produk

3. **Monitor Transaksi**
   - Lihat semua transaksi masuk
   - Cek detail pesanan customer
   - Update status pesanan

## 🎨 Fitur Desain

- **Modern UI/UX** - Desain clean dan intuitive
- **Responsive Layout** - Optimal di semua device
- **Color Scheme** - Green theme untuk nuansa segar
- **Smooth Animations** - Hover effects dan transitions
- **Toast Notifications** - Real-time feedback
- **Cart Drawer** - Sliding cart panel
- **Loading States** - Better user feedback

## 🔧 Kustomisasi

### Mengubah Warna Theme
Edit file `asset/css/style.css`:
```css
:root {
  --primary: #22c55e;        /* Hijau utama */
  --primary-dark: #16a34a;   /* Hijau gelap */
  --secondary: #dcfce7;      /* Hijau muda */
}
```

### Mengubah Logo/Brand
Edit file `includes/header.php`:
```php
<a class="navbar-brand" href="index.php">
  <i class="bi bi-basket2-fill"></i> SAYUR MAYUR
</a>
```

## 📱 Browser Support

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ⚠️ IE11 (limited support)

## 🐛 Troubleshooting

### Error: Database Connection Failed
- Pastikan MySQL server running
- Cek kredensial di `config/koneksi.php`
- Pastikan database sudah di-import

### Upload Gambar Gagal
- Cek permission folder `img/` (chmod 777)
- Pastikan ukuran file < 5MB
- Hanya accept: JPG, PNG, GIF, WEBP

### Session Expired
- Default timeout: 30 menit
- Bisa diubah di `admin/auth.php`

## 📈 Future Enhancements

- [ ] Payment Gateway Integration
- [ ] Email Notifications
- [ ] Order Tracking System
- [ ] Product Reviews & Ratings
- [ ] Wishlist Feature
- [ ] Multi-language Support
- [ ] Advanced Analytics Dashboard
- [ ] Push Notifications
- [ ] Social Media Integration

## 👨‍💻 Developer

**Muhammad Royani**
- GitHub: [@mhmmdroyani](https://github.com/mhmmdroyani)
- Project: Final Project E-Commerce

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Bootstrap Team untuk framework yang luar biasa
- Bootstrap Icons untuk icon library
- Komunitas PHP & MySQL
- Dosen Pembimbing Mata Kuliah E-Commerce

## 📞 Support

Jika ada pertanyaan atau masalah, silakan:
- Buat issue di GitHub repository
- Email: mhmmdroyani@example.com

---

**⭐ Jika project ini membantu, jangan lupa beri star!**

Made with ❤️ for E-Commerce Course Final Project
