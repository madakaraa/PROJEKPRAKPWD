<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php?error=Akses ilegal! Anda harus login sebagai User.");
    exit;
}

$nama = htmlspecialchars($_SESSION['nama']);
$inisial = strtoupper(substr($nama, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PinjamBareng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #0f172a;
            min-height: 100vh;
        }

        /* ===== HERO ===== */
        .hero {
            background: #0C1B33;
            padding: 2rem 2rem 5.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .hero-glow-1 {
            position: absolute;
            top: -80px; right: -80px;
            width: 380px; height: 380px;
            border-radius: 50%;
            background: #185FA5;
            opacity: 0.16;
            pointer-events: none;
        }

        .hero-glow-2 {
            position: absolute;
            bottom: -100px; left: 30px;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: #534AB7;
            opacity: 0.14;
            pointer-events: none;
        }

        .hero-inner { position: relative; z-index: 2; }

        /* Top bar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #5DCAA5;
        }

        .topbar-right { display: flex; align-items: center; gap: 10px; }

        .notif-btn {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.07);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: background 0.2s;
        }
        .notif-btn:hover { background: rgba(255,255,255,0.14); color: #fff; }

        .avatar-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 50px;
            padding: 4px 14px 4px 5px;
        }

        .avatar-circle {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: #185FA5;
            color: #B5D4F4;
            font-size: 12px;
            font-weight: 600;
            display: flex; align-items: center; justify-content: center;
        }

        .avatar-username {
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            font-weight: 400;
        }

        /* Greeting */
        .greeting-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(93,202,165,0.14);
            border: 1px solid rgba(93,202,165,0.28);
            border-radius: 50px;
            padding: 4px 14px;
            font-size: 11px;
            color: #9FE1CB;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .greeting-pill-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: #5DCAA5;
        }

        .hero-title {
            font-size: 2.4rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -0.5px;
            margin-bottom: 0.6rem;
        }

        .hero-title span { color: #85B7EB; }

        .hero-subtitle {
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            line-height: 1.7;
            max-width: 380px;
        }

        /* Mini stats inside hero */
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            margin-top: 2rem;
        }

        .mini-stat {
            padding: 0 1.4rem;
            border-right: 1px solid rgba(255,255,255,0.08);
        }
        .mini-stat:first-child { padding-left: 0; }
        .mini-stat:last-child { border-right: none; }

        .ms-label {
            font-size: 10px;
            color: rgba(255,255,255,0.32);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .ms-val {
            font-size: 1.7rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        .ms-val.teal  { color: #5DCAA5; }
        .ms-val.purple { color: #AFA9EC; }

        .ms-change {
            font-size: 11px;
            color: rgba(255,255,255,0.28);
            margin-top: 5px;
        }
        .ms-change.up { color: #5DCAA5; }

        /* ===== BODY ===== */
        .page-body {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 1.5rem 2.5rem;
        }

        /* ===== WELCOME BANNER ===== */
        .welcome-banner {
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 20px;
            padding: 1.8rem 2rem;
            margin-top: -3.2rem;
            position: relative;
            z-index: 10;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            overflow: hidden;
        }

        .wb-illus { flex-shrink: 0; width: 110px; height: 90px; }
        .wb-content { flex: 1; }

        .wb-eyebrow {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #5DCAA5;
            margin-bottom: 6px;
        }

        .wb-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
            margin-bottom: 6px;
        }

        .wb-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.65;
            max-width: 500px;
        }

        .wb-pills {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .wb-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 50px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
        }
        .wb-pill.blue   { background: #E6F1FB; color: #185FA5; }
        .wb-pill.green  { background: #EAF3DE; color: #27500A; }
        .wb-pill.purple { background: #EEEDFE; color: #3C3489; }

        .welcome-banner::after {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(93,202,165,0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        @media (max-width: 600px) {
            .welcome-banner { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .wb-illus { width: 80px; height: 65px; }
        }

        /* ===== SECTION HEADER ===== */
        .section-hd {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .section-link {
            font-size: 12px;
            color: #378ADD;
            cursor: pointer;
            text-decoration: none;
        }
        .section-link:hover { text-decoration: underline; color: #185FA5; }

        /* ===== MENU CARDS ===== */
        .menu-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .mcard {
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 18px;
            padding: 1.4rem;
            cursor: pointer;
            transition: border-color 0.2s, transform 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .mcard:hover {
            border-color: #c5d4e0;
            transform: translateY(-3px);
            color: inherit;
        }

        .mcard-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 50px;
            padding: 3px 11px;
            font-size: 11px;
            font-weight: 600;
            align-self: flex-start;
        }
        .mcard-tag.blue   { background: #E6F1FB; color: #0C447C; }
        .mcard-tag.green  { background: #EAF3DE; color: #27500A; }
        .mcard-tag.purple { background: #EEEDFE; color: #3C3489; }

        .mcard-tag-dot { width: 5px; height: 5px; border-radius: 50%; }
        .mcard-tag.blue .mcard-tag-dot   { background: #378ADD; }
        .mcard-tag.green .mcard-tag-dot  { background: #639922; }
        .mcard-tag.purple .mcard-tag-dot { background: #7F77DD; }

        .mcard-body { flex: 1; }

        .mcard-title {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .mcard-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .mcard-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }

        .mcard-count { font-size: 12px; color: #94a3b8; }

        .mcard-cta {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .mcard:hover .mcard-cta { color: #0f172a; }

        /* ===== ACTIVITY ===== */
        .activity-card {
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 18px;
            padding: 1.4rem;
            margin-bottom: 1.5rem;
        }

        .act-list { display: flex; flex-direction: column; gap: 14px; margin-top: 1rem; }

        .act-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .act-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .act-dot.green  { background: #5DCAA5; }
        .act-dot.amber  { background: #EF9F27; }
        .act-dot.blue   { background: #378ADD; }

        .act-info { flex: 1; }
        .act-title { font-size: 13px; color: #0f172a; font-weight: 500; }
        .act-time  { font-size: 11px; color: #94a3b8; margin-top: 2px; }

        .act-badge {
            font-size: 11px;
            border-radius: 50px;
            padding: 3px 10px;
            font-weight: 500;
            flex-shrink: 0;
        }
        .act-badge.returned { background: #E1F5EE; color: #085041; }
        .act-badge.active   { background: #FAEEDA; color: #633806; }
        .act-badge.new      { background: #E6F1FB; color: #0C447C; }

        /* ===== INFO BANNER ===== */
        .info-banner {
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
        }

        .info-icon-box {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: #E6F1FB;
            color: #185FA5;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .info-text { font-size: 12px; color: #64748b; line-height: 1.6; }
        .info-text strong { color: #0f172a; font-weight: 600; }

        /* ===== FOOTER ===== */
        .db-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.2rem;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }

        .footer-right { display: flex; align-items: center; gap: 6px; }
        .footer-user  { color: #64748b; font-weight: 500; }
        .footer-logout { color: #E24B4A; text-decoration: none; font-weight: 500; }
        .footer-logout:hover { text-decoration: underline; color: #A32D2D; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero { padding: 1.5rem 1.2rem 5rem; }
            .hero-title { font-size: 1.7rem; }
            .hero-stats { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .mini-stat { border-right: none; padding: 0; }
            .mini-stat:nth-child(odd) { border-right: 1px solid rgba(255,255,255,0.08); padding-right: 1rem; }
            .floating-cards, .menu-row { grid-template-columns: 1fr; }
            .page-body { padding: 0 1rem 2rem; }
        }
    </style>
</head>
<body>

<!-- ===== HERO SECTION ===== -->
<div class="hero">
    <div class="hero-dots"></div>
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>

    <div class="hero-inner" style="max-width: 960px; margin: 0 auto;">

        <!-- Top bar -->
        <div class="topbar">
            <div class="brand-name">
                <div class="brand-dot"></div>
                PinjamBareng
            </div>
            <div class="topbar-right">
                <a href="#" class="notif-btn" title="Notifikasi">
                    <i class="bi bi-bell" style="font-size: 15px;"></i>
                </a>
                <div class="avatar-pill">
                    <div class="avatar-circle"><?= $inisial; ?></div>
                    <span class="avatar-username"><?= strtolower($nama); ?></span>
                </div>
            </div>
        </div>

        <!-- Greeting -->
        <div class="greeting-pill">
            <div class="greeting-pill-dot"></div>
            Dashboard aktif
        </div>
        <h1 class="hero-title">
            Halo, <span><?= $nama; ?></span> —<br>selamat datang kembali.
        </h1>
        <p class="hero-subtitle">Semua yang kamu butuhkan untuk mengelola pinjaman ada di sini.</p>

        <!-- Mini stats -->
        <div class="hero-stats">
            <div class="mini-stat">
                <div class="ms-label">Tersedia</div>
                <div class="ms-val">24</div>
                <div class="ms-change up"><i class="bi bi-arrow-up"></i> +3 minggu ini</div>
            </div>
            <div class="mini-stat">
                <div class="ms-label">Aktif dipinjam</div>
                <div class="ms-val teal">3</div>
                <div class="ms-change">Berjalan</div>
            </div>
            <div class="mini-stat">
                <div class="ms-label">Transaksi</div>
                <div class="ms-val purple">12</div>
                <div class="ms-change up"><i class="bi bi-arrow-up"></i> +2 bulan ini</div>
            </div>
            <div class="mini-stat">
                <div class="ms-label">Pengembalian</div>
                <div class="ms-val">98%</div>
                <div class="ms-change up">Sangat baik</div>
            </div>
        </div>

    </div>
</div>

<!-- ===== PAGE BODY ===== -->
<div class="page-body">

    <!-- Welcome Banner (menggantikan floating cards) -->
    <div class="welcome-banner">
        <!-- Ilustrasi SVG: orang memegang kotak/paket -->
        <svg class="wb-illus" viewBox="0 0 110 90" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Bayangan -->
            <ellipse cx="55" cy="85" rx="30" ry="4" fill="#e2e8f0"/>
            <!-- Kotak utama -->
            <rect x="28" y="30" width="44" height="38" rx="6" fill="#E6F1FB"/>
            <rect x="28" y="30" width="44" height="38" rx="6" stroke="#185FA5" stroke-width="1.2"/>
            <!-- Garis tengah kotak -->
            <line x1="50" y1="30" x2="50" y2="68" stroke="#185FA5" stroke-width="1" stroke-dasharray="3 2" opacity="0.4"/>
            <!-- Pita kotak -->
            <rect x="40" y="30" width="20" height="8" rx="3" fill="#378ADD" opacity="0.25"/>
            <path d="M50 30 v-6" stroke="#378ADD" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M44 24 q6-6 12 0" stroke="#378ADD" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <!-- Tangan kiri -->
            <path d="M18 55 Q14 50 20 46 L30 50" stroke="#0C1B33" stroke-width="2" stroke-linecap="round" fill="none"/>
            <circle cx="17" cy="56" r="4" fill="#f8d7b5" stroke="#e0b98a" stroke-width="0.8"/>
            <!-- Tangan kanan -->
            <path d="M92 55 Q96 50 90 46 L80 50" stroke="#0C1B33" stroke-width="2" stroke-linecap="round" fill="none"/>
            <circle cx="93" cy="56" r="4" fill="#f8d7b5" stroke="#e0b98a" stroke-width="0.8"/>
            <!-- Badan orang -->
            <rect x="40" y="68" width="30" height="14" rx="5" fill="#0C1B33"/>
            <!-- Kepala -->
            <circle cx="55" cy="22" r="10" fill="#f8d7b5" stroke="#e0b98a" stroke-width="0.8"/>
            <!-- Rambut -->
            <path d="M45 20 Q50 12 60 14 Q66 16 65 22" fill="#2d1b0e" stroke="none"/>
            <!-- Bintang dekoratif -->
            <text x="88" y="22" font-size="12" fill="#EF9F27" font-family="sans-serif">✦</text>
            <text x="12" y="30" font-size="8" fill="#5DCAA5" font-family="sans-serif">✦</text>
        </svg>

        <div class="wb-content">
            <div class="wb-eyebrow">✦ Platform pinjam bersama</div>
            <div class="wb-title">Berbagi itu mudah, meminjam itu menyenangkan.</div>
            <div class="wb-desc">
                PinjamBareng hadir untuk menghubungkan kamu dengan barang-barang yang kamu butuhkan — dari komunitas, untuk komunitas. Pilih, pinjam, dan kembalikan tepat waktu agar sesama bisa merasakannya juga.
            </div>
            <div class="wb-pills">
                <span class="wb-pill blue"><i class="bi bi-box-seam"></i> Banyak Barang Tersedia</span>
                <span class="wb-pill green"><i class="bi bi-arrow-repeat"></i> Selalu Update</span>
                <span class="wb-pill purple"><i class="bi bi-shield-check"></i> Privasi Aman!</span>
            </div>
        </div>
    </div>

    <!-- NOTIFIKASI PESAN DARI KERANJANG/CHECKOUT -->
    <?php if(isset($_GET['pesan']) && isset($_SESSION['role']) && $_SESSION['role'] == 'user'): ?>
        <div class="alert alert-dismissible fade show shadow-sm mb-4" style="background-color: #EAF3DE; color: #27500A; border: 1px solid rgba(39, 80, 10, 0.2); border-radius: 16px; padding: 1rem 1.25rem;" role="alert">
            <i class="bi bi-check-circle-fill me-2" style="color: #5DCAA5;"></i> <strong>Peminjaman Sukses!</strong> <?= htmlspecialchars($_GET['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Menu Utama -->
    <div class="section-hd">
        <span class="section-title">Menu utama</span>
    </div>
    <div class="menu-row">
        <a href="barang.php" class="mcard">
            <div class="mcard-tag blue"><div class="mcard-tag-dot"></div>Katalog</div>
            <div class="mcard-body">
                <div class="mcard-title">Daftar Barang</div>
                <div class="mcard-desc">Telusuri semua barang yang tersedia untuk dipinjam dalam komunitas.</div>
            </div>
            <div class="mcard-footer">
                <span class="mcard-count">Lihat Daftar Barang</span>
                <span class="mcard-cta">Lihat semua <i class="bi bi-chevron-right"></i></span>
            </div>
        </a>

        <a href="keranjang_view.php" class="mcard">
            <div class="mcard-tag green"><div class="mcard-tag-dot"></div>Keranjang</div>
            <div class="mcard-body">
                <div class="mcard-title">Keranjang Pinjam</div>
                <div class="mcard-desc">Proses permintaan pinjaman yang sudah kamu pilih sebelumnya.</div>
            </div>
            <div class="mcard-footer">
                <span class="mcard-count">Yuk Pinjam</span>
                <span class="mcard-cta">Buka <i class="bi bi-chevron-right"></i></span>
            </div>
        </a>

        <a href="riwayat.php" class="mcard">
            <div class="mcard-tag purple"><div class="mcard-tag-dot"></div>Riwayat</div>
            <div class="mcard-body">
                <div class="mcard-title">Riwayat Transaksi</div>
                <div class="mcard-desc">Isi riwayat transaksi anda!</div>
            </div>
            <div class="mcard-footer">
                <span class="mcard-count">Histori Transaksi Anda</span>
                <span class="mcard-cta">Lihat <i class="bi bi-chevron-right"></i></span>
            </div>
        </a>
    </div>

    <!-- Aktivitas Terkini (Dinamis dari Database) -->
    <div class="section-hd">
        <span class="section-title">Aktivitas terkini</span>
        <a href="riwayat.php" class="section-link">Lihat semua</a>
    </div>
    <div class="activity-card">
        <div class="act-list">
            <?php
            // Mengambil 5 aktivitas terbaru
            // Pastikan fungsi waktu_lalu() sudah ada di koneksi.php
            $query_aktivitas = mysqli_query($conn, "SELECT * FROM aktivitas ORDER BY created_at DESC LIMIT 5");
            
            if($query_aktivitas && mysqli_num_rows($query_aktivitas) > 0) {
                while($akt = mysqli_fetch_assoc($query_aktivitas)) {
                    $warna_titik = ''; $badge_class = ''; $teks_badge = '';
                    
                    if($akt['kategori'] == 'selesai') {
                        $warna_titik = 'green'; $badge_class = 'returned'; $teks_badge = 'Selesai';
                    } elseif($akt['kategori'] == 'aktif') {
                        $warna_titik = 'amber'; $badge_class = 'active'; $teks_badge = 'Aktif';
                    } else {
                        $warna_titik = 'blue'; $badge_class = 'new'; $teks_badge = 'Baru';
                    }
            ?>
                <div class="act-item">
                    <div class="act-dot <?= $warna_titik; ?>"></div>
                    <div class="act-info">
                        <div class="act-title"><?= htmlspecialchars($akt['deskripsi']); ?></div>
                        <div class="act-time">
                            <?= function_exists('waktu_lalu') ? waktu_lalu($akt['created_at']) : $akt['created_at']; ?> 
                            <?= !empty($akt['sub_deskripsi']) ? ' • ' . htmlspecialchars($akt['sub_deskripsi']) : ''; ?>
                        </div>
                    </div>
                    <span class="act-badge <?= $badge_class; ?>"><?= $teks_badge; ?></span>
                </div>
            <?php 
                }
            } else {
                echo '<div class="act-item text-muted justify-content-center" style="font-size: 13px;">Belum ada aktivitas terekam.</div>';
            }
            ?>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <div class="info-icon-box"><i class="bi bi-info-circle-fill"></i></div>
        <div class="info-text">
            <strong>Platform berbasis kepercayaan.</strong> Kembalikan barang tepat waktu agar sesama anggota bisa memanfaatkannya. Hubungi admin jika ada kendala.
        </div>
    </div>

    <!-- Footer -->
    <div class="db-footer">
        <span>PinjamBareng &copy; 2026</span>
        <div class="footer-right">
            <span>Masuk sebagai <span class="footer-user"><?= strtolower($nama); ?></span></span>
            <span>&middot;</span>
            <a href="logout.php" class="footer-logout">Keluar</a>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>