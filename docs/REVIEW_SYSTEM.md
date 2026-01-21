# Sistem Review & Rating Produk

## 📋 Overview

Sistem review dan rating memungkinkan pelanggan memberikan ulasan terhadap produk yang mereka beli. Ini membantu meningkatkan kepercayaan dan transparansi.

## 🗄️ Struktur Database

### Tabel: `review`

```sql
CREATE TABLE `review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produk_id` int(11) NOT NULL,
  `nama_reviewer` varchar(100) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `komentar` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `review_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Penjelasan Field:

| Field | Tipe | Keterangan |
|-------|------|-----------|
| `id` | INT | Primary key, auto increment |
| `produk_id` | INT | Foreign key ke tabel produk |
| `nama_reviewer` | VARCHAR(100) | Nama pelanggan yang memberi review |
| `rating` | TINYINT(1) | Rating 1-5 bintang |
| `komentar` | TEXT | Isi ulasan/komentar produk |
| `created_at` | TIMESTAMP | Waktu review dibuat (auto) |

## 🎯 Fitur

### Status Saat Ini
- ✅ Tabel database sudah ada
- ✅ Foreign key ke produk sudah configured
- ⏳ Frontend UI belum diimplementasikan
- ⏳ Admin panel belum diimplementasikan

### Yang Sudah Ada
1. **Struktur database** - Tabel review sudah dibuat dengan constraint yang sesuai
2. **Validasi rating** - Hanya menerima nilai 1-5 (CHECK constraint)
3. **Auto timestamp** - Waktu pembuatan review otomatis tercatat

## 📝 Contoh Data

```sql
-- Contoh insert review
INSERT INTO review (produk_id, nama_reviewer, rating, komentar) 
VALUES (1, 'Budi Santoso', 5, 'Produk segar dan berkualitas tinggi!');

INSERT INTO review (produk_id, nama_reviewer, rating, komentar) 
VALUES (2, 'Siti Nurhaliza', 4, 'Bagus, tapi agak lama sampainya');

INSERT INTO review (produk_id, nama_reviewer, rating, komentar) 
VALUES (3, 'Ahmad Wijaya', 5, 'Rekomendasi banget, pasti beli lagi');
```

## 🔧 Implementasi Frontend

### Yang Bisa Ditambahkan:

#### 1. **Display Review di Product Detail**
```php
// product_detail.php
SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
FROM review 
WHERE produk_id = ?
```

#### 2. **Form Submit Review**
```html
<form method="POST" action="submit_review.php">
  <input type="hidden" name="produk_id" value="<?= $produk_id; ?>">
  <input type="text" name="nama_reviewer" placeholder="Nama Anda" required>
  
  <!-- Rating Stars -->
  <div class="rating-input">
    <input type="radio" name="rating" value="1"> ⭐
    <input type="radio" name="rating" value="2"> ⭐⭐
    <input type="radio" name="rating" value="3"> ⭐⭐⭐
    <input type="radio" name="rating" value="4"> ⭐⭐⭐⭐
    <input type="radio" name="rating" value="5"> ⭐⭐⭐⭐⭐
  </div>
  
  <textarea name="komentar" placeholder="Tulis ulasan Anda..."></textarea>
  <button type="submit">Kirim Review</button>
</form>
```

#### 3. **Tampilkan Rata-rata Rating**
```php
$rating_query = mysqli_query($conn, 
  "SELECT AVG(rating) as avg_rating, COUNT(*) as total 
   FROM review WHERE produk_id = $produk_id");
$rating_data = mysqli_fetch_assoc($rating_query);

echo "Rating: " . round($rating_data['avg_rating'], 1) . "/5 
      (" . $rating_data['total'] . " ulasan)";
```

#### 4. **List Semua Review**
```php
$reviews = mysqli_query($conn, 
  "SELECT * FROM review 
   WHERE produk_id = $produk_id 
   ORDER BY created_at DESC 
   LIMIT 10");

while($review = mysqli_fetch_assoc($reviews)) {
  echo "⭐ " . $review['rating'] . " - " . $review['nama_reviewer'];
  echo "<p>" . $review['komentar'] . "</p>";
  echo date('d M Y', strtotime($review['created_at']));
}
```

## 🛠️ Admin Panel

Sudah diimplementasikan di `admin/pages/products/reviews.php`:
- ✅ Lihat semua review per produk
- ✅ Hapus review yang tidak sesuai
- ✅ Filter berdasarkan rating
- ✅ Sidebar navigation dan breadcrumbs

## 📊 Query Berguna

### Ambil Rating Tertinggi
```sql
SELECT produk_id, nama_reviewer, rating, komentar, created_at
FROM review
ORDER BY rating DESC
LIMIT 10;
```

### Ambil Rating Terendah
```sql
SELECT produk_id, nama_reviewer, rating, komentar, created_at
FROM review
WHERE rating <= 2
ORDER BY created_at DESC;
```

### Produk dengan Review Terbanyak
```sql
SELECT produk_id, COUNT(*) as total_review, AVG(rating) as avg_rating
FROM review
GROUP BY produk_id
ORDER BY total_review DESC;
```

### Review Bulan Ini
```sql
SELECT * FROM review
WHERE MONTH(created_at) = MONTH(NOW())
AND YEAR(created_at) = YEAR(NOW())
ORDER BY created_at DESC;
```

## 🔐 Best Practices

1. **Validasi Input**
   - Pastikan rating hanya 1-5
   - Sanitasi komentar (XSS protection)
   - Validasi nama reviewer

2. **Security**
   - Gunakan prepared statement
   - Limit karakter komentar (max 500 chars)
   - Rate limit submission review

3. **UX**
   - Tampilkan rating visual (stars)
   - Sorting: newest/oldest/highest/lowest rating
   - Pagination untuk banyak review

4. **Moderation** (optional)
   - Approval system sebelum publish
   - Flag review yang mencurigakan
   - Block user dengan banyak review negatif

## 📌 Catatan

- Sistem review saat ini hanya struktur database
- Implementasi UI dan backend endpoint masih perlu dikerjakan
- Rekomendasi: implementasi setelah stabilisasi fitur core

---

**Status**: Architecture Ready ✅ | Implementation Pending ⏳
