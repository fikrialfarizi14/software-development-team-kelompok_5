<?php
require_once '../includes/session.php';
cekPetugas();

$pelanggan = mysqli_query($koneksi,
    "SELECT u.*, COUNT(p.id) as total_pesanan
     FROM users u
     LEFT JOIN pesanan p ON u.id = p.user_id
     WHERE u.role = 'pelanggan'
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Data Pelanggan</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Total Pesanan</th>
                    <th>Terdaftar</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($pelanggan)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['nama'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['no_hp'] ?: '-' ?></td>
                    <td><?= $row['total_pesanan'] ?> pesanan</td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
