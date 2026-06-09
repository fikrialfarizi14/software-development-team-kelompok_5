-- =============================================
-- LaundryHub - Database Setup
-- Jalankan file ini di phpMyAdmin atau MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS laundryhub;
USE laundryhub;

-- Tabel Users (pelanggan & admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    role ENUM('pelanggan', 'admin') DEFAULT 'pelanggan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Layanan Laundry
CREATE TABLE IF NOT EXISTS layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    estimasi_hari INT NOT NULL,
    keterangan TEXT
);

-- Tabel Pesanan
CREATE TABLE IF NOT EXISTS pesanan (
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
    tanggal_selesai DATE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (layanan_id) REFERENCES layanan(id)
);

-- Data awal: Admin
INSERT INTO users (nama, email, password, role) VALUES
('Admin LaundryHub', 'admin@laundryhub.com', MD5('admin123'), 'admin');

-- Data awal: Layanan
INSERT INTO layanan (nama_layanan, harga_per_kg, estimasi_hari, keterangan) VALUES
('Cuci Kering', 5000, 2, 'Cuci dan dikeringkan saja'),
('Cuci Setrika', 8000, 3, 'Cuci, kering, dan disetrika rapi'),
('Cuci Express', 12000, 1, 'Selesai dalam 1 hari');
