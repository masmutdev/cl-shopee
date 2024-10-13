<?php
require '../../config/koneksi.php';
require '../../config/functions.php';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: ../login.php');
            exit();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                unset($_SESSION['csrf_token']);

                header('Location: ../../admin/dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid Email/Password';
                header('Location: ../login.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid Email/Password';
            header('Location: ../login.php');
            exit();
        }
    }
} catch (PDOException $e) {
    echo 'Koneksi atau query bermasalah: ' . $e->getMessage();
}
?>
