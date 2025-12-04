<?php
// File ini akan di-include di setiap halaman
// Deteksi halaman saat ini untuk memberi kelas pada <body> dan header
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$is_homepage = ($current_page === 'index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'PT. Cargo Baru Anda'; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet (untuk Peta Interaktif) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?php echo $is_homepage ? 'homepage' : ''; ?>">
    <header class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="navbar-brand">
                <img src="images/ChatGPT_Image_Nov_3__2025__12_09_34_AM-removebg-preview.png" alt="PT Check Cargo" class="navbar-logo">
            </a>
            <nav class="navbar-nav">
                <a href="index.php">Home</a>
                <a href="tracking.php">Tracking</a>
                <a href="services.php">Services</a>
                <a href="contact.php">Contact Us</a>
                <a href="admin/">Login</a>
            </nav>
        </div>
    </header>
    
    <main>