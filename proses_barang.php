<?php
include 'koneksi.php';

$nama = $_POST['nama_barang'];
$deskripsi = $_POST['deskripsi'];
$kondisi = $_POST['kondisi'];
$status = $_POST['status'];
$stok = $_POST['stok'];
// Setelah query INSERT barang berhasil:
$nama_barang = $_POST['nama_barang'];
catat_aktivitas($conn, "$nama_barang ditambahkan ke katalog", "Oleh Admin", "baru");

$query = mysqli_query($conn, "INSERT INTO barang 
(nama_barang, deskripsi, kondisi, status, stok) 
VALUES ('$nama', '$deskripsi', '$kondisi', '$status', '$stok')");

if ($query) {
    header("Location: barang.php");
} else {
    echo "Gagal: " . mysqli_error($conn);
}
?>