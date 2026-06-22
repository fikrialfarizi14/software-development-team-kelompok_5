<?php
require_once '../includes/session.php';
cekPetugas();

$pesan = '';
$edit_data = null;

// Aktifkan / nonaktifkan layanan
// (Bukan dihapus permanen, supaya tidak error kalau layanan ini sudah pernah dipesan pelanggan)
if (isset($_GET['toggle'])) {
    $toggle_id = (int)$_GET['toggle'];
    mysqli_query($koneksi,
        "UPDATE layanan SET status = IF(status='aktif','nonaktif','aktif') WHERE id = $toggle_id"
    );
    $pesan = "Status layanan berhasil diubah.";
}

// Edit: ambil data
if (isset($_GET['edit'])) {
    $edit_id  = (int)$_GET['edit'];
    $result   = mysqli_query($koneksi, "SELECT * FROM layanan WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($result);
}

// Simpan (tambah atau edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = bersihkan($_POST['nama_layanan']);
    $harga      = (float)$_POST['harga_per_kg'];
    $estimasi   = (int)$_POST['estimasi_hari'];
    $keterangan = bersihkan($_POST['keterangan']);
    $edit_id    = (int)($_POST['edit_id'] ?? 0);

    if ($edit_id) {
        mysqli_query($koneksi,
            "UPDATE layanan SET nama_layanan='$nama', harga_per_kg=$harga, 
             estimasi_hari=$estimasi, keterangan='$keterangan'
             WHERE id = $edit_id"
        );
        $pesan = "Layanan berhasil diperbarui.";
    } else {
        mysqli_query($koneksi,
            "INSERT INTO layanan (nama_layanan, harga_per_kg, estimasi_hari, keterangan, status)
             VALUES ('$nama', $harga, $estimasi, '$keterangan', 'aktif')"
        );
        $pesan = "Layanan baru berhasil ditambahkan.";
    }
    $edit_data = null;
}

$layanan_list = mysqli_query($koneksi, "SELECT * FROM layanan ORDER BY id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Layanan - LaundryHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '_navbar.php'; ?>

<div class="container">
    <?php if ($pesan): ?>
        <div class="alert alert-success"><?= $pesan ?></div>
    <?php endif; ?>

    <!-- Form tambah/edit -->
    <div class="card">
        <h2><?= $edit_data ? 'Edit Layanan' : 'Tambah Layanan Baru' ?></h2>
        <form method="POST">
            <?php if ($edit_data): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nama Layanan</label>
                <input type="text" name="nama_layanan" required
                       value="<?= $edit_data['nama_layanan'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Harga per Kg (Rp)</label>
                <input type="number" name="harga_per_kg" required min="1000" step="500"
                       value="<?= $edit_data['harga_per_kg'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Estimasi Pengerjaan (hari)</label>
                <input type="number" name="estimasi_hari" required min="1" max="14"
                       value="<?= $edit_data['estimasi_hari'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan"><?= $edit_data['keterangan'] ?? '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">
                <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Layanan' ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="layanan.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Daftar layanan -->
    <div class="card">
        <h2>Daftar Layanan</h2>
        <table>
            <thead>
                <tr><th>Nama Layanan</th><th>Harga/kg</th><th>Estimasi</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($layanan_list)): ?>
                <tr>
                    <td><?= $row['nama_layanan'] ?></td>
                    <td><?= rupiah($row['harga_per_kg']) ?></td>
                    <td><?= $row['estimasi_hari'] ?> hari</td>
                    <td><?= $row['keterangan'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'aktif'): ?>
                            <span style="background:#198754;color:#fff;padding:2px 8px;border-radius:4px;">Aktif</span>
                        <?php else: ?>
                            <span style="background:#6c757d;color:#fff;padding:2px 8px;border-radius:4px;">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="layanan.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="layanan.php?toggle=<?= $row['id'] ?>" class="btn btn-sm <?= $row['status']==='aktif' ? 'btn-secondary' : 'btn-success' ?>">
                            <?= $row['status']==='aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <p style="font-size:12px;color:#666;margin-top:10px;">
            Catatan: layanan tidak dihapus permanen, hanya dinonaktifkan, supaya data pesanan lama yang memakai layanan ini tidak rusak.
            Layanan nonaktif tidak akan muncul di halaman pemesanan pelanggan.
        </p>
    </div>
</div>

</body>
</html>
