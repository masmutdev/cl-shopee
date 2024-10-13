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

// Ambil id dari parameter URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus data dari database
    $stmt = $pdo->prepare("DELETE FROM studio WHERE id = :id");
    if ($stmt === false) {
        die("Prepare statement failed: " . htmlspecialchars($pdo->errorInfo()[2]));
    }

    // Bind parameter
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $delete_success = true;
    } else {
        $delete_success = false;
    }

    $stmt = null; // Menutup statement
    $pdo = null; // Menutup koneksi

    if ($delete_success) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Sukses!",
                text: "Data berhasil dihapus.",
                icon: "success",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "studio-list.php";
            });
        </script>';
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            Swal.fire({
                title: "Gagal!",
                text: "Gagal menghapus data.",
                icon: "error",
                confirmButtonText: "OK",
                timer: 2000
            }).then(() => {
                window.location.href = "studio-list.php";
            });
        </script>';
    }
} else {
    echo "ID tidak diset dalam URL.";
}
?>
