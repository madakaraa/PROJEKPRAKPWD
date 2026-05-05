<?php
session_start();
include 'koneksi.php';

// 🔥 PROTEKSI GANDA: Pastikan yang masuk BENAR-BENAR ADMIN
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Akses ilegal! Anda bukan Admin.");
    exit;
}

// FORMAT NAMA ADMIN (Kapital di awal kata)
$nama_asli = isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Admin';
$nama = ucwords(strtolower($nama_asli));

// Mengambil beberapa data statistik untuk ditampilkan di Dashboard Admin
$jml_barang = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM barang"));
$jml_transaksi = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM peminjaman"));
$jml_menunggu = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM peminjaman WHERE status='menunggu'"));
$jml_dipinjam = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM peminjaman WHERE status='dipinjam'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; 
            color: #1e293b;
            overflow-x: hidden;
        }

        /* Latar Belakang Eksklusif Admin */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 350px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            z-index: -1;
            border-radius: 0 0 30px 30px;
        }

        /* Header & Navbar */
        .navbar-brand {
            font-weight: 800;
            color: #ffffff !important;
            letter-spacing: -0.5px;
        }

        .btn-logout {
            border-radius: 50px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background: #ef4444;
            border-color: #ef4444;
        }

        /* Welcome Section */
        .welcome-section {
            margin-top: 2rem;
            margin-bottom: 3rem;
            color: white;
        }

        /* Kartu Statistik Cepat */
        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        /* Kartu Menu Kendali (Control Menu) */
        .menu-card {
            border: 1px solid rgba(0,0,0,0.03);
            border-radius: 24px;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 2rem 1.5rem;
            text-decoration: none;
            color: #1e293b;
            display: block;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            position: relative;
        }
        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(13, 110, 253, 0.08);
            border-color: rgba(13, 110, 253, 0.2);
        }
        .menu-icon-top {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .menu-title {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0.3rem;
        }
        .menu-desc {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }

        /* Lencana Admin */
        .admin-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: white;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        /* Styling Aktivitas Terkini (Admin) */
        .admin-activity-wrap {
            margin-top: 3rem;
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .act-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .act-item:last-child { border-bottom: none; }
        .act-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .act-dot.green  { background: #10b981; }
        .act-badge.returned { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .act-dot.red    { background: #ef4444; }
        .act-badge.danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
        .act-dot.amber  { background: #f59e0b; }
        .act-dot.blue   { background: #3b82f6; }
        .act-info { flex: 1; }
        .act-title { font-size: 0.95rem; color: #1e293b; font-weight: 500; }
        .act-time  { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
        .act-badge {
            font-size: 0.75rem;
            border-radius: 50px;
            padding: 0.3rem 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }
        .act-badge.returned { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .act-badge.active   { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .act-badge.new      { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    </style>
</head>
<body>

<div class="container py-4 max-w-custom" style="max-width: 1100px;">

    <!-- Navbar Admin -->
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                <i class="bi bi-box-seam-fill fs-4"></i>
            </div>
            <h4 class="navbar-brand mb-0 fs-3">PinjamBareng</h4>
        </div>
        <a href="logout.php" class="btn btn-logout d-flex align-items-center text-decoration-none">
            <i class="bi bi-power me-2"></i> Keluar
        </a>
    </header>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <span class="admin-badge"><i class="bi bi-shield-lock-fill me-1"></i> Administrator</span>
        <h1 class="fw-bold mb-2">Pusat Kendali, <?= $nama; ?>.</h1>
        <p class="fs-6 fw-light opacity-75 m-0" style="max-width: 600px;">
            Pantau statistik, kelola inventaris barang, dan verifikasi setiap transaksi peminjaman di komunitas Anda dari satu tempat.
        </p>
    </div>

    <!-- Statistik Cepat (Quick Stats) -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-boxes"></i></div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $jml_barang; ?></h3>
                    <span class="text-muted small fw-medium">Total Barang</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-arrow-left-right"></i></div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $jml_transaksi; ?></h3>
                    <span class="text-muted small fw-medium">Total Transaksi</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $jml_menunggu; ?></h3>
                    <span class="text-muted small fw-medium">Butuh Verifikasi</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-box-arrow-up-right"></i></div>
                <div>
                    <h3 class="fw-bold mb-0"><?= $jml_dipinjam; ?></h3>
                    <span class="text-muted small fw-medium">Sedang Dipinjam</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Control Grid -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-grid-fill text-primary me-2"></i> Menu Manajemen</h5>
    <div class="row g-4">
        
        <!-- Menu 1: Kelola Barang -->
        <div class="col-lg-4 col-md-6">
            <a href="barang.php" class="menu-card h-100">
                <i class="bi bi-journal-plus menu-icon-top"></i>
                <h5 class="menu-title">Inventaris Barang</h5>
                <p class="menu-desc">Tambah, edit, atau hapus barang dari katalog sistem PinjamBareng.</p>
                <div class="mt-3 text-primary small fw-semibold">Kelola Inventaris <i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Menu 2: Verifikasi & Riwayat -->
        <div class="col-lg-4 col-md-6">
            <a href="riwayat.php" class="menu-card h-100 border-primary border-opacity-25" style="background-color: #f8faff;">
                <!-- Tambahan Notifikasi Merah jika ada yang menunggu -->
                <?php if($jml_menunggu > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></span>
                <?php endif; ?>
                
                <i class="bi bi-clipboard-check menu-icon-top"></i>
                <h5 class="menu-title">Verifikasi Transaksi</h5>
                <p class="menu-desc">Pantau siapa yang meminjam dan konfirmasi barang yang telah dikembalikan.</p>
                <div class="mt-3 text-primary small fw-semibold">Cek Riwayat <i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

        <!-- Menu 3: Manajemen Pengguna -->
        <div class="col-lg-4 col-md-6">
            <a href="kelola_pengguna.php" class="menu-card h-100">
                <i class="bi bi-people menu-icon-top text-primary" style="background: linear-gradient(135deg, #3b82f6, #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h5 class="menu-title">Kelola Pengguna</h5>
                <p class="menu-desc">Lihat daftar akun terdaftar, atur hak akses, atau blokir pengguna bermasalah.</p>
                <div class="mt-3 text-primary small fw-semibold">Kelola User <i class="bi bi-arrow-right"></i></div>
            </a>
        </div>

    </div>

    <!-- Aktivitas Terkini Admin (Dinamis Gabungan Peminjaman & Barang) -->
    <!-- Aktivitas Terkini Admin (Dinamis Gabungan Peminjaman & Barang) -->
   <div class="admin-activity-wrap">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h6 class="text-dark fw-bold mb-0"><i class="bi bi-activity text-primary me-2"></i>Aktivitas Sistem Terkini</h6>
        </div>
        
        <?php
        $log_aktivitas = [];

        // 1. Tarik Data Transaksi Peminjaman
        $q_pinjam = mysqli_query($conn, "SELECT p.id, p.status, u.nama, b.nama_barang, p.created_at,
                                         (SELECT COUNT(*) FROM peminjaman p2 WHERE p2.id_user = p.id_user AND p2.id <= p.id) AS urutan_tr 
                                         FROM peminjaman p 
                                         JOIN users u ON p.id_user = u.id 
                                         JOIN barang b ON p.id_barang = b.id 
                                         ORDER BY p.id DESC LIMIT 5");
        if($q_pinjam) {
            while($act = mysqli_fetch_assoc($q_pinjam)) {
                $log_aktivitas[] = [
                    'jenis' => 'transaksi',
                    'urutan'=> $act['urutan_tr'],
                    'user'  => ucwords(strtolower($act['nama'])),
                    'barang'=> $act['nama_barang'],
                    'status'=> strtolower($act['status']),
                    'waktu' => strtotime($act['created_at'])
                ];
            }
        }

        // 2. Tarik Data Log Admin (Tambah, Edit, Hapus)
        $q_log = mysqli_query($conn, "SELECT aksi, nama_barang, waktu FROM log_barang ORDER BY id DESC LIMIT 5");
        if($q_log) {
            while($log = mysqli_fetch_assoc($q_log)) {
                $log_aktivitas[] = [
                    'jenis' => 'log_barang',
                    'aksi'  => $log['aksi'],
                    'barang'=> $log['nama_barang'],
                    'waktu' => strtotime($log['waktu'])
                ];
            }
        }

        // 3. Urutkan Waktu (Asli sesuai jam kejadian)
        usort($log_aktivitas, function($a, $b) { return $b['waktu'] - $a['waktu']; });
        $log_aktivitas = array_slice($log_aktivitas, 0, 5);

        // TAMPILKAN KE HTML
        if(count($log_aktivitas) > 0) {
            foreach($log_aktivitas as $akt) {
                $nama_brg = htmlspecialchars($akt['barang']);
                
                // JIKA AKTIVITAS LOG BARANG (ADMIN)
                if($akt['jenis'] == 'log_barang') {
                    $aksi = $akt['aksi'];
                    if($aksi == 'tambah') {
                        $judul = "<strong>Admin</strong> menambahkan barang: <strong>$nama_brg</strong>";
                        $warna_titik = 'blue'; $badge_class = 'new'; $teks_badge = 'Baru';
                    } elseif($aksi == 'edit') {
                        $judul = "<strong>Admin</strong> mengupdate data: <strong>$nama_brg</strong>";
                        $warna_titik = 'amber'; $badge_class = 'active'; $teks_badge = 'Update';
                    } elseif($aksi == 'hapus') {
                        $judul = "<strong>Admin</strong> menghapus barang: <strong>$nama_brg</strong>";
                        $warna_titik = 'red'; $badge_class = 'danger'; $teks_badge = 'Hapus';
                    }
                    
                    echo '
                    <div class="act-item">
                        <div class="act-dot '.$warna_titik.'"></div>
                        <div class="act-info">
                            <div class="act-title">'.$judul.'</div>
                        </div>
                        <span class="act-badge '.$badge_class.'">'.$teks_badge.'</span>
                    </div>';
                } 
                // JIKA AKTIVITAS TRANSAKSI
                else {
                    $nama_user  = htmlspecialchars($akt['user']);
                    $status_act = $akt['status'];
                    $id_teks    = "TR-" . $akt['urutan'];
                    
                    if($status_act == 'menunggu') {
                        $judul = "<strong>$nama_user</strong> mengajukan peminjaman <strong>$nama_brg</strong>";
                        $sub   = "Menunggu Verifikasi"; $warna_titik = 'blue'; $badge_class = 'new'; $teks_badge = 'Baru';
                    } elseif($status_act == 'dipinjam' || $status_act == 'aktif') {
                        $judul = "<strong>$nama_user</strong> sedang meminjam <strong>$nama_brg</strong>";
                        $sub   = "Sedang Dipinjam"; $warna_titik = 'amber'; $badge_class = 'active'; $teks_badge = 'Aktif';
                    } elseif($status_act == 'menunggu_kembali') {
                        $judul = "<strong>$nama_user</strong> mengajukan pengembalian <strong>$nama_brg</strong>";
                        $sub   = "Pengecekan Admin"; $warna_titik = 'amber'; $badge_class = 'active'; $teks_badge = 'Cek';
                    } elseif($status_act == 'dikembalikan') {
                        $judul = "<strong>$nama_user</strong> telah mengembalikan <strong>$nama_brg</strong>";
                        $sub   = "Selesai"; $warna_titik = 'green'; $badge_class = 'returned'; $teks_badge = 'Selesai';
                    } else {
                        $judul = "<strong>$nama_user</strong> melakukan transaksi <strong>$nama_brg</strong>";
                        $sub   = "Info"; $warna_titik = 'blue'; $badge_class = 'new'; $teks_badge = 'Info';
                    }

                    echo '
                    <div class="act-item">
                        <div class="act-dot '.$warna_titik.'"></div>
                        <div class="act-info">
                            <div class="act-title">'.$judul.'</div>
                            <div class="act-time">ID: #'.$id_teks.' &bull; '.$sub.'</div>
                        </div>
                        <span class="act-badge '.$badge_class.'">'.$teks_badge.'</span>
                    </div>';
                }
            }
        } else {
            echo '<div class="text-muted text-center py-3 small">Belum ada log aktivitas terekam di sistem.</div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
