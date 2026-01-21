-- Database: sayur_mayur_app
-- Struktur database untuk E-commerce Sayur Mayur
-- Pastikan untuk menyesuaikan dengan kebutuhan Anda

-- Create database (jika belum ada)
CREATE DATABASE IF NOT EXISTS sayur_mayur_app;
USE sayur_mayur_app;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (password: admin123)
-- Password hash untuk 'admin123' menggunakan password_hash PHP
INSERT INTO `admin` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$e/hfN8HzqblwIlEr.dXhc.eQyJMSw0XnsFQhXL8hQqr4BLJvzFUPi', 'admin@sayurmayur.com');

-- Tabel Produk
CREATE TABLE IF NOT EXISTS `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text,
  `harga` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `kategori` varchar(50) DEFAULT NULL,
  `berat` int(11) DEFAULT NULL COMMENT 'Berat dalam gram',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data produk (13 produk dengan semua image yang tersedia)
INSERT INTO `produk` (`nama`, `deskripsi`, `harga`, `stock`, `gambar`, `kategori`, `berat`) VALUES
('Bayam', 'Bayam segar pilihan langsung dari petani lokal. Kaya akan zat besi dan vitamin A.', 8000.00, 50, 'bayam.jpg', 'Sayuran', 250),
('Kangkung', 'Kangkung segar berkualitas tinggi. Cocok untuk tumis dan lalapan segar.', 6000.00, 45, 'kangkung.jpg', 'Sayuran', 250),
('Wortel', 'Wortel segar manis dan renyah. Kaya akan vitamin A dan mineral.', 12000.00, 40, 'wortel.jpg', 'Sayuran', 500),
('Tomat', 'Tomat segar merah merona. Cocok untuk masakan, salad dan jus.', 10000.00, 60, 'tomat.jpg', 'Buah', 500),
('Brokoli', 'Brokoli segar hijau. Kaya serat dan nutrisi lengkap.', 18000.00, 30, 'brokoli.jpg', 'Sayuran', 400),
('Sawi', 'Sawi hijau segar dan renyah. Cocok untuk berbagai masakan tradisional.', 7000.00, 55, 'sawi.jpg', 'Sayuran', 250),
('Kentang', 'Kentang berkualitas untuk berbagai olahan masakan.', 15000.00, 70, 'kentang.jpg', 'Sayuran', 1000),
('Cabai Merah', 'Cabai merah segar pedas. Bumbu dapur yang tidak boleh terlewat.', 25000.00, 35, 'cabai.jpg', 'Bumbu', 100),
('Edamame', 'Edamame segar organik pilihan. Kaya protein dan nutrisi seimbang.', 14000.00, 42, 'edamame.jpg', 'Sayuran', 300),
('Petai', 'Petai segar berbau khas yang nikmat. Sumber protein nabati tinggi.', 16000.00, 25, 'petai.jpg', 'Daun-daunan', 200),
('Mangga', 'Mangga segar manis dari perkebunan terpilih. Buah tropis berkualitas.', 20000.00, 55, 'mangga.jpg', 'Buah', 600),
('Semangka', 'Semangka segar besar manis dan menyegarkan. Cocok untuk jus dan camilan.', 35000.00, 40, 'semangka.jpg', 'Buah', 3000),
('Nanas', 'Nanas segar manis dan berair. Kaya vitamin C untuk kesehatan.', 18000.00, 48, 'nanas.jpeg', 'Buah', 1000);

-- Tabel Ongkos Kirim (HARUS DIBUAT SEBELUM TRANSAKSI)
CREATE TABLE IF NOT EXISTS `ongkos_kirim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lokasi` varchar(100) NOT NULL UNIQUE,
  `biaya` decimal(10,2) NOT NULL,
  `deskripsi` text,
  `estimasi_hari` int(11) DEFAULT 1,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_aktif` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Ongkos Kirim Default
INSERT INTO `ongkos_kirim` (`lokasi`, `biaya`, `deskripsi`, `estimasi_hari`, `aktif`) VALUES
('Jakarta Pusat', 15000.00, 'Wilayah Jakarta Pusat', 1, 1),
('Jakarta Selatan', 15000.00, 'Wilayah Jakarta Selatan', 1, 1),
('Jakarta Barat', 15000.00, 'Wilayah Jakarta Barat', 1, 1),
('Jakarta Timur', 15000.00, 'Wilayah Jakarta Timur', 1, 1),
('Jakarta Utara', 15000.00, 'Wilayah Jakarta Utara', 1, 1),
('Tangerang', 20000.00, 'Kota Tangerang & Tangerang Selatan', 1, 1),
('Bekasi', 20000.00, 'Kota Bekasi & Bekasi Utara', 1, 1),
('Depok', 20000.00, 'Kota Depok', 1, 1),
('Bogor', 25000.00, 'Kota Bogor & Kabupaten Bogor', 2, 1);

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `subtotal` decimal(10,2) NOT NULL,
  `kode_voucher` varchar(50) DEFAULT NULL,
  `diskon` decimal(10,2) DEFAULT 0,
  `ongkos_kirim_id` int(11) DEFAULT NULL,
  `shipping_biaya` decimal(10,2) DEFAULT 0,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'COD',
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`ongkos_kirim_id`) REFERENCES `ongkos_kirim` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL UNIQUE,
  `deskripsi` text,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Kategori Default
INSERT INTO `kategori` (`nama`, `deskripsi`, `icon`) VALUES
('Sayuran', 'Berbagai jenis sayuran segar', 'bi-leaf'),
('Buah', 'Buah-buahan segar berkualitas', 'bi-apple'),
('Bumbu', 'Bumbu dapur lengkap', 'bi-spoon'),
('Daun-daunan', 'Daun-daunan untuk masakan', 'bi-flower1');

-- Tabel Pesan
CREATE TABLE IF NOT EXISTS `pesan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `subjek` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Voucher Diskon
CREATE TABLE IF NOT EXISTS `voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL UNIQUE,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('persen','nominal') NOT NULL,
  `nilai` decimal(10,2) NOT NULL,
  `min_pembelian` decimal(10,2) DEFAULT 0,
  `max_diskon` decimal(10,2) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  `terpakai` int(11) DEFAULT 0,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kode` (`kode`),
  KEY `idx_aktif` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Voucher Default
INSERT INTO `voucher` (`kode`, `nama`, `tipe`, `nilai`, `min_pembelian`, `max_diskon`, `kuota`, `tanggal_mulai`, `tanggal_selesai`, `aktif`) VALUES
('WELCOME10', 'Diskon 10% untuk Pelanggan Baru', 'persen', 10.00, 50000.00, 20000.00, 100, '2026-01-01', '2026-12-31', 1),
('PROMO50K', 'Potongan Rp 50.000', 'nominal', 50000.00, 200000.00, NULL, 50, '2026-01-01', '2026-12-31', 1),
('HEMAT20', 'Diskon 20% Max Rp 100.000', 'persen', 20.00, 100000.00, 100000.00, 200, '2026-01-01', '2026-12-31', 1);

-- Tabel Review Produk (Opsional untuk fitur lanjutan)
CREATE TABLE IF NOT EXISTS `review` (
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

-- Views untuk reporting (Opsional)
CREATE OR REPLACE VIEW `v_sales_summary` AS
SELECT 
  DATE(t.tanggal) as tanggal,
  COUNT(t.id) as total_transaksi,
  SUM(t.total) as total_pendapatan,
  AVG(t.total) as rata_rata_transaksi
FROM transaksi t
GROUP BY DATE(t.tanggal)
ORDER BY tanggal DESC;

CREATE OR REPLACE VIEW `v_produk_terlaris` AS
SELECT 
  p.id,
  p.nama,
  p.harga,
  SUM(dt.qty) as total_terjual,
  SUM(dt.subtotal) as total_pendapatan
FROM produk p
INNER JOIN detail_transaksi dt ON p.id = dt.produk_id
GROUP BY p.id, p.nama, p.harga
ORDER BY total_terjual DESC;

-- Indexes untuk performa
CREATE INDEX idx_produk_nama ON produk(nama);
CREATE INDEX idx_produk_kategori ON produk(kategori);
CREATE INDEX idx_produk_stock ON produk(stock);
