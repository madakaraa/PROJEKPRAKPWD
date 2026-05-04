<?php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_peminjaman = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Ambil data peminjaman terlebih dahulu untuk mengetahui id_barang dan jumlah yang dipinjam
    $cek_pinjam = mysqli_query($conn, "SELECT id_barang, jumlah FROM peminjaman WHERE id = '$id_peminjaman'");
    
    if (mysqli_num_rows($cek_pinjam) > 0) {
        $data_pinjam = mysqli_fetch_assoc($cek_pinjam);
        $id_barang   = $data_pinjam['id_barang'];
        $jumlah      = $data_pinjam['jumlah'];

        // 2. Ubah status peminjaman menjadi 'dikembalikan'
        $update_status = mysqli_query($conn, "UPDATE peminjaman SET status = 'dikembalikan' WHERE id = '$id_peminjaman'");

        if ($update_status) {
            // 3. Tambahkan kembali stok barang ke tabel barang
            // (Sekaligus memastikan status barang menjadi 'Tersedia' jika sebelumnya 'Habis')
            mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah, status = 'Tersedia' WHERE id = '$id_barang'");

            // 4. Catat ke log aktivitas admin (opsional jika fitur ini ada)
            if(function_exists('catat_aktivitas')){
                $nama_user = $_SESSION['nama'] ?? 'User';
                catat_aktivitas($conn, "$nama_user telah mengembalikan barang", "Selesai", "info");
            }

            // Kembalikan ke halaman riwayat dengan pesan sukses
            header("Location: riwayat.php?pesan=Terima kasih! Barang telah berhasil dikembalikan.");
            exit;
        } else {
            header("Location: riwayat.php?pesan=Gagal memproses pengembalian: " . mysqli_error($conn));
            exit;
        }
    } else {
        header("Location: riwayat.php?pesan=Data transaksi tidak ditemukan!");
        exit;
    }
} else {
    // Jika diakses tanpa ID
    header("Location: riwayat.php");
    exit;
}
?>