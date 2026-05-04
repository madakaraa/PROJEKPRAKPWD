<?php
include 'koneksi.php';

// Mengambil parameter dengan aman (Mencegah XSS)
$error   = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa; /* Warna latar konsisten dengan login */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        /* Styling Kartu Register */
        .register-card {
            width: 100%;
            max-width: 450px; /* Sedikit lebih lebar dari login karena form lebih banyak */
            background: #ffffff;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .brand-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }

        .register-title {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #212529;
        }

        /* Styling Form Input (Floating) */
        .form-floating > .form-control {
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: #86b7fe;
        }

        /* Styling Tombol */
        .btn-register {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2);
        }

        /* Alert Notifikasi Mengambang */
        .alert-custom {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 320px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border: none;
        }
    </style>
</head>
<body>

<?php if($success): ?>
    <div class="alert alert-success alert-dismissible fade show alert-custom d-flex align-items-center" role="alert" id="autoAlert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div><?= $success; ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show alert-custom d-flex align-items-center" role="alert" id="autoAlert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?= $error; ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="register-card text-center mx-3">
    <i class="bi bi-person-plus brand-icon"></i>
    <h3 class="register-title mb-1">Daftar Akun</h3>
    <p class="text-muted mb-4 small">Lengkapi data di bawah untuk bergabung</p>

    <form action="proses_register.php" method="POST">
        
        <div class="form-floating mb-3">
            <input type="text" name="nama" class="form-control" id="floatingNama" placeholder="Nama Lengkap" required>
            <label for="floatingNama" class="text-muted"><i class="bi bi-person-badge me-1"></i> Nama Lengkap</label>
        </div>

        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email" required>
            <label for="floatingEmail" class="text-muted"><i class="bi bi-envelope me-1"></i> Alamat Email</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" name="username" class="form-control" id="floatingUsername" placeholder="Username" autocomplete="off" required>
            <label for="floatingUsername" class="text-muted"><i class="bi bi-person me-1"></i> Username</label>
        </div>

        <div class="form-floating mb-4">
            <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" autocomplete="off" required>
            <label for="floatingPassword" class="text-muted"><i class="bi bi-shield-lock me-1"></i> Password</label>
        </div>

        <button type="submit" class="btn btn-primary btn-register w-100">
            Daftar Sekarang <i class="bi bi-check2-circle ms-1"></i>
        </button>
    </form>

    <div class="mt-4 pt-3 border-top">
        <p class="text-muted small mb-0">
            Sudah punya akun? <a href="login.php" class="text-decoration-none fw-semibold">Login di sini</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const alertBox = document.getElementById('autoAlert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => alertBox.remove(), 150);
        }, 3500);
    }
});
</script>

</body>
</html>