<?php
// Fungsi ambil Bobot dari Huruf (Masih sama)
function getGradePoint($letter) {
    $grades = [
        'A' => 4.00, 'A-' => 3.75,
        'B+' => 3.50, 'B' => 3.00, 'B-' => 2.75,
        'C+' => 2.50, 'C' => 2.00,
        'D' => 1.00, 'E' => 0.00
    ];
    return $grades[$letter] ?? 0.00;
}

// BARU: Fungsi Konversi Angka Akhir ke Huruf
// Standar umum universitas (bisa Anda sesuaikan range-nya)
function getLetterFromScore($score) {
    if ($score >= 85) return 'A';
    if ($score >= 80) return 'A-';
    if ($score >= 75) return 'B+';
    if ($score >= 70) return 'B';
    if ($score >= 65) return 'B-';
    if ($score >= 60) return 'C+';
    if ($score >= 55) return 'C';
    if ($score >= 40) return 'D';
    return 'E';
}
?>