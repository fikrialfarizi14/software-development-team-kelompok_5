<?php
require_once '../includes/session.php';
cekStaff(); // petugas & pemilik boleh lihat laporan

// Tentukan rentang tanggal berdasarkan periode yang dipilih
$periode = $_GET['periode'] ?? 'harian';
$label_periode = 'Hari Ini';

switch ($periode) {
    case 'mingguan':
        $mulai = date('Y-m-d', strtotime('-6 days'));
        $label_periode = '7 Hari Terakhir';
        break;
    case 'bulanan':
        $mulai = date('Y-m-01');
        $label_periode = 'Bulan Ini';
        break;
    default:
        $periode = 'harian';
        $mulai = date('Y-m-d');
        $label_periode = 'Hari Ini';
}
$selesai = date('Y-m-d', strtotime('+1 day')); // batas atas (eksklusif) supaya termasuk hari ini penuh

// Jumlah pesanan & pendapatan (dari transaksi yang sudah lunas) dalam periode ini
$row = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as jumlah FROM pesanan WHERE tanggal_pesan >= '$mulai' AND tanggal_pesan < '$selesai'"
));
$jumlah_pesanan = $row['jumlah'];

$row = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COALESCE(SUM(t.jumlah_bayar),0) as total
     FROM transaksi t
     WHERE t.status_bayar = 'lunas' AND t.tanggal_bayar >= '$mulai' AND t.tanggal_bayar < '$selesai'"
));
$total_pendapatan = $row['total'];

// Rincian per jenis layanan dalam periode ini (untuk grafik batang)
$per_layanan = mysqli_query($koneksi,
    "SELECT l.nama_layanan, COUNT(p.id) as jumlah, COALESCE(SUM(p.total_harga),0) as total
     FROM pesanan p
     JOIN layanan l ON p.layanan_id = l.id
     WHERE p.tanggal_pesan >= '$mulai' AND p.tanggal_pesan < '$selesai'
     GROUP BY l.id
     ORDER BY total DESC"
);
$data_layanan = [];
$max_total = 1; // hindari pembagian dengan nol
while ($r = mysqli_fetch_assoc($per_layanan)) {
    $data_layanan[] = $r;
    if ($r['total'] > $max_total) $max_total = $r['total'];
}

// Daftar pesanan dalam periode ini (tabel detail)
$daftar_pesanan = mysqli_query($koneksi,
    "SELECT p.*, l.nama_layanan, u.nama as nama_pelanggan
     FROM pesanan p
     JOIN layanan l ON p.layanan_id = l.id
     JOIN users u ON p.user_id = u.id
     WHERE p.tanggal_pesan >= '$mulai' AND p.tanggal_pesan < '$selesai'
     ORDER BY p.tanggal_pesan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Laporan Transaksi</h2>

        <!-- Pilih periode -->
        <div style="margin-bottom:15px;">
            Periode: &nbsp;
            <a href="laporan.php?periode=harian" class="btn btn-sm <?= $periode=='harian' ? 'btn-primary' : 'btn-secondary' ?>">Harian</a>
            <a href="laporan.php?periode=mingguan" class="btn btn-sm <?= $periode=='mingguan' ? 'btn-primary' : 'btn-secondary' ?>">Mingguan</a>
            <a href="laporan.php?periode=bulanan" class="btn btn-sm <?= $periode=='bulanan' ? 'btn-primary' : 'btn-secondary' ?>">Bulanan</a>
        </div>

        <p style="color:#666;margin-bottom:10px;">Menampilkan data: <strong><?= $label_periode ?></strong></p>

        <!-- Ringkasan -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="angka"><?= $jumlah_pesanan ?></div>
                <div class="label">Jumlah Pesanan</div>
            </div>
            <div class="stat-box" style="border-left-color:#28a745;">
                <div class="angka" style="color:#28a745;font-size:18px;"><?= rupiah($total_pendapatan) ?></div>
                <div class="label">Pendapatan (Lunas)</div>
            </div>
        </div>
    </div>

    <!-- Grafik batang sederhana per layanan -->
    <div class="card">
        <h2>Pendapatan per Jenis Layanan</h2>
        <?php if (empty($data_layanan)): ?>
            <p style="color:#888;">Belum ada data pesanan di periode ini.</p>
        <?php else: ?>
            <?php foreach ($data_layanan as $d): $persen = round(($d['total'] / $max_total) * 100); ?>
            <div style="margin-bottom:10px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">
                    <span><?= $d['nama_layanan'] ?> (<?= $d['jumlah'] ?> pesanan)</span>
                    <span><?= rupiah($d['total']) ?></span>
                </div>
                <div style="background:#eee;border-radius:3px;overflow:hidden;height:18px;">
                    <div style="background:#1a73e8;height:100%;width:<?= $persen ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tabel detail -->
    <div class="card">
        <h2>Detail Pesanan</h2>
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
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($daftar_pesanan) === 0): ?>
                <tr><td colspan="7" style="text-align:center;color:#888;">Tidak ada pesanan di periode ini.</td></tr>
            <?php else: ?>
            <?php while ($row = mysqli_fetch_assoc($daftar_pesanan)): ?>
                <tr>
                    <td><?= $row['kode_pesanan'] ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= $row['nama_layanan'] ?></td>
                    <td><?= $row['berat_kg'] ?> kg</td>
                    <td><?= rupiah($row['total_harga']) ?></td>
                    <td><?= labelStatus($row['status']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_pesan'])) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
