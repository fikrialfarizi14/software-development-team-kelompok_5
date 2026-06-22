-- =============================================
-- LaundryHub - Database Setup (REVISI sesuai proposal)
-- Jalankan file ini di phpMyAdmin atau MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS laundryhub_laravel2;
USE laundryhub_laravel2;

-- Hapus tabel lama kalau ada (urutan dibalik karena foreign key)
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS tracking_status;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS users;

-- =============================================
-- Tabel users (3 peran sesuai proposal: pelanggan, petugas, pemilik)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,     -- disimpan dalam bentuk hash bcrypt
    no_hp VARCHAR(20),
    alamat TEXT,
    role ENUM('pelanggan', 'petugas', 'pemilik') DEFAULT 'pelanggan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- Tabel layanan (ditambah kolom status sesuai proposal)
-- =============================================
CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    estimasi_hari INT NOT NULL,
    keterangan TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
);

-- =============================================
-- Tabel pesanan (ditambah kolom estimasi_selesai sesuai proposal)
-- =============================================
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    layanan_id INT NOT NULL,
    berat_kg DECIMAL(5,2) NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    alamat_pickup TEXT,
    catatan TEXT,
    status ENUM('menunggu', 'diproses', 'selesai_dicuci', 'siap_diambil', 'selesai') DEFAULT 'menunggu',
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimasi_selesai DATE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (layanan_id) REFERENCES layanan(id)
);

-- =============================================
-- Tabel tracking_status (BARU - riwayat perubahan status, sesuai proposal 5.2)
-- =============================================
CREATE TABLE tracking_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    status_baru VARCHAR(30) NOT NULL,
    catatan VARCHAR(255),
    waktu_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
);

-- =============================================
-- Tabel transaksi (BARU - pencatatan pembayaran, sesuai proposal 5.2)
-- Satu pesanan hanya punya satu transaksi (one-to-one) -> pesanan_id dibuat UNIQUE
-- =============================================
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL UNIQUE,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    metode_pembayaran ENUM('tunai', 'transfer', 'e-wallet') DEFAULT 'tunai',
    tanggal_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_bayar ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
);

-- =============================================
-- Data awal: akun petugas & pemilik
-- (Password sudah di-hash dengan bcrypt, BUKAN disimpan polos)
-- petugas@laundryhub.com  -> password: petugas123
-- pemilik@laundryhub.com  -> password: pemilik123
-- =============================================
INSERT INTO users (nama, email, password, role) VALUES
('Petugas LaundryHub', 'petugas@laundryhub.com', '$2b$10$BG8gDS8GQLqP7FSsFzEORu7QId5D.9V8GAriiy3/..4DrYoyBHt5G', 'petugas'),
('Pemilik LaundryHub', 'pemilik@laundryhub.com', '$2b$10$13m0Wdbp6WIFacQhGuJ43eY1gGG33TzvWNaGGT3WvKJhNYcIe9hrm', 'pemilik');

-- Data awal: Layanan
INSERT INTO layanan (nama_layanan, harga_per_kg, estimasi_hari, keterangan, status) VALUES
('Cuci Kering', 5000, 2, 'Cuci dan dikeringkan saja', 'aktif'),
('Cuci Setrika', 8000, 3, 'Cuci, kering, dan disetrika rapi', 'aktif'),
('Cuci Express', 12000, 1, 'Selesai dalam 1 hari', 'aktif');
