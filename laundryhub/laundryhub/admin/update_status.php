<?php
require_once '../includes/session.php';
cekAdmin();

$id = (int)($_GET['id'] ?? 0);
$pesanan = null;
$pesan = '';

if ($id) {
    $result = mysqli_query($koneksi,
        "SELECT p.*, l.nama_layanan, u.nama as nama_pelanggan
         FROM pesanan p 
         JOIN layanan l ON p.layanan_id = l.id
         JOIN users u ON p.user_id = u.id
         WHERE p.id = $id"
    );
    $pesanan = mysqli_fetch_assoc($result);
}

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pesanan) {
    $status_baru = bersihkan($_POST['status']);
    $valid_status = ['menunggu','diproses','selesai_dicuci','siap_diambil','selesai'];

    if (in_array($status_baru, $valid_status)) {
        mysqli_query($koneksi, "UPDATE pesanan SET status = '$status_baru' WHERE id = $id");
        $pesan = "Status berhasil diupdate!";
        // Refresh pesanan data
        $result = mysqli_query($koneksi,
            "SELECT p.*, l.nama_layanan, u.nama as nama_pelanggan
             FROM pesanan p 
             JOIN layanan l ON p.layanan_id = l.id
             JOIN users u ON p.user_id = u.id
             WHERE p.id = $id"
        );
        $pesanan = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Status - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">🧺 LaundryHub - Admin</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="pesanan.php">← Kembali ke Pesanan</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <?php if (!$pesanan): ?>
        <div class="alert alert-danger">Pesanan tidak ditemukan.</div>
    <?php else: ?>

    <div class="card">
        <h2>Update Status Pesanan</h2>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- Info pesanan -->
        <table style="margin-bottom:20px;">
            <tr><th>Kode Pesanan</th><td><strong><?= $pesanan['kode_pesanan'] ?></strong></td></tr>
            <tr><th>Pelanggan</th><td><?= $pesanan['nama_pelanggan'] ?></td></tr>
            <tr><th>Layanan</th><td><?= $pesanan['nama_layanan'] ?></td></tr>
            <tr><th>Berat</th><td><?= $pesanan['berat_kg'] ?> kg</td></tr>
            <tr><th>Total</th><td><?= rupiah($pesanan['total_harga']) ?></td></tr>
            <tr><th>Alamat Pickup</th><td><?= $pesanan['alamat_pickup'] ?: '-' ?></td></tr>
            <tr><th>Catatan</th><td><?= $pesanan['catatan'] ?: '-' ?></td></tr>
            <tr><th>Status Sekarang</th><td><?= labelStatus($pesanan['status']) ?></td></tr>
        </table>

        <!-- Form update status -->
        <form method="POST">
            <div class="form-group">
                <label>Ubah Status ke:</label>
                <select name="status">
                    <option value="menunggu"       <?= $pesanan['status']=='menunggu'       ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="diproses"       <?= $pesanan['status']=='diproses'       ? 'selected' : '' ?>>Sedang Diproses</option>
                    <option value="selesai_dicuci" <?= $pesanan['status']=='selesai_dicuci' ? 'selected' : '' ?>>Selesai Dicuci</option>
                    <option value="siap_diambil"   <?= $pesanan['status']=='siap_diambil'   ? 'selected' : '' ?>>Siap Diambil</option>
                    <option value="selesai"        <?= $pesanan['status']=='selesai'        ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="pesanan.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    <?php endif; ?>
</div>

</body>
</html>
