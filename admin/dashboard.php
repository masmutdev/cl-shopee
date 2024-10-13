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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}

// Query untuk mendapatkan data dari tabel shopee_datacenter
$sql = "SELECT kode_akun, email, username, status 
        FROM shopee_datacenter 
        GROUP BY kode_akun";
$stmt = $pdo->query($sql);
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php 


try {
    // SQL untuk menjumlahkan semua nilai di kolom purchase_value_rp, qty, dan affiliate_net_commision_rp
    $sqlTotal = "SELECT 
                SUM(purchase_value_rp) AS total_purchase_value, 
                SUM(qty) AS total_qty, 
                SUM(affiliate_net_commission_rp) AS total_affiliate_commission 
            FROM penjualan";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute();

    // Ambil hasilnya
    $resultTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    $total_purchase_value = $resultTotal['total_purchase_value'] ?? 0;
    $total_qty = $resultTotal['total_qty'] ?? 0;
    $total_affiliate_commision = $resultTotal['total_affiliate_commission'] ?? 0;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    // SQL untuk menjumlahkan semua nilai pada kolom total_komisi
    $sql_total_semua = "SELECT SUM(total_komisi) AS total_komisi_sum FROM komisi";

    // SQL untuk menjumlahkan total_komisi dengan metode_pembayaran 'Transfer Bank'
    $sql_transfer_bank = "SELECT SUM(total_komisi) AS total_komisi_transfer_bank 
    FROM komisi 
    WHERE metode_pembayaran = 'Transfer Bank'";

    // SQL untuk menjumlahkan total_komisi dengan metode_pembayaran 'Shopee'
    $sql_shopee = "SELECT SUM(total_komisi) AS total_komisi_shopee 
    FROM komisi 
    WHERE metode_pembayaran = 'ShopeePay'";
    
    // Siapkan dan eksekusi query
    $stmt_total_semua = $pdo->prepare($sql_total_semua);
    $stmt_total_semua->execute();
    $result_total_semua = $stmt_total_semua->fetch(PDO::FETCH_ASSOC);

    // Siapkan dan eksekusi query untuk Transfer Bank
    $stmt_transfer_bank = $pdo->prepare($sql_transfer_bank);
    $stmt_transfer_bank->execute();
    $result_transfer_bank = $stmt_transfer_bank->fetch(PDO::FETCH_ASSOC);
    
    // Siapkan dan eksekusi query untuk Shopee
    $stmt_shopee = $pdo->prepare($sql_shopee);
    $stmt_shopee->execute();
    $result_shopee = $stmt_shopee->fetch(PDO::FETCH_ASSOC);

    // Jika hasilnya tidak null, ambil nilainya, jika tidak, set ke 0
    $total_komisi_sum = $result_total_semua['total_komisi_sum'] !== null ? $result_total_semua['total_komisi_sum'] : 0;
    $total_komisi_transfer_bank = $result_transfer_bank['total_komisi_transfer_bank'] !== null ? $result_transfer_bank['total_komisi_transfer_bank'] : 0;
    $total_komisi_shopee = $result_shopee['total_komisi_shopee'] !== null ? $result_shopee['total_komisi_shopee'] : 0;
    
    // Format Rupiah untuk setiap total komisi
    $total_komisi_sum_formatted = "Rp " . number_format($total_komisi_sum, 0, ',', '.');
    $total_komisi_transfer_bank_formatted = "Rp " . number_format($total_komisi_transfer_bank, 0, ',', '.');
    $total_komisi_shopee_formatted = "Rp " . number_format($total_komisi_shopee, 0, ',', '.');
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <style>
        .status-create {
            background-color: #ff8900;
            color: #721c24;
        }

        .status-aktif {
            background-color: #b3f71a;
            color: #0f5132;
        }

        .status-dibatasi {
            background-color: #999999;
            color: #73ced0;
        }

        .status-nonaktif {
            background-color: #fa2c1d;
            color: #721c24;
        }
    </style>


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
                                <h4 class="page-title">Dashboard</h4>
                                <div class="page-title-right">
                                    <form class="float-sm-end mt-3 mt-sm-0">
                                        <div class="row g-2">
                                        <div class="col-md-auto">
                                                <div class="mb-1 mb-sm-0">
                                                    <input type="text" class="form-control" id="dash-daterange"
                                                        style="min-width: 210px;" />
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class='uil uil-file-alt me-1'></i>Download
                                                        <i class="icon"><span
                                                                data-feather="chevron-down"></span></i></button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item notify-item">
                                                            <i data-feather="mail" class="icon-dual icon-xs me-2"></i>
                                                            <span>Email</span>
                                                        </a>
                                                        <a href="#" class="dropdown-item notify-item">
                                                            <i data-feather="printer"
                                                                class="icon-dual icon-xs me-2"></i>
                                                            <span>Print</span>
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a href="#" class="dropdown-item notify-item">
                                                            <i data-feather="file" class="icon-dual icon-xs me-2"></i>
                                                            <span>Re-Generate</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Omset</span>
                                            <h3 class="mb-0"><?php echo number_format($total_purchase_value, 2, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-revenue-chart" class="apex-charts"></div>
                                            <!-- <span class="text-success fw-bold fs-13">
                                                <i class='uil uil-arrow-up'></i> 10.21%
                                            </span> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Item Terjual</span>
                                            <h3 class="mb-0"><?php echo number_format($total_qty, 0, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-product-sold-chart" class="apex-charts"></div>
                                            <!-- <span class="text-danger fw-bold fs-13">
                                                <i class='uil uil-arrow-down'></i> 5.05%
                                            </span> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <span class="text-muted text-uppercase fs-12 fw-bold">Estimasi Komisi</span>
                                            <h3 class="mb-0"><?php echo number_format($total_affiliate_commision, 2, ',', '.') ?></h3>
                                        </div>
                                        <div class="align-self-center flex-shrink-0">
                                            <div id="today-new-customer-chart" class="apex-charts"></div>
                                            <!-- <span class="text-success fw-bold fs-13">
                                                <i class='uil uil-arrow-up'></i> 25.16%
                                            </span> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- stats + charts -->
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <div class="dropdown float-end">
                                            <a href="#" class="dropdown-toggle arrow-none text-muted"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="uil uil-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <i class="uil uil-refresh me-2"></i>Refresh
                                                </a>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item">
                                                    <i class="uil uil-user-plus me-2"></i>Add New
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <!-- item-->
                                                <a href="javascript:void(0);" class="dropdown-item text-danger">
                                                    <i class="uil uil-exit me-2"></i>Exit
                                                </a>
                                            </div>
                                        </div>

                                        <h5 class="card-title header-title mb-0">Komisi</h5>
                                    </div>

                                    <!-- stat 1 -->
                                    <div class="d-flex p-3 border-bottom">
                                        <div class="flex-grow-1">
                                            <h4 class="mt-0 mb-1 fs-22"><?php echo $total_komisi_sum_formatted ?></h4>
                                            <span class="text-muted">Komisi Dibayarkan</span>
                                        </div>
                                        <i data-feather="users" class="align-self-center icon-dual icon-md"></i>
                                    </div>

                                    <!-- stat 2 -->
                                    <div class="d-flex p-3 border-bottom">
                                        <div class="flex-grow-1">
                                            <h4 class="mt-0 mb-1 fs-22"><?php echo $total_komisi_transfer_bank_formatted ?></h4>
                                            <span class="text-muted">Transfer Bank</span>
                                        </div>
                                        <i data-feather="image" class="align-self-center icon-dual icon-md"></i>
                                    </div>

                                    <!-- stat 3 -->
                                    <div class="d-flex p-3 border-bottom">
                                        <div class="flex-grow-1">
                                            <h4 class="mt-0 mb-1 fs-22"><?php echo $total_komisi_shopee_formatted ?></h4>
                                            <span class="text-muted">Shopeepay</span>
                                        </div>
                                        <i data-feather="shopping-bag" class="align-self-center icon-dual icon-md"></i>
                                    </div>

                                    <a href="" class="p-2 d-block text-start">View All <i
                                            class="uil-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="dropdown float-end">
                                        <a href="#" class="dropdown-toggle arrow-none text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="uil uil-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="hari_ini">
                                                Today
                                            </a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="7_hari">
                                                7 Days
                                            </a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="15_hari">
                                                15 Days
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="30_hari">
                                                1 Month
                                            </a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="6_bulan">
                                                6 Months
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item" data-periode="1_tahun">
                                                1 Year
                                            </a>
                                        </div>

                                    </div>
                                    <h5 class="card-title mb-0 header-title">Ringkasan Pendapatan</h5>

                                    <div id="revenue-chart" class="apex-charts mt-3" dir="ltr"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body pb-0">
                                    <div class="dropdown float-end">
                                        <a href="#" class="dropdown-toggle arrow-none text-muted"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="uil uil-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">
                                                <i class="uil uil-refresh me-2"></i>Refresh
                                            </a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">
                                                <i class="uil uil-user-plus me-2"></i>Tambah Target
                                            </a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">
                                                <i class="uil uil-user-plus me-2"></i>Edit Target
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item text-danger">
                                                <i class="uil uil-exit me-2"></i>Exit
                                            </a>
                                        </div>
                                    </div>

                                    <h5 class="card-title header-title">Target Omset</h5>
                                    <div id="targets-chart" class="apex-charts mt-3" dir="ltr"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- row -->

                    <!-- Accounts -->
                    <div class="container mt-4">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0 header-title">Recent Orders</h5>
                                            <div class="d-flex align-items-center">
                                                <input type="text" id="searchInput"
                                                    class="form-control form-control-sm me-2" placeholder="Search..."
                                                    style="width: 200px;">
                                                <a href="" class="btn btn-primary btn-sm">
                                                    <i class='uil uil-export me-1'></i> Export
                                                </a>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-hover table-nowrap mb-0" id="ordersTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Kode Akun</th>
                                                        <th scope="col">Username</th>
                                                        <th scope="col">Omset</th>
                                                        <th scope="col">Komisi</th>
                                                        <th scope="col">Komisi Tertahan</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($accounts as $account): ?>
                                                        <?php
                                                        // Ambil data omset
                                                        $stmt_omset = $pdo->prepare("
                                                            SELECT price_rp 
                                                            FROM penjualan 
                                                            WHERE username = :username 
                                                            ORDER BY order_time DESC 
                                                            LIMIT 1
                                                        ");
                                                        $stmt_omset->execute(['username' => $account['username']]);
                                                        $omset = $stmt_omset->fetchColumn();

                                                        // Ambil data omset
                                                        $stmt_tgl = $pdo->prepare("
                                                        SELECT order_time 
                                                        FROM penjualan 
                                                        WHERE username = :username 
                                                        ORDER BY order_time DESC 
                                                        LIMIT 1
                                                        ");
                                                        $stmt_tgl->execute(['username' => $account['username']]);
                                                        $tglOmset = $stmt_tgl->fetchColumn();

                                                        $tglOmset = $tglOmset !== false ? $tglOmset : '-';
                                                        $omset = $omset !== false ? $omset : 0;

                                                        // Ambil data komisi
                                                        $stmt_komisi = $pdo->prepare("
                                                            SELECT total_komisi 
                                                            FROM komisi 
                                                            WHERE username = :username 
                                                            ORDER BY waktu_pembayaran DESC 
                                                            LIMIT 1
                                                        ");
                                                        $stmt_komisi->execute(['username' => $account['username']]);
                                                        $komisi = $stmt_komisi->fetchColumn();

                                                        // Ambil data komisi tertahan
                                                        $stmt_komisi_tertahan = $pdo->prepare("
                                                            SELECT total_komisi 
                                                            FROM komisi 
                                                            WHERE username = :username 
                                                            AND status_pembayaran != 'Dibayarkan'
                                                            ORDER BY waktu_pembayaran DESC 
                                                            LIMIT 1
                                                        ");
                                                        $stmt_komisi_tertahan->execute(['username' => $account['username']]);
                                                        $komisi_tertahan = $stmt_komisi_tertahan->fetchColumn();

                                                        // Tentukan kelas status
                                                        $status = htmlspecialchars($account['status']);
                                                        $statusClass = '';

                                                        switch (strtolower($status)) {
                                                            case 'create':
                                                            case 'pending':
                                                                $statusClass = 'status-create';
                                                                break;
                                                            case 'aktif':
                                                                $statusClass = 'status-aktif';
                                                                break;
                                                            case 'dibatasi':
                                                            case 'delivered':
                                                                $statusClass = 'status-dibatasi';
                                                                break;
                                                            case 'nonaktif':
                                                            case 'declined':
                                                                $statusClass = 'status-nonaktif';
                                                                break;
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($account['kode_akun']); ?></td>
                                                            <td><?php echo htmlspecialchars($account['username']); ?></td>
                                                            <td><?php echo htmlspecialchars($omset); ?></td>
                                                            <td><?php echo htmlspecialchars($komisi); ?></td>
                                                            <td><?php echo htmlspecialchars($komisi_tertahan); ?></td>
                                                            <td><span class="badge <?php echo $statusClass; ?> py-1"><?php echo $status; ?></span></td>
                                                            <td><?php echo htmlspecialchars($tglOmset); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <div class="d-flex justify-content-between">
                                                <a href="" class="p-2">View All <i class="uil-arrow-right"></i></a>
                                            </div>
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
    <script src="../assets/libs/jquery/jquery.min.js"></script>
    <script src="../assets/libs/flatpickr/flatpickr.min.js"></script>

    <?php include 'partials/__dashboard-chart.php'?>

    <!-- App js -->
    <script src="../assets/js/app.min.js"></script>

</body>

</html>