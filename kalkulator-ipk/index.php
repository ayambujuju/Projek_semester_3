<?php require_once 'config/constants.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator IPK Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 100px 0; }
        .feature-icon { font-size: 3rem; color: #764ba2; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">🎓 Kalkulator IPK</a>
            <div class="ms-auto">
                <a href="views/auth/login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="views/auth/register.php" class="btn btn-warning">Daftar Sekarang</a>
            </div>
        </div>
    </nav>

    <header class="hero text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Hitung IPK Tanpa Ribet</h1>
            <p class="lead mb-4">Simpan riwayat nilai, hitung IP Semester, dan simulasikan target kelulusan Anda secara real-time.</p>
            <a href="views/auth/register.php" class="btn btn-lg btn-light text-primary fw-bold">Mulai Hitung Gratis</a>
        </div>
    </header>

    <section class="py-5">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">📊</div>
                    <h4>Real-time Calculation</h4>
                    <p>Ubah nilai mata kuliah dan lihat perubahan IPK Anda secara instan.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">🎯</div>
                    <h4>Target Simulasi</h4>
                    <p>Hitung berapa nilai yang Anda butuhkan untuk mencapai IPK Cum Laude.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">📂</div>
                    <h4>Export PDF</h4>
                    <p>Unduh laporan akademik Anda dengan rapi untuk keperluan administrasi.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-light py-4 text-center mt-auto">
        <div class="container">
            <p class="text-muted m-0">© 2024 Kalkulator IPK Indonesia. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>