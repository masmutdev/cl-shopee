<?php
require '../../config/koneksi.php'; // Sesuaikan path ke file koneksi

// Inisialisasi array untuk menyimpan hasil
$targets = [];
$bulans = [];

// SQL untuk mengambil data dari kolom 'bulan' dan 'target'
$sql = "SELECT bulan, target FROM target_bulanan ORDER BY bulan ASC LIMIT 3";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Fetch semua hasil
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Loop hasil dan masukkan ke dalam array terpisah
foreach ($results as $row) {
    $bulans[] = $row['bulan'];
    $targets[] = $row['target'];
}

// Kembalikan data dalam format JSON dengan dua array terpisah
echo json_encode([
    'bulan' => $bulans,
    'target' => $targets
]);
?>
