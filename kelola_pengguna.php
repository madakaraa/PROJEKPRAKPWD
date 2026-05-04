<?php
session_start();
include 'koneksi.php';

// Proteksi ganda: hanya admin yang boleh akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=Akses ilegal! Anda harus login sebagai Admin.");
    exit;
}

$nama_admin = htmlspecialchars($_SESSION['nama']);
$inisial_admin = strtoupper(substr($nama_admin, 0, 1));

// Notifikasi pesan
$pesan = '';
$tipe  = '';
if (isset($_GET['pesan'])) {
    $pesan = htmlspecialchars($_GET['pesan']);
    $tipe  = htmlspecialchars($_GET['tipe'] ?? 'sukses');
}

// Filter & Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

// Menyusun Query
$where = "WHERE 1=1"; 
$params = [];
$types  = '';

if ($search !== '') {
    $where .= " AND (nama LIKE ? OR username LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if ($filter_role !== '') {
    $where .= " AND role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

// Pagination
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Hitung total data
$sql_count = "SELECT COUNT(*) as total FROM users $where";
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

// Ambil data dari tabel users (Tanpa email dan status)
$sql = "SELECT id, nama, username, role FROM users $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params_paged = $params;
$params_paged[] = $per_page;
$params_paged[] = $offset;
$types_paged = $types . 'ii';
$stmt->bind_param($types_paged, ...$params_paged);
$stmt->execute();
$result = $stmt->get_result();

// Statistik ringkasan (Disesuaikan dengan data yang ada)
$total_user = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$jml_admin  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='admin'")->fetch_assoc()['c'];
$jml_biasa  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];

// Warna avatar acak
$avatar_colors = ['a', 'b', 'c', 'd', 'e', 'f'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - PinjamBareng Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; color: #0f172a; min-height: 100vh; }

        /* ===== HERO ===== */
        .hero { background: #0C1B33; padding: 1.75rem 2rem 5rem; position: relative; overflow: hidden; }
        .hero-inner { position: relative; z-index: 2; max-width: 960px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
        
        .back-btn {
            display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.7); 
            font-size: 13px; cursor: pointer; background: rgba(255,255,255,0.1); 
            border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; 
            padding: 5px 14px; text-decoration: none; transition: background 0.2s;
        }
        .back-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }

        .hero-title { font-size: 2rem; font-weight: 700; color: #fff; letter-spacing: -0.3px; margin-bottom: 0.4rem; }
        .hero-sub { font-size: 13px; color: rgba(255,255,255,0.5); line-height: 1.6; }

        /* ===== BODY ===== */
        .page-body { max-width: 960px; margin: 0 auto; padding: 0 1.5rem 2.5rem; }

        /* ===== STAT CARDS ===== */
        .stat-row {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
            margin-top: -2.8rem; position: relative; z-index: 10; margin-bottom: 1.75rem;
        }
        .scard {
            background: #fff; border: 1px solid #e8edf2; border-radius: 16px;
            padding: 1.25rem 1.4rem; display: flex; align-items: center; gap: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .scard-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px;
        }
        .scard-icon.blue { background: #E6F1FB; color: #185FA5; }
        .scard-icon.purple { background: #EEEDFE; color: #534AB7; }
        .scard-icon.green { background: #EAF3DE; color: #3B6D11; }
        .scard-val { font-size: 1.8rem; font-weight: 700; color: #0f172a; line-height: 1; }
        .scard-label { font-size: 12px; color: #64748b; margin-top: 3px; }

        /* ===== TOOLBAR ===== */
        .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; flex-wrap: wrap; }
        .search-wrap {
            display: flex; align-items: center; gap: 8px; background: #fff; 
            border: 1px solid #e2e8f0; border-radius: 10px; padding: 0 14px; flex: 1; min-width: 200px;
        }
        .search-wrap input { border: none; outline: none; font-size: 13px; width: 100%; padding: 9px 0; }
        .filter-sel {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 9px 14px; font-size: 13px; outline: none; cursor: pointer;
        }
        .btn-search {
            background: #0C1B33; color: #fff; border: none; border-radius: 10px;
            padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
        }

        /* ===== TABLE ===== */
        .table-card { background: #fff; border: 1px solid #e8edf2; border-radius: 18px; overflow: hidden; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        thead th {
            font-size: 11px; font-weight: 600; color: #94a3b8; letter-spacing: 0.7px; 
            text-transform: uppercase; padding: 1rem 1.25rem; background: #fafbfc; border-bottom: 1px solid #f1f5f9;
        }
        tbody tr { border-bottom: 1px solid #f8fafc; transition: background 0.15s; }
        tbody tr:hover { background: #fafbfc; }
        tbody td { padding: 1rem 1.25rem; vertical-align: middle; }

        /* Avatar */
        .u-avatar {
            width: 38px; height: 38px; border-radius: 50%; display: flex; 
            align-items: center; justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0;
        }
        .av-a { background: #E6F1FB; color: #0C447C; }
        .av-b { background: #EAF3DE; color: #27500A; }
        .av-c { background: #EEEDFE; color: #3C3489; }
        .av-d { background: #FBEAF0; color: #72243E; }
        .av-e { background: #FAEEDA; color: #633806; }
        .av-f { background: #E1F5EE; color: #085041; }
        .u-name { font-size: 14px; font-weight: 600; color: #0f172a; }
        .u-uname { font-size: 12px; color: #94a3b8; }

        /* Badges */
        .role-badge {
            display: inline-flex; align-items: center; gap: 5px;
            border-radius: 50px; padding: 4px 12px; font-size: 11px; font-weight: 600;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; }
        .role-admin { background: #EEEDFE; color: #3C3489; }
        .role-admin .badge-dot { background: #7F77DD; }
        .role-user { background: #E6F1FB; color: #0C447C; }
        .role-user .badge-dot { background: #378ADD; }

        /* Action buttons */
        .btn-del {
            display: inline-flex; align-items: center; gap: 5px; background: #FCEBEB; 
            color: #A32D2D; border: none; border-radius: 8px; padding: 7px 12px; 
            font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-del:hover { background: #F7C1C1; }
        .btn-del:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none; position: fixed; inset: 0; z-index: 999;
            background: rgba(15,23,42,0.55); align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #fff; border-radius: 20px; padding: 2rem;
            max-width: 380px; width: 90%; text-align: center;
        }
        .modal-icon {
            width: 60px; height: 60px; border-radius: 50%; background: #FCEBEB;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem; font-size: 24px; color: #E24B4A;
        }
        .modal-btns { display: flex; gap: 10px; margin-top: 1.5rem; }
        .btn-cancel { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; font-weight: 600; cursor: pointer; }
        .btn-confirm { flex: 1; padding: 10px; border: none; border-radius: 10px; background: #E24B4A; color: #fff; font-weight: 600; cursor: pointer; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
    </style>
</head>
<body>

<!-- MODAL KONFIRMASI HAPUS -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h5 class="fw-bold mb-2">Hapus pengguna ini?</h5>
        <p class="text-muted small mb-0">
            Akun <strong id="modalNama" class="text-dark"></strong> akan dihapus permanen. Aksi ini tidak dapat dibatalkan.
        </p>
        <div class="modal-btns">
            <button class="btn-cancel" onclick="closeModal()">Batal</button>
            <button class="btn-confirm" id="btnConfirmDelete">Ya, Hapus</button>
        </div>
    </div>
</div>

<div class="hero">
    <div class="hero-inner">
        <div class="topbar">
            <a href="dashboard_admin.php" class="back-btn"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
            <div class="text-white opacity-75 small"><i class="bi bi-person-circle me-1"></i> <?= $nama_admin ?></div>
        </div>
        <h1 class="hero-title">Manajemen Pengguna</h1>
        <p class="hero-sub">Kelola hak akses, lihat daftar pengguna, dan hapus akun jika diperlukan.</p>
    </div>
</div>

<div class="page-body">

    <!-- Statistik -->
    <div class="stat-row">
        <div class="scard">
            <div class="scard-icon blue"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="scard-val"><?= $total_user ?></div>
                <div class="scard-label">Total Pengguna</div>
            </div>
        </div>
        <div class="scard">
            <div class="scard-icon purple"><i class="bi bi-shield-lock-fill"></i></div>
            <div>
                <div class="scard-val"><?= $jml_admin ?></div>
                <div class="scard-label">Akun Admin</div>
            </div>
        </div>
        <div class="scard">
            <div class="scard-icon green"><i class="bi bi-person-check-fill"></i></div>
            <div>
                <div class="scard-val"><?= $jml_biasa ?></div>
                <div class="scard-label">User Biasa</div>
            </div>
        </div>
    </div>

    <!-- Pindahkan Notifikasi ke sini! 👇 -->
    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe == 'sukses' ? 'success' : 'danger' ?> alert-dismissible mb-4 shadow-sm" role="alert">
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- 👆 Batas Notifikasi -->

    <!-- Toolbar Pencarian -->
    <form method="GET" class="toolbar">
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="search" placeholder="Cari nama atau username..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="role" class="filter-sel">
            <option value="">Semua Role</option>
            <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= $filter_role == 'user' ? 'selected' : '' ?>>User Biasa</option>
        </select>
        <button type="submit" class="btn-search">Filter Data</button>
        <?php if($search || $filter_role): ?>
            <a href="kelola_pengguna.php" class="btn btn-light border small ms-2">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Tabel Data -->
    <div class="table-card table-responsive">
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="45%">Pengguna</th>
                    <th width="25%">Role</th>
                    <th width="25%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = $offset + 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                            $color = $avatar_colors[array_rand($avatar_colors)];
                            $inisial = strtoupper(substr($row['nama'], 0, 1));
                            $is_self = ($row['id'] == $_SESSION['id']); // Mencegah admin menghapus dirinya sendiri
                        ?>
                        <tr>
                            <td class="text-muted"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="u-avatar av-<?= $color ?>"><?= $inisial ?></div>
                                    <div>
                                        <div class="u-name"><?= htmlspecialchars($row['nama']) ?> <?= $is_self ? '<span class="badge bg-secondary ms-1" style="font-size:0.6rem;">Anda</span>' : '' ?></div>
                                        <div class="u-uname">@<?= htmlspecialchars($row['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($row['role'] == 'admin'): ?>
                                    <span class="role-badge role-admin"><div class="badge-dot"></div> Admin</span>
                                <?php else: ?>
                                    <span class="role-badge role-user"><div class="badge-dot"></div> User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-del" 
                                        <?= $is_self ? 'disabled title="Tidak bisa menghapus akun sendiri"' : '' ?>
                                        onclick="bukaModalHapus(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="empty-state"><i class="bi bi-inbox"></i><p>Tidak ada pengguna yang ditemukan.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Info Banner -->
    <div class="alert alert-light border d-flex align-items-center gap-3">
        <i class="bi bi-info-circle-fill text-primary fs-4"></i>
        <div class="small text-muted">
            <strong class="text-dark">Hati-hati dalam menghapus pengguna.</strong><br>
            Untuk menghindari error di sistem, pastikan pengguna yang dihapus sudah tidak memiliki transaksi peminjaman yang sedang berjalan.
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let hapusId = 0;
    const modal = document.getElementById('modalHapus');
    
    function bukaModalHapus(id, nama) {
        hapusId = id;
        document.getElementById('modalNama').innerText = nama;
        modal.classList.add('show');
    }
    
    function closeModal() {
        modal.classList.remove('show');
    }
    
    document.getElementById('btnConfirmDelete').addEventListener('click', function() {
        // Mengarahkan ke file eksekusi hapus (pastikan Anda sudah punya file hapus_pengguna.php)
        window.location.href = 'hapus_pengguna.php?id=' + hapusId;
    });
</script>
</body>
</html>
