<?php
require_once 'includes/session.php';
cekLogin();

$kode    = isset($_GET['kode']) ? bersihkan($_GET['kode']) : '';
$pesanan = null;
$riwayat = [];

if ($kode) {
    // JOIN dengan tabel transaksi untuk mengambil status_bayar dan metode_pembayaran
    $query = "SELECT p.*, l.nama_layanan, l.estimasi_hari, u.nama as nama_pelanggan,
                     t.status_bayar, t.metode_pembayaran
              FROM pesanan p
              JOIN layanan l ON p.layanan_id = l.id
              JOIN users u ON p.user_id = u.id
              LEFT JOIN transaksi t ON p.id = t.pesanan_id
              WHERE p.kode_pesanan = '$kode'";

    // Pelanggan hanya bisa lihat pesanannya sendiri. Petugas & pemilik boleh lihat semua.
    if ($_SESSION['role'] === 'pelanggan') {
        $query .= " AND p.user_id = " . $_SESSION['user_id'];
    }

    $result  = mysqli_query($koneksi, $query);
    $pesanan = mysqli_fetch_assoc($result);

    // Ambil riwayat perubahan status dari tabel tracking_status (urut dari yang terbaru)
    if ($pesanan) {
        $rw = mysqli_query($koneksi,
            "SELECT * FROM tracking_status WHERE pesanan_id = " . $pesanan['id'] . " ORDER BY waktu_update DESC"
        );
        while ($r = mysqli_fetch_assoc($rw)) {
            $riwayat[] = $r;
        }
    }
}

// Urutan status untuk visual stepper
$steps = [
    'menunggu'       => 'Menunggu Konfirmasi',
    'diproses'       => 'Sedang Diproses',
    'selesai_dicuci' => 'Selesai Dicuci',
    'siap_diambil'   => 'Siap Diambil',
    'selesai'        => 'Selesai',
];
$step_keys   = array_keys($steps);
$status_skrg = $pesanan ? array_search($pesanan['status'], $step_keys) : -1;

$halaman_kembali = in_array($_SESSION['role'], ['petugas', 'pemilik']) ? 'admin/dashboard.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pesanan - LaundryHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Badge style manual untuk status pembayaran */
        .badge-bayar {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .bg-lunas { background-color: #28a745; color: white; }
        .bg-belum { background-color: #dc3545; color: white; }
    </style>
</head>
<body>

<div class="navbar">
    <span class="brand">🧺 LaundryHub</span>
    <div>
        <a href="<?= $halaman_kembali ?>">← Kembali</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <h2>Tracking Pesanan</h2>

        <form method="GET" style="display:flex;gap:8px;margin-bottom:20px;">
            <input type="text" name="kode" placeholder="Masukkan kode pesanan (contoh: LH-20250101-ABCD)"
                   value="<?= htmlspecialchars($kode) ?>"
                   style="flex:1;padding:7px 10px;border:1px solid #ccc;border-radius:3px;">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <?php if ($kode && !$pesanan): ?>
            <div class="alert alert-danger">Pesanan tidak ditemukan.</div>
        <?php endif; ?>

        <?php if ($pesanan): ?>
        <table style="margin-bottom:20px;">
            <tr><th>Kode Pesanan</th><td><strong><?= $pesanan['kode_pesanan'] ?></strong></td></tr>
            <tr><th>Pelanggan</th><td><?= $pesanan['nama_pelanggan'] ?></td></tr>
            <tr><th>Layanan</th><td><?= $pesanan['nama_layanan'] ?></td></tr>
            <tr><th>Berat</th><td><?= $pesanan['berat_kg'] ?> kg</td></tr>
            <tr><th>Total Harga</th><td><?= rupiah($pesanan['total_harga']) ?></td></tr>
            
            <tr>
                <th>Status Pembayaran</th>
                <td>
                    <?php if (isset($pesanan['status_bayar']) && $pesanan['status_bayar'] === 'lunas'): ?>
                        <span class="badge-bayar bg-lunas">Sudah Dibayar (<?= ucfirst($pesanan['metode_pembayaran']) ?>)</span>
                    <?php else: ?>
                        <span class="badge-bayar bg-belum">Belum Dibayar</span>
                        <a href="proses_bayar_customer.php?id=<?= $pesanan['id'] ?>&kode=<?= $pesanan['kode_pesanan'] ?>" 
                           class="btn btn-sm btn-primary" style="margin-left: 10px; padding: 2px 8px; font-size: 11px; text-decoration: none;">
                           Bayar Sekarang
                        </a>
                    <?php endif; ?>
                </td>
            </tr>

            <tr><th>Tanggal Pesan</th><td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])) ?></td></tr>
            <tr><th>Estimasi Selesai</th><td><?= $pesanan['estimasi_selesai'] ? date('d/m/Y', strtotime($pesanan['estimasi_selesai'])) : '-' ?></td></tr>
            <?php if ($pesanan['catatan']): ?>
            <tr><th>Catatan</th><td><?= $pesanan['catatan'] ?></td></tr>
            <?php endif; ?>
        </table>

        <h3 style="margin-bottom:10px;font-size:14px;">Status Pesanan:</h3>
        <div class="tracking-steps">
            <?php foreach ($steps as $key => $label):
                $idx = array_search($key, $step_keys);
                $kelas = '';
                if ($idx < $status_skrg) $kelas = 'done';
                elseif ($idx === $status_skrg) $kelas = 'aktif';
            ?>
            <div class="step <?= $kelas ?>">
                <?php if ($idx < $status_skrg): ?>✓ <?php endif; ?>
                <?= $label ?>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="font-size:12px;color:#666;margin-top:10px;margin-bottom:20px;">
            ✅ Hijau = sudah selesai &nbsp; 🔵 Biru = status sekarang &nbsp; Abu = belum
        </p>

        <h3 style="margin-bottom:10px;font-size:14px;">Riwayat Perubahan Status:</h3>
        <?php if (empty($riwayat)): ?>
            <p style="color:#888;">Belum ada riwayat.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>Waktu</th><th>Status</th><th>Catatan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($r['waktu_update'])) ?></td>
                    <td><?= labelStatus($r['status_baru']) ?></td>
                    <td><?= $r['catatan'] ?: '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

</body>
</html>