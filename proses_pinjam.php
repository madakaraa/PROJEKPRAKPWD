<?php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$tgl_pinjam = $_POST['tgl_pinjam'];
$tgl_kembali = $_POST['tgl_kembali'];

// 1. Simpan transaksi utama
mysqli_query($conn, "INSERT INTO peminjaman 
(id_user, tanggal_pinjam, tanggal_kembali, status) 
VALUES ('$id_user', '$tgl_pinjam', '$tgl_kembali', 'dipinjam')");

// Ambil id peminjaman terakhir
$id_peminjaman = mysqli_insert_id($conn);

// 2. Simpan detail barang dari cart
foreach($_SESSION['cart'] as $id_barang => $jumlah){

    // Simpan ke detail peminjaman
    mysqli_query($conn, "INSERT INTO detail_peminjaman 
    (id_peminjaman, id_barang, jumlah) 
    VALUES ('$id_peminjaman', '$id_barang', '$jumlah')");
    
    // 🔥 TAMBAHAN UNTUK FITUR AKTIVITAS:
    // Ambil nama barang untuk dicatat di log
    $query_brg = mysqli_query($conn, "SELECT nama_barang FROM barang WHERE id='$id_barang'");
    $brg = mysqli_fetch_assoc($query_brg);
    
    if ($brg) {
        $nama_barang = $brg['nama_barang'];
        // Format tanggal agar lebih rapi (opsional)
        $tgl_format = date('d M Y', strtotime($tgl_kembali));
        
        // Catat aktivitas (warna kuning/aktif)
        catat_aktivitas($conn, "$nama_barang sedang dipinjam", "jatuh tempo $tgl_format", "aktif");
    }
}

// 3. Kosongkan keranjang
unset($_SESSION['cart']);

// Arahkan ke riwayat
header("Location: riwayat.php");
exit;
?>