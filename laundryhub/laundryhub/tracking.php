<?php
require_once 'includes/session.php';
cekLogin();

$kode   = isset($_GET['kode']) ? bersihkan($_GET['kode']) : '';
$pesanan = null;

if ($kode) {
    $query = "SELECT p.*, l.nama_layanan, l.estimasi_hari, u.nama as nama_pelanggan
              FROM pesanan p 
              JOIN layanan l ON p.layanan_id = l.id
              JOIN users u ON p.user_id = u.id
              WHERE p.kode_pesanan = '$kode'";

    // Pelanggan hanya bisa lihat pesanannya sendiri
    if ($_SESSION['role'] !== 'admin') {
        $query .= " AND p.user_id = " . $_SESSION['user_id'];
    }

    $result = mysqli_query($koneksi, $query);
    $pesanan = mysqli_fetch_assoc($result);
}

// Urutan status
$steps = [
    'menunggu'       => 'Menunggu Konfirmasi',
    'diproses'       => 'Sedang Diproses',
    'selesai_dicuci' => 'Selesai Dicuci',
    'siap_diambil'   => 'Siap Diambil',
    'selesai'        => 'Selesai',
];
$step_keys   = array_keys($steps);
$status_skrg = $pesanan ? array_search($pesanan['status'], $step_keys) : -1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pesanan - LaundryHub</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">🧺 LaundryHub</span>
    <div>
        <a href="<?= $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php' ?>">
            ← Kembali
        </a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <h2>Tracking Pesanan</h2>

        <!-- Form cari pesanan -->
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
        <!-- Info Pesanan -->
        <table style="margin-bottom:20px;">
            <tr><th>Kode Pesanan</th><td><strong><?= $pesanan['kode_pesanan'] ?></strong></td></tr>
            <tr><th>Pelanggan</th><td><?= $pesanan['nama_pelanggan'] ?></td></tr>
            <tr><th>Layanan</th><td><?= $pesanan['nama_layanan'] ?></td></tr>
            <tr><th>Berat</th><td><?= $pesanan['berat_kg'] ?> kg</td></tr>
            <tr><th>Total Harga</th><td><?= rupiah($pesanan['total_harga']) ?></td></tr>
            <tr><th>Tanggal Pesan</th><td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])) ?></td></tr>
            <?php if ($pesanan['catatan']): ?>
            <tr><th>Catatan</th><td><?= $pesanan['catatan'] ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- Tracking Steps -->
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

        <p style="font-size:12px;color:#666;margin-top:10px;">
            ✅ Hijau = sudah selesai &nbsp; 🔵 Biru = status sekarang &nbsp; Abu = belum
        </p>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
