<?php
require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../helpers/gpa_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $semester_id = $_POST['semester_id'];
    $course_name = $_POST['course_name'];
    $sks = $_POST['sks'];
    
    // 1. Ambil Nilai Angka (Skor)
    $uas = floatval($_POST['uas']);
    $uts = floatval($_POST['uts']);
    $tugas = floatval($_POST['tugas']);
    $absen = floatval($_POST['absen']);

    // 2. Ambil Settingan Bobot Persentase (Dari Input User)
    // Jika user tidak isi, default ke standar (40/30/20/10)
    $w_uas = intval($_POST['w_uas']); 
    $w_uts = intval($_POST['w_uts']);
    $w_tugas = intval($_POST['w_tugas']);
    $w_absen = intval($_POST['w_absen']);

    // Validasi Total Bobot harus 100% (Opsional, tapi bagus untuk keamanan data)
    $total_weight = $w_uas + $w_uts + $w_tugas + $w_absen;
    if ($total_weight != 100) {
        // Redirect balik jika bobot tidak 100% (bisa ditambah alert nanti)
        // Untuk sekarang kita paksa lanjut atau bisa die("Bobot harus 100%");
    }

    // 3. LOGIKA PEMBOBOTAN DINAMIS
    // Rumus: (Nilai * Persentase) / 100
    $final_score = ($uas * $w_uas / 100) + 
                   ($uts * $w_uts / 100) + 
                   ($tugas * $w_tugas / 100) + 
                   ($absen * $w_absen / 100);

    // 4. Konversi ke Huruf & Bobot IP
    $grade_letter = getLetterFromScore($final_score);
    $grade_point = getGradePoint($grade_letter);

    // 5. Simpan ke Database (Termasuk bobotnya agar tersimpan history-nya)
    $stmt = $pdo->prepare("INSERT INTO courses 
        (semester_id, course_name, sks, 
        score_uas, score_uts, score_tugas, score_presence, 
        weight_uas, weight_uts, weight_tugas, weight_presence,
        final_score, grade_letter, grade_point) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $semester_id, $course_name, $sks, 
        $uas, $uts, $tugas, $absen, 
        $w_uas, $w_uts, $w_tugas, $w_absen, // Simpan persentasenya juga
        $final_score, $grade_letter, $grade_point
    ]);

    header("Location: " . BASE_URL . "views/semester/detail.php?id=" . $semester_id);
}
?>