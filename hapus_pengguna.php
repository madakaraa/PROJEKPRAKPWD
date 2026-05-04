<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Ganda: Pastikan yang mengakses file ini HANYA Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Akses ilegal!");
    exit;
}

// 2. Cek apakah ada parameter 'id' yang dikirim dari URL
if (isset($_GET['id'])) {
    
    // Ambil ID pengguna yang mau dihapus dan amankan
    $id_hapus = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil ID Admin yang sedang login saat ini
    $id_admin_sekarang = $_SESSION['id'];

    // 3. KEAMANAN: Cegah Admin menghapus dirinya sendiri
    if ($id_hapus == $id_admin_sekarang) {
        header("Location: kelola_pengguna.php?pesan=Gagal! Anda tidak bisa menghapus akun Anda sendiri.&tipe=gagal");
        exit;
    }

    // 4. Eksekusi penghapusan data di tabel users
    $query = mysqli_query($conn, "DELETE FROM users WHERE id = '$id_hapus'");

    // 5. Cek apakah berhasil atau gagal
    if ($query) {
        // Jika sukses, kembalikan ke halaman kelola dengan pesan sukses
        header("Location: kelola_pengguna.php?pesan=Sip! Akun pengguna berhasil dihapus.&tipe=sukses");
        exit;
    } else {
        // Jika gagal (biasanya karena pengguna ini masih nyangkut di tabel transaksi/peminjaman)
        header("Location: kelola_pengguna.php?pesan=Gagal menghapus! Pastikan pengguna ini tidak sedang meminjam barang.&tipe=gagal");
        exit;
    }

} else {
    // Jika file ini diakses langsung tanpa membawa ID, lempar balik ke halaman kelola
    header("Location: kelola_pengguna.php");
    exit;
}
?>