<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php?pesan=belum_login");
    exit;
}

// Tolak akses jika bukan admin
if($_SESSION['role'] != "admin"){
    header("location:dashboard.php?pesan=akses_ditolak");
    exit;
}
?>