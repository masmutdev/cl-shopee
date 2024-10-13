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

// Ambil username dari GET request dengan PDO
$username = htmlspecialchars($_GET['username'] ?? '', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Error saat meng-upload file.");
    }

    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];

    // Cek ekstensi file CSV
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension !== 'csv') {
        die("File yang diupload bukan CSV.");
    }

    // Baca file CSV
    if (($handle = fopen($fileTmpPath, 'r')) !== false) {
        // Skip header
        fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            // Asumsikan kolom CSV sesuai dengan urutan yang Anda berikan
            list($order_id, $order_status, $conversion_id, $order_time, $complete_time, $click_time, 
                 $shop_name, $shop_id, $shop_type, $item_id, $item_name, $model_id, $product_type, 
                 $promotion_id, $l1_global_category, $l2_global_category, $l3_global_category, 
                 $price_rp, $qty, $seller_campaign_type, $campaign_partner, $purchase_value_rp, 
                 $refund_amount_rp, $item_shopee_commission_rate, $item_shopee_commission_rp, 
                 $item_seller_commission_rate, $item_seller_commission_rp, $item_total_commission_rp, 
                 $order_shopee_commission_rp, $order_seller_commission_rp, $total_order_commission_rp, 
                 $linked_mcn_name, $mcn_contract_id, $mcn_management_fee_rate, $mcn_management_fee_rp, 
                 $affiliate_agreement_fee_rate, $affiliate_net_commission_rp, $affiliate_item_status, 
                 $item_note, $attribution_type, $buyer_status, $tag_link1, $tag_link2, $tag_link3, 
                 $tag_link4, $tag_link5, $channel) = $data;

            // Periksa apakah order_id sudah ada
            $checkQuery = "SELECT COUNT(*) AS count FROM penjualan WHERE order_id = :order_id";
            $stmt = $pdo->prepare($checkQuery);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                // Jika ada, update data
                $updateQuery = "UPDATE penjualan SET 
                                    order_status=:order_status, conversion_id=:conversion_id, order_time=:order_time, 
                                    complete_time=:complete_time, click_time=:click_time, shop_name=:shop_name, 
                                    shop_id=:shop_id, shop_type=:shop_type, item_id=:item_id, item_name=:item_name, 
                                    model_id=:model_id, product_type=:product_type, promotion_id=:promotion_id, 
                                    l1_global_category=:l1_global_category, l2_global_category=:l2_global_category, 
                                    l3_global_category=:l3_global_category, price_rp=:price_rp, qty=:qty, 
                                    seller_campaign_type=:seller_campaign_type, campaign_partner=:campaign_partner, 
                                    purchase_value_rp=:purchase_value_rp, refund_amount_rp=:refund_amount_rp, 
                                    item_shopee_commission_rate=:item_shopee_commission_rate, 
                                    item_shopee_commission_rp=:item_shopee_commission_rp, 
                                    item_seller_commission_rate=:item_seller_commission_rate, 
                                    item_seller_commission_rp=:item_seller_commission_rp, 
                                    item_total_commission_rp=:item_total_commission_rp, 
                                    order_shopee_commission_rp=:order_shopee_commission_rp, 
                                    order_seller_commission_rp=:order_seller_commission_rp, 
                                    total_order_commission_rp=:total_order_commission_rp, linked_mcn_name=:linked_mcn_name, 
                                    mcn_contract_id=:mcn_contract_id, mcn_management_fee_rate=:mcn_management_fee_rate, 
                                    mcn_management_fee_rp=:mcn_management_fee_rp, 
                                    affiliate_agreement_fee_rate=:affiliate_agreement_fee_rate, 
                                    affiliate_net_commission_rp=:affiliate_net_commission_rp, 
                                    affiliate_item_status=:affiliate_item_status, item_note=:item_note, 
                                    attribution_type=:attribution_type, buyer_status=:buyer_status, 
                                    tag_link1=:tag_link1, tag_link2=:tag_link2, tag_link3=:tag_link3, 
                                    tag_link4=:tag_link4, tag_link5=:tag_link5, channel=:channel 
                                WHERE order_id=:order_id";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->bindParam(':order_status', $order_status);
                $stmt->bindParam(':conversion_id', $conversion_id);
                $stmt->bindParam(':order_time', $order_time);
                $stmt->bindParam(':complete_time', $complete_time);
                $stmt->bindParam(':click_time', $click_time);
                $stmt->bindParam(':shop_name', $shop_name);
                $stmt->bindParam(':shop_id', $shop_id);
                $stmt->bindParam(':shop_type', $shop_type);
                $stmt->bindParam(':item_id', $item_id);
                $stmt->bindParam(':item_name', $item_name);
                $stmt->bindParam(':model_id', $model_id);
                $stmt->bindParam(':product_type', $product_type);
                $stmt->bindParam(':promotion_id', $promotion_id);
                $stmt->bindParam(':l1_global_category', $l1_global_category);
                $stmt->bindParam(':l2_global_category', $l2_global_category);
                $stmt->bindParam(':l3_global_category', $l3_global_category);
                $stmt->bindParam(':price_rp', $price_rp);
                $stmt->bindParam(':qty', $qty);
                $stmt->bindParam(':seller_campaign_type', $seller_campaign_type);
                $stmt->bindParam(':campaign_partner', $campaign_partner);
                $stmt->bindParam(':purchase_value_rp', $purchase_value_rp);
                $stmt->bindParam(':refund_amount_rp', $refund_amount_rp);
                $stmt->bindParam(':item_shopee_commission_rate', $item_shopee_commission_rate);
                $stmt->bindParam(':item_shopee_commission_rp', $item_shopee_commission_rp);
                $stmt->bindParam(':item_seller_commission_rate', $item_seller_commission_rate);
                $stmt->bindParam(':item_seller_commission_rp', $item_seller_commission_rp);
                $stmt->bindParam(':item_total_commission_rp', $item_total_commission_rp);
                $stmt->bindParam(':order_shopee_commission_rp', $order_shopee_commission_rp);
                $stmt->bindParam(':order_seller_commission_rp', $order_seller_commission_rp);
                $stmt->bindParam(':total_order_commission_rp', $total_order_commission_rp);
                $stmt->bindParam(':linked_mcn_name', $linked_mcn_name);
                $stmt->bindParam(':mcn_contract_id', $mcn_contract_id);
                $stmt->bindParam(':mcn_management_fee_rate', $mcn_management_fee_rate);
                $stmt->bindParam(':mcn_management_fee_rp', $mcn_management_fee_rp);
                $stmt->bindParam(':affiliate_agreement_fee_rate', $affiliate_agreement_fee_rate);
                $stmt->bindParam(':affiliate_net_commission_rp', $affiliate_net_commission_rp);
                $stmt->bindParam(':affiliate_item_status', $affiliate_item_status);
                $stmt->bindParam(':item_note', $item_note);
                $stmt->bindParam(':attribution_type', $attribution_type);
                $stmt->bindParam(':buyer_status', $buyer_status);
                $stmt->bindParam(':tag_link1', $tag_link1);
                $stmt->bindParam(':tag_link2', $tag_link2);
                $stmt->bindParam(':tag_link3', $tag_link3);
                $stmt->bindParam(':tag_link4', $tag_link4);
                $stmt->bindParam(':tag_link5', $tag_link5);
                $stmt->bindParam(':channel', $channel);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->execute();
            } else {
                // Jika tidak ada, insert data baru
                $insertQuery = "INSERT INTO penjualan (order_id, order_status, conversion_id, order_time, complete_time, 
                                    click_time, shop_name, shop_id, shop_type, item_id, item_name, model_id, 
                                    product_type, promotion_id, l1_global_category, l2_global_category, 
                                    l3_global_category, price_rp, qty, seller_campaign_type, campaign_partner, 
                                    purchase_value_rp, refund_amount_rp, item_shopee_commission_rate, 
                                    item_shopee_commission_rp, item_seller_commission_rate, item_seller_commission_rp, 
                                    item_total_commission_rp, order_shopee_commission_rp, order_seller_commission_rp, 
                                    total_order_commission_rp, linked_mcn_name, mcn_contract_id, 
                                    mcn_management_fee_rate, mcn_management_fee_rp, affiliate_agreement_fee_rate, 
                                    affiliate_net_commission_rp, affiliate_item_status, item_note, attribution_type, 
                                    buyer_status, tag_link1, tag_link2, tag_link3, tag_link4, tag_link5, channel, username)
                                VALUES (:order_id, :order_status, :conversion_id, :order_time, :complete_time, 
                                        :click_time, :shop_name, :shop_id, :shop_type, :item_id, :item_name, 
                                        :model_id, :product_type, :promotion_id, :l1_global_category, 
                                        :l2_global_category, :l3_global_category, :price_rp, :qty, 
                                        :seller_campaign_type, :campaign_partner, :purchase_value_rp, 
                                        :refund_amount_rp, :item_shopee_commission_rate, :item_shopee_commission_rp, 
                                        :item_seller_commission_rate, :item_seller_commission_rp, 
                                        :item_total_commission_rp, :order_shopee_commission_rp, 
                                        :order_seller_commission_rp, :total_order_commission_rp, :linked_mcn_name, 
                                        :mcn_contract_id, :mcn_management_fee_rate, :mcn_management_fee_rp, 
                                        :affiliate_agreement_fee_rate, :affiliate_net_commission_rp, 
                                        :affiliate_item_status, :item_note, :attribution_type, :buyer_status, 
                                        :tag_link1, :tag_link2, :tag_link3, :tag_link4, :tag_link5, :channel, :username)";
                $stmt = $pdo->prepare($insertQuery);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':order_status', $order_status);
                $stmt->bindParam(':conversion_id', $conversion_id);
                $stmt->bindParam(':order_time', $order_time);
                $stmt->bindParam(':complete_time', $complete_time);
                $stmt->bindParam(':click_time', $click_time);
                $stmt->bindParam(':shop_name', $shop_name);
                $stmt->bindParam(':shop_id', $shop_id);
                $stmt->bindParam(':shop_type', $shop_type);
                $stmt->bindParam(':item_id', $item_id);
                $stmt->bindParam(':item_name', $item_name);
                $stmt->bindParam(':model_id', $model_id);
                $stmt->bindParam(':product_type', $product_type);
                $stmt->bindParam(':promotion_id', $promotion_id);
                $stmt->bindParam(':l1_global_category', $l1_global_category);
                $stmt->bindParam(':l2_global_category', $l2_global_category);
                $stmt->bindParam(':l3_global_category', $l3_global_category);
                $stmt->bindParam(':price_rp', $price_rp);
                $stmt->bindParam(':qty', $qty);
                $stmt->bindParam(':seller_campaign_type', $seller_campaign_type);
                $stmt->bindParam(':campaign_partner', $campaign_partner);
                $stmt->bindParam(':purchase_value_rp', $purchase_value_rp);
                $stmt->bindParam(':refund_amount_rp', $refund_amount_rp);
                $stmt->bindParam(':item_shopee_commission_rate', $item_shopee_commission_rate);
                $stmt->bindParam(':item_shopee_commission_rp', $item_shopee_commission_rp);
                $stmt->bindParam(':item_seller_commission_rate', $item_seller_commission_rate);
                $stmt->bindParam(':item_seller_commission_rp', $item_seller_commission_rp);
                $stmt->bindParam(':item_total_commission_rp', $item_total_commission_rp);
                $stmt->bindParam(':order_shopee_commission_rp', $order_shopee_commission_rp);
                $stmt->bindParam(':order_seller_commission_rp', $order_seller_commission_rp);
                $stmt->bindParam(':total_order_commission_rp', $total_order_commission_rp);
                $stmt->bindParam(':linked_mcn_name', $linked_mcn_name);
                $stmt->bindParam(':mcn_contract_id', $mcn_contract_id);
                $stmt->bindParam(':mcn_management_fee_rate', $mcn_management_fee_rate);
                $stmt->bindParam(':mcn_management_fee_rp', $mcn_management_fee_rp);
                $stmt->bindParam(':affiliate_agreement_fee_rate', $affiliate_agreement_fee_rate);
                $stmt->bindParam(':affiliate_net_commission_rp', $affiliate_net_commission_rp);
                $stmt->bindParam(':affiliate_item_status', $affiliate_item_status);
                $stmt->bindParam(':item_note', $item_note);
                $stmt->bindParam(':attribution_type', $attribution_type);
                $stmt->bindParam(':buyer_status', $buyer_status);
                $stmt->bindParam(':tag_link1', $tag_link1);
                $stmt->bindParam(':tag_link2', $tag_link2);
                $stmt->bindParam(':tag_link3', $tag_link3);
                $stmt->bindParam(':tag_link4', $tag_link4);
                $stmt->bindParam(':tag_link5', $tag_link5);
                $stmt->bindParam(':channel', $channel);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
            }
        }
        fclose($handle);
        echo "File berhasil di-upload dan diproses.";
    } else {
        die("Error saat membaca file CSV.");
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">Upload file CSV:</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
          </form>';
}
?>