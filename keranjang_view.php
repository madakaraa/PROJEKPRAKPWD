<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
$id_user = $_SESSION['id'] ?? 0;
$role = $_SESSION['role'] ?? 'user';

// Jalankan query dan simpan ke variabel $keranjang
$keranjang = mysqli_query($conn, "
    SELECT k.*, b.nama_barang, b.kondisi, b.stok, b.deskripsi
    FROM keranjang k
    JOIN barang b ON k.id_barang = b.id
    WHERE k.id_user = $id_user
");

$items = [];
$total_item = 0;

if ($keranjang) {
    while ($row = mysqli_fetch_assoc($keranjang)) {
        $items[] = $row;
        $total_item += $row['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PinjamBareng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink:        #0f1117;
            --ink-2:      #1c2030;
            --surface:    #ffffff;
            --muted:      #8a8fa8;
            --border:     rgba(255,255,255,0.07);
            --gold:       #c9a84c;
            --gold-light: #e8c96a;
            --blue:       #2563eb;
            --green:      #059669;
            --radius:     18px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--ink);
            color: #e2e5ef;
            min-height: 100vh;
        }

        /* ─── TOPBAR ──────────────────────────────── */
        .topbar {
            background: var(--ink-2);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .topbar-logo {
            width: 36px; height: 36px;
            background: var(--blue);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-logo i { color: white; font-size: 1rem; }
        .topbar-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.2rem;
            color: white;
            letter-spacing: 0.2px;
        }

        .topbar-steps {
            display: flex;
            align-items: center;
            gap: 0;
        }
        .step {
            display: flex; align-items: center; gap: 6px;
            font-size: 0.78rem; font-weight: 500;
            color: var(--muted);
        }
        .step.active { color: var(--gold); }
        .step.done   { color: #4ade80; }
        .step-num {
            width: 22px; height: 22px; border-radius: 50%;
            border: 1.5px solid currentColor;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 600;
        }
        .step-sep {
            width: 32px; height: 1px;
            background: var(--border);
            margin: 0 6px;
        }

        .btn-back {
            font-size: 0.82rem; color: var(--muted);
            text-decoration: none; display: flex; align-items: center; gap: 5px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: white; }

        /* ─── MAIN LAYOUT ─────────────────────────── */
        .checkout-wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 4rem;
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            align-items: start;
        }

        /* ─── PAGE TITLE ──────────────────────────── */
        .page-heading {
            grid-column: 1 / -1;
            margin-bottom: 0.25rem;
        }
        .page-heading h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            color: white;
            letter-spacing: -0.5px;
            line-height: 1.15;
        }
        .page-heading p {
            font-size: 0.875rem;
            color: var(--muted);
            margin-top: 0.3rem;
        }
        .gold-line {
            display: inline-block;
            width: 36px; height: 3px;
            background: var(--gold);
            border-radius: 2px;
            margin-bottom: 0.5rem;
        }

        /* ─── SECTION CARD ────────────────────────── */
        .section-card {
            background: var(--ink-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            animation: fadeUp 0.4s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .section-card:nth-child(2) { animation-delay: 0.05s; }
        .section-card:nth-child(3) { animation-delay: 0.10s; }

        .section-head {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .section-head-icon {
            width: 32px; height: 32px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
        }
        .icon-gold { background: rgba(201,168,76,0.15); color: var(--gold); }
        .icon-blue { background: rgba(37,99,235,0.15);  color: #93c5fd; }

        .section-head h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 1rem;
            color: white;
            font-weight: 400;
        }
        .section-head .count-pill {
            margin-left: auto;
            background: rgba(201,168,76,0.12);
            color: var(--gold);
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
            border: 1px solid rgba(201,168,76,0.2);
        }

        /* ─── ITEM ROWS ───────────────────────────── */
        .item-list { padding: 0.5rem 0; }

        .item-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.9rem 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .item-row:last-child { border-bottom: none; }
        .item-row:hover { background: rgba(255,255,255,0.02); }

        .item-icon-wrap {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: rgba(37,99,235,0.1);
            border: 1px solid rgba(37,99,235,0.2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: #93c5fd;
            font-size: 1.1rem;
        }

        .item-info { flex: 1; min-width: 0; }
        .item-name {
            font-size: 0.9rem; font-weight: 600;
            color: #e8eaf0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .item-meta {
            font-size: 0.75rem; color: var(--muted);
            margin-top: 2px;
        }
        .item-meta .kondisi-dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #4ade80;
            margin-right: 4px;
            vertical-align: middle;
        }

        .item-qty {
            display: flex;
            align-items: center;
            gap: 0;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .qty-btn {
            width: 30px; height: 30px;
            background: none; border: none;
            color: var(--muted);
            cursor: pointer; display: flex;
            align-items: center; justify-content: center;
            font-size: 1rem; transition: all 0.15s;
            text-decoration: none;
        }
        .qty-btn:hover { color: white; background: rgba(255,255,255,0.06); }
        .qty-val {
            width: 28px; text-align: center;
            font-size: 0.85rem; font-weight: 600; color: white;
        }

        /* ─── FORM PEMINJAMAN ─────────────────────── */
        .form-body { padding: 1.25rem 1.5rem; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .field-group { margin-bottom: 1rem; }
        .field-label {
            display: block;
            font-size: 0.75rem; font-weight: 600;
            color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }
        .field-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 0.7rem 1rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            color: #e2e5ef;
            outline: none;
            transition: all 0.2s;
        }
        .field-input:focus {
            border-color: var(--gold);
            background: rgba(201,168,76,0.05);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.1);
        }
        .field-input::placeholder { color: rgba(138,143,168,0.5); }

        select.field-input option { background: #1c2030; }

        .field-note {
            font-size: 0.72rem; color: var(--muted);
            margin-top: 0.3rem;
        }

        /* ─── SUMMARY CARD ────────────────────────── */
        .summary-card {
            background: var(--ink-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            position: sticky;
            top: 1.5rem;
            animation: fadeUp 0.4s ease 0.15s both;
        }

        .summary-head {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .summary-head h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 1rem; font-weight: 400;
            color: white;
        }

        .summary-body { padding: 1.25rem 1.5rem; }

        .sum-row {
            display: flex; justify-content: space-between;
            align-items: center;
            font-size: 0.85rem; color: var(--muted);
            padding: 0.45rem 0;
        }
        .sum-row span:last-child { color: #d0d4e4; font-weight: 500; }

        .sum-divider {
            height: 1px; background: var(--border);
            margin: 0.75rem 0;
        }

        /* Checkout Button */
        .btn-checkout {
            width: 100%;
            background: var(--gold);
            color: #0f1117;
            border: none;
            border-radius: 13px;
            padding: 0.9rem 1rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            margin-top: 0.5rem;
            letter-spacing: 0.2px;
        }
        .btn-checkout:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(201,168,76,0.3);
        }
        .btn-checkout:active { transform: translateY(0); }

        .secure-note {
            display: flex; align-items: center; justify-content: center;
            gap: 5px;
            font-size: 0.72rem; color: var(--muted);
            margin-top: 0.85rem;
        }
        .secure-note i { color: #4ade80; font-size: 0.75rem; }

        /* Info box */
        .info-box {
            background: rgba(201,168,76,0.07);
            border: 1px solid rgba(201,168,76,0.15);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.78rem;
            color: rgba(201,168,76,0.8);
            margin-top: 1rem;
            display: flex; gap: 8px;
        }
        .info-box i { flex-shrink: 0; margin-top: 1px; }

        /* Empty state */
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
            color: var(--muted);
        }
        .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.4; }
        .empty-state p { font-size: 0.875rem; }

        @media (max-width: 768px) {
            .checkout-wrap { grid-template-columns: 1fr; }
            .summary-card { position: static; }
            .form-row { grid-template-columns: 1fr; }
            .topbar-steps { display: none; }
        }
    </style>
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
    <!-- Link kembali dinamis berdasarkan role -->
    <a href="<?= ($role == 'admin') ? 'dashboard_admin.php' : 'dashboard.php'; ?>" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>

    <div class="topbar-steps">
        <div class="step done">
            <div class="step-num"><i class="bi bi-check" style="font-size:0.65rem;"></i></div>
            <span>Keranjang</span>
        </div>
        <div class="step-sep"></div>
        <div class="step active">
            <div class="step-num">2</div>
            <span>Checkout</span>
        </div>
        <div class="step-sep"></div>
        <div class="step">
            <div class="step-num">3</div>
            <span>Konfirmasi</span>
        </div>
    </div>

    <a href="barang.php" class="topbar-brand">
        <div class="topbar-logo"><i class="bi bi-box-seam"></i></div>
        <span class="topbar-name">PinjamBareng</span>
    </a>
</nav>

<!-- MAIN -->
<div class="checkout-wrap">

    <!-- PAGE HEADING -->
    <div class="page-heading">
        <div class="gold-line"></div>
        <h1>Konfirmasi Peminjaman</h1>
        <p>Periksa barang dan lengkapi detail peminjaman Anda.</p>
    </div>

    <!-- Peringatan Stok Maksimal (Muncul Jika Error) -->
    <?php if(isset($_SESSION['pesan_err'])): ?>
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" style="grid-column: 1 / -1;" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['pesan_err']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['pesan_err']); ?>
    <?php endif; ?>

    <!-- LEFT COLUMN -->
    <div style="display:flex; flex-direction:column; gap:1.5rem;">

        <!-- DAFTAR BARANG -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-head-icon icon-blue">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h2>Barang yang Dipinjam</h2>
                <span class="count-pill"><?= $total_item ?> item</span>
            </div>

            <div class="item-list">
                <?php if (empty($items)): ?>
                    <div class="empty-state">
                        <i class="bi bi-cart-x"></i>
                        <p>Keranjang Anda kosong.<br>Tambahkan barang terlebih dahulu.</p>
                        <a href="barang.php">Klik Disini</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-icon-wrap">
                            <i class="bi bi-box2"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-name"><?= htmlspecialchars($item['nama_barang']) ?></div>
                            <div class="item-meta">
                                <span class="kondisi-dot"></span>
                                Kondisi: <?= htmlspecialchars($item['kondisi']) ?>
                                &nbsp;&bull;&nbsp; Stok tersisa: <?= (int)$item['stok'] ?>
                            </div>
                        </div>
                        
                        <!-- TOMBOL PLUS MINUS SEKARANG TERHUBUNG KE DATABASE VIA AKSI_KERANJANG.PHP -->
                        <div class="item-qty">
                            <a href="aksi_keranjang.php?action=min&id=<?= $item['id'] ?>" class="qty-btn" title="Kurangi/Hapus">
                                <i class="bi bi-dash"></i>
                            </a>
                            <span class="qty-val"><?= (int)$item['jumlah'] ?></span>
                            <a href="aksi_keranjang.php?action=plus&id=<?= $item['id'] ?>" class="qty-btn <?= ($item['jumlah'] >= $item['stok']) ? 'opacity-50' : '' ?>" title="Tambah">
                                <i class="bi bi-plus"></i>
                            </a>
                        </div>
                        
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- FORM DETAIL PEMINJAMAN -->
        <div class="section-card">
            <div class="section-head">
                <div class="section-head-icon icon-gold">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h2>Detail Peminjaman</h2>
            </div>

            <div class="form-body">
                <form action="proses_checkout.php" method="POST" id="checkoutForm">

                    <div class="form-row">
                        <div class="field-group">
                            <label class="field-label">Tanggal Pinjam</label>
                            <input type="date" name="tgl_pinjam" class="field-input"
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Tanggal Kembali</label>
                            <input type="date" name="tgl_kembali" class="field-input"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nama Peminjam</label>
                        <input type="text" name="nama_peminjam" class="field-input"
                               placeholder="Nama lengkap Anda"
                               value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Tujuan / Keperluan</label>
                        <textarea name="keperluan" class="field-input" rows="3"
                                  placeholder="Contoh: untuk keperluan acara RT/RW..."
                                  style="resize:none; line-height:1.5;"></textarea>
                        <p class="field-note">Opsional — bantu admin memahami kebutuhan Anda.</p>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Metode Pengambilan</label>
                        <select name="metode_ambil" class="field-input">
                            <option value="ambil_sendiri">Ambil Sendiri di Tempat</option>
                            <option value="diantar">Diantar ke Lokasi</option>
                        </select>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: SUMMARY -->
    <div class="summary-card">
        <div class="summary-head">
            <h2>Ringkasan Peminjaman</h2>
        </div>
        <div class="summary-body">

            <?php foreach ($items as $item): ?>
            <div class="sum-row">
                <span><?= htmlspecialchars($item['nama_barang']) ?></span>
                <span>×<?= (int)$item['jumlah'] ?></span>
            </div>
            <?php endforeach; ?>

            <div class="sum-divider"></div>

            <div class="sum-row">
                <span>Total item</span>
                <span><?= $total_item ?> barang</span>
            </div>
            <div class="sum-row">
                <span>Durasi</span>
                <span id="durasi-text">3 hari</span>
            </div>
            <div class="sum-row">
                <span>Status</span>
                <span style="color: #4ade80;">Gratis</span>
            </div>

            <!-- BAGIAN BIAYA SUDAH DIHAPUS SESUAI REQUEST -->

            <div class="sum-divider"></div>

            <button class="btn-checkout" <?= empty($items) ? 'disabled style="opacity: 0.5;"' : '' ?> onclick="document.getElementById('checkoutForm').submit()">
                <i class="bi bi-check2-circle"></i>
                Ajukan Peminjaman
            </button>

            <div class="secure-note">
                <i class="bi bi-shield-check-fill"></i>
                Transaksi aman &amp; terverifikasi sistem
            </div>

            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                <span>Peminjaman akan dikonfirmasi oleh admin dalam 1×24 jam kerja.</span>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hitung durasi otomatis
const tglPinjam  = document.querySelector('[name="tgl_pinjam"]');
const tglKembali = document.querySelector('[name="tgl_kembali"]');
const durasiText = document.getElementById('durasi-text');

function hitungDurasi() {
    const a = new Date(tglPinjam.value);
    const b = new Date(tglKembali.value);
    const diff = Math.ceil((b - a) / (1000 * 60 * 60 * 24));
    durasiText.textContent = diff > 0 ? diff + ' hari' : '-';
}

tglPinjam.addEventListener('change', hitungDurasi);
tglKembali.addEventListener('change', hitungDurasi);
hitungDurasi();
</script>
</body>
</html>