<?php
require '../../config/koneksi.php';

// Inisialisasi array untuk menyimpan hasil
$purchase_values = [];
$qty_values = [];
$affiliate_commissions = [];

// Dapatkan tanggal hari ini
$today = new DateTime();

// Loop untuk 30 hari terakhir, dari hari ini ke belakang
for ($i = 0; $i < 30; $i++) {
    // Kurangi hari sesuai urutan
    $date = clone $today;
    $date->modify("-$i days");
    $formatted_date = $date->format('Y-m-d');

    // SQL untuk mendapatkan nilai total purchase_value_rp, qty, dan affiliate_net_commission_rp pada tanggal tersebut
    $sql = "SELECT 
                SUM(purchase_value_rp) AS total_purchase_value, 
                SUM(qty) AS total_qty, 
                SUM(affiliate_net_commission_rp) AS total_affiliate_commission 
            FROM penjualan 
            WHERE DATE(order_time) = :order_date";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':order_date', $formatted_date);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika tidak ada data, masukkan 0 ke dalam masing-masing array
    $purchase_values[] = $result['total_purchase_value'] !== null ? $result['total_purchase_value'] : 0;
    $qty_values[] = $result['total_qty'] !== null ? $result['total_qty'] : 0;
    $affiliate_commissions[] = $result['total_affiliate_commission'] !== null ? $result['total_affiliate_commission'] : 0;
}

// Kirim data dalam format JSON
echo json_encode([
    'purchase_values' => $purchase_values,
    'qty_values' => $qty_values,
    'affiliate_commissions' => $affiliate_commissions
]);
?>
