<?php require_once __DIR__ . '/../../config/constants.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator IPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-ipk { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>views/dashboard/index.php">🎓 Kalkulator IPK</a>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>actions/auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    <?php endif; ?>
  </div>
</nav>
<div class="container">