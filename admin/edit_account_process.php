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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $old_username = $_POST['old_username'] ?? ''; // Username yang ada sebelum perubahan
    $username = $_POST['username'] ?? '';
    $kode_akun = $_POST['kode_akun'] ?? '';
    $email = $_POST['email'] ?? '';
    $namaid = $_POST['namaid'] ?? '';
    $nik = $_POST['NIK'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $rekening = $_POST['rekening'] ?? '';
    $shopeepay = $_POST['shopeepay'] ?? '';
    $studio_id = $_POST['studio_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';

    // Update data di database
    $sql = "UPDATE shopee_datacenter SET kode_akun=:kode_akun, email=:email, namaid=:namaid, NIK=:nik, bank=:bank, rekening=:rekening, shopeepay=:shopeepay, status=:status, studio_id=:studio_id, keterangan=:keterangan, username=:username WHERE username=:old_username";
    
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        die("Prepare statement failed: " . htmlspecialchars($pdo->errorInfo()[2]));
    }

    // Bind parameters
    $stmt->bindParam(':kode_akun', $kode_akun);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':namaid', $namaid);
    $stmt->bindParam(':nik', $nik);
    $stmt->bindParam(':bank', $bank);
    $stmt->bindParam(':rekening', $rekening);
    $stmt->bindParam(':shopeepay', $shopeepay);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':studio_id', $studio_id);
    $stmt->bindParam(':keterangan', $keterangan);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':old_username', $old_username);

    $update_success = $stmt->execute();

    // Tutup koneksi
    $pdo = null;

    // Debug: Pesan setelah query
    echo 'Debug: Update process completed<br>';

    if ($update_success) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Sukses!",
                text: "Data berhasil diubah.",
                icon: "success",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "account-list.php"; // Redirect ke halaman account-list.php
            });
        </script>';
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Gagal!",
                text: "Gagal mengubah data.",
                icon: "error",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "edit-account.php?username=' . htmlspecialchars($old_username) . '"; // Redirect kembali ke halaman edit
            });
        </script>';
    }
} else {
    echo 'Debug: Tidak ada data POST<br>';
}
?>
