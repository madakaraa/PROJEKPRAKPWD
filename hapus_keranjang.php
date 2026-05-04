<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];

// ambil jumlah dari cart
$jumlah = $_SESSION['cart'][$id];

// balikin stok ke database
mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id='$id'");

// hapus dari keranjang
unset($_SESSION['cart'][$id]);

header("Location: keranjang_view.php");
?>