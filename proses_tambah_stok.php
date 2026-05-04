<?php
session_start();
include 'koneksi.php';

// Proteksi Ganda: Hanya admin yang boleh mengeksekusi
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Akses ilegal!");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang   = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $jml_tambah  = (int) $_POST['jml_tambah'];

    // Update stok di tabel barang
    // Jika statusnya sebelumnya 'Habis', kita ubah jadi 'Tersedia' karena sekarang ada stoknya
    $query = mysqli_query($conn, "UPDATE barang SET stok = stok + $jml_tambah, status = 'Tersedia' WHERE id = '$id_barang'");

    if ($query) {
        // (Opsional) Catat ke tabel aktivitas agar muncul di log
        if(function_exists('catat_aktivitas')){
            catat_aktivitas($conn, "Stok $nama_barang ditambahkan sebanyak $jml_tambah unit", "Oleh Admin", "baru");
        }
        
        // Kembalikan ke halaman barang dengan pesan sukses
        header("Location: barang.php?pesan=Stok $nama_barang berhasil ditambah!");
        exit;
    } else {
        echo "Gagal mengupdate stok: " . mysqli_error($conn);
    }
} else {
    header("Location: barang.php");
    exit;
}
?>