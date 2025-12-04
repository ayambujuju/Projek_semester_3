<?php 
$page_title = 'Lacak Kiriman Anda'; // Judul Halaman
require_once 'partials/header.php'; 
?>

<section class="page-hero tracking-hero">
    <div class="hero-overlay">
        <div class="container">
            <h1 class="animate-on-scroll is-visible">Lacak Kargo Anda</h1>
            <p class="section-subtitle-hero animate-on-scroll is-visible">
                Masukkan nomor resi (AWB) Anda untuk mendapatkan informasi real-time.
            </p>
            
            <form id="hero-tracking-form" class="hero-tracking-form tracking-page-form animate-on-scroll is-visible">
                <input type="text" name="receipt_number" placeholder="Masukkan Nomor Resi Anda..." required>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Lacak</button>
            </form>

        </div>
    </div>
</section>

<!-- ===== START: Tracking Result Section ===== -->
<section id="tracking-result-section" class="section-padding" style="display: none;">
    <div class="container">
        <div id="tracking-result-container">
            <!-- Hasil pelacakan dinamis akan dimuat di sini oleh JavaScript -->
        </div>
    </div>
</section>
<!-- ===== END: Tracking Result Section ===== -->



<?php 
require_once 'partials/footer.php'; 
?>
