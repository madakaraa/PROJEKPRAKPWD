<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$action = $_GET['action'] ?? '';
$id_keranjang = (int)($_GET['id'] ?? 0);

if ($id_keranjang > 0) {
    // Ambil data keranjang dan cek stok barang aslinya
    $cek = mysqli_query($conn, "SELECT k.*, b.stok FROM keranjang k JOIN barang b ON k.id_barang = b.id WHERE k.id = $id_keranjang AND k.id_user = $id_user");
    
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_assoc($cek);
        $jumlah_sekarang = $data['jumlah'];
        $stok_tersedia = $data['stok'];

        if ($action == 'plus') {
            // Cek biar user ga bisa plus melebihi stok yang ada
            if ($jumlah_sekarang < $stok_tersedia) {
                mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah + 1 WHERE id = $id_keranjang");
            } else {
                $_SESSION['pesan_err'] = "Stok maksimal barang ini hanya $stok_tersedia!";
            }
        } elseif ($action == 'min') {
            // Jika dikurang dan jumlahnya masih lebih dari 1, kurangi jumlahnya
            if ($jumlah_sekarang > 1) {
                mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah - 1 WHERE id = $id_keranjang");
            } else {
                // JIKA JUMLAHNYA 1 DAN DIPENCET MINUS, MAKA HAPUS DARI KERANJANG
                mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id_keranjang");
            }
        }
    }
}

// Kembalikan ke halaman checkout secara instan
header("Location: keranjang_view.php");
exit;
?>
