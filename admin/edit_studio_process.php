<?php
require '../config/koneksi.php';
require '../config/functions.php';

// Pengecekan sesi
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    // Jika sesi tidak ada, arahkan ke halaman login
    $_SESSION['error'] = 'Silahkan Login Dahulu!';
    header('Location: ../auth/login.php');
    exit();
}

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $studio_id = $_POST['id'] ?? '';
    $nama_std = $_POST['nama_std'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $host = $_POST['host'] ?? '';

    // Validasi input
    if (empty($nama_std)) {
        echo "Error: Nama Studio harus diisi.";
        exit();
    }

    // Update data studio
    $sql = "UPDATE studio SET nama_std = :nama_std, alamat = :alamat, host = :host WHERE id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nama_std', $nama_std);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':host', $host);
        $stmt->bindParam(':id', $studio_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirect setelah data berhasil diperbarui
            header("Location: studio-list.php");
            exit();
        } else {
            echo "Error: " . implode(", ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>