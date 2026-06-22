<?php
require_once 'includes/session.php';
cekLogin();

// Hanya pelanggan yang bisa akses halaman ini
if (in_array($_SESSION['role'], ['petugas', 'pemilik'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$pesan = '';
$sukses = false;

// Ambil daftar layanan yang masih aktif saja
$layanan_list = mysqli_query($koneksi, "SELECT * FROM layanan WHERE status = 'aktif' ORDER BY harga_per_kg ASC");

// Proses form pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $layanan_id    = (int)$_POST['layanan_id'];
    $berat_kg      = (float)$_POST['berat_kg'];
    $alamat_pickup = bersihkan($_POST['alamat_pickup']);
    $catatan       = bersihkan($_POST['catatan']);

    // Ambil harga & estimasi hari dari layanan yang dipilih (harus masih aktif)
    $lyr = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id = $layanan_id AND status = 'aktif'");
    $lyr_data = mysqli_fetch_assoc($lyr);

    if ($lyr_data && $berat_kg > 0) {
        $total_harga = $berat_kg * $lyr_data['harga_per_kg'];
        $kode        = generateKodePesanan();
        $user_id     = $_SESSION['user_id'];
        $estimasi_hr = (int)$lyr_data['estimasi_hari'];

        $query = "INSERT INTO pesanan 
                    (kode_pesanan, user_id, layanan_id, berat_kg, total_harga, alamat_pickup, catatan, estimasi_selesai)
                  VALUES 
                    ('$kode', $user_id, $layanan_id, $berat_kg, $total_harga, '$alamat_pickup', '$catatan',
                     DATE_ADD(CURDATE(), INTERVAL $estimasi_hr DAY))";

        if (mysqli_query($koneksi, $query)) {
            $pesanan_id = mysqli_insert_id($koneksi);

            // Catat riwayat status pertama (Menunggu Konfirmasi) ke tracking_status
            catatTracking($pesanan_id, 'menunggu', 'Pesanan dibuat oleh pelanggan');

            $sukses = true;
            $pesan = "Pesanan berhasil dibuat! Kode pesanan kamu: <strong>$kode</strong>";
        } else {
            $pesan = "Gagal membuat pesanan.";
        }
    } else {
        $pesan = "Data tidak valid!";
    }
}

// MODIFIKASI: Ambil data pesanan serta status_bayar dari tabel transaksi memakai LEFT JOIN
$pesanan_saya = mysqli_query($koneksi,
    "SELECT p.*, l.nama_layanan, t.status_bayar 
     FROM pesanan p 
     JOIN layanan l ON p.layanan_id = l.id
     LEFT JOIN transaksi t ON p.id = t.pesanan_id
     WHERE p.user_id = " . $_SESSION['user_id'] . "
     ORDER BY p.tanggal_pesan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LaundryHub - Beranda</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Style inline pembantu untuk menyamakan tampilan tombol bayar */
        .btn-pay-direct {
            background-color: #28a745; 
            color: white; 
            text-decoration: none; 
            padding: 5px 10px; 
            border-radius: 3px; 
            font-size: 12px; 
            font-weight: bold;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-pay-direct:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="navbar">
    <span class="brand">🧺 LaundryHub</span>
    <div>
        <span style="color:#fff; margin-right: 15px;">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="card">
        <h2>Pesan Laundry</h2>

        <?php if ($pesan): ?>
            <div class="alert <?= $sukses ? 'alert-success' : 'alert-danger' ?>"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Jenis Layanan</label>
                <select name="layanan_id" id="layanan_id" required onchange="hitungHarga()">
                    <option value="">-- Pilih Layanan --</option>
                    <?php mysqli_data_seek($layanan_list, 0); while ($lyr = mysqli_fetch_assoc($layanan_list)): ?>
                    <option value="<?= $lyr['id'] ?>" 
                            data-harga="<?= $lyr['harga_per_kg'] ?>"
                            data-estimasi="<?= $lyr['estimasi_hari'] ?>">
                        <?= $lyr['nama_layanan'] ?> - <?= rupiah($lyr['harga_per_kg']) ?>/kg 
                        (estimasi <?= $lyr['estimasi_hari'] ?> hari)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Perkiraan Berat (kg)</label>
                <input type="number" name="berat_kg" id="berat_kg" step="0.5" min="1" max="50"
                       placeholder="Contoh: 3" required onkeyup="hitungHarga()">
            </div>

            <div class="form-group">
                <label>Estimasi Total Harga</label>
                <input type="text" id="estimasi_harga" readonly value="-" style="background:#f8f8f8;">
            </div>

            <div class="form-group">
                <label>Alamat Pickup (opsional)</label>
                <textarea name="alamat_pickup" placeholder="Isi jika ingin dijemput..."></textarea>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" placeholder="Catatan khusus (misal: ada noda, jenis kain, dll)"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Buat Pesanan</button>
        </form>
    </div>

    <div class="card">
        <h2>Pesanan Saya</h2>

        <?php if (mysqli_num_rows($pesanan_saya) === 0): ?>
            <p style="color:#888;">Belum ada pesanan.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Layanan</th>
                    <th>Berat</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Estimasi Selesai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($pesanan_saya)): ?>
                <tr>
                    <td><strong><?= $row['kode_pesanan'] ?></strong></td>
                    <td><?= $row['nama_layanan'] ?></td>
                    <td><?= $row['berat_kg'] ?> kg</td>
                    <td><?= rupiah($row['total_harga']) ?></td>
                    <td><?= labelStatus($row['status']) ?></td>
                    <td><?= $row['estimasi_selesai'] ? date('d/m/Y', strtotime($row['estimasi_selesai'])) : '-' ?></td>
                    <td>
                        <a href="tracking.php?kode=<?= $row['kode_pesanan'] ?>" class="btn btn-sm btn-secondary">
                            Tracking
                        </a>

                        <?php if (isset($row['status_bayar']) && $row['status_bayar'] === 'lunas'): ?>
                            <span style="color: #28a745; font-weight: bold; margin-left: 10px; font-size: 13px;">
                                ✓ Sudah Dibayar
                            </span>
                        <?php else: ?>
                            <a href="proses_bayar_customer.php?id=<?= $row['id'] ?>&kode=<?= $row['kode_pesanan'] ?>"
                            class="btn-pay-direct" style="margin-left: 5px;">
                            Bayar
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<script>
function hitungHarga() {
    var sel    = document.getElementById('layanan_id');
    var berat  = parseFloat(document.getElementById('berat_kg').value) || 0;
    var harga  = 0;
    var opt    = sel.options[sel.selectedIndex];

    if (opt && opt.dataset.harga) {
        harga = parseFloat(opt.dataset.harga) * berat;
    }

    document.getElementById('estimasi_harga').value =
        harga > 0 ? 'Rp ' + harga.toLocaleString('id-ID') : '-';
}
</script>

</body>
</html>