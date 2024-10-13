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

$username = isset($_GET['username']) ? $_GET['username'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $username) {
    $filePath = $_FILES['komisi_csv']['tmp_name'];

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        fgetcsv($handle); // Melewati header

        $sql = "INSERT INTO komisi (username, waktu_komisi_dibayarkan, periode_validasi, total_komisi, metode_pembayaran, status_pembayaran, waktu_pembayaran) 
                VALUES (:username, :waktu_komisi_dibayarkan, :periode_validasi, :total_komisi, :metode_pembayaran, :status_pembayaran, :waktu_pembayaran)";

        $stmt = $pdo->prepare($sql);

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Convert dates with error checking
            $waktu_komisi_dibayarkan = $data[0] ? DateTime::createFromFormat('d-m-Y', $data[0]) ? DateTime::createFromFormat('d-m-Y', $data[0])->format('Y-m-d') : null : null;
            $periode_validasi = $data[1] ? DateTime::createFromFormat('d-m-Y', $data[1]) ? DateTime::createFromFormat('d-m-Y', $data[1])->format('Y-m-d') : null : null;
            $waktu_pembayaran = $data[5] ? DateTime::createFromFormat('d-m-Y', $data[5]) ? DateTime::createFromFormat('d-m-Y', $data[5])->format('Y-m-d') : null : null;

            // Handle null values for other columns
            $total_komisi = $data[2] ?? '0.00';
            $metode_pembayaran = $data[3] ?? 'Unknown';
            $status_pembayaran = $data[4] ?? 'Unknown';

            // Bind parameters and execute the statement
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':waktu_komisi_dibayarkan', $waktu_komisi_dibayarkan, PDO::PARAM_NULL);
            $stmt->bindParam(':periode_validasi', $periode_validasi, PDO::PARAM_NULL);
            $stmt->bindParam(':total_komisi', $total_komisi);
            $stmt->bindParam(':metode_pembayaran', $metode_pembayaran);
            $stmt->bindParam(':status_pembayaran', $status_pembayaran);
            $stmt->bindParam(':waktu_pembayaran', $waktu_pembayaran, PDO::PARAM_NULL);

            if (!$stmt->execute()) {
                echo "Error: " . implode(", ", $stmt->errorInfo());
            }
        }

        fclose($handle);
        header('Location: komisi-report.php');
        exit();
    } else {
        echo "Error opening file.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Komisi</title>
    <link rel="stylesheet" href="path/to/bootstrap.min.css">
</head>

<body>
    <div class="container mt-4">
        <h2>Edit Komisi for <?php echo htmlspecialchars($username); ?></h2>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="komisi_csv">Upload Komisi CSV</label>
                <input type="file" class="form-control-file" id="komisi_csv" name="komisi_csv" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Upload</button>
        </form>
    </div>
    <script src="path/to/bootstrap.bundle.min.js"></script>
</body>

</html>