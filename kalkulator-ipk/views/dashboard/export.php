<?php 
require_once '../../config/database.php';
require_once '../../helpers/auth_helper.php';
requireLogin(); // Tidak pakai header.php agar bersih saat diprint

$user_id = $_SESSION['user_id'];
// Ambil semua data sekaligus
$stmt = $pdo->prepare("SELECT * FROM semesters WHERE user_id = ? ORDER BY semester_no ASC");
$stmt->execute([$user_id]);
$semesters = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan IPK - <?= $_SESSION['name'] ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Cetak PDF</button>
        <a href="index.php">Kembali</a>
    </div>

    <div class="header">
        <h2>Laporan Hasil Studi</h2>
        <p>Nama: <?= $_SESSION['name'] ?> | Tanggal Cetak: <?= date('d-m-Y') ?></p>
    </div>

    <?php 
    $grand_total_sks = 0;
    $grand_total_bobot = 0;

    foreach($semesters as $sem): 
        $q = $pdo->prepare("SELECT * FROM courses WHERE semester_id = ?");
        $q->execute([$sem['id']]);
        $courses = $q->fetchAll();
        
        $sem_sks = 0;
        $sem_bobot = 0;
    ?>
        <h3>Semester <?= $sem['semester_no'] ?> (<?= $sem['academic_year'] ?>)</h3>
        <table>
            <thead>
                <tr><th>Mata Kuliah</th><th>SKS</th><th>Nilai</th><th>Mutu</th></tr>
            </thead>
            <tbody>
                <?php foreach($courses as $c): 
                    $mutu = $c['sks'] * $c['grade_point'];
                    $sem_sks += $c['sks'];
                    $sem_bobot += $mutu;
                ?>
                <tr>
                    <td><?= $c['course_name'] ?></td>
                    <td><?= $c['sks'] ?></td>
                    <td><?= $c['grade_letter'] ?> (<?= $c['grade_point'] ?>)</td>
                    <td><?= $mutu ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right"><strong>IP Semester</strong></td>
                    <td><strong><?= ($sem_sks > 0) ? number_format($sem_bobot/$sem_sks, 2) : '0.00' ?></strong></td>
                </tr>
            </tbody>
        </table>
        <?php 
        $grand_total_sks += $sem_sks;
        $grand_total_bobot += $sem_bobot;
        endforeach; 
        ?>

    <div style="margin-top: 30px; border: 2px solid #000; padding: 10px; width: 300px;">
        <h3>Rangkuman Akhir</h3>
        <p>Total SKS: <?= $grand_total_sks ?></p>
        <p>Total IPK: <strong><?= ($grand_total_sks > 0) ? number_format($grand_total_bobot/$grand_total_sks, 2) : '0.00' ?></strong></p>
    </div>
</body>
</html>