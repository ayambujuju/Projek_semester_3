<?php
// api/geocode.php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Define a directory for caching geocode results.
// Make sure this directory exists and is writable by the web server.
define('GEOCODE_CACHE_DIR', __DIR__ . '/geocode_cache');
define('GEOCODE_CACHE_EXPIRY', 86400 * 30); // Cache for 30 days

// Ensure the cache directory exists
if (!is_dir(GEOCODE_CACHE_DIR)) {
    mkdir(GEOCODE_CACHE_DIR, 0755, true);
}

function getCacheKey($locationQuery) {
    return md5(strtolower(trim($locationQuery)));
}

function getFromCache($key) {
    $cacheFile = GEOCODE_CACHE_DIR . '/' . $key . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < GEOCODE_CACHE_EXPIRY) {
        return file_get_contents($cacheFile);
    }
    return null;
}

function saveToCache($key, $data) {
    $cacheFile = GEOCODE_CACHE_DIR . '/' . $key . '.json';
    file_put_contents($cacheFile, $data);
}

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
            'timeout' => 10 // Increased timeout to 10 seconds
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
        error_log("Geocode API: file_get_contents failed for URL: " . $url);
    }
    
    return null;
}

if (!isset($_GET['location']) || empty(trim($_GET['location']))) {
    echo json_encode([]);
    exit;
}

$location = trim($_GET['location']);
// Clean up common typos - this can be expanded
$location = str_ireplace('JAKARTA TIMUT', 'JAKARTA TIMUR', $location);

$result = null;

try {
    $result = fetchGeocode($location);

    if ($result === null) {
        // Fallback strategy: simplify complex location strings
        // Example: "SORTING CENTER JAKARTA" -> "JAKARTA"
        $words = explode(' ', str_replace(',', '', strtoupper($location)));
        $common_words_to_remove = ['GUDANG', 'SORTING', 'CENTER', 'HUB', 'WAREHOUSE', 'KANTOR', 'CABANG', 'AGEN', 'DC', 'TRANSIT'];
        
        $filtered_words = array_diff($words, $common_words_to_remove);
        $simplified_location = trim(implode(' ', $filtered_words));

        if (!empty($simplified_location) && strtolower($simplified_location) !== strtolower($location)) {
             error_log("Geocode API: Original location '{$location}' failed. Trying simplified '{$simplified_location}'");
             $result = fetchGeocode($simplified_location);
        }
    }

    if ($result !== null) {
        echo $result;
    } else {
        // Log the location that failed to geocode for debugging
        error_log("Geocode API: All attempts failed for location: '{$location}'");
        echo json_encode([]);
    }

} catch (Exception $e) {
    error_log("Geocode API Exception: " . $e->getMessage());
    echo json_encode([]);
}

exit;