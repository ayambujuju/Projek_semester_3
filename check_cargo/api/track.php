<?php
header('Content-Type: application/json');
require_once '../partials/db.php';

// Check for database connection errors first
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi ke database gagal: ' . $conn->connect_error,
        'data' => null
    ]);
    exit;
}

$response = [
    'success' => false,
    'message' => 'Nomor resi tidak valid.',
    'data' => null
];

if (!isset($_GET['resi']) || empty($_GET['resi'])) {
    $response['message'] = 'Nomor resi tidak boleh kosong.';
    echo json_encode($response);
    exit;
}

try {
    $resi = $_GET['resi'];

    $stmt = $conn->prepare("SELECT * FROM shipments WHERE tracking_number = ?");
    if ($stmt) {
        $stmt->bind_param("s", $resi);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($shipment = $result->fetch_assoc()) {
                
                $history_stmt = $conn->prepare("SELECT * FROM shipment_history WHERE shipment_id = ? ORDER BY timestamp DESC");
                $history = [];
                if($history_stmt) {
                    $history_stmt->bind_param("i", $shipment['id']);
                    $history_stmt->execute();
                    $history_result = $history_stmt->get_result();
                    $history = $history_result->fetch_all(MYSQLI_ASSOC);
                    $history_stmt->close();
                }

                // If history is empty, create a synthetic first entry
                if (empty($history) && $shipment) {
                    $history[] = [
                        'id' => '0',
                        'shipment_id' => $shipment['id'],
                        'timestamp' => $shipment['last_updated'] ?? date('Y-m-d H:i:s'),
                        'location' => $shipment['sender_address'],
                        'description' => 'Informasi pengiriman telah dibuat.',
                        'status' => 'Pending'
                    ];
                }

                // Let JavaScript handle date formatting for better consistency
                $tracking_data = [
                    'tracking_number' => $shipment['tracking_number'] ?? null,
                    'status' => $shipment['status'] ?? 'N/A',
                    'last_updated' => $shipment['last_updated'] ?? null,
                    'sender' => [
                        'name' => $shipment['sender_name'] ?? 'N/A',
                        'phone' => $shipment['sender_phone'] ?? 'N/A',
                        'address' => $shipment['sender_address'] ?? 'N/A'
                    ],
                    'receiver' => [
                        'name' => $shipment['receiver_name'] ?? 'N/A',
                        'phone' => $shipment['receiver_phone'] ?? 'N/A',
                        'address' => $shipment['receiver_address'] ?? 'N/A'
                    ],
                    'cargo' => [
                        'type' => $shipment['cargo_type'] ?? 'N/A',
                        'weight' => $shipment['cargo_weight'] ?? 'N/A',
                        'dimensions' => $shipment['cargo_dimensions'] ?? 'N/A',
                        'package_count' => $shipment['package_count'] ?? 0,
                        'special_instructions' => $shipment['special_instructions'] ?? ''
                    ],
                    'service_type' => $shipment['service_type'] ?? 'N/A',
                    'estimated_arrival' => $shipment['estimated_arrival'] ?? null, // Pass raw date
                    'history' => $history, // Pass raw history
                    'proof_of_delivery_url' => $shipment['proof_of_delivery_url'] ?? null,
                    
                    // == PENAMBAHAN UNTUK PETA ==
                    'current_lat' => $shipment['current_lat'] ?? null,
                    'current_long' => $shipment['current_long'] ?? null,
                    'dest_lat' => $shipment['dest_lat'] ?? null,
                    'dest_long' => $shipment['dest_long'] ?? null,
                    'current_location' => end($history)['location'] ?? 'N/A' // Ambil lokasi terkini dari riwayat terakhir
                ];

                $response = [
                    'success' => true,
                    'message' => 'Data ditemukan.',
                    'data' => $tracking_data
                ];

            } else {
                $response['message'] = "Nomor resi <strong>" . htmlspecialchars($resi) . "</strong> tidak ditemukan.";
            }
        } else {
            $response['message'] = 'Gagal mengeksekusi query: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Gagal mempersiapkan query: ' . $conn->error;
    }
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan pada server: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>