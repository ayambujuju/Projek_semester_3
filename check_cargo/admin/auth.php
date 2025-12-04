<?php
session_start();

// IMPORTANT: This is a temporary and insecure login mechanism.
// For a real-world application, you should fetch user credentials 
// from a database and use hashed passwords.

// Hardcoded credentials
$valid_username = 'admin';
$valid_password = 'password'; // In a real app, this should be a hashed password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if credentials are valid
    // For a real app, you would use password_verify($password, $hashed_password_from_db)
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variable to mark user as logged in
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;

        // Redirect to the dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        // Redirect back to login page with an error
        header('Location: index.php?error=1');
        exit;
    }
} else {
    // If not a POST request, redirect to login page
    header('Location: index.php');
    exit;
}
