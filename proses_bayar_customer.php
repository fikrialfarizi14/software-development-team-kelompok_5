<?php
require_once 'includes/session.php';
cekLogin();

$id_pesanan   = isset($_GET['id']) ? (int)bersihkan($_GET['id']) : 0;
$kode_pesanan = isset($_GET['kode']) ? bersihkan($_GET['kode']) : '';

if (!$id_pesanan) {
    echo "<script>alert('ID Pesanan tidak valid!'); window.location.href='index.php';</script>";
    exit;
}

// 1. Ambil detail total harga untuk ditampilkan di form
$query_harga = mysqli_query($koneksi, "SELECT total_harga FROM pesanan WHERE id = '$id_pesanan'");
$data_pesanan = mysqli_fetch_assoc($query_harga);
$jumlah_bayar = $data_pesanan['total_harga'];

$pesan = '';

// 2. Proses ketika form diklik submit oleh pelanggan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_pembayaran = bersihkan($_POST['metode_pembayaran']);
    $waktu_sekarang    = date('Y-m-d H:i:s');

    // Cek apakah data transaksi sudah ada di database
    $query_cek = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE pesanan_id = '$id_pesanan'");

    if (mysqli_num_rows($query_cek) > 0) {
        // Update menjadi lunas berdasarkan pilihan customer
        $sql = "UPDATE transaksi SET 
                jumlah_bayar = '$jumlah_bayar',
                metode_pembayaran = '$metode_pembayaran',
                status_bayar = 'lunas',
                tanggal_bayar = '$waktu_sekarang'
                WHERE pesanan_id = '$id_pesanan'";
    } else {
        // Insert data baru
        $sql = "INSERT INTO transaksi (pesanan_id, jumlah_bayar, metode_pembayaran, tanggal_bayar, status_bayar) 
                VALUES ('$id_pesanan', '$jumlah_bayar', '$metode_pembayaran', '$waktu_sekarang', 'lunas')";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>
                alert('Terima kasih! Pembayaran menggunakan " . ucfirst($metode_pembayaran) . " berhasil dicatat.');
                window.location.href = 'index.php';
              </script>";
        exit;
    } else {
        $pesan = "Gagal memproses transaksi: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran - LaundryHub</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-box { max-width: 500px; margin: 40px auto; padding: 25px; border-radius: 8px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .text-center { text-align: center; }
        .mb-3 { margin-bottom: 15px; }
        .w-100 { width: 100%; }
        .info-harga { background: #e2e8f0; padding: 10px; border-radius: 4px; font-weight: bold; font-size: 20px; color: #e53e3e; text-align: center;}
        
        /* Style untuk kotak informasi instruksi rekening */
        .rekening-box {
            background: #edf2f7;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            border-left: 4px solid #3182ce;
            font-size: 13px;
        }
        .rekening-item {
            margin-bottom: 8px;
        }
        .rekening-item:last-child { margin-bottom: 0; }
    </style>
</head>
<body style="background-color: #f7fafc;">

<div class="container">
    <div class="form-box">
        <h2 class="text-center">💳 Detail Pembayaran</h2>
        <p class="text-center" style="color: #718096; font-size: 14px;">Silakan transfer sesuai nominal ke salah satu rekening di bawah ini.</p>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-danger"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label style="font-weight: bold;">Total Tagihan:</label>
            <div class="info-harga"><?= rupiah($jumlah_bayar) ?></div>
        </div>

        <div class="rekening-box mb-3">
            <div style="font-weight: bold; margin-bottom: 8px; color: #2b6cb0;">📌 Pilihan Pembayaran Pemilik Toko:</div>
            
            <div class="rekening-item">
                <strong>🏦 Bank BCA</strong><br>
                No. Rekening: <code style="font-size:14px; font-weight:bold; color:#2d3748;">1234-5678-90</code><br>
                a.n. Owner LaundryHub
            </div>
            <hr style="border: 0; border-top: 1px dashed #cbd5e0; margin: 8px 0;">
            <div class="rekening-item">
                <strong>📱 E-Wallet (OVO / Dana / Gopay)</strong><br>
                Nomor HP: <code style="font-size:14px; font-weight:bold; color:#2d3748;">0857-4580-5413</code><br>
                a.n. LaundryHub Indonesia
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="metode_pembayaran" style="font-weight: bold;">Pilih Metode Pembayaran Yang Anda Gunakan:</label>
                <select name="metode_pembayaran" id="metode_pembayaran" required style="width:100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="transfer">🏦 Sudah Transfer via Bank</option>
                    <option value="e-wallet">📱 Sudah Bayar via E-Wallet</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label style="font-weight: bold;">Upload Bukti Transfer / Screenshot Nota</label>
                <input type="file" name="bukti_transfer" accept="image/*" style="width:100%; margin-top: 5px;">
                <small style="color: #a0aec0; font-size: 11px;">*Upload file gambar bukti transaksi Anda disini.</small>
            </div>

            <button type="submit" class="btn btn-success w-100" style="background: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 15px; font-size: 15px;">
                Konfirmasi & Kirim Bukti Bayar
            </button>
        </form>
        
        <div class="text-center" style="margin-top: 20px;">
            <a href="index.php" style="color: #718096; text-decoration: none; font-size: 13px;">← Kembali ke Beranda</a>
        </div>
    </div>
</div>

</body>
</html>