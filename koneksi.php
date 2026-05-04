<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pinjambarang";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi untuk mencatat aktivitas ke database
function catat_aktivitas($conn, $deskripsi, $sub_deskripsi, $kategori) {
    $desc = mysqli_real_escape_string($conn, $deskripsi);
    $sub = mysqli_real_escape_string($conn, $sub_deskripsi);
    $kat = mysqli_real_escape_string($conn, $kategori);
    
    mysqli_query($conn, "INSERT INTO aktivitas (deskripsi, sub_deskripsi, kategori) VALUES ('$desc', '$sub', '$kat')");
}

// Fungsi untuk mengubah waktu menjadi "2 jam lalu", dll
function waktu_lalu($datetime) {
    $waktu = strtotime($datetime);
    $sekarang = time();
    $selisih = $sekarang - $waktu;

    if ($selisih < 60) return "Baru saja";
    if ($selisih < 3600) return floor($selisih / 60) . " menit lalu";
    if ($selisih < 86400) return floor($selisih / 3600) . " jam lalu";
    if ($selisih < 604800) return floor($selisih / 86400) . " hari lalu";
    return date("d M Y", $waktu);
}
?>

