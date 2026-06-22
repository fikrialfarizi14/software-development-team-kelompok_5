<?php
// =============================================
// Konfigurasi Database
// Sesuaikan dengan setting MySQL kamu
// =============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // username MySQL kamu
define('DB_PASS', '');           // password MySQL kamu (kosong jika pakai XAMPP default)
define('DB_NAME', 'laundryhub_laravel2');

// Buat koneksi
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($koneksi, "utf8");

// =============================================
// Fungsi-fungsi helper
// =============================================

// Generate kode pesanan unik
function generateKodePesanan() {
    return 'LH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

// Bersihkan input dari user (untuk ditampilkan / dipakai di query sederhana)
function bersihkan($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, htmlspecialchars(trim($data)));
}

// Format rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Label status pesanan (badge berwarna)
function labelStatus($status) {
    $labels = [
        'menunggu'      => '<span style="background:#ffc107;color:#000;padding:2px 8px;border-radius:4px;">Menunggu</span>',
        'diproses'      => '<span style="background:#0d6efd;color:#fff;padding:2px 8px;border-radius:4px;">Diproses</span>',
        'selesai_dicuci'=> '<span style="background:#6f42c1;color:#fff;padding:2px 8px;border-radius:4px;">Selesai Dicuci</span>',
        'siap_diambil'  => '<span style="background:#20c997;color:#fff;padding:2px 8px;border-radius:4px;">Siap Diambil</span>',
        'selesai'       => '<span style="background:#198754;color:#fff;padding:2px 8px;border-radius:4px;">Selesai</span>',
    ];
    return $labels[$status] ?? $status;
}

// Label status pembayaran (badge berwarna)
function labelBayar($status) {
    if ($status === 'lunas') {
        return '<span style="background:#198754;color:#fff;padding:2px 8px;border-radius:4px;">Lunas</span>';
    }
    return '<span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:4px;">Belum Bayar</span>';
}

// Catat satu baris riwayat ke tabel tracking_status
// Dipanggil setiap kali status pesanan berubah, supaya histori-nya tersimpan
function catatTracking($pesanan_id, $status_baru, $catatan = '') {
    global $koneksi;
    $pesanan_id  = (int) $pesanan_id;
    $status_baru = mysqli_real_escape_string($koneksi, $status_baru);
    $catatan     = mysqli_real_escape_string($koneksi, $catatan);
    mysqli_query($koneksi,
        "INSERT INTO tracking_status (pesanan_id, status_baru, catatan)
         VALUES ($pesanan_id, '$status_baru', '$catatan')"
    );
}
?>
