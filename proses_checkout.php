<?php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id'];

// Tangkap data dari form keranjang_view.php
$tgl_pinjam  = mysqli_real_escape_string($conn, $_POST['tgl_pinjam']);
$tgl_kembali = mysqli_real_escape_string($conn, $_POST['tgl_kembali']);
// Jika tabel database lu punya kolom keperluan/no_telp, lu bisa tambahkan di query INSERT nanti.
// $keperluan   = mysqli_real_escape_string($conn, $_POST['keperluan']); 

// 1. Ambil semua barang yang ada di keranjang user ini
$query_keranjang = mysqli_query($conn, "SELECT * FROM keranjang WHERE id_user = '$id_user'");

if (mysqli_num_rows($query_keranjang) == 0) {
    // Kalau keranjang kosong, kembalikan ke halaman barang
    header("Location: barang.php");
    exit;
}

$berhasil = true;

// 2. Looping data keranjang, pindahkan satu per satu ke tabel peminjaman
while ($item = mysqli_fetch_assoc($query_keranjang)) {
    $id_barang = $item['id_barang'];
    $jumlah    = $item['jumlah'];
    $status    = 'menunggu'; // Status awal saat diajukan

    // Query masukkan data ke tabel peminjaman
    $insert = mysqli_query($conn, "INSERT INTO peminjaman (id_user, id_barang, jumlah, tgl_pinjam, tgl_kembali, status) 
                                   VALUES ('$id_user', '$id_barang', '$jumlah', '$tgl_pinjam', '$tgl_kembali', '$status')");

    if (!$insert) {
        $berhasil = false;
    } else {
        // (Opsional) Kurangi stok barang agar tidak dipinjam orang lain di saat bersamaan
        mysqli_query($conn, "UPDATE barang SET stok = stok - $jumlah WHERE id = '$id_barang'");
    }
}

if ($berhasil) {
    // 3. Hapus / Kosongkan keranjang milik user ini karena sudah di-checkout
    mysqli_query($conn, "DELETE FROM keranjang WHERE id_user = '$id_user'");

    // 4. TIKET PULANG: Arahkan ke Dashboard dengan membawa Pesan Sukses
    header("Location: dashboard.php?pesan=" . urlencode("Pengajuan barang berhasil dikirim dan menunggu verifikasi Admin!"));
    exit;
} else {
    echo "Gagal memproses peminjaman: " . mysqli_error($conn);
}
?>
