<?php
require_once 'includes/session.php';
cekLogin(); // Pastikan user sudah login sebagai pelanggan

$id_pesanan = isset($_GET['id']) ? bersihkan($_GET['id']) : '';

if (!$id_pesanan) {
    header("Location: index.php");
    exit;
}

// 1. Ambil data pesanan milik pelanggan
$query_pesanan = mysqli_query($koneksi, "
    SELECT p.*, l.nama_layanan
    FROM pesanan p
    JOIN layanan l ON p.layanan_id = l.id
    WHERE p.id = '$id_pesanan'
");
$pesanan = mysqli_fetch_assoc($query_pesanan);

if (!$pesanan) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// 2. Cek status transaksi saat ini di database
$query_transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE pesanan_id = '$id_pesanan'");
$transaksi = mysqli_fetch_assoc($query_transaksi);

// 3. Eksekusi ketika pelanggan menekan tombol Bayar
if (isset($_POST['proses_bayar_otomatis'])) {
    $metode = $_POST['metode_pembayaran'];
    $jumlah_bayar = $pesanan['total_harga'];
    $waktu_sekarang = date('Y-m-d H:i:s');

    if ($transaksi) {
        // Jika data transaksi sudah di-generate admin tapi belum dibayar, kita UPDATE
        $sql = "UPDATE transaksi SET 
                jumlah_bayar = '$jumlah_bayar',
                metode_pembayaran = '$metode',
                status_bayar = 'lunas',
                tanggal_bayar = '$waktu_sekarang'
                WHERE pesanan_id = '$id_pesanan'";
    } else {
        // Jika data transaksi belum ada di tabel, kita INSERT baru
        $sql = "INSERT INTO transaksi (pesanan_id, jumlah_bayar, metode_pembayaran, tanggal_bayar, status_bayar) 
                VALUES ('$id_pesanan', '$jumlah_bayar', '$metode', '$waktu_sekarang', 'lunas')";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Pembayaran Otomatis Berhasil! Status Anda kini LUNAS.'); window.location.href='index.php';</script>";
    } else {
        echo "Gagal memproses pembayaran: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang