<?php
session_start();
require '../partials/db.php';

header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid action.'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        // ACTION: Get a shipment with its full history
        case 'get_shipment_with_history':
            $resi = $_GET['resi'] ?? null;
            if (!$resi) {
                $response['message'] = 'Tracking number is required.';
                break;
            }

            $stmt_shipment = $conn->prepare("SELECT * FROM shipments WHERE tracking_number = ?");
            if (!$stmt_shipment) throw new Exception("Prepare failed (shipment): " . $conn->error);
            $stmt_shipment->bind_param("s", $resi);
            $stmt_shipment->execute();
            $result_shipment = $stmt_shipment->get_result();
            
            if ($shipment = $result_shipment->fetch_assoc()) {
                $stmt_history = $conn->prepare("SELECT * FROM shipment_history WHERE shipment_id = ? ORDER BY timestamp DESC");
                if (!$stmt_history) throw new Exception("Prepare failed (history): " . $conn->error);
                $stmt_history->bind_param("i", $shipment['id']);
                $stmt_history->execute();
                $result_history = $stmt_history->get_result();
                $history = $result_history->fetch_all(MYSQLI_ASSOC);
                
                $shipment['history'] = $history;
                $response = ['success' => true, 'data' => $shipment];
                $stmt_history->close();
            } else {
                $response['message'] = 'Shipment not found for the provided tracking number.';
            }
            $stmt_shipment->close();
            break;

        // ACTION: Add a new history entry
        case 'add_history':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Invalid request method.';
                break;
            }
            $shipment_id = $_POST['shipment_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $location = $_POST['location'] ?? null;
            $description = $_POST['description'] ?? null;

            if (!$shipment_id || !$status || !$location || !$description) {
                $response['message'] = 'Missing required fields.';
                break;
            }

            $conn->begin_transaction();
            try {
                $stmt_insert = $conn->prepare("INSERT INTO shipment_history (shipment_id, status, description, location) VALUES (?, ?, ?, ?)");
                if (!$stmt_insert) throw new Exception("Prepare failed (insert): " . $conn->error);
                $stmt_insert->bind_param("isss", $shipment_id, $status, $description, $location);
                $stmt_insert->execute();
                $new_history_id = $conn->insert_id;
                $stmt_insert->close();

                $stmt_update = $conn->prepare("UPDATE shipments SET status = ? WHERE id = ?");
                if (!$stmt_update) throw new Exception("Prepare failed (update): " . $conn->error);
                $stmt_update->bind_param("si", $status, $shipment_id);
                $stmt_update->execute();
                $stmt_update->close();

                if (!empty($location)) {
                    if (function_exists('fetchGeocode')) {
                        $geocode_json = fetchGeocode($location);
                        if ($geocode_json) {
                            $geocode_data = json_decode($geocode_json, true);
                            if (!empty($geocode_data) && isset($geocode_data[0]['lat'], $geocode_data[0]['lon'])) {
                                $new_lat = $geocode_data[0]['lat'];
                                $new_lon = $geocode_data[0]['lon'];

                                $stmt_update_coords = $conn->prepare("UPDATE shipments SET current_lat = ?, current_long = ? WHERE id = ?");
                                if ($stmt_update_coords) {
                                    $stmt_update_coords->bind_param("ssi", $new_lat, $new_lon, $shipment_id);
                                    $stmt_update_coords->execute();
                                    $stmt_update_coords->close();
                                }
                            }
                        }
                    }
                }

                $conn->commit();
                $response = ['success' => true, 'message' => 'History entry added successfully.', 'new_history_id' => $new_history_id];

            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = "Transaction failed: " . $e->getMessage();
            }
            break;

        case 'delete_history':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Invalid request method.';
                break;
            }
            $history_id = $_POST['history_id'] ?? null;
            if (!$history_id) {
                $response['message'] = 'Missing history ID.';
                break;
            }

            $conn->begin_transaction();
            try {
                $shipment_id = null;
                $stmt_get_sid = $conn->prepare("SELECT shipment_id FROM shipment_history WHERE id = ?");
                if (!$stmt_get_sid) throw new Exception("Prepare failed (get_sid): " . $conn->error);
                $stmt_get_sid->bind_param("i", $history_id);
                $stmt_get_sid->execute();
                $stmt_get_sid->bind_result($shipment_id);
                $stmt_get_sid->fetch();
                $stmt_get_sid->close();

                if (!$shipment_id) {
                    throw new Exception("Could not find shipment associated with history item.");
                }

                $stmt_delete = $conn->prepare("DELETE FROM shipment_history WHERE id = ?");
                if (!$stmt_delete) throw new Exception("Prepare failed (delete): " . $conn->error);
                $stmt_delete->bind_param("i", $history_id);
                $stmt_delete->execute();
                $stmt_delete->close();
                
                $new_latest_status = 'Pending';
                $new_latest_ts = null;
                $new_latest_location = null;

                $stmt_latest = $conn->prepare("SELECT status, timestamp, location FROM shipment_history WHERE shipment_id = ? ORDER BY timestamp DESC LIMIT 1");
                if (!$stmt_latest) throw new Exception("Prepare failed (latest): " . $conn->error);
                $stmt_latest->bind_param("i", $shipment_id);
                $stmt_latest->execute();
                $stmt_latest->bind_result($new_latest_status_res, $new_latest_ts_res, $new_latest_location_res);
                if ($stmt_latest->fetch()) {
                    $new_latest_status = $new_latest_status_res;
                    $new_latest_ts = $new_latest_ts_res;
                    $new_latest_location = $new_latest_location_res;
                }
                $stmt_latest->close();
                
                $update_sql = "UPDATE shipments SET status = ?, last_updated = ? WHERE id = ?";
                $stmt_update_parent = $conn->prepare($update_sql);
                if (!$stmt_update_parent) throw new Exception("Prepare failed (update_parent): " . $conn->error);
                $stmt_update_parent->bind_param("ssi", $new_latest_status, $new_latest_ts, $shipment_id);
                $stmt_update_parent->execute();
                $stmt_update_parent->close();

                if (!empty($new_latest_location)) {
                     if (function_exists('fetchGeocode')) {
                        $geocode_json = fetchGeocode($new_latest_location);
                        if ($geocode_json) {
                            $geocode_data = json_decode($geocode_json, true);
                            if (!empty($geocode_data) && isset($geocode_data[0]['lat'], $geocode_data[0]['lon'])) {
                                $new_lat = $geocode_data[0]['lat'];
                                $new_lon = $geocode_data[0]['lon'];

                                $stmt_update_coords = $conn->prepare("UPDATE shipments SET current_lat = ?, current_long = ? WHERE id = ?");
                                if ($stmt_update_coords) {
                                    $stmt_update_coords->bind_param("ssi", $new_lat, $new_lon, $shipment_id);
                                    $stmt_update_coords->execute();
                                    $stmt_update_coords->close();
                                }
                            }
                        }
                    }
                } else {
                    $stmt_nullify_coords = $conn->prepare("UPDATE shipments SET current_lat = NULL, current_long = NULL WHERE id = ?");
                    if ($stmt_nullify_coords) {
                        $stmt_nullify_coords->bind_param("i", $shipment_id);
                        $stmt_nullify_coords->execute();
                        $stmt_nullify_coords->close();
                    }
                }

                $conn->commit();
                $response = ['success' => true, 'message' => 'History entry deleted successfully.'];

            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = "Transaction failed: " . $e->getMessage();
            }
            break;
            
        case 'get_history_item':
            $history_id = $_GET['history_id'] ?? null;
            if (!$history_id) {
                $response['message'] = 'Missing history ID.';
                break;
            }
            $stmt = $conn->prepare("SELECT * FROM shipment_history WHERE id = ?");
            if(!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("i", $history_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($history_item = $result->fetch_assoc()) {
                $response = ['success' => true, 'data' => $history_item];
            } else {
                $response['message'] = 'History item not found.';
            }
            $stmt->close();
            break;

        case 'update_history':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $response['message'] = 'Invalid request method.';
                break;
            }
            $history_id = $_POST['history_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $location = $_POST['location'] ?? null;
            $description = $_POST['description'] ?? null;

            if (!$history_id || !$status || !$location || !$description) {
                $response['message'] = 'Missing required fields for update.';
                break;
            }

            $conn->begin_transaction();
            try {
                $stmt_update = $conn->prepare("UPDATE shipment_history SET status = ?, description = ?, location = ? WHERE id = ?");
                if (!$stmt_update) throw new Exception("Prepare failed (update): " . $conn->error);
                $stmt_update->bind_param("sssi", $status, $description, $location, $history_id);
                $stmt_update->execute();
                $stmt_update->close();

                $shipment_id = null;
                $stmt_get_sid = $conn->prepare("SELECT shipment_id FROM shipment_history WHERE id = ?");
                if (!$stmt_get_sid) throw new Exception("Prepare failed (get_sid): " . $conn->error);
                $stmt_get_sid->bind_param("i", $history_id);
                $stmt_get_sid->execute();
                $stmt_get_sid->bind_result($shipment_id);
                $stmt_get_sid->fetch();
                $stmt_get_sid->close();

                if (!$shipment_id) throw new Exception("Could not find parent shipment.");

                $latest_id = null;
                $stmt_check = $conn->prepare("SELECT id FROM shipment_history WHERE shipment_id = ? ORDER BY timestamp DESC LIMIT 1");
                if (!$stmt_check) throw new Exception("Prepare failed (check): " . $conn->error);
                $stmt_check->bind_param("i", $shipment_id);
                $stmt_check->execute();
                $stmt_check->bind_result($latest_id);
                $stmt_check->fetch();
                $stmt_check->close();
                
                if ($latest_id == $history_id) {
                    $stmt_update_parent = $conn->prepare("UPDATE shipments SET status = ? WHERE id = ?");
                    if (!$stmt_update_parent) throw new Exception("Prepare failed (update_parent): " . $conn->error);
                    $stmt_update_parent->bind_param("si", $status, $shipment_id);
                    $stmt_update_parent->execute();
                    $stmt_update_parent->close();

                    if (!empty($location) && function_exists('fetchGeocode')) {
                        $geocode_json = fetchGeocode($location);
                        if ($geocode_json) {
                            $geocode_data = json_decode($geocode_json, true);
                            if (!empty($geocode_data) && isset($geocode_data[0]['lat'], $geocode_data[0]['lon'])) {
                                $new_lat = $geocode_data[0]['lat'];
                                $new_lon = $geocode_data[0]['lon'];

                                $stmt_update_coords = $conn->prepare("UPDATE shipments SET current_lat = ?, current_long = ? WHERE id = ?");
                                if ($stmt_update_coords) {
                                    $stmt_update_coords->bind_param("ssi", $new_lat, $new_lon, $shipment_id);
                                    $stmt_update_coords->execute();
                                    $stmt_update_coords->close();
                                }
                            }
                        }
                    }
                }

                $conn->commit();
                $response = ['success' => true, 'message' => 'History entry updated successfully.'];

            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = "Transaction failed: " . $e->getMessage();
            }
            break;

        default:
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Server Error: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);


// --- Geocoding Functions (Copied from api/geocode.php) ---

if (!defined('GEOCODE_CACHE_DIR')) {
    define('GEOCODE_CACHE_DIR', __DIR__ . '/../api/geocode_cache');
}
if (!defined('GEOCODE_CACHE_EXPIRY')) {
    define('GEOCODE_CACHE_EXPIRY', 86400 * 30);
}

if (!function_exists('getCacheKey')) {
    function getCacheKey($locationQuery) {
        return md5(strtolower(trim($locationQuery)));
    }
}

if (!function_exists('getFromCache')) {
    function getFromCache($key) {
        if (!is_dir(GEOCODE_CACHE_DIR)) {
            mkdir(GEOCODE_CACHE_DIR, 0755, true);
        }
        $cacheFile = GEOCODE_CACHE_DIR . '/' . $key . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < GEOCODE_CACHE_EXPIRY) {
            return file_get_contents($cacheFile);
        }
        return null;
    }
}

if (!function_exists('saveToCache')) {
    function saveToCache($key, $data) {
        if (!is_dir(GEOCODE_CACHE_DIR)) {
            mkdir(GEOCODE_CACHE_DIR, 0755, true);
        }
        $cacheFile = GEOCODE_CACHE_DIR . '/' . $key . '.json';
        file_put_contents($cacheFile, $data);
    }
}

if (!function_exists('fetchGeocode')) {
    function fetchGeocode($locationQuery) {
        if (empty($locationQuery)) {
            return null;
        }

        $cacheKey = getCacheKey($locationQuery);
        $cachedResult = getFromCache($cacheKey);

        if ($cachedResult !== null) {
            return $cachedResult;
        }

        $baseUrl = 'https://nominatim.openstreetmap.org/search';
        $query = urlencode($locationQuery . ', Indonesia');
        $url = "{$baseUrl}?q={$query}&format=json&limit=1";

        $options = [
            'http' => [
                'header' => "User-Agent: Gemini-Check-Cargo-App/1.0 (dev@localhost.dev)\r\n",
                'timeout' => 10
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response !== false) {
            $data = json_decode($response, true);
            if (!empty($data)) {
                saveToCache($cacheKey, $response);
                return $response;
            }
        } else {
             error_log("Geocode API (CRUD): file_get_contents failed for URL: " . $url);
        }
        
        $words = explode(' ', str_replace(',', '', strtoupper($locationQuery)));
        $common_words_to_remove = ['GUDANG', 'SORTING', 'CENTER', 'HUB', 'WAREHOUSE', 'KANTOR', 'CABANG', 'AGEN', 'DC', 'TRANSIT'];
        $filtered_words = array_diff($words, $common_words_to_remove);
        $simplified_location = trim(implode(' ', $filtered_words));

        if (!empty($simplified_location) && strtolower($simplified_location) !== strtolower($locationQuery)) {
             $response = fetchGeocode($simplified_location); 
             if ($response !== null) {
                return $response;
             }
        }
        
        error_log("Geocode API (CRUD): All attempts failed for location: '{$locationQuery}'");
        return null;
    }
}
?>