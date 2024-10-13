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

$username = $_GET['username'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $username) {
    $filePath = $_FILES['komisi_csv']['tmp_name'];

    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        fgetcsv($handle); // Melewati header

        // Siapkan koneksi PDO
        $pdo->beginTransaction();

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Convert dates with error checking
            $waktu_komisi_dibayarkan = $data[0] ? (DateTime::createFromFormat('d-m-Y', $data[0]) ? DateTime::createFromFormat('d-m-Y', $data[0])->format('Y-m-d') : null) : null;
            $periode_validasi = $data[1] ? (DateTime::createFromFormat('d-m-Y', $data[1]) ? DateTime::createFromFormat('d-m-Y', $data[1])->format('Y-m-d') : null) : null;
            $waktu_pembayaran = $data[5] ? (DateTime::createFromFormat('d-m-Y', $data[5]) ? DateTime::createFromFormat('d-m-Y', $data[5])->format('Y-m-d') : null) : null;

            // Handle null values for other columns
            $total_komisi = $data[2] ?? '0.00';
            $metode_pembayaran = $data[3] ?? 'Unknown';
            $status_pembayaran = $data[4] ?? 'Unknown';

            // Prepare SQL query to check for existing records with the same 'periode_validasi'
            $sqlCheck = "SELECT status_pembayaran FROM komisi WHERE username = :username AND periode_validasi = :periode_validasi";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->bindParam(':username', $username);
            $stmtCheck->bindParam(':periode_validasi', $periode_validasi);
            $stmtCheck->execute();
            $existing_status_pembayaran = $stmtCheck->fetchColumn();

            if ($existing_status_pembayaran === false) {
                // Record does not exist, so insert
                $sqlInsert = "INSERT INTO komisi (username, waktu_komisi_dibayarkan, periode_validasi, total_komisi, metode_pembayaran, status_pembayaran, waktu_pembayaran) 
                              VALUES (:username, :waktu_komisi_dibayarkan, :periode_validasi, :total_komisi, :metode_pembayaran, :status_pembayaran, :waktu_pembayaran)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->bindParam(':username', $username);
                $stmtInsert->bindParam(':waktu_komisi_dibayarkan', $waktu_komisi_dibayarkan);
                $stmtInsert->bindParam(':periode_validasi', $periode_validasi);
                $stmtInsert->bindParam(':total_komisi', $total_komisi);
                $stmtInsert->bindParam(':metode_pembayaran', $metode_pembayaran);
                $stmtInsert->bindParam(':status_pembayaran', $status_pembayaran);
                $stmtInsert->bindParam(':waktu_pembayaran', $waktu_pembayaran);

                if (!$stmtInsert->execute()) {
                    echo "Error inserting record: " . implode(", ", $stmtInsert->errorInfo());
                    $pdo->rollBack();
                    fclose($handle);
                    exit();
                }
            } elseif ($existing_status_pembayaran !== $status_pembayaran) {
                // Record exists and status_pembayaran has changed, so update
                $sqlDelete = "DELETE FROM komisi WHERE username = :username AND periode_validasi = :periode_validasi";
                $stmtDelete = $pdo->prepare($sqlDelete);
                $stmtDelete->bindParam(':username', $username);
                $stmtDelete->bindParam(':periode_validasi', $periode_validasi);

                if (!$stmtDelete->execute()) {
                    echo "Error deleting record: " . implode(", ", $stmtDelete->errorInfo());
                    $pdo->rollBack();
                    fclose($handle);
                    exit();
                }

                // Insert the new record
                $sqlInsert = "INSERT INTO komisi (username, waktu_komisi_dibayarkan, periode_validasi, total_komisi, metode_pembayaran, status_pembayaran, waktu_pembayaran) 
                              VALUES (:username, :waktu_komisi_dibayarkan, :periode_validasi, :total_komisi, :metode_pembayaran, :status_pembayaran, :waktu_pembayaran)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->bindParam(':username', $username);
                $stmtInsert->bindParam(':waktu_komisi_dibayarkan', $waktu_komisi_dibayarkan);
                $stmtInsert->bindParam(':periode_validasi', $periode_validasi);
                $stmtInsert->bindParam(':total_komisi', $total_komisi);
                $stmtInsert->bindParam(':metode_pembayaran', $metode_pembayaran);
                $stmtInsert->bindParam(':status_pembayaran', $status_pembayaran);
                $stmtInsert->bindParam(':waktu_pembayaran', $waktu_pembayaran);

                if (!$stmtInsert->execute()) {
                    echo "Error inserting record: " . implode(", ", $stmtInsert->errorInfo());
                    $pdo->rollBack();
                    fclose($handle);
                    exit();
                }
            }
        }

        $pdo->commit();
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/min/dropzone.min.css">
<link rel="stylesheet" href="path/to/bootstrap.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .custom-file-upload {
            border: 1px solid #ced4da;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .custom-file-upload:hover {
            background-color: #e2e6ea;
        }

        .card {
            border: 1px solid #ced4da;
            border-radius: 8px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
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
                                    <h4 class="page-title">File Upload</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Shreyu</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">File Upload</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title -->  

                        <!-- Form Upload -->

                        <div class="container mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Edit Komisi for <?php echo htmlspecialchars($username); ?></h4>
                                </div>
                                <div class="card-body">
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="komisi_csv">Upload Komisi CSV</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="komisi_csv" name="komisi_csv" required>
                                                <label class="custom-file-label" for="komisi_csv">Choose file</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>

                             <!-- Bootstrap JS and dependencies -->
                            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
                            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                            <script>
                                // Update the label of the file input with the selected file name
                                document.querySelector('.custom-file-input').addEventListener('change', function (e) {
                                    var fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
                                    e.target.nextElementSibling.innerText = fileName;
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
                                <script>document.write(new Date().getFullYear())</script> &copy; Shreyu theme by <a href="">Coderthemes</a> 
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