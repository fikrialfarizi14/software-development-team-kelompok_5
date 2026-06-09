<?php
session_start();
require_once 'koneksi.php';

// Cek apakah user sudah login
function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Cek apakah user adalah admin
function cekAdmin() {
    cekLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}
?>
