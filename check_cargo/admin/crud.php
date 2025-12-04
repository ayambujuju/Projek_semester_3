<?php
session_start();
require '../partials/db.php';

// Security check: ensure user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

function redirect_with_message($status, $message) {
    header("Location: tracking.php?status=$status&message=" . urlencode($message));
    exit;
}

switch ($action) {
    case 'create_shipment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $conn->prepare("INSERT INTO shipments (tracking_number, status, sender_name, sender_phone, sender_address, receiver_name, receiver_phone, receiver_address, cargo_type, cargo_weight, cargo_dimensions, package_count, special_instructions, service_type, estimated_arrival, dest_lat, dest_long) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                redirect_with_message('error', 'Database prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("sssssssssssisssss",
                $_POST['tracking_number'],
                $_POST['status'],
                $_POST['sender_name'],
                $_POST['sender_phone'],
                $_POST['sender_address'],
                $_POST['receiver_name'],
                $_POST['receiver_phone'],
                $_POST['receiver_address'],
                $_POST['cargo_type'],
                $_POST['cargo_weight'],
                $_POST['cargo_dimensions'],
                $_POST['package_count'],
                $_POST['special_instructions'],
                $_POST['service_type'],
                $_POST['estimated_arrival'],
                $_POST['dest_lat'],
                $_POST['dest_long']
            );

            if ($stmt->execute()) {
                redirect_with_message('success', 'New shipment created successfully.');
            } else {
                redirect_with_message('error', 'Error creating shipment: ' . $stmt->error);
            }
            $stmt->close();
        }
        break;

    case 'get_shipment':
        header('Content-Type: application/json');
        $response = ['success' => false, 'data' => null];
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($shipment = $result->fetch_assoc()) {
                        $response['success'] = true;
                        $response['data'] = $shipment;
                    }
                }
                $stmt->close();
            }
        }
        echo json_encode($response);
        exit;

    case 'update_shipment':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $conn->prepare("UPDATE shipments SET tracking_number = ?, status = ?, sender_name = ?, sender_phone = ?, sender_address = ?, receiver_name = ?, receiver_phone = ?, receiver_address = ?, cargo_type = ?, cargo_weight = ?, cargo_dimensions = ?, package_count = ?, special_instructions = ?, service_type = ?, estimated_arrival = ? WHERE id = ?");
            if ($stmt === false) {
                redirect_with_message('error', 'Database prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("sssssssssssisssi",
                $_POST['tracking_number'],
                $_POST['status'],
                $_POST['sender_name'],
                $_POST['sender_phone'],
                $_POST['sender_address'],
                $_POST['receiver_name'],
                $_POST['receiver_phone'],
                $_POST['receiver_address'],
                $_POST['cargo_type'],
                $_POST['cargo_weight'],
                $_POST['cargo_dimensions'],
                $_POST['package_count'],
                $_POST['special_instructions'],
                $_POST['service_type'],
                $_POST['estimated_arrival'],
                $_POST['id']
            );

            if ($stmt->execute()) {
                redirect_with_message('success', 'Shipment updated successfully.');
            } else {
                redirect_with_message('error', 'Error updating shipment: ' . $stmt->error);
            }
            $stmt->close();
        }
        break;

    case 'delete_shipment':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("DELETE FROM shipments WHERE id = ?");
            if ($stmt === false) {
                redirect_with_message('error', 'Database prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                redirect_with_message('success', 'Shipment deleted successfully.');
            } else {
                redirect_with_message('error', 'Error deleting shipment: ' . $stmt->error);
            }
            $stmt->close();
        }
        break;

    default:
        header('Location: tracking.php');
        exit;
}

$conn->close();