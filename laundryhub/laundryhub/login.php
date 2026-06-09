<?php
session_start();
require_once 'includes/koneksi.php';

// Kalau sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pesan = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = bersihkan($_POST['email']);
    $password = md5($_POST['password']); // MD5 sederhana untuk pemula

    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['nama']     = $user['nama'];
        $_SESSION['role']     = $user['role'];

        // Redirect sesuai role
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $pesan = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - LaundryHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrap">
    <div style="text-align:center;margin-bottom:20px;">
        <h1 style="color:#1a73e8;font-size:24px;">🧺 LaundryHub</h1>
        <p style="color:#666;font-size:13px;">Sistem Informasi Laundry</p>
    </div>

    <div class="card">
        <h2>Login</h2>

        <?php if ($pesan): ?>
            <div class="alert alert-danger"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@kamu.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>

        <p style="text-align:center;margin-top:12px;font-size:13px;">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
    </div>

    <div class="alert alert-info" style="font-size:12px;">
        <strong>Akun Admin Default:</strong><br>
        Email: admin@laundryhub.com<br>
        Password: admin123
    </div>
</div>
</body>
</html>
