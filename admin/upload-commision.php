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

// Periksa apakah file diunggah
if (isset($_FILES['komisi_csv']) && $_FILES['komisi_csv']['error'] === UPLOAD_ERR_OK) {
    $filePath = $_FILES['komisi_csv']['tmp_name'];

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        fgetcsv($handle); // Melewati header
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Convert dates with error checking
            $waktu_komisi_dibayarkan = $data[0] ? (DateTime::createFromFormat('d-m-Y', $data[0]) ? DateTime::createFromFormat('d-m-Y', $data[0])->format('Y-m-d') : null) : null;
            $periode_validasi = $data[1] ? (DateTime::createFromFormat('d-m-Y', $data[1]) ? DateTime::createFromFormat('d-m-Y', $data[1])->format('Y-m-d') : null) : null;
            $waktu_pembayaran = $data[5] ? (DateTime::createFromFormat('d-m-Y', $data[5]) ? DateTime::createFromFormat('d-m-Y', $data[5])->format('Y-m-d') : null) : null;

            // Handle null values for other columns
            $total_komisi = $data[2] ?? '0.00';
            $metode_pembayaran = $data[3] ?? 'Unknown';
            $status_pembayaran = $data[4] ?? 'Unknown';

            // Prepare SQL query with null handling
            $sql = "INSERT INTO komisi (username, waktu_komisi_dibayarkan, periode_validasi, total_komisi, metode_pembayaran, status_pembayaran, waktu_pembayaran) 
                    VALUES (:username, :waktu_komisi_dibayarkan, :periode_validasi, :total_komisi, :metode_pembayaran, :status_pembayaran, :waktu_pembayaran)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':username', $_POST['username']);
            $stmt->bindValue(':waktu_komisi_dibayarkan', $waktu_komisi_dibayarkan, PDO::PARAM_STR);
            $stmt->bindValue(':periode_validasi', $periode_validasi, PDO::PARAM_STR);
            $stmt->bindValue(':total_komisi', $total_komisi, PDO::PARAM_STR);
            $stmt->bindValue(':metode_pembayaran', $metode_pembayaran, PDO::PARAM_STR);
            $stmt->bindValue(':status_pembayaran', $status_pembayaran, PDO::PARAM_STR);
            $stmt->bindValue(':waktu_pembayaran', $waktu_pembayaran, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "error", "message" => "Error: " . implode(", ", $stmt->errorInfo())));
                exit();
            }
        }
        fclose($handle);
        echo json_encode(array("status" => "success", "message" => "File uploaded and data inserted successfully."));
        exit();
    } else {
        echo json_encode(array("status" => "error", "message" => "Error opening file."));
        exit();
    }
} else {
    echo json_encode(array("status" => "error", "message" => "No file uploaded or upload error."));
    exit();
}
?>
