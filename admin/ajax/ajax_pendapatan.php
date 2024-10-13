<?php
require '../../config/koneksi.php';

// Inisialisasi array untuk menyimpan hasil
$total_komisi_values = [];
$tanggal_values = [];

// Ambil periode waktu dari permintaan AJAX
$periode_waktu = $_GET['periode'] ?? '30_hari';

// Dapatkan tanggal hari ini
$today = new DateTime();
$start_date = clone $today;

// Tentukan rentang waktu berdasarkan periode yang dipilih
switch ($periode_waktu) {
    case '7_hari':
        $days = 7;
        break;
    case '15_hari':
        $days = 15;
        break;
    case '30_hari':
        $days = 30;
        break;
    case '6_bulan':
        $months = 6;
        break;
    case '1_tahun':
        $months = 12;
        break;
    case 'hari_ini':
    default:
        $days = 1;
        break;
}

// Cek apakah start_date dan end_date disediakan
$start_date_param = $_GET['start_date'] ?? null;
$end_date_param = $_GET['end_date'] ?? null;

if ($start_date_param && $end_date_param) {
    // Jika start_date dan end_date disediakan
    $start_date = new DateTime($start_date_param);
    $end_date = new DateTime($end_date_param);
    $end_date->modify('+1 day'); // Tambahkan satu hari untuk mencakup tanggal akhir
} else {
    // Jika periode waktu harian atau bulanan
    if (isset($days)) {
        // Perulangan harian
        for ($i = 0; $i < $days; $i++) {
            $date = clone $today;
            $date->modify("-$i days");
            $formatted_date = $date->format('Y-m-d');

            // SQL untuk mendapatkan total komisi per hari
            $sql = "SELECT 
                        SUM(total_komisi) AS total_komisi,
                        DATE(waktu_komisi_dibayarkan) AS tanggal 
                    FROM komisi 
                    WHERE DATE(waktu_komisi_dibayarkan) = :tanggal
                    AND status_pembayaran != 'Sedang Divalidasi'";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':tanggal', $formatted_date);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Masukkan nilai total_komisi dan tanggal ke dalam array, jika tidak ada data, masukkan 0
            $total_komisi_values[] = $result['total_komisi'] !== null ? $result['total_komisi'] : 0;
            $tanggal_values[] = $result['tanggal'] !== null ? $result['tanggal'] : $formatted_date;
        }
    } elseif (isset($months)) {
        // Perulangan bulanan
        for ($i = 0; $i < $months; $i++) {
            $start_of_month = clone $today;
            $start_of_month->modify("first day of -$i month");
            $end_of_month = clone $start_of_month;
            $end_of_month->modify("last day of this month");

            // SQL untuk mendapatkan total komisi per bulan
            $sql = "SELECT 
                        SUM(total_komisi) AS total_komisi,
                        MONTHNAME(waktu_komisi_dibayarkan) AS bulan 
                    FROM komisi 
                    WHERE DATE(waktu_komisi_dibayarkan) BETWEEN :start_of_month AND :end_of_month
                    AND status_pembayaran != 'Sedang Divalidasi'";

            $stmt = $pdo->prepare($sql);
            $start_of_month_formatted = $start_of_month->format('Y-m-d');
            $end_of_month_formatted = $end_of_month->format('Y-m-d');

            $stmt->bindParam(':start_of_month', $start_of_month_formatted);
            $stmt->bindParam(':end_of_month', $end_of_month_formatted);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Masukkan nilai total_komisi dan nama bulan ke dalam array, jika tidak ada data, masukkan 0
            $total_komisi_values[] = $result['total_komisi'] !== null ? $result['total_komisi'] : 0;
            $tanggal_values[] = $result['bulan'] !== null ? $result['bulan'] : $start_of_month->format('F');
        }
    }
}

// Jika start_date dan end_date ditentukan, lakukan query untuk periode tersebut
if (isset($start_date) && isset($end_date)) {
    // Menambahkan semua tanggal dari start_date hingga end_date ke dalam tanggal_values
    $current_date = clone $start_date;
    while ($current_date < $end_date) {
        $formatted_date = $current_date->format('Y-m-d');
        $tanggal_values[] = $formatted_date;

        // SQL untuk mendapatkan total komisi untuk tanggal tersebut
        $sql = "SELECT 
                    SUM(total_komisi) AS total_komisi
                FROM komisi 
                WHERE DATE(waktu_komisi_dibayarkan) = :tanggal
                AND status_pembayaran != 'Sedang Divalidasi'";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tanggal', $formatted_date);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Masukkan nilai total_komisi ke dalam array, jika tidak ada data, masukkan 0
        $total_komisi_values[] = $result['total_komisi'] !== null ? $result['total_komisi'] : 0;

        // Pindah ke tanggal berikutnya
        $current_date->modify('+1 day');
    }
}

// Jika tidak ada data pada total_komisi_values, set semua ke 0
if (empty($total_komisi_values)) {
    $total_komisi_values = array_fill(0, count($tanggal_values), 0);
}

// Kirim data dalam format JSON
echo json_encode([
    'total_komisi_values' => $total_komisi_values, // Tidak perlu dibalik karena kita sudah mengisi dengan urutan yang benar
    'tanggal_values' => $tanggal_values // Tidak perlu dibalik karena kita sudah mengisi dengan urutan yang benar
]);
?>
