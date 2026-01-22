<?php 
require_once '../../config/database.php';
require_once '../../helpers/auth_helper.php';
require_once '../layouts/header.php'; 
requireLogin();

// Logika Hitung Simulasi (Jika Form Disubmit)
$hasil_simulasi = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_ipk = floatval($_POST['target_ipk']);
    $sisa_sks = intval($_POST['sisa_sks']);
    
    // Ambil data saat ini
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT SUM(c.sks) as total_sks, SUM(c.sks * c.grade_point) as total_bobot 
        FROM courses c 
        JOIN semesters s ON c.semester_id = s.id 
        WHERE s.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $current = $stmt->fetch();
    
    $current_sks = $current['total_sks'] ?? 0;
    $current_bobot = $current['total_bobot'] ?? 0;
    
    $total_sks_nanti = $current_sks + $sisa_sks;
    $target_bobot_total = $target_ipk * $total_sks_nanti;
    $bobot_dibutuhkan = $target_bobot_total - $current_bobot;
    
    if ($sisa_sks > 0) {
        $ip_wajib = $bobot_dibutuhkan / $sisa_sks;
        $hasil_simulasi = number_format($ip_wajib, 2);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0"><i class="fas fa-calculator"></i> Simulasi Target IPK</h5>
            </div>
            <div class="card-body">
                <p>Hitung berapa IP rata-rata yang harus Anda dapatkan di sisa semester untuk mencapai wisuda dengan IPK impian.</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Target IPK Wisuda (Contoh: 3.50)</label>
                        <input type="number" step="0.01" name="target_ipk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Estimasi Sisa SKS (Yang belum diambil)</label>
                        <input type="number" name="sisa_sks" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Hitung Target</button>
                </form>

                <?php if ($hasil_simulasi !== null): ?>
                <div class="alert alert-info mt-4 text-center">
                    Untuk mencapai IPK <strong><?= $_POST['target_ipk'] ?></strong>,<br>
                    Anda harus memperoleh rata-rata IP:
                    <h2 class="fw-bold mt-2 <?= ($hasil_simulasi > 4.00) ? 'text-danger' : 'text-success' ?>">
                        <?= $hasil_simulasi ?>
                    </h2>
                    <?php if($hasil_simulasi > 4.00): ?>
                        <small class="text-danger">Mustahil dicapai (Maks 4.00). Kurangi target atau ambil SKS lebih banyak.</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="index.php">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>