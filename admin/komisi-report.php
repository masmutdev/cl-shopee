<?php
require '../config/koneksi.php';
require '../config/functions.php';

// Pengecekan sesi
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    // Jika sesi tidak ada, arahkan ke halaman login
    $_SESSION['error'] = 'Silahkan Login Dahulu !';
    header('Location: ../auth/login.php');
    exit();
}

// Inisialisasi variabel
$startDate = isset($_POST['start_date']) ? DateTime::createFromFormat('d/m/Y', $_POST['start_date'])->format('Y-m-d') : null;
$endDate = isset($_POST['end_date']) ? DateTime::createFromFormat('d/m/Y', $_POST['end_date'])->format('Y-m-d') : null;

// Query untuk mengambil data komisi dengan LEFT JOIN
$sql = "SELECT sd.username, 
               IFNULL(SUM(k.total_komisi), 0) AS total_komisi, 
               IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'transfer bank' THEN k.total_komisi ELSE 0 END), 0) AS transfer_bank, 
               IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'shopeepay' THEN k.total_komisi ELSE 0 END), 0) AS shopeepay
        FROM shopee_datacenter sd
        LEFT JOIN komisi k ON sd.username = k.username 
                            AND k.status_pembayaran = 'dibayarkan' 
                            AND (k.waktu_pembayaran BETWEEN :startDate AND :endDate)
        GROUP BY sd.username";

$params = [];
if ($startDate && $endDate) {
    $params[':startDate'] = $startDate;
    $params[':endDate'] = $endDate;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$komisiData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mendapatkan tanggal awal dan akhir
$dateRangeSql = "SELECT MIN(k.waktu_pembayaran) AS min_date, MAX(k.waktu_pembayaran) AS max_date
                 FROM komisi k
                 WHERE k.status_pembayaran = 'dibayarkan'";

if ($startDate && $endDate) {
    $dateRangeSql .= " AND k.waktu_pembayaran BETWEEN :startDate AND :endDate";
}

$stmtDateRange = $pdo->prepare($dateRangeSql);
if ($startDate && $endDate) {
    $stmtDateRange->bindParam(':startDate', $startDate);
    $stmtDateRange->bindParam(':endDate', $endDate);
}
$stmtDateRange->execute();

if ($dateRangeData = $stmtDateRange->fetch(PDO::FETCH_ASSOC)) {
    $tanggalAwal = $dateRangeData['min_date'];
    $tanggalAkhir = $dateRangeData['max_date'];
}

// Query untuk tanggal paling awal dan paling akhir
$dateRangeSql = "SELECT MIN(k.waktu_pembayaran) AS earliest_date, MAX(k.waktu_pembayaran) AS latest_date
                 FROM komisi k";

$stmtDateRange = $pdo->query($dateRangeSql);
$dateRangeData = $stmtDateRange->fetch(PDO::FETCH_ASSOC);

$earliestDate = $dateRangeData['earliest_date'] ?? null;
$latestDate = $dateRangeData['latest_date'] ?? null;

// Query untuk menghitung total komisi per username dengan filter tanggal
$sql = "SELECT sd.username, 
               IFNULL(SUM(k.total_komisi), 0) AS total_komisi, 
               IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'transfer bank' THEN k.total_komisi ELSE 0 END), 0) AS transfer_bank, 
               IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'shopeepay' THEN k.total_komisi ELSE 0 END), 0) AS shopeepay
        FROM shopee_datacenter sd
        LEFT JOIN komisi k ON sd.username = k.username";

if ($startDate && $endDate) {
    $sql .= " WHERE k.waktu_pembayaran BETWEEN :startDate AND :endDate";
}
$sql .= " GROUP BY sd.username";

$stmt = $pdo->prepare($sql);
if ($startDate && $endDate) {
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
}
$stmt->execute();

$komisiData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk total keseluruhan di card dengan filter tanggal
$cardSql = "SELECT 
                IFNULL(SUM(k.total_komisi), 0) AS total_komisi, 
                IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'transfer bank' THEN k.total_komisi ELSE 0 END), 0) AS total_transfer_bank, 
                IFNULL(SUM(CASE WHEN k.metode_pembayaran = 'shopeepay' THEN k.total_komisi ELSE 0 END), 0) AS total_shopeepay
            FROM komisi k";

if ($startDate && $endDate) {
    $cardSql .= " WHERE k.waktu_pembayaran BETWEEN :startDate AND :endDate";
}

$stmtCard = $pdo->prepare($cardSql);
if ($startDate && $endDate) {
    $stmtCard->bindParam(':startDate', $startDate);
    $stmtCard->bindParam(':endDate', $endDate);
}
$stmtCard->execute();

$cardData = $stmtCard->fetch(PDO::FETCH_ASSOC);
$totalKomisi = $cardData['total_komisi'] ?? 0;
$totalTransferBank = $cardData['total_transfer_bank'] ?? 0;
$totalShopeePay = $cardData['total_shopeepay'] ?? 0;

// Query untuk total komisi tertahan
$totalTertahanSql = "SELECT 
                        IFNULL(SUM(CASE WHEN k.status_pembayaran IN ('Sedang Divalidasi', 'Menunggu Dibayar', 'Tidak Dapat Membayar') THEN k.total_komisi ELSE 0 END), 0) AS total_komisi_tertahan
                    FROM komisi k";

if ($startDate && $endDate) {
    $totalTertahanSql .= " WHERE k.waktu_pembayaran BETWEEN :startDate AND :endDate";
}

$stmtTertahan = $pdo->prepare($totalTertahanSql);
if ($startDate && $endDate) {
    $stmtTertahan->bindParam(':startDate', $startDate);
    $stmtTertahan->bindParam(':endDate', $endDate);
}
$stmtTertahan->execute();

$totalTertahanData = $stmtTertahan->fetch(PDO::FETCH_ASSOC);
$totalKomisiTertahan = $totalTertahanData['total_komisi_tertahan'] ?? 0;

// Query untuk total komisi tertahan per username tanpa filter tanggal
$totalTertahanPerUserSql = "SELECT 
                                k.username,
                                IFNULL(SUM(CASE WHEN k.status_pembayaran IN ('Sedang Divalidasi', 'Menunggu Dibayar', 'Tidak Dapat Membayar') THEN k.total_komisi ELSE 0 END), 0) AS total_komisi_tertahan
                            FROM komisi k
                            GROUP BY k.username";

$stmtTertahanPerUser = $pdo->query($totalTertahanPerUserSql);
$totalTertahanPerUserData = $stmtTertahanPerUser->fetchAll(PDO::FETCH_ASSOC);

// Gabungkan data berdasarkan username
$combinedData = [];
foreach ($komisiData as $data) {
    $combinedData[$data['username']] = [
        'total_komisi' => $data['total_komisi'],
        'transfer_bank' => $data['transfer_bank'],
        'shopeepay' => $data['shopeepay'],
        'komisi_tertahan' => 0 // Default value, will be updated later
    ];
}

foreach ($totalTertahanPerUserData as $tertahan) {
    if (isset($combinedData[$tertahan['username']])) {
        $combinedData[$tertahan['username']]['komisi_tertahan'] = $tertahan['total_komisi_tertahan'];
    }
}

// Hitung total komisi tertahan dari nilai-nilai di kolom 'komisi_tertahan'
$totalKomisiTertahan = array_sum(array_column($combinedData, 'komisi_tertahan'));

// Tutup koneksi
$pdo = null;
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Dashboard | Affiliate Dashboard by AffiateX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico">

    <!-- plugins -->
    <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

    <link href="../assets/css/bootstrap-dark.min.css" rel="stylesheet" type="text/css" id="bs-dark-stylesheet" disabled />
    <link href="../assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="app-dark-stylesheet" disabled />

    <!-- icons -->
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />


    <!-- CSS for Bootstrap and Flatpickr -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- App CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
    <link href="../assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />
    <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- JS for Bootstrap and Flatpickr -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function () {
            // Inisialisasi Flatpickr untuk startDate dan endDate
            flatpickr("#startDate", {
                dateFormat: "Y-m-d",
                defaultDate: "<?= htmlspecialchars($startDate) ?>"
            });
            flatpickr("#endDate", {
                dateFormat: "Y-m-d",
                defaultDate: "<?= htmlspecialchars($endDate) ?>"
            });

            // Filter table berdasarkan input
            document.getElementById('searchInput').addEventListener('input', function () {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('#ordersTable tbody tr');

                rows.forEach(function (row) {
                    var cells = row.querySelectorAll('td');
                    var isMatch = Array.from(cells).some(function (cell) {
                        return cell.textContent.toLowerCase().includes(searchTerm);
                    });
                    row.style.display = isMatch ? '' : 'none';
                });
            });
        });
    </script>


    <style>
        /* Ensure all elements have a uniform height */
        .form-control,
        .btn {
            height: 38px;
            /* Adjust this value to match your desired height */
        }

        .form-inline .form-group {
            margin-bottom: 0;
        }

        .btn {
            height: 32px;
            /* Adjust this value as needed */
            line-height: 24px;
            /* Center text vertically */
            padding: 0 12px;
            /* Add horizontal padding */
        }

        .form-inline input[type="text"] {
            font-size: 14px;
            width: 120px;
        }

        .form-inline .form-control-sm {
            height: 24px;
            /* Make sure input fields are the same height */
        }

        .form-inline .btn {
            margin-left: 10px;
        }

        /* Set width for date inputs to match search input */
        .form-inline .form-control {
            text-align: center;
        }

        .form-inline .form-control-sm {
            width: 200px;
        }

        .form-inline .ms-auto {
            margin-left: auto;
        }
    </style>

</head>

<body class="loading"
    data-layout='{"mode": "light", "width": "fluid", "menuPosition": "fixed", "sidebar": { "color": "light", "size": "default", "showuser": false}, "topbar": {"color": "light"}, "showRightSidebarOnPageLoad": true}'>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <div class="navbar-custom">
            <div class="container-fluid">
                <ul class="list-unstyled topnav-menu float-end mb-0">

                    <li class="dropdown d-inline-block d-lg-none">
                        <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            <i data-feather="search"></i>
                        </a>
                        <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                            <form class="p-3">
                                <input type="text" class="form-control" placeholder="Search ..."
                                    aria-label="search here">
                            </form>
                        </div>
                    </li>

                    <li class="dropdown notification-list topbar-dropdown">
                        <a class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <i data-feather="bell"></i>
                            <span class="badge bg-danger rounded-circle noti-icon-badge">6</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-lg">

                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5 class="m-0">
                                    <span class="float-end">
                                        <a href="" class="text-dark"><small>Clear All</small></a>
                                    </span>Notification
                                </h5>
                            </div>

                            <div class="noti-scroll" data-simplebar>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                    <div class="notify-icon bg-primary"><i class="uil uil-user-plus"></i></div>
                                    <p class="notify-details">New user registered.<small class="text-muted">5 hours
                                            ago</small>
                                    </p>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                    <div class="notify-icon">
                                        <img src="../assets/images/users/avatar-1.jpg" class="img-fluid rounded-circle"
                                            alt="" />
                                    </div>
                                    <p class="notify-details">Karen Robinson</p>
                                    <p class="text-muted mb-0 user-msg">
                                        <small>Wow ! this admin looks good and awesome design</small>
                                    </p>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                    <div class="notify-icon">
                                        <img src="../assets/images/users/avatar-2.jpg" class="img-fluid rounded-circle"
                                            alt="" />
                                    </div>
                                    <p class="notify-details">Cristina Pride</p>
                                    <p class="text-muted mb-0 user-msg">
                                        <small>Hi, How are you? What about our next meeting</small>
                                    </p>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom active">
                                    <div class="notify-icon bg-success"><i class="uil uil-comment-message"></i> </div>
                                    <p class="notify-details">
                                        Jaclyn Brunswick commented on Dashboard<small class="text-muted">1 min
                                            ago</small>
                                    </p>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                    <div class="notify-icon bg-danger"><i class="uil uil-comment-message"></i></div>
                                    <p class="notify-details">
                                        Caleb Flakelar commented on Admin<small class="text-muted">4 days ago</small>
                                    </p>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="notify-icon bg-primary">
                                        <i class="uil uil-heart"></i>
                                    </div>
                                    <p class="notify-details">
                                        Carlos Crouch liked <b>Admin</b> <small class="text-muted">13 days ago</small>
                                    </p>
                                </a>
                            </div>

                            <!-- All-->
                            <a href="javascript:void(0);"
                                class="dropdown-item text-center text-primary notify-item notify-all">
                                View all <i class="fe-arrow-right"></i>
                            </a>

                        </div>
                    </li>

                    <li class="dropdown notification-list topbar-dropdown">
                        <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-user rounded-circle" width="38" height="38"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <circle cx="12" cy="7" r="4" />
                                <path d="M5.5 21c0 -1.5 3.5 -4 6.5 -4s6.5 2.5 6.5 4" />
                            </svg>
                            <span class="pro-user-name ms-1">
                                Nik Patel <i class="uil uil-angle-down"></i>
                            </span>
                        </a>


                        <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Welcome !</h6>
                            </div>

                            <a href="pages-profile.html" class="dropdown-item notify-item">
                                <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>My Account</span>
                            </a>

                            <div class="dropdown-divider"></div>

                            <a href="../auth/logout.php" class="dropdown-item notify-item">
                                <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Logout</span>
                            </a>

                        </div>
                    </li>


                </ul>

                <!-- LOGO -->
                <div class="logo-box">
                    <a href="index.html" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="../assets/images/logo-sm.png" alt="" height="24">
                            <!-- <span class="logo-lg-text-light">Shreyu</span> -->
                        </span>
                        <span class="logo-lg">
                            <img src="../assets/images/logo-dark.png" alt="" height="24">
                            <!-- <span class="logo-lg-text-light">S</span> -->
                        </span>
                    </a>

                    <a href="index.html" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="../assets/images/logo-sm.png" alt="" height="24">
                        </span>
                        <span class="logo-lg">
                            <img src="../assets/images/logo-light.png" alt="" height="24">
                        </span>
                    </a>
                </div>

                <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                    <li>
                        <button class="button-menu-mobile">
                            <i data-feather="menu"></i>
                        </button>
                    </li>

                    <li>
                        <!-- Mobile menu toggle (Horizontal Layout)-->
                        <a class="navbar-toggle nav-link" data-bs-toggle="collapse"
                            data-bs-target="#topnav-menu-content">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                        <!-- End mobile menu toggle-->
                    </li>

                    <li class="dropdown d-none d-xl-block">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                            aria-haspopup="false" aria-expanded="false">
                            Create New
                            <i class="uil uil-angle-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <!-- item-->
                            <a href="add-account.php" class="dropdown-item">
                                <i class="uil  uil-user-square me-1"></i><span>Master Akun</span>
                            </a>

                            <!-- item-->
                            <a href="add-studio.php" class="dropdown-item">
                                <i class="uil  uil-building me-1"></i><span>Master Studio</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="uil uil-user-plus me-1"></i><span>Data Karyawan</span>
                            </a>

                            <div class="dropdown-divider"></div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="uil uil-question-circle me-1"></i><span>Help & Support</span>
                            </a>

                        </div>
                    </li>

                </ul>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- end Topbar -->

        <!-- ========== Left Sidebar Start ========== -->
        <div class="left-side-menu">

            <div class="h-100" data-simplebar>

                <!--- Sidemenu -->
                <div id="sidebar-menu">

                    <ul id="side-menu">

                        <!-- <li class="menu-title">Navigation</li> -->

                        <li>
                            <a href="index.php">
                                <i data-feather="home"></i>
                                <span> Dashboard </span>
                            </a>
                        </li>


                        <li class="menu-title mt-2">Report</li>

                        <li>
                            <a href="#">
                                <i data-feather="bar-chart-2"></i>
                                <span> Sales </span>
                            </a>
                        </li>

                        <li>
                            <a href="komisi-report.php">
                                <i data-feather="dollar-sign"></i>
                                <span> Commision </span>
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <i data-feather="user-check"></i>
                                <span> Staff </span>
                            </a>
                        </li>

                        <li class="menu-title mt-2">Database</li>

                        <li>
                            <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i data-feather="at-sign"></i>
                                <span> Master Akun </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarProjects">
                                <ul class="nav-second-level">
                                    <li><a href="add-account.php">Tambah Akun</a></li>
                                    <li><a href="account-list.php">Edit Akun</a></li>
                                </ul>
                            </div>
                        </li>

                        <li>
                            <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i data-feather="radio"></i>
                                <span> Studio </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarProjects">
                                <ul class="nav-second-level">
                                    <li><a href="add-studio.php">Tambah Studio</a></li>
                                    <li><a href="studio-list.php">Edit Studio</a></li>
                                </ul>
                            </div>
                        </li>

                        <li>
                            <a href="#sidebarProjects" data-bs-toggle="collapse">
                                <i data-feather="users"></i>
                                <span> Master Staff </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarProjects">
                                <ul class="nav-second-level">
                                    <li><a href="project-list.html">Tambah Staff</a></li>
                                    <li><a href="project-detail.html">Edit Staff</a></li>
                                </ul>
                            </div>
                        </li>

                        <li class="menu-title mt-2"></li>

                        <li>
                            <a href="index.php">
                                <i data-feather="settings"></i>
                                <span> Settings </span>
                            </a>
                        </li>

                        </li>
                    </ul>

                </div>
                <!-- End Sidebar -->

                <div class="clearfix"></div>

            </div>
            <!-- Sidebar -left -->

        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Commision Report</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->


                    <!-- charts -->
                    <div class="row">
                        <div class="col-md-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Total Komisi</span>
                                            <h3 class="mb-0"><?= number_format($totalKomisi, 0, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-revenue-chart" class="apex-charts"></div>
                                            <span class="text-success fw-bold fs-13">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Transfer Bank</span>
                                            <h3 class="mb-0"><?= number_format($totalTransferBank, 0, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-product-sold-chart" class="apex-charts">
                                            </div>
                                            <span class="text-danger fw-bold fs-13">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Shopeepay</span>
                                            <h3 class="mb-0"><?= number_format($totalShopeePay, 0, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-new-customer-chart" class="apex-charts">
                                            </div>
                                            <span class="text-success fw-bold fs-13"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Tertahan</span>
                                            <h3 class="mb-0"><?= number_format($totalKomisiTertahan, 0, ',', '.') ?>
                                            </h3>
                                            </h3>
                                        </div>
                                        <div class=" align-self-center flex-shrink-0">
                                            <div id="today-new-visitors-chart" class="apex-charts">
                                            </div>
                                            <span class="text-danger fw-bold fs-13">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Cards -->


                    <!-- Accounts -->
                    <div class="container mt-4">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="GET"
                                            class="form-inline d-flex justify-content-between align-items-center">
                                            <!-- Filter Tanggal di sebelah kiri -->
                                            <div class="form-group mb-0 me-2">
                                                <input type="text" id="startDate" name="startDate"
                                                    class="form-control form-control-sm" placeholder="YYYY-MM-DD"
                                                    value="<?= htmlspecialchars($startDate) ?>">
                                            </div>
                                            <label for="endDate" class="mb-0 me-2">Hingga</label>
                                            <div class="form-group mb-0 me-2">
                                                <input type="text" id="endDate" name="endDate"
                                                    class="form-control form-control-sm" placeholder="YYYY-MM-DD"
                                                    value="<?= htmlspecialchars($endDate) ?>">
                                            </div>
                                            <button type="submit" class="btn btn-primary mb-0">Filter</button>
                                            <!-- Kolom search dan tombol export di sebelah kanan -->
                                            <div class="d-flex align-items-center ms-auto">
                                                <input type="text" id="searchInput"
                                                    class="form-control form-control-sm me-2" placeholder="Search..."
                                                    style="width: 200px;">
                                                <button href="export.php" class="btn btn-primary mb-0"><i
                                                        class='uil uil-export me-1'></i>Export</button>
                                            </div>

                                            <div class="table-responsive mt-4">
                                                <table class="table table-hover table-nowrap mb-0" id="ordersTable">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Username</th>
                                                            <th scope="col">Tanggal</th>
                                                            <th scope="col">Total Komisi</th>
                                                            <th scope="col">Transfer Bank</th>
                                                            <th scope="col">Shopeepay</th>
                                                            <th scope="col">Komisi Tertahan</th>
                                                            <th scope="col">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($combinedData as $username => $data): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($username) ?></td>
                                                                <td><?php echo ($tanggalAwal && $tanggalAkhir) ? htmlspecialchars(DateTime::createFromFormat('Y-m-d', $tanggalAwal)->format('d-m-Y') . ' - ' . DateTime::createFromFormat('Y-m-d', $tanggalAkhir)->format('d-m-Y')) : '-'; ?>
                                                                </td>
                                                                <td><?= number_format($data['total_komisi'], 0, ',', '.') ?>
                                                                </td>
                                                                <td><?= number_format($data['transfer_bank'], 0, ',', '.') ?>
                                                                </td>
                                                                <td><?= number_format($data['shopeepay'], 0, ',', '.') ?>
                                                                </td>
                                                                <td><?= number_format($data['komisi_tertahan'], 0, ',', '.') ?>
                                                                </td>
                                                                <td>
                                                                    <a
                                                                        href="edit-komisi.php?username=<?= urlencode($username) ?>">
                                                                        <i
                                                                            class="uil uil-pen me-2 text-muted vertical-middle"></i>
                                                                    </a>
                                                                    <a href="reset-komisi.php?username=<?= urlencode($username) ?>"
                                                                        onclick="return confirm('Are you sure you want to delete this account?');">
                                                                        <i
                                                                            class="uil uil-trash-alt me-2 text-muted vertical-middle"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div> <!-- end table-responsive-->
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                    </div>

                    <script>
                        document.getElementById('searchInput').addEventListener('keyup', function () {
                            var input = this.value.toLowerCase();
                            var rows = document.querySelectorAll('#ordersTable tbody tr');

                            rows.forEach(function (row) {
                                var cells = row.querySelectorAll('td');
                                var match = Array.from(cells).some(function (cell) {
                                    return cell.textContent.toLowerCase().includes(input);
                                });

                                row.style.display = match ? '' : 'none';
                            });
                        });
                    </script>

                    <!-- end row -->

                </div> <!-- container -->

            </div> <!-- content -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>document.write(new Date().getFullYear())</script> &copy; Shopee Affiliate Dashboard
                            by <a href="">affiliateX</a>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end footer-links d-none d-sm-block">
                                <a href="javascript:void(0);">About Us</a>
                                <a href="javascript:void(0);">Help</a>
                                <a href="javascript:void(0);">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../assets/js/vendor.min.js"></script>

    <!-- optional plugins -->
    <script src="../assets/libs/moment/min/moment.min.js"></script>
    <script src="../assets/libs/apexcharts/apexcharts.min.js"></script>
    <script src="../assets/libs/flatpickr/flatpickr.min.js"></script>

    <!-- page js -->
    <script src="../assets/js/pages/dashboard.init.js"></script>

    <!-- App js -->
    <script src="../assets/js/app.min.js"></script>

</body>

</html>