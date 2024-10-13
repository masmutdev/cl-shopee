<?php
require '../config/koneksi.php';
require '../config/functions.php';

// Mulai sesi jika belum dimulai
session_start();

// Pengecekan sesi
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    // Jika sesi tidak ada, arahkan ke halaman login
    $_SESSION['error'] = 'Silahkan Login Dahulu!';
    header('Location: ../auth/login.php');
    exit();
}

// Pastikan koneksi database sudah ada
if (!$pdo) {
    die("Connection failed: " . $pdo->errorInfo()[2]);
}

// Set header untuk output CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="orders.csv"');

// Open output stream for writing
$output = fopen('php://output', 'w');

// Tulis header CSV
fputcsv($output, array('Kode Akun', 'Username', 'Password', 'Email', 'Password Email', 'NIK', 'Pemilik Identitas', 'Bank', 'Nomor Rekening', 'Shopeepay', 'Status Data Pembayaran', 'Status', 'Keterangan'));

// Ambil data dari database
$query = "SELECT kode_akun, username, password_akun, email, password, NIK, namaid, bank, rekening, shopeepay, data_pembayaran, status, keterangan FROM shopee_datacenter";
$stmt = $pdo->query($query);

if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} else {
    die("Query failed: " . implode(", ", $pdo->errorInfo()));
}

// Tutup output stream
fclose($output);

// Akhiri skrip
exit();
?>