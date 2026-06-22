<?php
require_once '../includes/session.php';
cekPetugas();

$id = (int)($_GET['id'] ?? 0);

$result = mysqli_query($koneksi,
    "SELECT p.*, l.nama_layanan, l.harga_per_kg, u.nama as nama_pelanggan, u.no_hp,
            t.status_bayar, t.metode_pembayaran, t.tanggal_bayar
     FROM pesanan p
     JOIN layanan l ON p.layanan_id = l.id
     JOIN users u ON p.user_id = u.id
     LEFT JOIN transaksi t ON t.pesanan_id = p.id
     WHERE p.id = $id"
);
$nota = mysqli_fetch_assoc($result);

if (!$nota) {
    die("Pesanan tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota - <?= $nota['kode_pesanan'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; max-width: 380px; margin: 20px auto; }
        h2 { text-align: center; margin-bottom: 2px; }
        .sub { text-align: center; color: #666; margin-bottom: 15px; font-size: 12px; }
        hr { border: none; border-top: 1px dashed #999; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        .label { color: #555; width: 45%; }
        .total { font-weight: bold; font-size: 15px; }
        .btn-print {
            display: block; width: 100%; margin-top: 20px; padding: 10px;
            background: #1a73e8; color: #fff; border: none; border-radius: 4px;
            font-size: 14px; cursor: pointer;
        }
        @media print {
            .btn-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <h2>🧺 LaundryHub</h2>
    <p class="sub">Nota Pesanan Laundry</p>
    <hr>

    <table>
        <tr><td class="label">Kode Pesanan</td><td><strong><?= $nota['kode_pesanan'] ?></strong></td></tr>
        <tr><td class="label">Tanggal</td><td><?= date('d/m/Y H:i', strtotime($nota['tanggal_pesan'])) ?></td></tr>
        <tr><td class="label">Pelanggan</td><td><?= $nota['nama_pelanggan'] ?></td></tr>
        <tr><td class="label">No. HP</td><td><?= $nota['no_hp'] ?: '-' ?></td></tr>
    </table>
    <hr>

    <table>
        <tr><td class="label">Layanan</td><td><?= $nota['nama_layanan'] ?></td></tr>
        <tr><td class="label">Berat</td><td><?= $nota['berat_kg'] ?> kg</td></tr>
        <tr><td class="label">Harga/kg</td><td><?= rupiah($nota['harga_per_kg']) ?></td></tr>
    </table>
    <hr>

    <table>
        <tr><td class="label total">Total Bayar</td><td class="total"><?= rupiah($nota['total_harga']) ?></td></tr>
        <tr><td class="label">Status Bayar</td><td><?= $nota['status_bayar'] === 'lunas' ? 'LUNAS' : 'BELUM BAYAR' ?></td></tr>
        <?php if ($nota['status_bayar'] === 'lunas'): ?>
        <tr><td class="label">Metode</td><td><?= ucfirst($nota['metode_pembayaran']) ?></td></tr>
        <?php endif; ?>
    </table>
    <hr>

    <p class="sub">Terima kasih telah menggunakan LaundryHub!</p>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Nota</button>

</body>
</html>
