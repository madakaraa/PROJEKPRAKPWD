<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = md5($_POST['password']);

// Verifikasi kredensial user
$data = mysqli_query($koneksi,"SELECT * FROM users WHERE username='$username' AND password='$password'");
$cek = mysqli_num_rows($data);

if($cek > 0){
    $row = mysqli_fetch_assoc($data);
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $row['role']; // admin atau warga
    $_SESSION['id_user'] = $row['id'];
    $_SESSION['status'] = "login";
    header("location:dashboard.php");
} else {
    header("location:index.php?pesan=gagal");
}
?>