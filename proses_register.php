<?php
include 'koneksi.php';

$nama     = $_POST['nama'];
$email    = $_POST['email'];
$username = $_POST['username'];
    if($username == 'admin'){
    $role = 'admin';
} else {
    $role = 'user';
}
$password = $_POST['password'];

// validasi
if (empty($nama) || empty($email) || empty($username) || empty($password)) {
    header("Location: register.php?error=Semua field wajib diisi");
    exit;
}

// cek duplicate
$cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' OR username='$username'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: register.php?error=Email atau Username sudah digunakan");
    exit;
}

// hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// insert
$query = mysqli_query($conn, "INSERT INTO users (nama, email, username, password) 
VALUES ('$nama', '$email', '$username', '$password_hash')");

if ($query) {
    header("Location: login.php?success=Registrasi berhasil");
} else {
    header("Location: register.php?error=Registrasi gagal");
}
exit;
?>