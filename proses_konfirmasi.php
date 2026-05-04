<?php
session_start();
include 'koneksi.php';

// Pastikan hanya Admin yang bisa melakukan konfirmasi
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Akses ilegal!");
    exit;
}

// Cek apakah ada parameter 'id' yang dikirim dari URL
if (isset($_GET['id'])) {
    
    // Amankan ID dari URL
    $id_peminjaman = mysqli_real_escape_string($conn, $_GET['id']);

    // Update status di tabel peminjaman menjadi 'dipinjam'
    $query = mysqli_query($conn, "UPDATE peminjaman SET status = 'dipinjam' WHERE id = '$id_peminjaman'");

    // Cek apakah query berhasil
    if ($query) {
        // Jika berhasil, kembalikan ke halaman riwayat dengan pesan sukses
        header("Location: riwayat.php?pesan=Sip! Peminjaman berhasil dikonfirmasi dan barang resmi dipinjam.");
        exit;
    } else {
        // Jika gagal karena error database
        header("Location: riwayat.php?pesan=Gagal mengonfirmasi peminjaman: " . mysqli_error($conn));
        exit;
    }

} else {
    // Jika file ini diakses langsung tanpa parameter ID, kembalikan ke riwayat
    header("Location: riwayat.php");
    exit;
}
?>