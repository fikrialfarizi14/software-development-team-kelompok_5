<?php
session_start();
require_once 'koneksi.php';

// -----------------------------------------------------------------
// Cek apakah halaman yang sedang diakses ada di dalam folder admin/
// Dipakai supaya redirect "Location:" selalu menuju alamat yang benar,
// baik diakses dari folder utama maupun dari dalam folder admin/.
// -----------------------------------------------------------------
function sedangDiFolderAdmin() {
    return strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
}

// Cek apakah user sudah login (peran apa saja)
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        $tujuan = sedangDiFolderAdmin() ? '../login.php' : 'login.php';
        header("Location: $tujuan");
        exit();
    }
}

// Cek apakah user adalah petugas (mengelola pesanan, layanan, transaksi, dll)
function cekPetugas() {
    cekLogin();
    if ($_SESSION['role'] !== 'petugas') {
        $tujuan = sedangDiFolderAdmin() ? '../index.php' : 'index.php';
        header("Location: $tujuan");
        exit();
    }
}

// Cek apakah user adalah petugas ATAU pemilik
// (dipakai di halaman yang boleh dilihat keduanya, misal Dashboard & Laporan)
function cekStaff() {
    cekLogin();
    if (!in_array($_SESSION['role'], ['petugas', 'pemilik'])) {
        $tujuan = sedangDiFolderAdmin() ? '../index.php' : 'index.php';
        header("Location: $tujuan");
        exit();
    }
}
?>
