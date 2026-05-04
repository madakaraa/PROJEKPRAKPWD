<?php
session_start();
include 'koneksi.php';

// Pastikan hanya user yang bisa memasukkan ke keranjang
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = (int) $_POST['id_barang'];
    $jumlah    = (int) $_POST['jumlah'];
    $id_user   = $_SESSION['id']; // Ambil ID user yang sedang login

    // Cek apakah barang ini sudah ada di keranjang user yang bersangkutan
    $cek_keranjang = mysqli_query($conn, "SELECT * FROM keranjang WHERE id_user = $id_user AND id_barang = $id_barang");

    if (mysqli_num_rows($cek_keranjang) > 0) {
        // Jika barang sudah ada di keranjang, tambahkan saja jumlahnya (+1)
        mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah + $jumlah WHERE id_user = $id_user AND id_barang = $id_barang");
    } else {
        // Jika barang belum ada, buat baris baru di tabel keranjang
        mysqli_query($conn, "INSERT INTO keranjang (id_user, id_barang, jumlah) VALUES ($id_user, $id_barang, $jumlah)");
    }

    // Redirect kembali ke halaman barang dengan pesan sukses
    header("Location: barang.php?pesan=Sip! Barang berhasil ditambahkan ke keranjang.");
    exit;
} else {
    // Jika diakses langsung tanpa lewat form, tendang balik ke halaman barang
    header("Location: barang.php");
    exit;
}
?>