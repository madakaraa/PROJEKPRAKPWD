<?php
session_start();
include 'koneksi.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Tangkap data dan sesuaikan dengan nama kolom di tabel 'users' lu
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']); 
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telp  = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $no_rumah = mysqli_real_escape_string($conn, $_POST['no_rumah']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $role = 'user'; 

    $cek_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_username) > 0) {
        $pesan = "Username sudah terdaftar. Pakai yang lain ya!";
    } else {
        // Query insert menggunakan kolom 'nama' sesuai struktur tabel lu
        $query = "INSERT INTO users (nama, email, username, password, no_telp, no_rumah, role) 
                  VALUES ('$nama', '$email', '$username', '$password', '$no_telp', '$no_rumah', '$role')";
                  
        if (mysqli_query($conn, $query)) {
            // Alihkan ke login.php dengan parameter sukses agar notifikasi muncul
            header("Location: login.php?success=" . urlencode("Akun sudah dibuat, silakan login!"));
            exit;
        } else {
            $pesan = "Registrasi gagal: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - PinjamBareng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 1rem; }
        .register-card { background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); width: 100%; max-width: 420px; }
        .header-icon { color: #0d6efd; font-size: 2.5rem; text-align: center; margin-bottom: 0.5rem; display: block; }
        .register-card h2 { text-align: center; font-weight: 700; color: #0f172a; margin-bottom: 0.3rem; font-size: 1.5rem; }
        .register-card p { text-align: center; color: #64748b; font-size: 0.85rem; margin-bottom: 1.5rem; }
        .input-group-custom { display: flex; align-items: center; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.6rem 1rem; margin-bottom: 1rem; transition: all 0.2s; background: white; }
        .input-group-custom:focus-within { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
        .input-group-custom i { color: #64748b; margin-right: 12px; font-size: 1.1rem; }
        .input-group-custom input { border: none; outline: none; width: 100%; font-size: 0.9rem; color: #1e293b; background: transparent; }
        .btn-register { width: 100%; background: #0d6efd; color: white; border: none; padding: 0.75rem; border-radius: 10px; font-weight: 600; font-size: 0.95rem; margin-top: 0.5rem; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-register:hover { background: #0b5ed7; }
        .login-link { text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #64748b; }
        .login-link a { color: #0d6efd; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="register-card">
    <i class="bi bi-person-plus header-icon"></i>
    <h2>Daftar Akun</h2>
    <p>Lengkapi data di bawah untuk bergabung</p>

    <?php if($pesan): ?>
        <div class="alert alert-danger" style="font-size: 0.85rem; padding: 0.7rem; border-radius: 10px;">
            <i class="bi bi-exclamation-circle me-1"></i> <?= $pesan; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-group-custom">
            <i class="bi bi-person-vcard"></i>
            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" placeholder="Alamat Email" required>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-telephone"></i>
            <input type="number" name="no_telp" placeholder="No. Telepon / WhatsApp" required>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-house-door"></i>
            <input type="text" name="no_rumah" placeholder="No. Rumah" required>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-person"></i>
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="input-group-custom">
            <i class="bi bi-shield-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <button type="submit" class="btn-register">
            Daftar Sekarang <i class="bi bi-check-circle"></i>
        </button>
    </form>

    <div class="login-link">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
</div>

</body>
</html>
