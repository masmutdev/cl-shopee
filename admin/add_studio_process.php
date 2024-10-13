<?php
// Include file koneksi database
require '../config/koneksi.php';
require '../config/functions.php';

// Mulai sesi
session_start();

// Pengecekan sesi
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    // Jika sesi tidak ada, arahkan ke halaman login
    $_SESSION['error'] = 'Silahkan Login Dahulu!';
    header('Location: ../auth/login.php');
    exit();
}

// Variabel untuk menyimpan pesan sukses atau error
$message = '';
$errors = [];

// Proses jika formulir disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari formulir dan hapus spasi yang tidak perlu
    $nama_std = trim($_POST['nama_std'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $host = trim($_POST['host'] ?? '');

    // Validasi
    if (empty($nama_std)) {
        $errors[] = 'Nama Studio harus diisi.';
    }

    // Jika tidak ada kesalahan, simpan data ke database
    if (empty($errors)) {
        try {
            // Menyiapkan statement SQL
            $sql = "INSERT INTO studio (nama_std, alamat, host) VALUES (:nama_std, :alamat, :host)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nama_std', $nama_std);
            $stmt->bindParam(':alamat', $alamat);
            $stmt->bindParam(':host', $host);

            if ($stmt->execute()) {
                $message = 'Studio berhasil ditambahkan.';
            } else {
                $errors[] = 'Terjadi kesalahan saat menambahkan studio: ' . implode(", ", $stmt->errorInfo());
            }
        } catch (PDOException $e) {
            $errors[] = 'Terjadi kesalahan saat menambahkan studio: ' . $e->getMessage();
        }
    }
}

// Redirect dengan pesan sukses atau error
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: studio-list.php');
} else {
    $_SESSION['success_message'] = $message;
    header('Location: studio-list.php');
}
exit();
