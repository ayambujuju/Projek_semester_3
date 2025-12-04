<?php 
require_once 'partials/db.php'; 
$page_title = 'Home - Solusi Logistik Premium & Terpercaya';

// Ambil data testimoni dari database
$testimonials = [];
$sql = "SELECT author, content FROM testimonials WHERE is_published = 1 ORDER BY id DESC LIMIT 5"; // Ambil 5 testimoni terbaru
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

require_once 'partials/header.php'; 
?>

<!-- REVISI: HERO SECTION MEWAH DENGAN KEN BURNS EFFECT -->
<section class="hero-tracking">
    <div class="hero-background-image"></div>
    <div class="hero-overlay">
        <div class="container">
            <h1>Solusi Logistik Premium Untuk Bisnis Anda</h1>
            <p class="section-subtitle-hero">Pengiriman cepat, aman, dan terpercaya ke seluruh penjuru Indonesia. Kami adalah partner logistik yang Anda butuhkan.</p>
            
            <!-- Form Tracking yang Lebih Menonjol -->
            <!-- Tombol CTA Baru -->
            <a href="contact.php" class="btn btn-primary btn-offer">Dapatkan Penawaran</a>
        </div>
    </div>
</section>

<!-- REVISI: BAGIAN LAYANAN UNGGULAN (FEATURED SERVICES) -->
<section class="featured-services-section section-padding">
    <div class="container">
        <h2 class="section-title">Layanan Unggulan Kami</h2>
        <p class="section-subtitle">Dirancang untuk memenuhi setiap kebutuhan logistik Anda dengan standar tertinggi.</p>
        <div class="featured-services-grid">
            <!-- Layanan 1: Kargo Darat -->
            <div class="service-card">
                <i class="fa-solid fa-truck-fast service-card-icon"></i>
                <div class="service-card-content">
                    <h4>Kargo Darat</h4>
                    <p>Armada modern kami menjamin pengiriman darat yang efisien, tepat waktu, dan aman untuk semua jenis muatan.</p>
                </div>
            </div>
            <!-- Layanan 2: Kargo Laut -->
            <div class="service-card">
                <i class="fa-solid fa-ship service-card-icon"></i>
                <div class="service-card-content">
                    <h4>Kargo Laut</h4>
                    <p>Solusi pengiriman via laut yang ekonomis dan andal untuk muatan skala besar, antar pulau di seluruh Indonesia.</p>
                </div>
            </div>
            <!-- Layanan 3: Kargo Udara -->
            <div class="service-card">
                <i class="fa-solid fa-plane-up service-card-icon"></i>
                <div class="service-card-content">
                    <h4>Kargo Udara</h4>
                    <p>Kecepatan adalah prioritas. Layanan kargo udara kami memastikan barang Anda tiba di tujuan dalam waktu sesingkat mungkin.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REVISI: BAGIAN MENGAPA MEMILIH KAMI (WHY CHOOSE US) - GAYA ELEGAN -->
<section class="why-us-elegant-section section-padding">
    <div class="container">
        <div class="why-us-grid">
            <div class="why-us-text-content">
                <h2 class="section-title" style="text-align: left;">Partner Logistik Terpercaya</h2>
                <p class="section-subtitle" style="text-align: left; max-width: 450px;">
                    Kami tidak hanya mengirim barang, kami mengantarkan kepercayaan. Dengan pengalaman dan dedikasi, kami menjadi pilihan utama untuk kebutuhan logistik Anda.
                </p>
                <a href="services.php" class="btn btn-primary">Lihat Semua Layanan</a>
            </div>
            <div class="why-us-cards-grid">
                <!-- Card 1 -->
                <div class="why-us-card">
                    <i class="fa-solid fa-shield-halved why-us-card-icon"></i>
                    <h4>Keamanan Terjamin</h4>
                    <p>Setiap pengiriman dipantau ketat dengan sistem modern untuk menjamin keamanan barang Anda.</p>
                </div>
                <!-- Card 2 -->
                <div class="why-us-card">
                    <i class="fa-solid fa-map-location-dot why-us-card-icon"></i>
                    <h4>Jangkauan Nasional</h4>
                    <p>Jaringan kami tersebar luas, siap melayani pengiriman ke seluruh kota besar dan daerah terpencil.</p>
                </div>
                <!-- Card 3 -->
                <div class="why-us-card">
                    <i class="fa-solid fa-headset why-us-card-icon"></i>
                    <h4>Dukungan 24/7</h4>
                    <p>Tim customer service kami selalu siap membantu Anda kapan pun Anda membutuhkan informasi.</p>
                </div>
                <!-- Card 4 -->
                <div class="why-us-card">
                    <i class="fa-solid fa-hand-holding-dollar why-us-card-icon"></i>
                    <h4>Harga Kompetitif</h4>
                    <p>Dapatkan penawaran harga terbaik dengan kualitas layanan premium tanpa kompromi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BARU: VISI & MISI SECTION -->
<section id="vision-mission-section" class="section-padding" style="background-color: var(--gray-color);">
    <div class="container">
        <h2 class="section-title">Visi & Misi Kami</h2>
        <p class="section-subtitle">Landasan kami dalam memberikan layanan terbaik.</p>
        <div class="vision-mission-grid">
            <!-- Visi -->
            <div class="vision-mission-card">
                <div class="vision-mission-icon">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div class="vision-mission-content">
                    <h3>Visi</h3>
                    <p>Menjadi perusahaan logistik terdepan di Indonesia yang dikenal karena keandalan, inovasi, dan pelayanan pelanggan yang superior.</p>
                </div>
            </div>
            <!-- Misi -->
            <div class="vision-mission-card">
                <div class="vision-mission-icon">
                    <i class="fa-solid fa-rocket"></i>
                </div>
                <div class="vision-mission-content">
                    <h3>Misi</h3>
                    <p>Memberikan solusi logistik yang terintegrasi dan efisien dengan memanfaatkan teknologi terkini dan sumber daya manusia yang profesional untuk kepuasan mitra kami.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- BARU: GALERI KEGIATAN -->
<section class="gallery-section section-padding">
    <div class="container">
        <h2 class="section-title">Galeri Kegiatan Kami</h2>
        <p class="section-subtitle">Momen-momen dedikasi kami dalam melayani Anda.</p>
        <div class="gallery-grid">
            <!-- Gambar 1 -->
            <div class="gallery-card">
                <img src="images/images (1).jpg" alt="Aktivitas Gudang">
                <div class="card-content">
                    <h4>Penyortiran & Pengepakan</h4>
                    <p>Proses sortir barang di gudang utama kami yang berjalan cepat dan sistematis.</p>
                </div>
            </div>
            <!-- Gambar 2 -->
            <div class="gallery-card">
                <img src="images/images.jpg" alt="Armada Truk">
                <div class="card-content">
                    <h4>Armada Siap Berangkat</h4>
                    <p>Jajaran armada truk kami yang siap mendistribusikan barang ke seluruh negeri.</p>
                </div>
            </div>
            <!-- Gambar 3 -->
            <div class="gallery-card">
                <img src="images/IMG_20141206_223159.jpg" alt="Pemuatan Kargo">
                <div class="card-content">
                    <h4>Pemuatan Kargo Pesawat</h4>
                    <p>Barang siap untuk pengiriman udara, memastikan kecepatan dan ketepatan waktu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REVISI: BAGIAN TESTIMONI DENGAN SLIDER OTOMATIS -->
<section class="testimonials-section section-padding">
    <div class="container">
        <h2 class="section-title">Apa Kata Mereka Tentang Kami</h2>
        <p class="section-subtitle">Kepercayaan klien adalah aset terbesar kami.</p>
    </div>
    
    <div class="testimonial-slider">
        <div class="testimonial-track">
            <?php if (!empty($testimonials)): ?>
                <?php 
                // Duplikasi testimoni untuk efek loop yang mulus
                $looped_testimonials = array_merge($testimonials, $testimonials);
                foreach ($looped_testimonials as $testimonial): 
                ?>
                    <div class="testimonial-card">
                        <p class="testimonial-content">"<?= htmlspecialchars($testimonial['content']); ?>"</p>
                        <h5 class="testimonial-author">- <?= htmlspecialchars($testimonial['author']); ?></h5>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="testimonial-card">
                    <p class="testimonial-content">"Belum ada testimoni untuk ditampilkan saat ini."</p>
                    <h5 class="testimonial-author">- Manajemen</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- BARU: STATS SECTION DENGAN ANGKA -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>1,500+</h3>
                <p>Pengiriman per Bulan</p>
            </div>
            <div class="stat-item">
                <h3>250+</h3>
                <p>Kota Tujuan</p>
            </div>
            <div class="stat-item">
                <h3>99.8%</h3>
                <p>Tingkat Keberhasilan</p>
            </div>
            <div class="stat-item">
                <h3>500+</h3>
                <p>Klien Korporat</p>
            </div>
        </div>
    </div>
</section>


<?php 
$conn->close();
require_once 'partials/footer.php'; 
?>