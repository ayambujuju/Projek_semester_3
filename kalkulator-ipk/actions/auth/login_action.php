<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        header("Location: " . BASE_URL . "views/dashboard/index.php");
    } else {
        header("Location: " . BASE_URL . "views/auth/login.php?error=wrong_credentials");
    }
}
?>