<?php
session_start();
require_once 'includes/koneksi.php';

$pesan = '';
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = bersihkan($_POST['nama']);
    $email    = bersihkan($_POST['email']);
    $no_hp    = bersihkan($_POST['no_hp']);

    // Hash password dengan bcrypt (BUKAN MD5) sesuai kebutuhan keamanan di proposal
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Cek email sudah ada belum
    $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Email sudah terdaftar!";
    } else {
        // Akun yang daftar sendiri lewat form ini selalu berperan sebagai pelanggan.
        // Akun petugas/pemilik dibuat langsung oleh pengelola lewat database.
        $query = "INSERT INTO users (nama, email, password, no_hp, role) 
                  VALUES ('$nama', '$email', '$password', '$no_hp', 'pelanggan')";
        if (mysqli_query($koneksi, $query)) {
            $sukses = true;
        } else {
            $pesan = "Gagal mendaftar. Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - LaundryHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrap">
    <div style="text-align:center;margin-bottom:20px;">
        <h1 style="color:#1a73e8;font-size:24px;">🧺 LaundryHub</h1>
        <p style="color:#666;font-size:13px;">Buat Akun Baru</p>
    </div>

    <div class="card">
        <h2>Daftar Akun</h2>

        <?php if ($sukses): ?>
            <div class="alert alert-success">Pendaftaran berhasil! <a href="login.php">Login sekarang</a></div>
        <?php else: ?>

        <?php if ($pesan): ?>
            <div class="alert alert-danger"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Nama kamu" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@kamu.com" required>
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Daftar</button>
        </form>

        <p style="text-align:center;margin-top:12px;font-size:13px;">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
