<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $id => $jumlah){
        mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id='$id'");
    }
}

unset($_SESSION['cart']);

header("Location: keranjang_view.php");
?>