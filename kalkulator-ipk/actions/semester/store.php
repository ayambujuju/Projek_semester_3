<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../helpers/auth_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $semester_no = $_POST['semester_no'];
    $academic_year = $_POST['academic_year'];

    $stmt = $pdo->prepare("INSERT INTO semesters (user_id, semester_no, academic_year) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $semester_no, $academic_year]);

    header("Location: " . BASE_URL . "views/dashboard/index.php");
}
?>