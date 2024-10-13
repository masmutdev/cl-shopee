<?php
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

// Pastikan koneksi database sudah ada
if (!$pdo) {
    die("Connection failed: " . $pdo->errorInfo()[2]);
}

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kode_akun = $_POST['kode_akun'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_akun = $_POST['password_akun'] ?? '';
    $namaid = $_POST['namaid'] ?? '';
    $nik = $_POST['NIK'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $rekening = $_POST['rekening'] ?? '';
    $shopeepay = $_POST['shopeepay'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status = $_POST['status'] ?? '';
    $data_pembayaran = $_POST['data_pembayaran'] ?? '';
    $studio_id = $_POST['studio_id'] ?? '';

    // Cek apakah username diisi
    if (empty($username)) {
        echo "Error: Username harus diisi.";
        exit();
    }

    // Cek apakah kode_akun sudah ada
    $sqlCheck = "SELECT * FROM shopee_datacenter WHERE kode_akun = :kode_akun";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':kode_akun', $kode_akun);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        echo "Error: Kode akun sudah ada.";
    } else {
        // Insert data jika belum ada
        $sql = "INSERT INTO shopee_datacenter (kode_akun, username, email, password, password_akun, namaid, NIK, bank, rekening, shopeepay, keterangan, status, data_pembayaran, studio_id) 
                VALUES (:kode_akun, :username, :email, :password, :password_akun, :namaid, :nik, :bank, :rekening, :shopeepay, :keterangan, :status, :data_pembayaran, :studio_id)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':kode_akun', $kode_akun);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':password_akun', $password_akun);
        $stmt->bindParam(':namaid', $namaid);
        $stmt->bindParam(':nik', $nik);
        $stmt->bindParam(':bank', $bank);
        $stmt->bindParam(':rekening', $rekening);
        $stmt->bindParam(':shopeepay', $shopeepay);
        $stmt->bindParam(':keterangan', $keterangan);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':data_pembayaran', $data_pembayaran);
        $stmt->bindParam(':studio_id', $studio_id);

        if ($stmt->execute()) {
            // Redirect setelah data berhasil ditambahkan
            header("Location: account-list.php");
            exit();
        } else {
            echo "Error: " . implode(", ", $stmt->errorInfo());
        }
    }
}
?>
