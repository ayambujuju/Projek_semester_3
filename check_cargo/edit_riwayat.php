<?php
// Pastikan hanya admin yang bisa akses
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

require_once 'partials/db.php'; // Path diperbaiki sesuai struktur proyek Anda

$page_title = 'Edit Riwayat Perjalanan';
require_once 'admin/partials/header.php'; // Asumsi header admin ada di admin/partials/

$riwayat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data_riwayat = null;

if ($riwayat_id > 0) {
    // Ambil data riwayat yang ada dari database
    $stmt = $conn->prepare("SELECT * FROM riwayat_kargo WHERE id_riwayat = ?"); // Ganti 'riwayat_kargo' dan 'id_riwayat' jika perlu
    $stmt->bind_param("i", $riwayat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $data_riwayat = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$data_riwayat) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Data riwayat tidak ditemukan.</div></div>";
    require_once 'admin/partials/footer.php'; // Asumsi footer admin ada di admin/partials/
    exit();
}
?>

<div class="container mt-4">
    <h2>Edit Riwayat Perjalanan</h2>
    <hr>

    <form action="proses_edit_riwayat.php" method="POST">
        <!-- Input tersembunyi untuk menyimpan ID riwayat -->
        <input type="hidden" name="id_riwayat" value="<?php echo htmlspecialchars($data_riwayat['id_riwayat']); ?>">
        
        <!-- Asumsi ada ID kargo untuk redirect kembali -->
        <input type="hidden" name="id_kargo" value="<?php echo htmlspecialchars($data_riwayat['id_kargo']); ?>">

        <div class="form-group mb-3">
            <label for="status">Status</label>
            <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($data_riwayat['status']); ?>" required>
        </div>

        <div class="form-group mb-3">
            <label for="lokasi">Lokasi</label>
            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($data_riwayat['lokasi']); ?>" required>
        </div>

        <div class="form-group mb-3">
            <label for="tanggal">Tanggal dan Waktu</label>
            <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d\TH:i', strtotime($data_riwayat['tanggal'])); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="detail_kargo.php?id=<?php echo htmlspecialchars($data_riwayat['id_kargo']); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php
require_once 'admin/partials/footer.php'; // Asumsi footer admin ada di admin/partials/
?>