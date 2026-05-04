<?php
session_start();
include 'koneksi.php';

// Proteksi (harus login)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$role    = $_SESSION['role'];

// Logika Pemisahan Query
if ($role == 'admin') {
    $query_sql = "SELECT peminjaman.*, users.nama AS peminjam_nama 
                  FROM peminjaman 
                  LEFT JOIN users ON peminjaman.id_user = users.id 
                  ORDER BY peminjaman.id DESC";
} else {
    $id_user_safe = mysqli_real_escape_string($conn, $id_user);
    $query_sql = "SELECT * FROM peminjaman WHERE id_user = '$id_user_safe' ORDER BY id DESC";
}

$peminjaman = mysqli_query($conn, $query_sql);

// --- LOGIKA MENGHITUNG TR PER USER ---
$user_totals = [];
$q_totals = mysqli_query($conn, "SELECT id_user, COUNT(*) as total FROM peminjaman GROUP BY id_user");
while($rt = mysqli_fetch_assoc($q_totals)) {
    $user_totals[$rt['id_user']] = $rt['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .history-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .history-header {
            background-color: #fdfdfd;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }

        .history-body { padding: 1.5rem; }

        .history-footer {
            background-color: #fdfdfd;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }

        .table-detail { margin-bottom: 0; }
        .table-detail th {
            font-size: 0.85rem; color: #6c757d; text-transform: uppercase;
            border-bottom: 2px solid #f1f3f5; padding-left: 0;
        }
        .table-detail td {
            font-size: 0.95rem; vertical-align: middle;
            border-bottom: 1px solid #f1f3f5; padding-left: 0;
        }
        .table-detail tr:last-child td { border-bottom: none; }

        /* Styling Badge Status untuk 3 Tahapan */
        .badge-status { padding: 0.5em 1em; font-weight: 500; border-radius: 8px; }
        .badge-dipinjam { background-color: rgba(253, 126, 20, 0.1); color: #fd7e14; }
        .badge-menunggu { background-color: rgba(255, 193, 7, 0.15); color: #b07d00; }
        .badge-dikembalikan { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    </style>
</head>
<body>

<div class="container py-5 max-w-custom" style="max-width: 900px;">

    <!-- Notifikasi Sukses/Gagal -->
    <?php if(isset($_GET['pesan'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['pesan']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0">Riwayat Peminjaman</h3>
            <p class="text-muted small mb-0">
                <?= ($role == 'admin') ? "Pantau dan konfirmasi pengembalian barang." : "Daftar transaksi dan status pengembalian barang Anda."; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="barang.php" class="btn btn-light border-0 shadow-sm rounded-pill px-3">
                <i class="bi bi-box-seam me-1"></i> Daftar Barang
            </a>
            <a href="<?= ($role == 'admin') ? 'dashboard_admin.php' : 'dashboard.php'; ?>" class="btn btn-primary shadow-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if($peminjaman && mysqli_num_rows($peminjaman) == 0): ?>
        <div class="text-center py-5 text-muted bg-white rounded-4 shadow-sm border border-light">
            <i class="bi bi-receipt fs-1 d-block mb-3 text-secondary"></i>
            <h5>Belum Ada Riwayat</h5>
        </div>
    <?php endif; ?>

    <?php 
    if($peminjaman): 
        while($p = mysqli_fetch_assoc($peminjaman)): 
            $status_saat_ini = strtolower($p['status']);
            
            // Generate Nomor TR Dinamis per User
            $u_id = $p['id_user'];
            $tr_number = $user_totals[$u_id];
            $user_totals[$u_id]--; // Hitung mundur untuk transaksi yang lebih lama
    ?>
        <div class="history-card">
            
            <div class="history-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fw-bold text-primary d-flex align-items-center gap-2">
                    <!-- TAMPILAN TR SEKARANG MENGGUNAKAN VARIABEL $tr_number -->
                    <span><i class="bi bi-hash"></i> TR-<?= $tr_number; ?></span>
                    
                    <!-- Khusus Admin: Tampilkan nama si peminjam -->
                    <?php if($role == 'admin' && isset($p['peminjam_nama'])): ?>
                        <span class="badge bg-light text-dark border ms-2 fw-normal">
                            <i class="bi bi-person text-secondary me-1"></i> <?= htmlspecialchars($p['peminjam_nama']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- LABEL STATUS -->
                <div>
                    <?php if($status_saat_ini == 'dipinjam' || $status_saat_ini == 'disetujui'): ?>
                        <span class="badge badge-status badge-dipinjam">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Sedang Dipinjam
                        </span>
                    <?php elseif($status_saat_ini == 'menunggu'): ?>
                        <span class="badge badge-status badge-menunggu">
                            <i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi Admin
                        </span>
                    <?php else: ?>
                        <span class="badge badge-status badge-dikembalikan">
                            <i class="bi bi-check2-all me-1"></i> Sudah Dikembalikan
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="history-body">
                <div class="row mb-4 bg-light rounded-3 p-3">
                    <div class="col-sm-6 mb-2 mb-sm-0">
                        <small class="text-muted d-block"><i class="bi bi-calendar-arrow-up me-1"></i> Tanggal Pinjam</small>
                        <span class="fw-medium"><?= htmlspecialchars($p['tgl_pinjam']); ?></span>
                    </div>
                    <div class="col-sm-6 border-sm-start">
                        <small class="text-muted d-block"><i class="bi bi-calendar-arrow-down me-1"></i> Tanggal Kembali</small>
                        <span class="fw-medium"><?= htmlspecialchars($p['tgl_kembali']); ?></span>
                    </div>
                </div>

                <!-- Info Tambahan Peminjam (Metode Ambil & Keperluan) -->
                <?php if(!empty($p['metode_ambil']) || !empty($p['keperluan'])): ?>
                <div class="mb-4">
                    <?php if(!empty($p['metode_ambil'])): ?>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle me-2">
                            <i class="bi bi-truck me-1"></i> <?= ucwords(str_replace('_', ' ', htmlspecialchars($p['metode_ambil']))); ?>
                        </span>
                    <?php endif; ?>
                    <?php if(!empty($p['keperluan'])): ?>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle">
                            <i class="bi bi-card-text me-1"></i> <?= htmlspecialchars($p['keperluan']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            
                <div class="table-responsive px-2">
                    <table class="table table-detail mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 border-0 text-muted" style="font-size: 0.8rem;">NAMA BARANG</th>
                                <th width="25%" class="text-center border-0 text-muted" style="font-size: 0.8rem;">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $id_barang = mysqli_real_escape_string($conn, $p['id_barang']);
                            $detail = mysqli_query($conn, "SELECT nama_barang FROM barang WHERE id = '$id_barang'");
                            $d = mysqli_fetch_assoc($detail);
                            $nama_brg = $d ? htmlspecialchars($d['nama_barang']) : 'Barang Dihapus';
                            ?>
                            <tr>
                                <td class="fw-medium text-dark ps-3 py-3 border-bottom border-light">
                                    <i class="bi bi-box2 text-muted me-2"></i> <?= $nama_brg; ?>
                                </td>
                                <td class="text-center py-3 border-bottom border-light">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 border border-primary-subtle">
                                        <?= htmlspecialchars($p['jumlah']); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
                
            <!-- AREA TOMBOL AKSI BAWAH -->
            <div class="history-footer text-end">
                
                <?php if($status_saat_ini == 'dipinjam' || $status_saat_ini == 'disetujui'): ?>
                    
                    <?php if($role == 'user'): ?>
                        <a href="proses_kembalikan.php?id=<?= $p['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-medium" onclick="return confirm('Apakah Anda sudah menyerahkan barang ini ke Admin?')">
                            <i class="bi bi-arrow-return-left me-1"></i> Saya Sudah Kembalikan
                        </a>
                    <?php else: ?>
                        <span class="text-muted small fw-medium"><i class="bi bi-person-gear me-1"></i> Menunggu user mengembalikan barang</span>
                    <?php endif; ?>

                <?php elseif($status_saat_ini == 'menunggu'): ?>
                    
                    <?php if($role == 'admin'): ?>
                        <a href="proses_konfirmasi.php?id=<?= $p['id']; ?>" class="btn btn-success btn-sm rounded-pill px-4 fw-medium shadow-sm" onclick="return confirm('Setujui peminjaman ini?')">
                            <i class="bi bi-check-circle me-1"></i> Konfirmasi Peminjaman
                        </a>
                        <a href="proses_tolak.php?id=<?= $p['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-3 fw-medium shadow-sm" onclick="return confirm('Tolak peminjaman ini?')">
                            <i class="bi bi-x-circle me-1"></i> Tolak
                        </a>
                    <?php else: ?>
                        <span class="text-warning small fw-medium"><i class="bi bi-clock-history me-1"></i> Sedang diverifikasi oleh Admin</span>
                    <?php endif; ?>

                <?php elseif($status_saat_ini == 'ditolak'): ?>
                    <span class="text-danger small fw-medium">
                        <i class="bi bi-x-circle-fill me-1"></i> Peminjaman Ditolak Admin
                    </span>
                <?php else: ?>
                    <span class="text-success small fw-medium">
                        <i class="bi bi-check-circle-fill me-1"></i> Sudah Dikembalikan
                    </span>
                <?php endif; ?>
                
            </div>

        </div>
    <?php 
        endwhile; 
    endif; 
    ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>