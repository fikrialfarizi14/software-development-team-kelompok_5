<?php
// Partial navbar untuk halaman admin/.
// Cukup di-include, jangan diakses langsung dari browser.
$role = $_SESSION['role'] ?? '';
?>
<div class="navbar">
    <span class="brand">🧺 LaundryHub - <?= $role === 'pemilik' ? 'Pemilik' : 'Petugas' ?></span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <?php if ($role === 'petugas'): ?>
            <a href="pesanan.php">Pesanan</a>
            <a href="layanan.php">Layanan</a>
            <a href="pelanggan.php">Pelanggan</a>
            <a href="transaksi.php">Transaksi</a>
        <?php endif; ?>
        <a href="laporan.php">Laporan</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>
