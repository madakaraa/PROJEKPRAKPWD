<?php
include 'koneksi.php';

$id_peminjaman = $_GET['id'];

// ambil semua barang di transaksi ini
$data = mysqli_query($conn, "SELECT * FROM detail_peminjaman WHERE id_peminjaman='$id_peminjaman'");

while($d = mysqli_fetch_assoc($data)) {
    $id_barang = $d['id_barang'];
    $jumlah = $d['jumlah'];

    // balikin stok
    mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id='$id_barang'");
}

// ubah status peminjaman
mysqli_query($conn, "UPDATE peminjaman SET status='dikembalikan' WHERE id='$id_peminjaman'");

header("Location: riwayat.php");
?>