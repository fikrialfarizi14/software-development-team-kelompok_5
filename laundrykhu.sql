-- ============================================================
-- LaundryKu - Database Setup
-- ============================================================

CREATE DATABASE IF NOT EXISTS laundrykhu
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE laundrykhu;

-- ------------------------------------------------------------
-- TABEL: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)  NOT NULL,
  email       VARCHAR(100)  NOT NULL UNIQUE,
  password    VARCHAR(255)  NOT NULL,
  role        ENUM('customer','admin','petugas') DEFAULT 'customer',
  phone       VARCHAR(20),
  address     TEXT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- TABEL: layanan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS layanan (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nama_layanan    VARCHAR(100) NOT NULL,
  deskripsi       TEXT,
  harga_per_kg    DECIMAL(10,2) NOT NULL,
  estimasi_hari   INT NOT NULL DEFAULT 2,
  status          ENUM('aktif','nonaktif') DEFAULT 'aktif',
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- TABEL: pesanan
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan    VARCHAR(20) NOT NULL UNIQUE,
  id_customer     INT NOT NULL,
  id_layanan      INT NOT NULL,
  berat_kg        DECIMAL(5,2) NOT NULL,
  total_harga     DECIMAL(12,2) NOT NULL,
  status_pesanan  ENUM('menunggu','diproses','selesai','diambil') DEFAULT 'menunggu',
  tanggal_masuk   DATE NOT NULL,
  estimasi_selesai DATE,
  catatan         TEXT,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_customer) REFERENCES users(id),
  FOREIGN KEY (id_layanan)  REFERENCES layanan(id)
);

-- ------------------------------------------------------------
-- TABEL: tracking_status
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tracking_status (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  id_pesanan  INT NOT NULL,
  status_baru ENUM('menunggu','diproses','dicuci','selesai','diambil') NOT NULL,
  catatan     TEXT,
  waktu_update DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pesanan) REFERENCES pesanan(id)
);

-- ------------------------------------------------------------
-- TABEL: transaksi
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transaksi (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  id_pesanan          INT NOT NULL UNIQUE,
  jumlah_bayar        DECIMAL(12,2) NOT NULL,
  metode_pembayaran   ENUM('cash','transfer','e-wallet') DEFAULT 'cash',
  tanggal_bayar       DATE,
  status_bayar        ENUM('belum','lunas') DEFAULT 'belum',
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pesanan) REFERENCES pesanan(id)
);

-- ============================================================
-- DATA AWAL (SEEDER)
-- ============================================================

-- Admin & Customer default
INSERT INTO users (name, email, password, role, phone) VALUES
('Admin LaundryKu', 'admin@laundrykhu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081200000001'),
('Petugas Satu',    'petugas@laundrykhu.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas','081200000002'),
('Customer Demo',   'customer@email.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer','085100000003');
-- Password semua: password (hash bcrypt Laravel-compatible, ganti jika pakai PHP password_hash)

-- Layanan default
INSERT INTO layanan (nama_layanan, deskripsi, harga_per_kg, estimasi_hari) VALUES
('Cuci Kering',    'Cuci dan keringkan saja tanpa setrika', 5000, 2),
('Cuci Setrika',   'Cuci, keringkan, dan setrika rapi',    8000, 3),
('Express',        'Selesai dalam 1 hari kerja',           12000, 1),
('Cuci Sepatu',    'Khusus cuci sepatu bersih menyeluruh', 25000, 3);
