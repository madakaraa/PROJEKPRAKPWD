<?php
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : "";
$error   = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue-deep:   #0a2540;
            --blue-accent: #2563eb;
            --gold:        #f59e0b;
            --surface:     #ffffff;
            --text-main:   #0a2540;
            --text-muted:  #64748b;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            background: #f0f4ff;
            overflow-x: hidden;
        }

        /* ─── LEFT PANEL ─────────────────────────────── */
        .panel-left {
            flex: 1;
            background: var(--blue-deep);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            overflow: hidden;
            min-height: 100vh;
        }
        .panel-left::before,
        .panel-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .panel-left::before { width: 560px; height: 560px; top: -150px; right: -180px; }
        .panel-left::after  { width: 340px; height: 340px; bottom: -100px; left: -120px; }

        .ring { position: absolute; border-radius: 50%; border: 1px solid rgba(37,99,235,0.25); }
        .ring-1 { width: 180px; height: 180px; top: 38%; right: -40px; }
        .ring-2 { width:  80px; height:  80px; bottom: 28%; left: 48px; }

        .left-brand { position: relative; z-index: 2; }

        .left-logo {
            width: 52px; height: 52px;
            background: var(--blue-accent);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
        }
        .left-logo svg { width: 26px; height: 26px; fill: white; }

        .left-brand h1 {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .left-brand p {
            color: rgba(255,255,255,0.45);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 300;
        }

        /* ─── ILLUSTRATION ───────────────────────────── */
        .left-illustration {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 0;
            gap: 1.5rem;
        }

        .ill-headline {
            font-family: 'Syne', sans-serif;
            font-size: 1.55rem;
            font-weight: 800;
            color: white;
            line-height: 1.3;
            text-align: center;
            max-width: 300px;
        }
        .ill-headline .hl { color: #93c5fd; }

        .ill-desc {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.38);
            text-align: center;
            max-width: 270px;
            line-height: 1.65;
        }

        .ill-divider {
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.3;
        }
        .ill-divider span { width: 6px; height: 6px; background: #2563eb; border-radius: 50%; }
        .ill-divider .line { flex: 1; height: 1px; background: #3b82f6; }

        .ill-pills {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            max-width: 300px;
        }
        .ill-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 0.75rem 1rem;
        }

        .pill-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }
        .pill-icon.blue   { background: rgba(37,99,235,0.3);   color: #93c5fd; }
        .pill-icon.amber  { background: rgba(245,158,11,0.2);  color: #fcd34d; }
        .pill-icon.green  { background: rgba(16,185,129,0.2);  color: #6ee7b7; }

        .pill-text strong { display: block; font-size: 0.82rem; color: rgba(255,255,255,0.88); font-weight: 600; }
        .pill-text span   { font-size: 0.72rem; color: rgba(255,255,255,0.35); }

        .pill-check { color: rgba(110,231,183,0.65); font-size: 0.9rem; margin-left: auto; }

        /* ─── STATS ──────────────────────────────────── */
        .left-footer { position: relative; z-index: 2; }
        .left-footer > small {
            display: block;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.18);
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-row { display: flex; gap: 2rem; }
        .stat-item span {
            display: block;
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
        }
        .stat-item small { font-size: 0.72rem; color: rgba(255,255,255,0.28); }

        /* ─── RIGHT PANEL ─────────────────────────────── */
        .panel-right {
            width: 480px;
            flex-shrink: 0;
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .login-box { width: 100%; max-width: 380px; }

        .login-header { margin-bottom: 2rem; }
        .login-header h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }
        .login-header p { color: var(--text-muted); font-size: 0.875rem; margin-top: 0.3rem; }

        .role-toggle {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
            margin-bottom: 1.75rem;
        }
        .role-toggle input[type="radio"] { display: none; }
        .role-toggle label {
            flex: 1; text-align: center;
            padding: 0.6rem 1rem;
            font-size: 0.875rem; font-weight: 500;
            color: var(--text-muted);
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex; align-items: center; justify-content: center;
            gap: 6px; margin: 0;
        }
        .role-toggle input[type="radio"]:checked + label {
            background: white;
            color: var(--blue-accent);
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
        }

        .field-group { margin-bottom: 1rem; }
        .field-label {
            font-size: 0.8rem; font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
            display: block; letter-spacing: 0.2px;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; font-size: 1rem;
            pointer-events: none; transition: color 0.2s;
        }
        .field-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.6rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem; color: var(--text-main);
            background: #fafafa;
            transition: all 0.2s ease; outline: none;
        }
        .field-input:focus {
            border-color: var(--blue-accent);
            background: white;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }
        .field-wrap:focus-within .field-icon { color: var(--blue-accent); }

        .toggle-pass {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; font-size: 1rem;
            background: none; border: none; padding: 0; transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--blue-accent); }

        .btn-masuk {
            width: 100%;
            background: var(--blue-accent); color: white;
            border: none; border-radius: 12px;
            padding: 0.85rem 1rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px; transition: all 0.25s ease; margin-top: 1.5rem;
        }
        .btn-masuk:hover {
            background: #1d4ed8; transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.25);
        }
        .btn-masuk:active { transform: translateY(0); }

        .accent-dot {
            display: inline-block; width: 6px; height: 6px;
            background: var(--gold); border-radius: 50%;
            vertical-align: middle; margin-right: 6px;
        }

        .register-link {
            text-align: center; margin-top: 1.75rem;
            padding-top: 1.25rem; border-top: 1px solid #f1f5f9;
            font-size: 0.85rem; color: var(--text-muted);
        }
        .register-link a {
            color: var(--blue-accent); font-weight: 600; text-decoration: none;
        }
        .register-link a:hover { opacity: 0.8; }

        .alert-float {
            position: fixed; top: 1.5rem; right: 1.5rem;
            z-index: 9999; min-width: 300px; max-width: 380px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border: none; font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem; font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .panel-left { min-height: auto; padding: 2rem; }
            .left-illustration { display: none; }
            .panel-right { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show alert-float d-flex align-items-center" role="alert" id="autoAlert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <div><?= $success; ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show alert-float d-flex align-items-center" role="alert" id="autoAlert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <div><?= $error; ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- LEFT PANEL -->
<div class="panel-left">
    <div class="ring ring-1"></div>
    <div class="ring ring-2"></div>

    <div class="left-brand">
        <div class="left-logo">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 8l-9-6-9 6v2l9 6 9-6V8zm-9 8.5L3 11v2l9 6 9-6v-2l-9 5.5z"/>
            </svg>
        </div>
        <h1>Pinjam<br>Bareng</h1>
        <p>Platform peminjaman barang terpercaya</p>
    </div>

    <div class="left-illustration">
        <p class="ill-headline">
            Pinjam apa saja,<br>kapan saja,<br>dengan <span class="hl">mudah</span>
        </p>

        <p class="ill-desc">
            Bergabunglah bersama ribuan pengguna yang sudah mempercayai PinjamBareng untuk kebutuhan sehari-hari.
        </p>

        <div class="ill-divider" style="width:100%; max-width:300px;">
            <span></span>
            <div class="line"></div>
            <span></span>
        </div>

        <div class="ill-pills">
            <div class="ill-pill">
                <div class="pill-icon blue">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="pill-text">
                    <strong>Aman &amp; Terpercaya</strong>
                    <span>Setiap transaksi terverifikasi sistem</span>
                </div>
                <i class="bi bi-check-circle-fill pill-check"></i>
            </div>

            <div class="ill-pill">
                <div class="pill-icon amber">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="pill-text">
                    <strong>Proses Cepat</strong>
                    <span>Persetujuan dalam hitungan menit</span>
                </div>
                <i class="bi bi-check-circle-fill pill-check"></i>
            </div>

            <div class="ill-pill">
                <div class="pill-icon green">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="pill-text">
                    <strong>Komunitas Aktif</strong>
                    <span>Pengguna siap berbagi!</span>
                </div>
                <i class="bi bi-check-circle-fill pill-check"></i>
            </div>
        </div>
    </div>

    <div class="left-footer">
        <small>Platform stats</small>
        <div class="stat-row">
            <div class="stat-item">
                <span>1.2K</span>
                <small>Pengguna aktif</small>
            </div>
            <div class="stat-item">
                <span>4.8K</span>
                <small>Barang tersedia</small>
            </div>
            <div class="stat-item">
                <span>98%</span>
                <small>Kepuasan</small>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="panel-right">
    <div class="login-box">
        <div class="login-header">
            <h2>Selamat datang 👋</h2>
            <p>Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <form action="proses_login.php" method="POST">
            <div class="role-toggle">
                <input type="radio" name="role" id="roleUser" value="user" checked>
                <label for="roleUser">
                    <i class="bi bi-person-fill"></i> User
                </label>
                <input type="radio" name="role" id="roleAdmin" value="admin">
                <label for="roleAdmin">
                    <i class="bi bi-shield-fill"></i> Admin
                </label>
            </div>

            <div class="field-group">
                <label class="field-label" for="username">
                    <span class="accent-dot"></span>Username
                </label>
                <div class="field-wrap">
                    <i class="bi bi-person field-icon"></i>
                    <input type="text" name="username" id="username" class="field-input" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">
                    <span class="accent-dot"></span>Password
                </label>
                <div class="field-wrap">
                    <i class="bi bi-lock field-icon"></i>
                    <input type="password" name="password" id="password" class="field-input" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pass" onclick="togglePassword()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-masuk">
                Masuk ke Akun <i class="bi bi-arrow-right-short"></i>
            </button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const alertBox = document.getElementById('autoAlert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.classList.remove('show');
            setTimeout(() => alertBox.remove(), 200);
        }, 3500);
    }
});
function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pw.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>