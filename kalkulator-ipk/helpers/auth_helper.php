<?php
session_start();

function isLogin() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLogin()) {
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit;
    }
}
?>