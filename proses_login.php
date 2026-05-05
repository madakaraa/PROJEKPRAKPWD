<?php
session_start();
include 'koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$role     = mysqli_real_escape_string($conn, $_POST['role']);

// Query cari user sesuai input[cite: 1, 2]
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = '$role'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    
    // Simpan semua data penting ke Session
    $_SESSION['login'] = true;
    $_SESSION['id']    = $data['id'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama']  = $data['nama']; // KOLOM INI WAJIB ADA DI TABEL LU
    $_SESSION['role']  = $data['role'];

    if ($data['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
} else {
    // Balik ke login kalau salah
    header("Location: login.php?error=" . urlencode("Username, Password, atau Role salah!"));
    exit;
}
?>
