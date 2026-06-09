<?php
require_once '../includes/session.php';
cekAdmin();

// Ambil statistik
$total_pesanan  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan"))[0];
$pesanan_baru   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan WHERE status='menunggu'"))[0];
$pesanan_proses = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan WHERE status IN ('diproses','selesai_dicuci')"))[0];
$total_pelanggan= mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE role='pelanggan'"))[0];

$pendapatan_row = mysqli_fetch_row(mysqli_query($koneksi, "SELECT SUM(total_harga) FROM pesanan WHERE status='selesai'"));
$total_pendapatan = $pendapatan_row[0] ?? 0;

// 10 pesanan terbaru
$pesanan_terbaru = mysqli_query($koneksi,
    "SELECT p.*, l.nama_layanan, u.nama as nama_pelanggan 
     FROM pesanan p 
     JOIN layanan l ON p.layanan_id = l.id
     JOIN users u ON p.user_id = u.id
     ORDER BY p.tanggal_pesan DESC LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">🧺 LaundryHub - Admin</span>
    <div>
        <a href="pesanan.php">Pesanan</a>
        <a href="layanan.php">Layanan</a>
        <a href="pelanggan.php">Pelanggan</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2 style="margin-bottom:15px;">Dashboard</h2>

    <!-- Statistik -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="angka"><?= $total_pesanan ?></div>
            <div class="label">Total Pesanan</div>
        </div>
        <div class="stat-box" style="border-left-color:#ffc107;">
            <div class="angka" style="color:#ffc107;"><?= $pesanan_baru ?></div>
            <div class="label">Menunggu Konfirmasi</div>
        </div>
        <div class="stat-box" style="border-left-color:#0d6efd;">
            <div class="angka" style="color:#0d6efd;"><?= $pesanan_proses ?></div>
            <div class="label">Sedang Diproses</div>
        </div>
        <div class="stat-box" style="border-left-color:#28a745;">
            <div class="angka" style="color:#28a745;"><?= $total_pelanggan ?></div>
            <div class="label">Total Pelanggan</div>
        </div>
        <div class="stat-box" style="border-left-color:#dc3545;">
            <div class="angka" style="color:#dc3545;font-size:18px;"><?= rupiah($total_pendapatan) ?></div>
            <div class="label">Total Pendapatan</div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="card">
        <h2>Pesanan Terbaru</h2>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Berat</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                <tr>
                    <td><?= $row['kode_pesanan'] ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= $row['nama_layanan'] ?></td>
                    <td><?= $row['berat_kg'] ?> kg</td>
                    <td><?= rupiah($row['total_harga']) ?></td>
                    <td><?= labelStatus($row['status']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_pesan'])) ?></td>
                    <td>
                        <a href="update_status.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                            Update
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <p style="margin-top:10px;"><a href="pesanan.php">Lihat semua pesanan →</a></p>
    </div>
</div>

</body>
</html>
