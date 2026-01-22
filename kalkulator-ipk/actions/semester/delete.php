<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../helpers/auth_helper.php';
requireLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Pastikan yang dihapus adalah milik user yang sedang login
    $stmt = $pdo->prepare("DELETE FROM semesters WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

header("Location: " . BASE_URL . "views/dashboard/index.php");
?>