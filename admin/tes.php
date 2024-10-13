<?php 
require '../config/koneksi.php';
require '../config/functions.php';

// Cek apakah parameter 'start_date' dan 'end_date' ada dalam URL
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];


try {
    // SQL untuk menjumlahkan semua nilai di kolom purchase_value_rp, qty, dan affiliate_net_commision_rp
    $sqlTotal = "SELECT 
                SUM(purchase_value_rp) AS total_purchase_value, 
                SUM(qty) AS total_qty, 
                SUM(affiliate_net_commission_rp) AS total_affiliate_commission 
            FROM penjualan
            WHERE DATE(order_time) BETWEEN :start_date AND :end_date";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->bindParam(':start_date', $start_date);
    $stmtTotal->bindParam(':end_date', $end_date);
    $stmtTotal->execute();

    // Ambil hasilnya
    $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    $total_purchase_value = $resultTotal['total_purchase_value'] ?? 0;
    $total_qty = $resultTotal['total_qty'] ?? 0;
    $total_affiliate_commision = $resultTotal['total_affiliate_commission'] ?? 0;

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

try {
    // SQL untuk menjumlahkan semua nilai pada kolom total_komisi di rentang waktu tertentu
    $sql_total_semua = "SELECT SUM(total_komisi) AS total_komisi_sum 
    FROM komisi 
    WHERE DATE(waktu_komisi_dibayarkan) BETWEEN :start_date AND :end_date";

    // SQL untuk menjumlahkan total_komisi dengan metode_pembayaran 'Transfer Bank' di rentang waktu tertentu
    $sql_transfer_bank = "SELECT SUM(total_komisi) AS total_komisi_transfer_bank 
    FROM komisi 
    WHERE metode_pembayaran = 'Transfer Bank' 
    AND DATE(waktu_komisi_dibayarkan) BETWEEN :start_date AND :end_date";

    // SQL untuk menjumlahkan total_komisi dengan metode_pembayaran 'ShopeePay' di rentang waktu tertentu
    $sql_shopee = "SELECT SUM(total_komisi) AS total_komisi_shopee 
    FROM komisi 
    WHERE metode_pembayaran = 'ShopeePay' 
    AND DATE(waktu_komisi_dibayarkan) BETWEEN :start_date AND :end_date";
    
    // Siapkan dan eksekusi query untuk semua komisi
    $stmt_total_semua = $pdo->prepare($sql_total_semua);
    $stmt_total_semua->bindParam(':start_date', $start_date);
    $stmt_total_semua->bindParam(':end_date', $end_date);
    $stmt_total_semua->execute();
    $result_total_semua = $stmt_total_semua->fetch(PDO::FETCH_ASSOC);

    // Siapkan dan eksekusi query untuk Transfer Bank
    $stmt_transfer_bank = $pdo->prepare($sql_transfer_bank);
    $stmt_transfer_bank->bindParam(':start_date', $start_date);
    $stmt_transfer_bank->bindParam(':end_date', $end_date);
    $stmt_transfer_bank->execute();
    $result_transfer_bank = $stmt_transfer_bank->fetch(PDO::FETCH_ASSOC);
    
    // Siapkan dan eksekusi query untuk ShopeePay
    $stmt_shopee = $pdo->prepare($sql_shopee);
    $stmt_shopee->bindParam(':start_date', $start_date);
    $stmt_shopee->bindParam(':end_date', $end_date);
    $stmt_shopee->execute();
    $result_shopee = $stmt_shopee->fetch(PDO::FETCH_ASSOC);

    // Jika hasilnya tidak null, ambil nilainya, jika tidak, set ke 0
    $total_komisi_sum = $result_total_semua['total_komisi_sum'] !== null ? $result_total_semua['total_komisi_sum'] : 0;
    $total_komisi_transfer_bank = $result_transfer_bank['total_komisi_transfer_bank'] !== null ? $result_transfer_bank['total_komisi_transfer_bank'] : 0;
    $total_komisi_shopee = $result_shopee['total_komisi_shopee'] !== null ? $result_shopee['total_komisi_shopee'] : 0;

    // Gabungkan semua hasil ke dalam array
    $response = [
        "total_purchase_value" => $total_purchase_value,
        "total_qty" => $total_qty,
        "total_affiliate_commision" => $total_affiliate_commision,
        "total_komisi_sum" => $total_komisi_sum,
        "total_komisi_transfer_bank" => $total_komisi_transfer_bank,
        "total_komisi_shopee" => $total_komisi_shopee
    ];

    // Encode menjadi JSON dan tampilkan
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
