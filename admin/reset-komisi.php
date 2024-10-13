<?php
require '../config/koneksi.php';
require '../config/functions.php';

// Pengecekan sesi
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    // Jika sesi tidak ada, arahkan ke halaman login
    $_SESSION['error'] = 'Silahkan Login Dahulu!';
    header('Location: ../auth/login.php');
    exit();
}

// Ambil username dari query string
$username = isset($_GET['username']) ? $_GET['username'] : null;

if ($username) {
    try {
        // Siapkan dan jalankan query delete
        $sql = "DELETE FROM komisi WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);

        if ($stmt->execute()) {
            header('Location: komisi-report.php');
            exit();
        } else {
            echo "Error: " . implode(", ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Tutup koneksi PDO (opsional karena akan otomatis ditutup pada akhir skrip)
$pdo = null;
?>