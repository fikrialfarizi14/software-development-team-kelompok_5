<?php
require_once '../includes/session.php';
cekStaff(); // boleh diakses petugas maupun pemilik

// Ambil statistik
$total_pesanan  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan"))[0];
$pesanan_baru   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan WHERE status='menunggu'"))[0];
$pesanan_proses = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pesanan WHERE status IN ('diproses','selesai_dicuci')"))[0];
$total_pelanggan= mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE role='pelanggan'"))[0];

// Total pendapatan dihitung dari transaksi yang sudah lunas (bukan sekadar status pesanan)
$pendapatan_row   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT SUM(jumlah_bayar) FROM transaksi WHERE status_bayar='lunas'"));
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
    <title>Dashboard - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '_navbar.php'; ?>

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
            <div class="label">Total Pendapatan (Lunas)</div>
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
                    <?php if ($_SESSION['role'] === 'petugas'): ?>
                    <th>Aksi</th>
                    <?php endif; ?>
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
                    <?php if ($_SESSION['role'] === 'petugas'): ?>
                    <td>
                        <a href="update_status.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                            Update
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($_SESSION['role'] === 'petugas'): ?>
        <p style="margin-top:10px;"><a href="pesanan.php">Lihat semua pesanan →</a></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
