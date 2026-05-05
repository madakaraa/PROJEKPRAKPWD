<?php
session_start();
include 'koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kondisi     = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $stok        = (int)$_POST['stok'];
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    
    // --- PROSES UPLOAD GAMBAR ---
    $nama_file_gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Ekstensi yang diperbolehkan
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Buat nama file unik agar tidak bentrok
            $nama_file_gambar = uniqid() . '-' . time() . '.' . $file_ext;
            $upload_path = 'uploads/' . $nama_file_gambar;
            
            // Pastikan folder uploads ada
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            move_uploaded_file($file_tmp, $upload_path);
        } else {
            $pesan = "Gagal: Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        }
    }

    if(empty($pesan)) {
        // Query Insert ke Database
        $query = "INSERT INTO barang (nama_barang, deskripsi, kondisi, stok, status, gambar) 
                  VALUES ('$nama_barang', '$deskripsi', '$kondisi', '$stok', '$status', '$nama_file_gambar')";
                  
        if (mysqli_query($conn, $query)) {
            header("Location: barang.php?pesan=" . urlencode("Barang '$nama_barang' berhasil ditambahkan!"));
            // update
            $nama_log = mysqli_real_escape_string($conn, $_POST['nama_barang']); 
            mysqli_query($conn, "INSERT INTO log_barang (aksi, nama_barang) VALUES ('tambah', '$nama_log')");
            exit;
        } else {
            $pesan = "Gagal menambahkan barang: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .form-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            width: 100%;
            max-width: 600px;
            padding: 3rem 2.5rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .header-icon {
            width: 54px; height: 54px;
            background: rgba(37,99,235,0.1);
            color: #2563eb;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1.5rem;
        }

        .page-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            display: flex; align-items: center; gap: 6px;
        }

        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #1e293b;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
        }

        .radio-group {
            display: flex;
            gap: 1rem;
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .radio-option {
            flex: 1;
        }
        
        .radio-option input[type="radio"] { display: none; }
        
        .radio-option label {
            display: block;
            text-align: center;
            padding: 0.6rem 0;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        /* Styling spesifik untuk pilihan kondisi */
        #kondisiBaru:checked + label { background: rgba(37,99,235,0.1); color: #2563eb; border-color: rgba(37,99,235,0.2); }
        #kondisiBaik:checked + label { background: rgba(16,185,129,0.1); color: #059669; border-color: rgba(16,185,129,0.2); }
        #kondisiRusak:checked + label { background: rgba(239,68,68,0.1); color: #dc2626; border-color: rgba(239,68,68,0.2); }

        .btn-submit {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            margin-top: 1.5rem;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.2);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn-back:hover { color: #0f172a; }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-icon">
        <i class="bi bi-box-seam"></i>
    </div>
    <h1 class="page-title">Tambah Barang Baru</h1>
    <p class="page-subtitle">Masukkan detail barang ke dalam sistem</p>

    <?php if($pesan): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan; ?>
        </div>
    <?php endif; ?>

    <!-- PENTING: enctype="multipart/form-data" wajib ada untuk upload file! -->
    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag text-muted"></i> Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Proyektor Epson" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-card-text text-muted"></i> Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan detail barang..."></textarea>
        </div>

        <div class="row mb-3">
            <div class="col-md-7">
                <label class="form-label"><i class="bi bi-star text-muted"></i> Kondisi</label>
                <!-- Pilihan Kondisi ala Checkbox (Radio Buttons) -->
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" name="kondisi" id="kondisiBaru" value="Baru" checked>
                        <label for="kondisiBaru">Baru</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="kondisi" id="kondisiBaik" value="Baik">
                        <label for="kondisiBaik">Baik</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" name="kondisi" id="kondisiRusak" value="Rusak">
                        <label for="kondisiRusak">Rusak</label>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <label class="form-label"><i class="bi bi-boxes text-muted"></i> Stok Awal</label>
                <input type="number" name="stok" class="form-control" min="0" value="1" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="bi bi-image text-muted"></i> Foto Barang</label>
            <input type="file" name="gambar" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
            <small class="text-muted" style="font-size:0.75rem;">Format: JPG, PNG, WEBP (Maksimal 2MB).</small>
        </div>

        <div class="mb-4">
            <label class="form-label"><i class="bi bi-info-circle text-muted"></i> Status Publikasi</label>
            <select name="status" class="form-select">
                <option value="Tersedia">Tersedia (Bisa dipinjam)</option>
                <option value="Habis">Habis / Disembunyikan</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">
            <i class="bi bi-save me-1"></i> Simpan Data Barang
        </button>
        
        <a href="barang.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Batal & Kembali
        </a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
