<?php 
require_once '../../config/database.php';
require_once '../../helpers/auth_helper.php';
require_once '../layouts/header.php'; 
requireLogin();

$semester_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE semester_id = ?");
$stmt->execute([$semester_id]);
$courses = $stmt->fetchAll();

// Info semester
$s_stmt = $pdo->prepare("SELECT * FROM semesters WHERE id = ?");
$s_stmt->execute([$semester_id]);
$semester = $s_stmt->fetch();
?>

<div class="mb-3">
    <a href="<?= BASE_URL ?>views/dashboard/index.php" class="text-decoration-none">&larr; Kembali ke Dashboard</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="m-0">Input Komponen Nilai</h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>actions/course/store.php" method="POST">
                    <input type="hidden" name="semester_id" value="<?= $semester_id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Mata Kuliah</label>
                        <input type="text" name="course_name" class="form-control" placeholder="Contoh: Algoritma" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah SKS</label>
                        <input type="number" name="sks" class="form-control" required min="1" max="6">
                    </div>

                    <hr>
                    <p class="small text-muted mb-2">Masukkan nilai angka (0-100):</p>
                    
                    <div class="row">
    <div class="col-12 d-flex justify-content-between mb-1">
        <span class="small fw-bold text-muted">Nilai (0-100)</span>
        <span class="small fw-bold text-muted">Bobot %</span>
    </div>

    <div class="col-8 mb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text w-25">UAS</span>
            <input type="number" name="uas" class="form-control" placeholder="Nilai" required step="0.01">
        </div>
    </div>
    <div class="col-4 mb-2">
        <input type="number" name="w_uas" class="form-control form-control-sm text-center bg-light" value="40" required>
    </div>

    <div class="col-8 mb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text w-25">UTS</span>
            <input type="number" name="uts" class="form-control" placeholder="Nilai" required step="0.01">
        </div>
    </div>
    <div class="col-4 mb-2">
        <input type="number" name="w_uts" class="form-control form-control-sm text-center bg-light" value="30" required>
    </div>

    <div class="col-8 mb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text w-25">Tgs</span>
            <input type="number" name="tugas" class="form-control" placeholder="Nilai" required step="0.01">
        </div>
    </div>
    <div class="col-4 mb-2">
        <input type="number" name="w_tugas" class="form-control form-control-sm text-center bg-light" value="20" required>
    </div>

    <div class="col-8 mb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text w-25">Abs</span>
            <input type="number" name="absen" class="form-control" placeholder="Nilai" required step="0.01">
        </div>
    </div>
    <div class="col-4 mb-2">
        <input type="number" name="w_absen" class="form-control form-control-sm text-center bg-light" value="10" required>
    </div>
</div>

<div class="alert alert-warning py-1 mt-1 mb-3" style="font-size: 0.75rem;">
    <i class="fas fa-exclamation-triangle"></i> Pastikan total kolom kanan (Bobot) adalah <strong>100%</strong>.
</div>

                    <div class="alert alert-info py-2 mt-2 small">
                        <i class="fas fa-info-circle"></i> Nilai Akhir & Huruf akan dihitung otomatis oleh sistem.
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan & Hitung</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <h4>Kartu Studi Semester <?= $semester['semester_no'] ?></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th rowspan="2" style="vertical-align: middle;">Mata Kuliah</th>
                        <th rowspan="2" style="vertical-align: middle;">SKS</th>
                        <th colspan="5">Rincian Nilai</th>
                        <th rowspan="2" style="vertical-align: middle;">Mutu</th>
                        <th rowspan="2" style="vertical-align: middle;">Aksi</th>
                    </tr>
                    <tr class="text-center small">
                        <th>UAS</th><th>UTS</th><th>Tgs</th><th>Abs</th>
                        <th>Akhir (H)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_sks = 0;
                    $total_mutu = 0;
                    foreach($courses as $c): 
                        $mutu = $c['sks'] * $c['grade_point'];
                        $total_sks += $c['sks'];
                        $total_mutu += $mutu;
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $c['course_name'] ?></td>
                        <td class="text-center"><?= $c['sks'] ?></td>
                        
                        <td class="small text-center"><?= $c['score_uas'] ?></td>
                        <td class="small text-center"><?= $c['score_uts'] ?></td>
                        <td class="small text-center"><?= $c['score_tugas'] ?></td>
                        <td class="small text-center"><?= $c['score_presence'] ?></td>
                        
                        <td class="text-center fw-bold text-primary">
                            <?= $c['final_score'] ?><br>
                            <span class="badge bg-dark"><?= $c['grade_letter'] ?> (<?= $c['grade_point'] ?>)</span>
                        </td>

                        <td class="text-center fw-bold"><?= $mutu ?></td>
                        <td class="text-center">
                             <a href="../../actions/course/delete.php?id=<?= $c['id'] ?>&semester_id=<?= $semester_id ?>" 
                               class="text-danger" onclick="return confirm('Hapus matkul ini?')">
                               <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="7" class="text-end">Total SKS & Mutu:</td>
                        <td class="text-center"><?= $total_sks ?></td>
                        <td class="text-center"><?= $total_mutu ?></td>
                        <td></td>
                    </tr>
                    <tr class="bg-light">
                        <td colspan="7" class="text-end text-primary">IP SEMESTER INI:</td>
                        <td colspan="3" class="text-primary fs-4 text-start ps-4">
                            <?= ($total_sks > 0) ? number_format($total_mutu / $total_sks, 2) : "0.00" ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>