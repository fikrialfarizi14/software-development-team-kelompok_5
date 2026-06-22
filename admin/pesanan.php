<?php
require_once '../includes/session.php';
cekPetugas();

// Filter status
$filter = isset($_GET['status']) ? bersihkan($_GET['status']) : '';
$where  = $filter ? "WHERE p.status = '$filter'" : '';

$pesanan_semua = mysqli_query($koneksi,
    "SELECT p.*, l.nama_layanan, u.nama as nama_pelanggan, u.no_hp
     FROM pesanan p 
     JOIN layanan l ON p.layanan_id = l.id
     JOIN users u ON p.user_id = u.id
     $where
     ORDER BY p.tanggal_pesan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Kelola Pesanan</h2>

        <!-- Filter -->
        <div style="margin-bottom:12px;">
            Filter status: &nbsp;
            <a href="pesanan.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
            <a href="pesanan.php?status=menunggu" class="btn btn-sm <?= $filter=='menunggu' ? 'btn-primary' : 'btn-secondary' ?>">Menunggu</a>
            <a href="pesanan.php?status=diproses" class="btn btn-sm <?= $filter=='diproses' ? 'btn-primary' : 'btn-secondary' ?>">Diproses</a>
            <a href="pesanan.php?status=selesai_dicuci" class="btn btn-sm <?= $filter=='selesai_dicuci' ? 'btn-primary' : 'btn-secondary' ?>">Selesai Dicuci</a>
            <a href="pesanan.php?status=siap_diambil" class="btn btn-sm <?= $filter=='siap_diambil' ? 'btn-primary' : 'btn-secondary' ?>">Siap Diambil</a>
            <a href="pesanan.php?status=selesai" class="btn btn-sm <?= $filter=='selesai' ? 'btn-primary' : 'btn-secondary' ?>">Selesai</a>
        </div>

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
            <?php if (mysqli_num_rows($pesanan_semua) === 0): ?>
                <tr><td colspan="8" style="text-align:center;color:#888;">Tidak ada pesanan.</td></tr>
            <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($pesanan_semua)): ?>
                <tr>
                    <td><small><?= $row['kode_pesanan'] ?></small></td>
                    <td>
                        <?= $row['nama_pelanggan'] ?>
                        <?php if ($row['no_hp']): ?>
                        <br><small style="color:#666;"><?= $row['no_hp'] ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['nama_layanan'] ?></td>
                    <td><?= $row['berat_kg'] ?> kg</td>
                    <td><?= rupiah($row['total_harga']) ?></td>
                    <td><?= labelStatus($row['status']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_pesan'])) ?></td>
                    <td>
                        <a href="update_status.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Update</a>
                        <a href="../tracking.php?kode=<?= $row['kode_pesanan'] ?>" class="btn btn-sm btn-secondary">Lihat</a>
                        <a href="cetak_nota.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" target="_blank">Cetak Nota</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
