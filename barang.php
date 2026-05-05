<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];

// --- LOGIKA EDIT & UPLOAD GAMBAR BARU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_barang']) && $role == 'admin') {
    $id_edit   = mysqli_real_escape_string($conn, $_POST['id_barang']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $kond_baru = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $tambah    = (int)$_POST['jml_tambah'];

    $query_gambar = "";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $nama_file_gambar = uniqid() . '-' . time() . '.' . $file_ext;
            $upload_path = 'uploads/' . $nama_file_gambar;
            
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            move_uploaded_file($file_tmp, $upload_path);
            $query_gambar = ", gambar = '$nama_file_gambar'";
        }
    }

    $query_update = "UPDATE barang SET nama_barang = '$nama_baru', kondisi = '$kond_baru', stok = stok + $tambah $query_gambar WHERE id = '$id_edit'";
    
    if (mysqli_query($conn, $query_update)) {
        mysqli_query($conn, "UPDATE barang SET status = 'Tersedia' WHERE id = '$id_edit' AND stok > 0 AND kondisi != 'Rusak'");
        header("Location: barang.php?pesan=Sip! Data barang berhasil diperbarui.");
        exit;
    } else {
        $pesan = "Gagal memperbarui: " . mysqli_error($conn);
    }
}

$pesan = isset($_GET['pesan']) ? htmlspecialchars($_GET['pesan']) : (isset($pesan) ? $pesan : '');

// KEMBALIKAN QUERY NORMAL KE SEMULA (SEMUA BARANG TAMPIL)
$data = mysqli_query($conn, "SELECT * FROM barang ORDER BY id DESC");

$barang_list = [];
if($data){
    while($row = mysqli_fetch_assoc($data)){
        $barang_list[] = $row;
    }
}

$cart_qtys = [];
if ($role == 'user') {
    $id_user = $_SESSION['id'];
    $q_cart = mysqli_query($conn, "SELECT id_barang, jumlah FROM keranjang WHERE id_user = '$id_user'");
    if ($q_cart) {
        while ($c = mysqli_fetch_assoc($q_cart)) {
            $cart_qtys[$c['id_barang']] = (int)$c['jumlah'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink:        #0f1117;
            --ink-2:      #161b27;
            --ink-3:      #1c2235;
            --surface:    #1e2438;
            --border:     rgba(255,255,255,0.07);
            --border-md:  rgba(255,255,255,0.11);
            --muted:      #7a8099;
            --text:       #dde1f0;
            --gold:       #c9a84c;
            --gold-light: #e8c96a;
            --blue:       #2563eb;
            --blue-soft:  rgba(37,99,235,0.15);
            --green:      #059669;
            --green-soft: rgba(5,150,105,0.12);
            --red-soft:   rgba(220,38,38,0.12);
            --radius:     16px;
            --radius-sm:  10px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body { font-family: 'DM Sans', sans-serif; background: var(--ink); color: var(--text); min-height: 100vh; }

        .topbar { position: sticky; top: 0; z-index: 100; background: rgba(15,17,23,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 0 2rem; height: 62px; display: flex; align-items: center; justify-content: space-between; }
        .topbar-left { display: flex; align-items: center; gap: 14px; }
        .topbar-logo { width: 36px; height: 36px; background: var(--blue); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; flex-shrink: 0; }
        .topbar-brand { font-family: 'DM Serif Display', serif; font-size: 1.15rem; color: white; text-decoration: none; }
        .topbar-sep { width: 1px; height: 20px; background: var(--border-md); }
        .topbar-page { font-size: 0.82rem; color: var(--muted); }

        .topbar-right { display: flex; align-items: center; gap: 8px; }
        .btn-nav { display: flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 500; cursor: pointer; text-decoration: none; transition: all 0.2s ease; border: none; }
        .btn-ghost { background: rgba(255,255,255,0.05); border: 1px solid var(--border-md); color: var(--text); }
        .btn-ghost:hover { background: rgba(255,255,255,0.09); color: white; }
        .btn-primary-nav { background: var(--blue); color: white; }
        .btn-primary-nav:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.3); }
        .btn-gold { background: var(--gold); color: #0f1117; font-weight: 700; }
        .btn-gold:hover { background: var(--gold-light); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(201,168,76,0.3); }

        .main-wrap { max-width: 1100px; margin: 0 auto; padding: 2.5rem 1.5rem 4rem; }

        .page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 2rem; gap: 1rem; animation: fadeUp 0.4s ease both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
        .ph-left .gold-bar { width: 32px; height: 3px; background: var(--gold); border-radius: 2px; margin-bottom: 0.6rem; }
        .ph-left h1 { font-family: 'DM Serif Display', serif; font-size: 2rem; color: white; letter-spacing: -0.5px; line-height: 1; }
        .ph-left p { font-size: 0.85rem; color: var(--muted); margin-top: 0.3rem; }

        .stats-row { display: flex; gap: 8px; margin-top: 1rem; }
        .stat-pill { display: flex; align-items: center; gap: 6px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.78rem; color: var(--muted); }
        .stat-pill b { color: var(--text); font-weight: 600; }
        .stat-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; }
        .stat-pill .dot.amber { background: var(--gold); }

        .search-wrap { position: relative; margin-bottom: 1.25rem; animation: fadeUp 0.4s ease 0.05s both; }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.95rem; pointer-events: none; }
        .search-input { width: 100%; max-width: 340px; background: var(--ink-2); border: 1px solid var(--border-md); border-radius: var(--radius-sm); padding: 0.6rem 1rem 0.6rem 2.4rem; font-family: 'DM Sans', sans-serif; font-size: 0.875rem; color: var(--text); outline: none; transition: all 0.2s; }
        .search-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }

        .table-card { background: var(--ink-2); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; animation: fadeUp 0.4s ease 0.1s both; }
        .table-card table { width: 100%; border-collapse: collapse; }
        thead tr { border-bottom: 1px solid var(--border-md); }
        thead th { padding: 0.85rem 1.25rem; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: var(--muted); white-space: nowrap; }
        thead th:first-child { padding-left: 1.5rem; }
        thead th:last-child { padding-right: 1.5rem; text-align: center; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,0.025); }
        tbody td { padding: 1rem 1.25rem; font-size: 0.875rem; vertical-align: middle; }
        tbody td:first-child { padding-left: 1.5rem; }
        tbody td:last-child { padding-right: 1.5rem; }

        .td-no { color: var(--muted); font-size: 0.8rem; font-variant-numeric: tabular-nums; }
        .item-cell { display: flex; align-items: center; gap: 12px; }
        .item-avatar { width: 44px; height: 44px; border-radius: 10px; background: var(--surface); border: 1px solid var(--border-md); display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 1.2rem; flex-shrink: 0; overflow: hidden; }
        .item-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
        .item-name-text { font-weight: 600; color: #e8eaf6; font-size: 0.9rem; }
        .td-desc { color: var(--muted); font-size: 0.82rem; max-width: 200px; }

        .kondisi-pill { display: inline-flex; align-items: center; gap: 5px; padding: 0.28rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
        .kondisi-baik { background: rgba(5,150,105,0.12); color: #34d399; border: 1px solid rgba(52,211,153,0.2); }
        .kondisi-baru { background: rgba(37,99,235,0.12); color: #93c5fd; border: 1px solid rgba(147,197,253,0.2); }
        .kondisi-rusak{ background: rgba(220,38,38,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }

        .status-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
        .badge-tersedia { display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.7rem; background: var(--green-soft); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); border-radius: 8px; font-size: 0.73rem; font-weight: 700; letter-spacing: 0.3px; }
        .badge-tersedia::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #4ade80; }
        .badge-habis { display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.7rem; background: var(--red-soft); color: #f87171; border: 1px solid rgba(248,113,113,0.2); border-radius: 8px; font-size: 0.73rem; font-weight: 700; }
        .badge-rusak { display: inline-flex; align-items: center; gap: 4px; padding: 0.25rem 0.7rem; background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); border-radius: 8px; font-size: 0.73rem; font-weight: 700; }
        
        .stok-text { font-size: 0.75rem; color: var(--muted); }

        .action-cell { display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-act { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; border: 1px solid var(--border-md); background: rgba(255,255,255,0.04); color: var(--muted); cursor: pointer; text-decoration: none; transition: all 0.18s ease; }
        .btn-act:hover { transform: translateY(-1px); }
        .btn-act-cart:hover { background: var(--blue-soft); border-color: rgba(37,99,235,0.4); color: #93c5fd; }
        .btn-act-edit:hover { background: var(--gold); border-color: var(--gold-light); color: #0f1117; }
        .btn-act-del:hover { background: var(--red-soft); border-color: rgba(248,113,113,0.3); color: #f87171; }
        .btn-act[disabled] { opacity: 0.3; cursor: not-allowed; pointer-events: none; }

        .empty-state { padding: 4rem 2rem; text-align: center; color: var(--muted); }
        .empty-icon { width: 60px; height: 60px; border-radius: 16px; background: var(--surface); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--muted); margin: 0 auto 1rem; }
        .empty-state p { font-size: 0.875rem; }
        .notif-bar { display: flex; align-items: center; gap: 10px; background: rgba(5,150,105,0.1); border: 1px solid rgba(74,222,128,0.2); border-radius: var(--radius-sm); padding: 0.75rem 1.1rem; font-size: 0.85rem; color: #4ade80; margin-bottom: 1.25rem; animation: fadeUp 0.3s ease both; }

        .modal-content { background: var(--ink-2) !important; border: 1px solid var(--border-md) !important; border-radius: var(--radius) !important; color: var(--text) !important; }
        .modal-header { border-bottom: 1px solid var(--border) !important; padding: 1.25rem 1.5rem !important; }
        .modal-title { font-family: 'DM Serif Display', serif !important; font-size: 1.1rem !important; color: white !important; font-weight: 400 !important; }
        .modal-body { padding: 1.25rem 1.5rem !important; }
        .modal-footer { border-top: 1px solid var(--border) !important; padding: 1rem 1.5rem !important; }
        .btn-close { filter: invert(1) brightness(0.6) !important; }
        .modal-label { font-size: 0.75rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.4rem; }
        .modal-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-md); border-radius: 10px; padding: 0.65rem 1rem; font-family: 'DM Sans', sans-serif; font-size: 0.875rem; color: var(--text); outline: none; transition: all 0.2s; }
        .modal-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        select.modal-input option { background: var(--ink-2); color: white; }
        .modal-info-box { background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--muted); }
        .modal-info-box b { color: var(--text); }
        .btn-modal-cancel { background: rgba(255,255,255,0.05); border: 1px solid var(--border-md); color: var(--muted); border-radius: 10px; padding: 0.5rem 1.1rem; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
        .btn-modal-cancel:hover { color: white; }
        .btn-modal-save { background: var(--green); border: none; color: white; border-radius: 10px; padding: 0.5rem 1.4rem; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-modal-save:hover { background: #047857; }
    </style>
</head>
<body>

<nav class="topbar">
    <div class="topbar-left">
        <div class="topbar-logo"><i class="bi bi-box-seam"></i></div>
        <a href="<?= ($role=='admin') ? 'dashboard_admin.php' : 'dashboard.php' ?>" class="topbar-brand">PinjamBareng</a>
        <div class="topbar-sep"></div>
        <span class="topbar-page">Daftar Barang</span>
    </div>

    <div class="topbar-right">
        <a href="<?= ($role=='admin') ? 'dashboard_admin.php' : 'dashboard.php' ?>" class="btn-nav btn-ghost">
            <i class="bi bi-arrow-left" style="font-size:0.8rem;"></i> Kembali
        </a>
        <?php if($role == 'user'): ?>
        <a href="keranjang_view.php" class="btn-nav btn-gold">
            <i class="bi bi-cart3"></i> Keranjang
        </a>
        <?php endif; ?>
        <?php if($role == 'admin'): ?>
        <a href="tambah_barang.php" class="btn-nav btn-primary-nav">
            <i class="bi bi-plus-lg"></i> Tambah Barang
        </a>
        <?php endif; ?>
    </div>
</nav>

<div class="main-wrap">

    <?php if ($pesan): ?>
    <div class="notif-bar">
        <i class="bi bi-check-circle-fill"></i> <?= $pesan ?>
    </div>
    <?php endif; ?>

    <div class="page-header">
        <div class="ph-left">
            <div class="gold-bar"></div>
            <h1>Daftar Barang</h1>
            <p>Kelola dan pilih barang yang tersedia di sistem.</p>

            <?php
            $total_barang   = count($barang_list);
            $total_tersedia = 0;
            foreach ($barang_list as $b) {
                if(strtolower($b['status']) == 'tersedia' && $b['stok'] > 0 && strtolower($b['kondisi']) != 'rusak') {
                    $total_tersedia++;
                }
            }
            ?>
            <div class="stats-row">
                <div class="stat-pill">
                    <span class="dot amber"></span>
                    <b><?= $total_barang ?></b> Total Barang
                </div>
                <div class="stat-pill">
                    <span class="dot"></span>
                    <b><?= $total_tersedia ?></b> Tersedia
                </div>
                <div class="stat-pill" style="color:var(--muted);">
                    <i class="bi bi-person" style="font-size:0.8rem;"></i>
                    <?= ucfirst($role) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Cari nama barang...">
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="22%">Nama Barang</th>
                    <th width="25%">Deskripsi</th>
                    <th width="14%">Kondisi</th>
                    <th width="18%">Status / Stok</th>
                    <th width="17%">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php
            $no = 1;
            if(count($barang_list) > 0):
                foreach($barang_list as $d):
                    $id_barang   = $d['id'];
                    $nama_barang = htmlspecialchars($d['nama_barang']);
                    $deskripsi   = htmlspecialchars($d['deskripsi']) ?: '—';
                    $kondisi     = htmlspecialchars($d['kondisi']);
                    $status      = strtolower($d['status']);
                    $stok_asli   = (int)$d['stok'];
                    $gambar_file = isset($d['gambar']) ? $d['gambar'] : '';
                    
                    $jml_keranjang = isset($cart_qtys[$id_barang]) ? $cart_qtys[$id_barang] : 0;
                    $stok_tampil   = max(0, $stok_asli - $jml_keranjang); 

                    $tersedia = ($status == 'tersedia' && $stok_tampil > 0 && strtolower($kondisi) != 'rusak');

                    $kondisi_class = match(strtolower($kondisi)) {
                        'baik'  => 'kondisi-baik',
                        'baru'  => 'kondisi-baru',
                        'rusak' => 'kondisi-rusak',
                        default => 'kondisi-baik',
                    };
            ?>
            <tr class="table-row">
                <td class="td-no"><?= $no++ ?></td>
                <td>
                    <div class="item-cell">
                        <div class="item-avatar">
                            <?php if (!empty($gambar_file) && file_exists('uploads/' . $gambar_file)): ?>
                                <img src="uploads/<?= htmlspecialchars($gambar_file) ?>" alt="img">
                            <?php else: ?>
                                <i class="bi bi-box2"></i>
                            <?php endif; ?>
                        </div>
                        <span class="item-name-text"><?= $nama_barang ?></span>
                    </div>
                </td>
                <td class="td-desc"><?= $deskripsi ?></td>
                <td>
                    <span class="kondisi-pill <?= $kondisi_class ?>">
                        <?= $kondisi ?>
                    </span>
                </td>
                <td>
                    <div class="status-cell">
                        <?php if ($tersedia): ?>
                            <span class="badge-tersedia">Tersedia</span>
                            <span class="stok-text">Stok: <?= $stok_tampil ?></span>
                        <?php elseif (strtolower($kondisi) == 'rusak'): ?>
                            <span class="badge-rusak">&#x25CF; Sedang Rusak</span>
                            <span class="stok-text">Tidak bisa dipinjam</span>
                        <?php else: ?>
                            <span class="badge-habis">&#x25CF; Stok Habis</span>
                            <span class="stok-text">Stok: <?= $stok_tampil ?></span>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="action-cell">

                        <?php if ($role == 'user'): ?>
                            <?php if ($tersedia): ?>
                                <form action="keranjang.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="id_barang" value="<?= $id_barang ?>">
                                    <input type="hidden" name="jumlah" value="1">
                                    <button type="submit" class="btn-act btn-act-cart" title="Tambah ke Keranjang">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-act" disabled title="Stok Kosong / Rusak / Maksimal">
                                    <i class="bi bi-cart-x"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($role == 'admin'): ?>
                            <button type="button"
                                    class="btn-act btn-act-edit"
                                    title="Edit Barang & Stok"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit<?= $id_barang ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <a href="hapus_barang.php?id=<?= $id_barang ?>"
                               class="btn-act btn-act-del"
                               title="Hapus Barang"
                               onclick="return confirm('Hapus <?= $nama_barang ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>

                    </div>
                </td>
            </tr>
            <?php 
                endforeach; 
            else: 
            ?>
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="bi bi-box-seam"></i></div>
                        <p>Belum ada data barang di sistem.</p>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($role == 'admin'): ?>
    <?php foreach($barang_list as $d): ?>
        <div class="modal fade" id="modalEdit<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="edit_barang" value="1">
                            <input type="hidden" name="id_barang" value="<?= $d['id'] ?>">
                            
                            <label class="modal-label">Nama Barang</label>
                            <input type="text" name="nama_barang" class="modal-input" style="margin-bottom:1rem;" value="<?= htmlspecialchars($d['nama_barang']) ?>" required>
                            
                            <label class="modal-label">Kondisi Barang</label>
                            <select name="kondisi" class="modal-input" style="margin-bottom:1.25rem;">
                                <option value="Baru" <?= $d['kondisi'] == 'Baru' ? 'selected' : '' ?>>Baru</option>
                                <option value="Baik" <?= $d['kondisi'] == 'Baik' ? 'selected' : '' ?>>Baik</option>
                                <option value="Rusak" <?= $d['kondisi'] == 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                            </select>
                            
                            <label class="modal-label">Ganti Foto Barang <span style="text-transform:none;font-weight:400;">(Opsional)</span></label>
                            <input type="file" name="gambar" class="modal-input" accept="image/png, image/jpeg, image/jpg, image/webp" style="margin-bottom:1rem;">

                            <div class="modal-info-box" style="margin-bottom:1rem;">
                                Stok asli saat ini: <b><?= (int)$d['stok'] ?> unit</b>
                            </div>

                            <label class="modal-label">Tambah Stok Baru <span style="text-transform:none;font-weight:400;">(Opsional)</span></label>
                            <input type="number" name="jml_tambah" class="modal-input" min="0" value="0">
                        </div>
                        <div class="modal-footer" style="display:flex;justify-content:flex-end;gap:8px;">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-modal-save">Simpan</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById('searchInput');
const rows = document.querySelectorAll('#tableBody .table-row');

searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    rows.forEach(row => {
        const name = row.querySelector('.item-name-text').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
