<?php
session_start();

if($_SESSION['role'] != 'admin'){
    header("Location: barang.php");
    exit;
}
include 'koneksi.php';

$id = $_GET['id'];

$query = mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");

if ($query) {
    header("Location: barang.php");
} else {
    echo "Gagal hapus: " . mysqli_error($conn);
}
?>