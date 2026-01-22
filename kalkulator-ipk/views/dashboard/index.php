<?php 
require_once '../../config/database.php';
require_once '../../helpers/auth_helper.php';
require_once '../layouts/header.php'; 
requireLogin();

$user_id = $_SESSION['user_id'];

// Ambil data semester
$stmt = $pdo->prepare("SELECT * FROM semesters WHERE user_id = ? ORDER BY semester_no ASC");
$stmt->execute([$user_id]);
$semesters = $stmt->fetchAll();

// Hitung Total IPK
$total_sks = 0;
$total_bobot = 0;

foreach ($semesters as $sem) {
    // Query hitung per semester
    $q_course = $pdo->prepare("SELECT SUM(sks) as ts, SUM(sks * grade_point) as tb FROM courses WHERE semester_id = ?");
    $q_course->execute([$sem['id']]);
    $res = $q_course->fetch();
    
    $total_sks += $res['ts'] ?? 0;
    $total_bobot += $res['tb'] ?? 0;
}

$ipk = ($total_sks > 0) ? number_format($total_bobot / $total_sks, 2) : "0.00";
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-ipk p-4 shadow-sm">
            <h2 class="m-0">Halo, <?= $_SESSION['name'] ?>!</h2>
            <p class="lead">IPK Kumulatif Anda:</p>
            <h1 class="display-3 fw-bold"><?= $ipk ?></h1>
            <p>Total SKS: <?= $total_sks ?></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Daftar Semester</h4>
    <div>
        <a href="simulation.php" class="btn btn-info text-white"><i class="fas fa-calculator"></i> Simulasi</a>
        <a href="export.php" class="btn btn-secondary"><i class="fas fa-print"></i> Export</a>
        
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
            <i class="fas fa-plus"></i> Tambah Semester
        </button>
    </div>
</div>

<div class="card mb-3 shadow-sm border-0">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            </div>
        <div>
            <a href="../semester/detail.php?id=<?= $sem['id'] ?>" class="btn btn-outline-primary btn-sm">Lihat</a>
            
            <a href="../../actions/semester/delete.php?id=<?= $sem['id'] ?>" 
               class="btn btn-outline-danger btn-sm" 
               onclick="return confirm('Yakin hapus semester ini beserta isinya?')">Hapus</a>
        </div>
    </div>
</div>
        

        <?php foreach ($semesters as $sem): 
            // Hitung IP Semester ini saja
            $q_course = $pdo->prepare("SELECT SUM(sks) as ts, SUM(sks * grade_point) as tb FROM courses WHERE semester_id = ?");
            $q_course->execute([$sem['id']]);
            $res = $q_course->fetch();
            $ip_sem = ($res['ts'] > 0) ? number_format($res['tb'] / $res['ts'], 2) : "0.00";
        ?>
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Semester <?= $sem['semester_no'] ?> <span class="text-muted small">(<?= $sem['academic_year'] ?>)</span></h5>
                    <span class="badge bg-success">IP: <?= $ip_sem ?></span>
                    <span class="badge bg-secondary">SKS: <?= $res['ts'] ?? 0 ?></span>
                </div>
                <a href="../semester/detail.php?id=<?= $sem['id'] ?>" class="btn btn-outline-primary btn-sm">Lihat Detail &rarr;</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="addSemesterModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="<?= BASE_URL ?>actions/semester/store.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Semester Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Semester Ke-</label>
            <input type="number" name="semester_no" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tahun Akademik (Contoh: 2025/2026)</label>
            <input type="text" name="academic_year" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once '../layouts/footer.php'; ?>