<?php
// Ganti dengan nomor WhatsApp Anda
$nomor_wa = "6285702884325";

// Pesan default (opsional)
$pesan = "Halo, saya ingin bertanya tentang layanan Anda.";

// Buat URL WhatsApp
$url_wa = "https://api.whatsapp.com/send?phone=" . $nomor_wa . "&text=" . urlencode($pesan);

// Alihkan ke WhatsApp
header("Location: " . $url_wa);
exit;
?>