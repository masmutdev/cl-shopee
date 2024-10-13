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

// Ambil username dari parameter URL
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Hapus data dari database
    $stmt = $pdo->prepare("DELETE FROM shopee_datacenter WHERE username = :username");
    if ($stmt === false) {
        throw new Exception("Prepare statement failed.");
    }

    // Bind parameter
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();

    echo "Execute statement success.<br>";

    if ($stmt->rowCount() > 0) {
        $delete_success = true;
    } else {
        $delete_success = false;
    }

    // Menutup statement
    $stmt = null;

    if ($delete_success) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Sukses!",
                text: "Akun berhasil dihapus.",
                icon: "success",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "index.php";
            });
        </script>';
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Gagal!",
                text: "Gagal menghapus akun.",
                icon: "error",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "index.php";
            });
        </script>';
    }
} else {
    echo "Username not set in URL.";
}

// Menutup koneksi PDO
$pdo = null;
?>
