<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];
$role = $_SESSION['role'];

// --- LOGIKA SETUJUI PEMINJAMAN AWAL (KHUSUS ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setujui_pinjam']) && $role == 'admin') {
    $id_pinjam = $_POST['id_pinjam'];
    mysqli_query($conn, "UPDATE peminjaman SET status = 'dipinjam' WHERE id = '$id_pinjam'");
    header("Location: riwayat.php?pesan=" . urlencode("Peminjaman berhasil disetujui!"));
    exit;
}

// --- LOGIKA USER KLIK "SUDAH DIKEMBALIKAN" ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajukan_kembali']) && $role == 'user') {
    $id_pinjam = $_POST['id_pinjam'];
    mysqli_query($conn, "UPDATE peminjaman SET status = 'menunggu_kembali' WHERE id = '$id_pinjam'");
    header("Location: riwayat.php?pesan=" . urlencode("Mantap! Menunggu admin mengecek kondisi barang Anda."));
    exit;
}

// --- LOGIKA ADMIN CEK KONDISI & TERIMA BARANG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kembalikan_barang']) && $role == 'admin') {
    $id_pinjam = $_POST['id_pinjam'];
    $id_barang = $_POST['id_barang'];
    $jumlah = (int)$_POST['jumlah'];
    $kondisi_kembali = $_POST['kondisi_kembali'];

    mysqli_query($conn, "UPDATE peminjaman SET status = 'dikembalikan', kondisi_kembali = '$kondisi_kembali' WHERE id = '$id_pinjam'");

    if ($kondisi_kembali == 'Baik') {
        mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah, status = 'Tersedia' WHERE id = '$id_barang'");
    } else {
        $cek_barang = mysqli_query($conn, "SELECT * FROM barang WHERE id = '$id_barang'");
        if ($data_brg = mysqli_fetch_assoc($cek_barang)) {
            $nama_brg   = mysqli_real_escape_string($conn, $data_brg['nama_barang']);
            $desc_brg   = mysqli_real_escape_string($conn, $data_brg['deskripsi']);
            $gambar_brg = isset($data_brg['gambar']) ? mysqli_real_escape_string($conn, $data_brg['gambar']) : '';

            $cek_rusak = mysqli_query($conn, "SELECT id FROM barang WHERE nama_barang = '$nama_brg' AND kondisi = 'Rusak'");
            if (mysqli_num_rows($cek_rusak) > 0) {
                $id_rusak = mysqli_fetch_assoc($cek_rusak)['id'];
                mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id = '$id_rusak'");
            } else {
                mysqli_query($conn, "INSERT INTO barang (nama_barang, deskripsi, kondisi, status, stok, gambar) VALUES ('$nama_brg', '$desc_brg', 'Rusak', 'Habis', '$jumlah', '$gambar_brg')");
            }
        }
    }
    header("Location: riwayat.php?pesan=" . urlencode("Sip! Barang dikonfirmasi dengan kondisi: " . $kondisi_kembali));
    exit;
}

// --- LOGIKA HAPUS RIWAYAT TRANSAKSI (SOFT DELETE KHUSUS ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_riwayat']) && $role == 'admin') {
    $id_pinjam = $_POST['id_pinjam'];
    mysqli_query($conn, "UPDATE peminjaman SET hapus_admin = 1 WHERE id = '$id_pinjam'");
    header("Location: riwayat.php?pesan=" . urlencode("Riwayat transaksi berhasil dibersihkan dari tampilan Admin!"));
    exit;
}
// -----------------------------------------------------

// --- UPDATE QUERY: MENGHITUNG URUTAN TRANSAKSI PER USER ---
if ($role == 'admin') {
    $query = "SELECT p.*, b.nama_barang, u.username,
                     (SELECT COUNT(*) FROM peminjaman p2 WHERE p2.id_user = p.id_user AND p2.id <= p.id) AS urutan_tr 
              FROM peminjaman p 
              JOIN barang b ON p.id_barang = b.id 
              JOIN users u ON p.id_user = u.id 
              WHERE p.hapus_admin = 0 
              ORDER BY p.id DESC";
} else {
    $query = "SELECT p.*, b.nama_barang, u.username,
                     (SELECT COUNT(*) FROM peminjaman p2 WHERE p2.id_user = p.id_user AND p2.id <= p.id) AS urutan_tr 
              FROM peminjaman p 
              JOIN barang b ON p.id_barang = b.id 
              JOIN users u ON p.id_user = u.id 
              WHERE p.id_user = '$id_user' 
              ORDER BY p.id DESC";
}

$result = mysqli_query($conn, $query);
$pesan_notif = isset($_GET['pesan']) ? htmlspecialchars($_GET['pesan']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - PinjamBareng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; padding-bottom: 3rem; }
        .page-header { max-width: 900px; margin: 3rem auto 2rem; display: flex; justify-content: space-between; align-items: center; }
        .page-title h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin: 0; }
        .page-title p { font-size: 0.9rem; color: #64748b; margin: 0; }
        .header-btns { display: flex; gap: 10px; }
        .btn-outline { border: 1px solid #e2e8f0; background: white; color: #0f172a; padding: 0.5rem 1rem; border-radius: 50px; font-weight: 500; font-size: 0.9rem; text-decoration: none; }
        .btn-primary-custom { background: #0d6efd; color: white; padding: 0.5rem 1rem; border-radius: 50px; font-weight: 500; font-size: 0.9rem; text-decoration: none; transition: all 0.2s; }
        .btn-primary-custom:hover { background: #0b5ed7; color: white; }
        
        .history-card { max-width: 900px; margin: 0 auto 1.5rem; background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .hc-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .hc-id { font-weight: 700; color: #0d6efd; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; }
        .hc-user { background: #f8fafc; border: 1px solid #e2e8f0; padding: 3px 10px; border-radius: 50px; font-size: 0.75rem; color: #475569; font-weight: 500; }
        .hc-telp { font-size: 0.8rem; color: #64748b; font-weight: 400; margin-left: 5px; }
        
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .status-dikembalikan { background: #d1fae5; color: #059669; }
        .status-dipinjam { background: #fef3c7; color: #d97706; }
        .status-menunggu { background: #e0f2fe; color: #0284c7; }
        .status-mengecek { background: #f3e8ff; color: #9333ea; }
        
        .hc-body { padding: 1.5rem; }
        .date-box { background: #f8fafc; border-radius: 12px; padding: 1rem 1.5rem; display: flex; gap: 4rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .db-item span { display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 4px; }
        .db-item strong { display: block; font-size: 0.95rem; color: #0f172a; font-weight: 600; }
        
        .tag-list { display: flex; gap: 8px; margin-bottom: 1.5rem; }
        .tag { border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 5px; }
        .tag-blue { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        
        .item-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem; }
        .item-name { font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .item-qty { border: 1px solid #bfdbfe; color: #2563eb; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.8rem; font-weight: 600; }
        
        .hc-footer { padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; align-items: center; }
        
        .admin-action-box { display: flex; align-items: center; gap: 15px; }
        .form-select-sm { width: auto; border-radius: 8px; font-size: 0.85rem; }
        .btn-konfirmasi { background: #10b981; color: white; border: none; padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; }
        .btn-konfirmasi:hover { background: #059669; }
        .btn-setujui { background: #0d6efd; color: white; border: none; padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-setujui:hover { background: #0b5ed7; }
        
        .info-kondisi-balik { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }
        .kondisi-msg { padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .msg-baik { background: #d1fae5; color: #059669; }
        .msg-rusak { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

        .btn-delete { border: 1px solid #fecaca; color: #dc2626; background: white; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-delete:hover { background: #fee2e2; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Riwayat Peminjaman</h1>
            <p>Pantau dan konfirmasi pengembalian barang.</p>
        </div>
        <div class="header-btns">
            <a href="barang.php" class="btn-outline"><i class="bi bi-box-seam me-1"></i> Daftar Barang</a>
            <a href="<?= ($role == 'admin') ? 'dashboard_admin.php' : 'dashboard.php'; ?>" class="btn-primary-custom"><i class="bi bi-house-door me-1"></i> Dashboard</a>
        </div>
    </div>

    <?php if($pesan_notif): ?>
        <div class="alert alert-success" style="max-width: 900px; margin: 0 auto 1.5rem; border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_notif; ?>
        </div>
    <?php endif; ?>

    <?php 
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $status = strtolower($row['status']);
            $badge_class = 'status-menunggu';
            $badge_icon = 'bi-clock-history';
            $badge_text = 'Menunggu Persetujuan';

            if ($status == 'dikembalikan') {
                $badge_class = 'status-dikembalikan'; $badge_icon = 'bi-check-circle-fill'; $badge_text = 'Sudah Dikembalikan';
            } elseif ($status == 'dipinjam' || $status == 'aktif') {
                $badge_class = 'status-dipinjam'; $badge_icon = 'bi-arrow-repeat'; $badge_text = 'Sedang Dipinjam';
            } elseif ($status == 'menunggu_kembali') {
                $badge_class = 'status-mengecek'; $badge_icon = 'bi-box-seam'; $badge_text = 'Pengecekan Admin';
            }
    ?>
    <div class="history-card">
        <div class="hc-header">
            <div class="hc-id">
                <!-- MENGGUNAKAN URUTAN TRANSAKSI KHUSUS PER USER -->
                # TR-<?= $row['urutan_tr']; ?> 
                <span class="hc-user"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($row['username']); ?></span>
                
                <?php if($role == 'admin' && !empty($row['no_telp'])): ?>
                    <a href="https://wa.me/<?= $row['no_telp']; ?>" target="_blank" class="hc-telp text-decoration-none" title="Hubungi WhatsApp">
                        <i class="bi bi-whatsapp text-success"></i> <?= htmlspecialchars($row['no_telp']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="status-badge <?= $badge_class; ?>">
                <i class="bi <?= $badge_icon; ?>"></i> <?= $badge_text; ?>
            </div>
        </div>

        <div class="hc-body">
            <div class="date-box">
                <div class="db-item">
                    <span>Tanggal Pinjam</span>
                    <strong><?= $row['tgl_pinjam']; ?></strong>
                </div>
                <div class="db-item">
                    <span>Tanggal Kembali</span>
                    <strong><?= $row['tgl_kembali']; ?></strong>
                </div>
                
                <?php if($role == 'admin'): ?>
                <div class="db-item" style="margin-left: auto;">
                    <span>Atas Nama (Form)</span>
                    <strong class="text-primary"><?= htmlspecialchars($row['nama_peminjam']); ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <div class="tag-list">
                <div class="tag"><i class="bi bi-truck"></i> <?= str_replace('_', ' ', ucwords($row['metode_ambil'])); ?></div>
                <?php if(!empty($row['keperluan'])): ?>
                    <div class="tag tag-blue"><i class="bi bi-card-text"></i> <?= htmlspecialchars($row['keperluan']); ?></div>
                <?php endif; ?>
            </div>

            <div class="item-row">
                <div class="item-name"><i class="bi bi-box2 text-secondary"></i> <?= htmlspecialchars($row['nama_barang']); ?></div>
                <div class="item-qty" title="Jumlah dipinjam"><?= $row['jumlah']; ?></div>
            </div>
        </div>

        <div class="hc-footer">
            <?php if ($status == 'dikembalikan'): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div class="info-kondisi-balik">
                        <?php if (!empty($row['kondisi_kembali'])): ?>
                            <?php if ($row['kondisi_kembali'] == 'Rusak'): ?>
                                <div class="kondisi-msg msg-rusak">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Barang dikembalikan rusak
                                </div>
                            <?php else: ?>
                                <div class="kondisi-msg msg-baik">
                                    <i class="bi bi-check-circle-fill"></i> Barang dikembalikan baik
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size: 0.8rem; font-weight: 500; margin-top: 4px;">
                            <i class="bi bi-check2-all"></i> Transaksi Selesai
                        </div>
                    </div>

                    <?php if ($role == 'admin'): ?>
                    <form action="" method="POST" style="margin: 0;">
                        <input type="hidden" name="hapus_riwayat" value="1">
                        <!-- ID asli database tetep dipakai buat proses update/hapus -->
                        <input type="hidden" name="id_pinjam" value="<?= $row['id']; ?>">
                        <button type="submit" class="btn-delete" onclick="return confirm('Sembunyikan riwayat transaksi ini dari tampilan Admin?')">
                            <i class="bi bi-trash"></i> Bersihkan
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            
            <?php elseif ($status == 'menunggu'): ?>
                <?php if ($role == 'admin'): ?>
                    <form action="" method="POST" style="margin: 0;">
                        <input type="hidden" name="setujui_pinjam" value="1">
                        <input type="hidden" name="id_pinjam" value="<?= $row['id']; ?>">
                        <button type="submit" class="btn-setujui" onclick="return confirm('Setujui peminjaman barang ini?')">
                            <i class="bi bi-check2-circle"></i> Setujui Peminjaman
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-muted" style="font-size: 0.85rem;">
                        Menunggu persetujuan admin.
                    </div>
                <?php endif; ?>

            <?php elseif ($status == 'dipinjam' || $status == 'aktif'): ?>
                <?php if ($role == 'admin'): ?>
                    <div class="text-primary" style="font-size: 0.85rem; font-weight: 500;">
                        <i class="bi bi-info-circle"></i> Barang sedang digunakan oleh peminjam.
                    </div>
                <?php else: ?>
                    <form action="" method="POST" style="margin: 0; width: 100%; text-align: right;">
                        <input type="hidden" name="ajukan_kembali" value="1">
                        <input type="hidden" name="id_pinjam" value="<?= $row['id']; ?>">
                        <span class="text-muted me-3" style="font-size: 0.85rem;">Barang sudah selesai digunakan?</span>
                        <button type="submit" class="btn-primary-custom" onclick="return confirm('Serahkan kembali barang ke admin?')">
                            <i class="bi bi-box-arrow-in-right"></i> Sudah Dikembalikan
                        </button>
                    </form>
                <?php endif; ?>

            <?php elseif ($status == 'menunggu_kembali'): ?>
                <?php if ($role == 'admin'): ?>
                    <form action="" method="POST" class="admin-action-box">
                        <input type="hidden" name="kembalikan_barang" value="1">
                        <input type="hidden" name="id_pinjam" value="<?= $row['id']; ?>">
                        <input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
                        <input type="hidden" name="jumlah" value="<?= $row['jumlah']; ?>">
                        
                        <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Kondisi Balik:</span>
                        <select name="kondisi_kembali" class="form-select form-select-sm">
                            <option value="Baik">✅ Baik / Aman</option>
                            <option value="Rusak">❌ Rusak / Cacat</option>
                        </select>
                        <button type="submit" class="btn-konfirmasi" onclick="return confirm('Konfirmasi pengembalian barang ini?')">
                            Terima Barang
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-primary" style="font-size: 0.85rem; font-weight: 500;">
                        <i class="bi bi-hourglass-split"></i> Menunggu admin mengecek kondisi barang Anda.
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
    <?php 
        }
    } else {
        echo '<div class="text-center text-muted" style="padding: 4rem 0;">Belum ada riwayat transaksi.</div>';
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
