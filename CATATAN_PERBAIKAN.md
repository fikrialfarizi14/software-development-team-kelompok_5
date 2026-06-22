# LaundryHub — Catatan Perbaikan

Kode ini sudah dibenahi supaya sesuai dengan proposal, **tetap pakai PHP polos (mysqli)
seperti sebelumnya — tidak pakai Laravel.** Sudah saya jalankan dan uji coba penuh
(install MySQL + PHP server beneran, register, pesan, ubah status, bayar, lihat
laporan, cetak nota) dan semuanya berjalan tanpa error.

## 1. Bug yang diperbaiki

- **Redirect salah arah.** Dulu kalau belum login lalu buka `index.php` atau
  `tracking.php` langsung dari folder utama, `cekLogin()` malah mengarahkan ke alamat
  yang salah (`../login.php`, padahal harusnya `login.php`). Sekarang
  `includes/session.php` otomatis mendeteksi apakah halaman sedang dibuka dari folder
  `admin/` atau bukan, jadi redirect-nya selalu benar dari halaman manapun.
- **Hapus layanan bisa bikin error.** Tombol "Hapus" di kelola layanan akan gagal
  (fatal error foreign key) kalau layanan itu sudah pernah dipesan pelanggan. Sekarang
  diganti tombol **Aktifkan/Nonaktifkan** — datanya aman, dan layanan nonaktif otomatis
  hilang dari pilihan pemesanan pelanggan.
- **Password disimpan dengan MD5.** Proposal poin 4.3 secara eksplisit minta bcrypt.
  Sekarang pakai `password_hash()` / `password_verify()` bawaan PHP (bcrypt asli, bukan
  MD5 yang gampang dibobol).

## 2. Fitur dari proposal yang sebelumnya belum ada, sekarang sudah dibuat

| Fitur di proposal | File |
|---|---|
| 3 peran pengguna: Pelanggan, Petugas, Pemilik (bagian 3) | `users.role`, semua file `admin/*.php` |
| Tabel `tracking_status` — riwayat status (5.2) | `database.sql`, dicatat otomatis tiap status berubah |
| Tabel `transaksi` — pencatatan pembayaran (5.2, 4.2.5) | `database.sql`, `admin/transaksi.php` |
| Halaman Laporan harian/mingguan/bulanan (4.2.6, 5.3.8) | `admin/laporan.php` |
| Mencetak nota (5.3.7) | `admin/cetak_nota.php` |
| Dashboard Pemilik — lihat laporan & statistik saja (3) | `admin/dashboard.php`, `admin/_navbar.php` |
| Kolom `status` aktif/nonaktif pada layanan (5.2.2) | `database.sql`, `admin/layanan.php` |
| Kolom `estimasi_selesai` pada pesanan (5.2.3) | `database.sql`, dihitung otomatis saat pesan |

## 3. Struktur peran (role)

- **pelanggan** — daftar sendiri lewat `register.php`, bisa pesan & tracking.
- **petugas** — kelola pesanan, layanan, pelanggan, transaksi, lihat dashboard & laporan.
- **pemilik** — hanya bisa lihat dashboard & laporan (read-only), tidak bisa ubah data.

Akun petugas & pemilik **tidak didaftarkan lewat form**, tapi sudah disiapkan di
`database.sql` (akun staf biasanya dibuatkan, bukan daftar sendiri):

| Peran | Email | Password |
|---|---|---|
| Petugas | petugas@laundryhub.com | petugas123 |
| Pemilik | pemilik@laundryhub.com | pemilik123 |

## 4. Cara menjalankan (XAMPP/Laragon, dkk)

1. Taruh folder `laundryhub/` ke dalam `htdocs/`.
2. Buka phpMyAdmin → tab SQL → jalankan isi `database.sql` (akan otomatis membuat
   semua tabel + akun di atas).
3. Cek `includes/koneksi.php` — sesuaikan `DB_USER`/`DB_PASS` kalau perlu.
4. Buka `http://localhost/laundryhub/` di browser.

## 5. Alur singkat untuk presentasi/demo

1. Daftar & login sebagai pelanggan → buat pesanan → lihat halaman tracking.
2. Login sebagai **petugas** → buka *Pesanan* → klik *Update* → ganti status + isi
   catatan → cek lagi tracking di sisi pelanggan, riwayatnya sudah bertambah.
3. Di *Update Status*, klik *Kelola Pembayaran* → catat pembayaran.
4. Buka *Laporan* → pilih periode → lihat grafik & tabel.
5. Login sebagai **pemilik** → tunjukkan dia hanya melihat Dashboard & Laporan, tidak
   bisa mengubah apa pun.
6. Dari *Pesanan*, klik *Cetak Nota* → tunjukkan tampilan nota siap print.
