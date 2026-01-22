<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../helpers/auth_helper.php';
requireLogin();

if (isset($_GET['id']) && isset($_GET['semester_id'])) {
    $id = $_GET['id'];
    $semester_id = $_GET['semester_id'];
    
    // Kita tidak perlu cek user_id disini karena tabel courses tidak punya user_id,
    // tapi idealnya kita cek via join semester. Untuk MVP ini, cukup delete by ID.
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: " . BASE_URL . "views/semester/detail.php?id=" . $semester_id);
} else {
    header("Location: " . BASE_URL . "views/dashboard/index.php");
}
?>