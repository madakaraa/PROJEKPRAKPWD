<?php
session_start();
include 'koneksi.php';

// Pastikan hanya admin yang bisa menghapus
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_hapus = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil nama barang sebelum beneran dihapus buat masuk ke log
$cek_nama = mysqli_query($conn, "SELECT nama_barang FROM barang WHERE id = '$id_hapus'");
if($data_brg = mysqli_fetch_assoc($cek_nama)) {
    $nama_barang_dihapus = mysqli_real_escape_string($conn, $data_brg['nama_barang']);
    
    // Eksekusi hapus barang dari tabel
    $query_hapus = mysqli_query($conn, "DELETE FROM barang WHERE id = '$id_hapus'");
    
    if ($query_hapus) {
        // 👇 TAMBAHAN: REKAM LOG HAPUS KE DATABASE 👇
        mysqli_query($conn, "INSERT INTO log_barang (aksi, nama_barang) VALUES ('hapus', '$nama_barang_dihapus')");
        
        header("Location: barang.php?pesan=" . urlencode("Sip! Barang berhasil dihapus dari sistem."));
        exit;
    } else {
        header("Location: barang.php?pesan=" . urlencode("Gagal menghapus barang: " . mysqli_error($conn)));
        exit;
    }
} else {
    header("Location: barang.php?pesan=" . urlencode("Barang tidak ditemukan di sistem."));
    exit;
}
?>
