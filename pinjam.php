<?php
session_start();
include 'koneksi.php';

$id_barang = $_GET['id'];
?>

<h3>Pinjam Barang</h3>

<form action="proses_pinjam.php" method="POST">
    <input type="hidden" name="id_barang" value="<?= $id_barang; ?>">

    <label>Tanggal Pinjam</label><br>
    <input type="date" name="tgl_pinjam" required><br><br>

    <label>Tanggal Kembali</label><br>
    <input type="date" name="tgl_kembali" required><br><br>

    <button type="submit">Pinjam</button>
</form>