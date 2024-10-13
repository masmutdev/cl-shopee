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

// Fetch studios from database
$studios = [];
$sql = "SELECT id, nama_std FROM studio";
$stmt = $pdo->query($sql);
if ($stmt) {
    $studios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $kode_akun = $_POST['kode_akun'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_akun = $_POST['password_akun'] ?? '';
    $namaid = $_POST['namaid'] ?? '';
    $nik = $_POST['NIK'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $rekening = $_POST['rekening'] ?? '';
    $shopeepay = $_POST['shopeepay'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $status = $_POST['status'] ?? '';
    $data_pembayaran = $_POST['data_pembayaran'] ?? '';

    // Cek apakah username diisi
    if (empty($username)) {
        echo "Error: Username harus diisi.";
        exit();
    }

    // Cek apakah data sudah ada
    $sqlCheck = "SELECT * FROM shopee_datacenter WHERE kode_akun = :kode_akun";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':kode_akun', $kode_akun);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        echo "Error: Kode akun sudah ada.";
    } else {
        // Insert data jika belum ada
        $sql = "INSERT INTO shopee_datacenter (kode_akun, username, email, password, password_akun, namaid, NIK, bank, rekening, shopeepay, keterangan, status, data_pembayaran) 
                VALUES (:kode_akun, :username, :email, :password, :password_akun, :namaid, :nik, :bank, :rekening, :shopeepay, :keterangan, :status, :data_pembayaran)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':kode_akun', $kode_akun);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':password_akun', $password_akun);
        $stmt->bindParam(':namaid', $namaid);
        $stmt->bindParam(':nik', $nik);
        $stmt->bindParam(':bank', $bank);
        $stmt->bindParam(':rekening', $rekening);
        $stmt->bindParam(':shopeepay', $shopeepay);
        $stmt->bindParam(':keterangan', $keterangan);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':data_pembayaran', $data_pembayaran);

        if ($stmt->execute()) {
            // Redirect setelah data berhasil ditambahkan
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . implode(", ", $stmt->errorInfo());
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
    
    <head>

        <meta charset="utf-8" />
        <title>Add-Account | AffiateX</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- Include Bootstrap CSS for styling (optional) -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

        <!-- App favicon -->
        <link rel="shortcut icon" href="../assets/images/favicon.ico">

        <!-- plugins -->
        <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

		<!-- App css -->
		<link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
		<link href="../assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

		<link href="../assets/css/bootstrap-dark.min.css" rel="stylesheet" type="text/css" id="bs-dark-stylesheet" disabled />
		<link href="../assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="app-dark-stylesheet"  disabled />

		<!-- icons -->
		<link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    </head>

    <body class="loading" data-layout='{"mode": "light", "width": "fluid", "menuPosition": "fixed", "sidebar": { "color": "light", "size": "default", "showuser": false}, "topbar": {"color": "light"}, "showRightSidebarOnPageLoad": true}'>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- Topbar Start -->
            <div class="navbar-custom">
                <div class="container-fluid">
                    <ul class="list-unstyled topnav-menu float-end mb-0">
    
                        <li class="dropdown d-inline-block d-lg-none">
                            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <i data-feather="search"></i>
                            </a>
                            <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                                <form class="p-3">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="search here">
                                </form>
                            </div>
                        </li>
            
                        <li class="dropdown notification-list topbar-dropdown">
                            <a class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
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
                                        <p class="notify-details">New user registered.<small class="text-muted">5 hours ago</small>
                                        </p>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                        <div class="notify-icon">
                                            <img src="../assets/images/users/avatar-1.jpg" class="img-fluid rounded-circle" alt="" />
                                        </div>
                                        <p class="notify-details">Karen Robinson</p>
                                        <p class="text-muted mb-0 user-msg">
                                            <small>Wow ! this admin looks good and awesome design</small>
                                        </p>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                                        <div class="notify-icon">
                                            <img src="../assets/images/users/avatar-2.jpg" class="img-fluid rounded-circle" alt="" />
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
                                            Jaclyn Brunswick commented on Dashboard<small class="text-muted">1 min ago</small>
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
                                <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                                    View all <i class="fe-arrow-right"></i>
                                </a>
    
                            </div>
                        </li>
    
                        <li class="dropdown notification-list topbar-dropdown">
                        <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user rounded-circle" width="38" height="38" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
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
                            <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                                <div class="lines">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                            <!-- End mobile menu toggle-->
                        </li>   
            
                        <li class="dropdown d-none d-xl-block">
                            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                Create New
                                <i class="uil uil-angle-down"></i> 
                            </a>
                            <div class="dropdown-menu">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="uil  uil-user-square me-1"></i><span>Master Akun</span>
                                </a>
    
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
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

                    <!-- User box -->
                    <div class="user-box text-center">
                        <img src="../assets/images/users/avatar-1.jpg" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
                        <div class="dropdown">
                            <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block" data-bs-toggle="dropdown">Nik Patel</a>
                            <div class="dropdown-menu user-pro-dropdown">

                                <a href="pages-profile.html" class="dropdown-item notify-item">
                                    <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>My Account</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i data-feather="settings" class="icon-dual icon-xs me-1"></i><span>Settings</span>
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i data-feather="help-circle" class="icon-dual icon-xs me-1"></i><span>Support</span>
                                </a>
                                <a href="pages-lock-screen.html" class="dropdown-item notify-item">
                                    <i data-feather="lock" class="icon-dual icon-xs me-1"></i><span>Lock Screen</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Logout</span>
                                </a>

                            </div>
                        </div>
                        <p class="text-muted">Admin Head</p>
                    </div>

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
                                <a href="#">
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
                                        <li><a href="project-list.html">Tambah Akun</a></li>
                                        <li><a href="project-detail.html">Edit Akun</a></li>
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <a href="#sidebarTasks" data-bs-toggle="collapse">
                                    <i data-feather="radio"></i>
                                    <span> Studio </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse" id="sidebarTasks">
                                    <ul class="nav-second-level">
                                        <li><a href="task-list.html">Tambah Studio</a></li>
                                        <li><a href="task-board.html">Edit Studio</a></li>
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

                            <li>
                                <a href="apps-file-manager.html">
                                    <i data-feather="file-plus"></i>
                                    <span> File Manager </span>
                                </a>
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
                        
                        <!-- Start Page Title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title">Add account</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End Page Title -->

                        <form action="add_account_process.php" method="POST">
                            <div class="row">
                                <!-- Form Input Fields -->
                                <div class="col-lg-6">
                                    <div class="card h-100"> <!-- Tambahkan kelas h-100 untuk tinggi penuh -->
                                        <div class="card-body">
                                            <h4 class="header-title mt-0 mb-1">Informasi Akun</h4>
                                            <p class="sub-header">Masukkan informasi seputar akun Anda</p>
                                            <div class="mb-3">
                                                <label class="form-label">Kode Akun</label>
                                                <input type="text" name="kode_akun" class="form-control" placeholder="SHOPEE001">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="text" name="email" class="form-control" placeholder="Emailakun@gmail.com">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password Email</label>
                                                <input type="text" name="password" class="form-control" placeholder="******">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control" placeholder="Username akun tanpa spasi" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password Akun</label>
                                                <input type="text" name="password_akun" class="form-control" placeholder="******">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Studio</label>
                                                <select name="studio_id" class="form-select">
                                                    <?php foreach ($studios as $studio) : ?>
                                                        <option value="<?php echo $studio['id']; ?>"><?php echo $studio['nama_std']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status Akun</label>
                                                <select name="status" class="form-select">
                                                    <option value="Create">Create</option>
                                                    <option value="Aktif">Aktif</option>
                                                    <option value="Dibatasi">Dibatasi</option>
                                                    <option value="Nonaktif">Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status Data Pembayaran</label>
                                                <select name="data_pembayaran" class="form-select">
                                                    <option value="Kosong">Kosong</option>
                                                    <option value="Ditinjau">Ditinjau</option>
                                                    <option value="Verifikasi">Verifikasi</option>
                                                    <option value="Sah">Sah</option>
                                                    <option value="Tidak Sah">Tidak Sah</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Identitas -->
                                <div class="col-lg-6">
                                    <div class="card h-100"> <!-- Tambahkan kelas h-100 untuk tinggi penuh -->
                                        <div class="card-body">
                                            <h4 class="header-title mt-0 mb-1">Identitas</h4>
                                            <p class="sub-header">Masukkan identitas terdaftar di akun Anda</p>
                                            <div class="mb-3">
                                                <label class="form-label">Nama Pemilik Identitas</label>
                                                <input type="text" name="namaid" class="form-control" placeholder="James Bond">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">NIK</label>
                                                <input type="text" name="NIK" class="form-control" placeholder="16 DIGIT NIK">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">BANK</label>
                                                <input type="text" name="bank" class="form-control" placeholder="Bank Central Asia (BCA)">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nomor Rekening</label>
                                                <input type="text" name="rekening" class="form-control" placeholder="2288 0089 008">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">ShopeePay</label>
                                                <input type="text" name="shopeepay" class="form-control" placeholder="0857 XXXX XXXX">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan" class="form-control" placeholder="Catatan Anda!" rows="4"></textarea>
                                            </div>
                                            <div class="text-end"> <!-- Tambahkan kelas text-end untuk tombol ke kanan -->
                                                <br></br>
                                                <button type="submit" class="btn btn-primary">Tambah Akun</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Row -->
                        </form>

                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> &copy; Shopee Affiliate Dashboard by <a href="">affiliateX</a> 
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

         <!-- Include Bootstrap JS and dependencies (optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        
    </body>
</html>