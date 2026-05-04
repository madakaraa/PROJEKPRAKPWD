<?php
session_start();

// Proteksi: pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
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
            background-color: #f8f9fa;
            color: #343a40;
            padding: 2rem 1rem;
        }

        /* Container Pembungkus */
        .form-container {
            max-width: 550px;
            margin: 0 auto;
        }

        /* Styling Kartu Form */
        .form-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .header-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }

        .form-title {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #212529;
        }

        /* Styling Form Input */
        .form-label {
            font-weight: 500;
            color: #495057;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 0.6rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
            border-color: #86b7fe;
        }

        /* Styling Tombol */
        .btn-submit {
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.2);
        }

        .btn-back {
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-card">
        
        <div class="text-center mb-4 pb-3 border-bottom">
            <i class="bi bi-box-seam header-icon"></i>
            <h3 class="form-title mb-1">Tambah Barang Baru</h3>
            <p class="text-muted small mb-0">Masukkan detail barang ke dalam sistem</p>
        </div>

        <form action="proses_barang.php" method="POST">
            
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-tag me-1"></i> Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Proyektor Epson" required>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="bi bi-card-text me-1"></i> Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan detail barang..." required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-star-half me-1"></i> Kondisi</label>
                    <input type="text" name="kondisi" class="form-control" placeholder="Contoh: Baik / Baru" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="bi bi-boxes me-1"></i> Stok</label>
                    <input type="number" name="stok" class="form-control" min="0" placeholder="0" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="bi bi-info-circle me-1"></i> Status</label>
                <select name="status" class="form-select" required>
                    <option value="tersedia">Tersedia</option>
                    <option value="dipinjam">Dipinjam</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save me-1"></i> Simpan Data Barang
                </button>
                <a href="barang.php" class="btn btn-light border btn-back">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>