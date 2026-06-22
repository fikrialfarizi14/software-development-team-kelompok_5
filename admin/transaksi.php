<?php
require_once '../includes/session.php';
cekPetugas();

$pesan = '';

// Catat pembayaran (insert kalau belum ada transaksi, update kalau sudah ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $jumlah     = (float)$_POST['jumlah_bayar'];
    $metode     = bersihkan($_POST['metode_pembayaran']);
    $metode_valid = ['tunai','transfer','e-wallet'];

    if ($pesanan_id && $jumlah > 0 && in_array($metode, $metode_valid)) {
        // Cek apakah pesanan ini sudah punya transaksi
        $cek = mysqli_query($koneksi, "SELECT id FROM transaksi WHERE pesanan_id = $pesanan_id");

        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($koneksi,
                "UPDATE transaksi 
                 SET jumlah_bayar = $jumlah, metode_pembayaran = '$metode', 
                     status_bayar = 'lunas', tanggal_bayar = NOW()
                 WHERE pesanan_id = $pesanan_id"
            );
        } else {
            mysqli_query($koneksi,
                "INSERT INTO transaksi (pesanan_id, jumlah_bayar, metode_pembayaran, status_bayar)
                 VALUES ($pesanan_id, $jumlah, '$metode', 'lunas')"
            );
        }
        $pesan = "Pembayaran berhasil dicatat.";
    } else {
        $pesan = "Data pembayaran tidak valid.";
    }
}

// Kalau datang dari tombol "Kelola Pembayaran" di update_status.php, langsung tampilkan form untuk pesanan itu
$pesanan_dipilih = isset($_GET['pesanan_id']) ? (int)$_GET['pesanan_id'] : 0;

// Ambil semua pesanan beserta status pembayarannya
$semua = mysqli_query($koneksi,
    "SELECT p.id, p.kode_pesanan, p.total_harga, p.status, u.nama as nama_pelanggan,
            t.jumlah_bayar, t.metode_pembayaran, t.status_bayar, t.tanggal_bayar
     FROM pesanan p
     JOIN users u ON p.user_id = u.id
     LEFT JOIN transaksi t ON t.pesanan_id = p.id
     ORDER BY p.tanggal_pesan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi & Pembayaran - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Style tambahan agar status bayar memiliki warna badge yang jelas */
        .badge-lunas {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-belum {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="container">

    <?php if ($pesan): ?>
        <div class="alert alert-success"><?= $pesan ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Catat Pembayaran</h2>
        <form method="POST">
            <div class="form-group">
                <label>Pesanan</label>
                <select name="pesanan_id" id="pesanan_id" required onchange="isiTotal()">
                    <option value="">-- Pilih Pesanan --</option>
                    <?php
                    mysqli_data_seek($semua, 0);
                    while ($row = mysqli_fetch_assoc($semua)):
                        $selected = ($pesanan_dipilih === (int)$row['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= $row['id'] ?>" data-total="<?= $row['total_harga'] ?>" <?= $selected ?>>
                        <?= $row['kode_pesanan'] ?> - <?= $row['nama_pelanggan'] ?> (<?= rupiah($row['total_harga']) ?>)
                        <?= $row['status_bayar'] === 'lunas' ? ' [sudah lunas]' : '' ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jumlah Bayar (Rp)</label>
                <input type="number" name="jumlah_bayar" id="jumlah_bayar" required min="0" step="500">
            </div>
            
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="metode_pembayaran" class="form-control" required>
                    <option value="tunai">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank (VA / Manual)</option>
                    <option value="e-wallet">E-Wallet (QRIS / OVO / Dana / Gopay)</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">Simpan Pembayaran (Tandai Lunas)</button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Transaksi</h2>
        <table>
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Total Tagihan</th>
                    <th>Status Bayar</th>
                    <th>Metode</th>
                    <th>Tanggal Bayar</th>
                </tr>
            </thead>
            <tbody>
            <?php mysqli_data_seek($semua, 0); while ($row = mysqli_fetch_assoc($semua)): ?>
                <tr>
                    <td><?= $row['kode_pesanan'] ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= rupiah($row['total_harga']) ?></td>
                    <td>
                        <?php if (isset($row['status_bayar']) && $row['status_bayar'] === 'lunas'): ?>
                            <span class="badge-lunas">Sudah Dibayar</span>
                        <?php else: ?>
                            <span class="badge-belum">Belum Bayar</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['metode_pembayaran'] ? ucfirst($row['metode_pembayaran']) : '-' ?></td>
                    <td><?= $row['tanggal_bayar'] ? date('d/m/Y H:i', strtotime($row['tanggal_bayar'])) : '-' ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function isiTotal() {
    var sel = document.getElementById('pesanan_id');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.total) {
        document.getElementById('jumlah_bayar').value = opt.dataset.total;
    }
}
// Kalau halaman dibuka langsung dengan pesanan terpilih, isi otomatis jumlah bayarnya
window.onload = isiTotal;
</script>

</body>
</html>