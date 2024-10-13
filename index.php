<?php
require 'config/koneksi.php';
require 'config/functions.php';

startSession();

// Pengecekan sesi login
if (isset($_SESSION['username']) && isset($_SESSION['email'])) {
    header('Location: admin/dashboard.php');
    exit();
} else {
    header('Location: auth/login.php');
    exit();
}
