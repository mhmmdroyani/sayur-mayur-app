# ⚡ QUICK START GUIDE - SAYUR MAYUR

Panduan cepat untuk langsung menjalankan website dalam 5 menit.

## 📋 Prasyarat
- Laragon / XAMPP / WAMP sudah terinstall
- MySQL server running
- Browser modern (Chrome, Firefox, Edge, Safari)

---

## 🚀 Setup Cepat (5 Menit)

### Step 1: Import Database (1 menit)
```
1. Buka: http://localhost/phpmyadmin
2. Klik "New" untuk database baru
3. Nama database: sayur_mayur_app
4. Charset: utf8mb4_general_ci
5. Klik "Create"
6. Pilih database "sayur_mayur_app"
7. Klik tab "Import"
8. Pilih file "database.sql" dari folder project
9. Klik "Import"
```

### Step 2: Verifikasi Koneksi (30 detik)
File `config/koneksi.php` sudah sesuai default:
```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sayur_mayur_app";
```

Jika berbeda, sesuaikan kredensial Anda.

### Step 3: Setup Folder Upload (30 detik)
Pastikan folder `img/` punya write permission:
```bash
# Tidak perlu di Windows, sudah otomatis
# Di Linux/Mac: chmod 777 img/
```

### Step 4: Akses Website (30 detik)
```
Frontend:  http://localhost/sayur_mayur_app/
Admin:     http://localhost/sayur_mayur_app/admin/
```

### Step 5: Login Admin (30 detik)
```
Username: admin
Password: admin123
```

---

## ✅ Test Checklist

### Homepage (index.php)
- [ ] Load dengan baik
- [ ] Hero section terlihat
- [ ] Featured products muncul
- [ ] Navigation bekerja
- [ ] Wishlist badge visible di navbar

### Products Page (products.php)
- [ ] 8 produk sample terlihat
- [ ] Search box berfungsi
- [ ] Filter sort bekerja
- [ ] Pagination ada
- [ ] Wishlist button (♥) di setiap kartu produk

### Cart & Checkout
- [ ] Add to cart → Toast muncul
- [ ] Cart drawer slide
- [ ] Checkout form berfungsi
- [ ] Invoice generated setelah submit
- [ ] Subtotal display correctly

### Admin Panel
- [ ] Login dengan admin/admin123
- [ ] Dashboard muncul
- [ ] Kelola produk berfungsi dengan search & filter
- [ ] Transaction list terlihat dengan search & filter
- [ ] Payment method column visible
- [ ] Date filter (today/week/month) bekerja

---

## 🎯 Fitur yang Bisa Langsung Dicoba

### 1. Belanja Produk
```
1. Klik "Produk" di navbar
2. Cari "Bayam"
3. Klik "Tambah ke Keranjang"
4. Toast: "Produk ditambahkan ke keranjang" ✅
5. Klik icon keranjang
6. Lihat cart drawer ✅
```

### 2. Wishlist Feature
```
1. Klik "Produk" di navbar
2. Klik icon hati (♥) di kartu produk untuk save favorit
3. Lihat counter di navbar Wishlist bertambah ✅
4. Klik "Wishlist" di navbar untuk melihat produk favorit ✅
5. Klik "Tambah ke Keranjang" dari wishlist ✅
```

### 3. Checkout
```
1. Di cart, klik "Checkout" button
2. Isi form:
   - Nama: John Doe
   - Telepon: 08123456789
   - Alamat: Jl. Mana Saja No. 123
3. Klik "Proses Pesanan"
4. Invoice akan ditampilkan ✅
5. Subtotal, diskon, & total terupdate dengan benar ✅
```

### 4. Admin - Tambah Produk
```
1. Login admin (admin/admin123)
2. Klik "Kelola Produk"
3. Gunakan search & filter untuk cari produk
4. Klik "Tambah Produk Baru"
5. Isi form produk
6. Upload gambar
7. Klik "Simpan"
8. Produk baru muncul di katalog ✅
```

### 5. Admin - Filter Transaksi
```
1. Login admin (admin/admin123)
2. Klik "Riwayat Transaksi"
3. Gunakan filter:
   - Cari dengan ID / Nama / No. Telepon
   - Filter status pesanan
   - Filter metode pembayaran
   - Filter tanggal (Hari Ini / Minggu Ini / Bulan Ini) ✅
4. Lihat payment method column ✅
```

---

## 📱 Responsive Design Test

### Desktop (1920px)
```bash
F12 → Esc → Resize browser
Terlihat normal, semua fit
```

### Tablet (768px)
```bash
DevTools → Toggle device toolbar
iPad view → Layout adjust ✅
```

### Mobile (375px)
```bash
DevTools → iPhone SE view
Touch-friendly buttons ✅
Sliding drawer ✅
```

---

## 🔐 Security Test

### 1. SQL Injection Test
```
Coba di search: ' OR '1'='1
Expected: Not vulnerable ✅
Hanya search untuk keyword
```

### 2. XSS Test
```
Coba di contact form: <script>alert('xss')</script>
Expected: Not vulnerable ✅
Script tidak execute
```

### 3. CSRF Test
```
Login admin, copy form
Buka di tab baru tanpa token
Expected: Rejected ✅
Perlu valid token
```

---

## 🆘 Troubleshooting

### Error: "Koneksi database gagal"
```
✓ Pastikan MySQL running
✓ Cek username/password di koneksi.php
✓ Database 'sayur_mayur_app' sudah dibuat?
```

### Error: "Upload gambar gagal"
```
✓ Folder img/ ada write permission?
✓ File size < 5MB?
✓ Format: JPG, PNG, GIF, WEBP?
```

### Error: "Session timeout" di admin
```
✓ Normal - session timeout 30 menit
✓ Login ulang
✓ Bisa diubah di admin/auth.php
```

### Halaman blank/error
```
✓ Check file exists
✓ Buka: Inspect → Console → lihat error
✓ Buka server error log
```

---

## 📞 Helpful Resources

### Files Penting:
- **Homepage**: `index.php`
- **Produk**: `products.php`
- **Checkout**: `checkout.php`, `proses_checkout.php`
- **Admin Login**: `admin/login.php`
- **Database Config**: `config/koneksi.php`
- **Helper Functions**: `config/functions.php`
- **Database Schema**: `database.sql`

### Dokumentasi Lengkap:
- `README.md` - Complete documentation
- `PANDUAN_PROJECT.md` - For evaluators
- `IMPROVEMENT_SUMMARY.md` - Change summary

---

## 🎓 Untuk Presentasi

### Points Penting untuk Highlight:

**Security:**
- Prepared statements mencegah SQL injection
- CSRF token protection
- XSS sanitization
- Password hashing bcrypt

**Features:**
- Search & filter produk
- Shopping cart dengan localStorage
- Secure checkout dengan validation
- Professional invoice
- Admin panel lengkap

**Design:**
- Modern responsive UI
- Toast notifications
- Smooth animations
- Mobile-friendly

**Code Quality:**
- Helper functions reusable
- Separated concerns
- Clean architecture
- Well documented

---

## ✨ Default Data

### Admin
```
Username: admin
Password: admin123
Email: admin@sayurmayur.com
```

### Sample Products (8 produk)
```
1. Bayam Segar - Rp 8.000
2. Kangkung - Rp 6.000
3. Wortel - Rp 12.000
4. Tomat - Rp 10.000
5. Brokoli - Rp 18.000
6. Sawi Hijau - Rp 7.000
7. Kentang - Rp 15.000
8. Cabai Merah - Rp 25.000
```

---

## 🎉 Siap Dimulai!

```
1. Import database.sql
2. Akses http://localhost/sayur_mayur_app/
3. Explore fitur-fitur
4. Login admin untuk test admin panel
5. Baca dokumentasi untuk detail lebih lanjut
```

**Happy testing! 🚀**

---

**Support & Questions?**
- Baca README.md untuk detail lengkap
- Baca PANDUAN_PROJECT.md untuk informasi teknis
- Check troubleshooting section di README.md

Made with ❤️ for smooth project setup
