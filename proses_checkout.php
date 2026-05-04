<?php
session_start();
include 'koneksi.php';

// Pastikan hanya user yang bisa melakukan checkout
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user       = $_SESSION['id'];
    $tgl_pinjam    = mysqli_real_escape_string($conn, $_POST['tgl_pinjam']);
    $tgl_kembali   = mysqli_real_escape_string($conn, $_POST['tgl_kembali']);
    $nama_peminjam = mysqli_real_escape_string($conn, $_POST['nama_peminjam']);
    $keperluan     = mysqli_real_escape_string($conn, $_POST['keperluan']);
    $metode_ambil  = mysqli_real_escape_string($conn, $_POST['metode_ambil']);

    // 1. Ambil semua barang dari keranjang milik user ini
    $keranjang = mysqli_query($conn, "SELECT * FROM keranjang WHERE id_user = '$id_user'");

    if (mysqli_num_rows($keranjang) > 0) {
        
        // 2. Looping memproses setiap barang
        while ($item = mysqli_fetch_assoc($keranjang)) {
            $id_barang = $item['id_barang'];
            $jumlah    = $item['jumlah'];

            // Masukkan ke riwayat peminjaman
            $query_pinjam = "INSERT INTO peminjaman 
                            (id_user, id_barang, jumlah, tgl_pinjam, tgl_kembali, nama_peminjam, keperluan, metode_ambil, status) 
                            VALUES 
                            ('$id_user', '$id_barang', '$jumlah', '$tgl_pinjam', '$tgl_kembali', '$nama_peminjam', '$keperluan', '$metode_ambil', 'menunggu')";
            mysqli_query($conn, $query_pinjam);

            // Kurangi stok barang
            mysqli_query($conn, "UPDATE barang SET stok = stok - $jumlah WHERE id = '$id_barang'");
            
            // Ubah jadi habis kalau stok 0
            mysqli_query($conn, "UPDATE barang SET status = 'Habis' WHERE id = '$id_barang' AND stok <= 0");
        }

        // 3. Kosongkan keranjang user
        mysqli_query($conn, "DELETE FROM keranjang WHERE id_user = '$id_user'");

        // 4. Catat aktivitas (Opsional)
        if(function_exists('catat_aktivitas')){
            catat_aktivitas($conn, "$nama_peminjam mengajukan peminjaman baru", "Menunggu Verifikasi", "baru");
        }

        // 5. REDIRECT KE DASHBOARD DENGAN URL-ENCODE (INI KUNCI BIAR PESANNYA MUNCUL!)
        $pesan_sukses = "Barang berhasil diajukan dan sedang menunggu verifikasi Admin.";
        header("Location: dashboard.php?pesan=" . urlencode($pesan_sukses));
        exit;

    } else {
        // Kalau keranjangnya kosong
        $pesan_gagal = "Keranjang Anda kosong!";
        header("Location: keranjang_view.php?pesan=" . urlencode($pesan_gagal));
        exit;
    }

} else {
    header("Location: keranjang_view.php");
    exit;
}
?>