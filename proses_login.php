<?php
session_start();
include 'koneksi.php';

// 1. Ambil data dari form login
$username     = mysqli_real_escape_string($conn, $_POST['username']);
$password     = $_POST['password'];
$role_pilihan = isset($_POST['role']) ? $_POST['role'] : ''; // Menangkap tombol yang diklik

// 2. Cari data user di database
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$data  = mysqli_fetch_assoc($query);

if ($data) {
    // 3. Cek Password
    if (password_verify($password, $data['password'])) {

        // 🔥 4. PENJAGA PINTU UTAMA: 
        // Samakan role di DB dengan tombol yang diklik di halaman login
        $role_db   = strtolower(trim($data['role']));
        $role_klik = strtolower(trim($role_pilihan));

        // JIKA TOMBOL YANG DIKLIK TIDAK SAMA DENGAN JABATAN ASLINYA
        if ($role_db !== $role_klik) {
            // Tendang kembali ke halaman login!
            header("Location: login.php?error=Akses ditolak! Anda harus memilih tab " . ucfirst($role_db) . " untuk akun ini.");
            exit;
        }

        // ✅ JIKA COCOK (Admin klik Admin, User klik User): Izinkan masuk
        $_SESSION['login'] = true;
        $_SESSION['id']    = $data['id'];
        $_SESSION['nama']  = $data['nama'];
        $_SESSION['role']  = $data['role'];

        // Arahkan ke dashboard masing-masing
        if ($role_db == 'admin') {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;

    } else {
        header("Location: login.php?error=Password salah");
        exit;
    }
} else {
    header("Location: login.php?error=Username tidak ditemukan");
    exit;
}
?>