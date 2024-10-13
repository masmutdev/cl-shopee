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

// Ambil data username dan total nilai dari tabel shopee_datacenter
$sql = "SELECT sd.username, 
               SUM(p.purchase_value_rp) AS total_purchase_value_rp, 
               SUM(p.total_order_commission_rp) AS total_commission_rp
        FROM shopee_datacenter sd
        LEFT JOIN penjualan p ON sd.username = p.username
        GROUP BY sd.username";

$stmt = $pdo->prepare($sql);
if (!$stmt->execute()) {
    die("Query failed: " . implode(", ", $stmt->errorInfo()));
}

// Ambil hasil query
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tampilkan data
foreach ($data as $row) {
    echo "Username: " . htmlspecialchars($row['username']) . "<br>";
    echo "Total Purchase Value: " . htmlspecialchars($row['total_purchase_value_rp']) . "<br>";
    echo "Total Commission: " . htmlspecialchars($row['total_commission_rp']) . "<br><br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tabel Penjualan</title>
    <link rel="stylesheet" href="path/to/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Tabel Penjualan</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Total Purchase Value (Rp)</th>
                    <th>Total Commission (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo number_format($row['total_purchase_value_rp'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($row['total_commission_rp'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="upload-penjualan.php?username=<?php echo urlencode($row['username']); ?>" class="btn btn-primary">Upload Penjualan</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="path/to/bootstrap.bundle.min.js"></script>
</body>
</html>
