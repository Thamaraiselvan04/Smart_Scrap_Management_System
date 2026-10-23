<?php
// check_distance.php  (no spaces before the <?php above!)
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(E_ALL);

// Always send clean JSON, even if something crashes
function sendJson($data) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        sendJson([
            'status'  => 'error',
            'message' => 'Server error: ' . $e['message'] . ' (line ' . $e['line'] . ')',
        ]);
    }
});

// Safe logger: writes to a file, never to stderr (IIS turns stderr into HTTP 500)
function safeLog($msg) {
    @file_put_contents(__DIR__ . '/geocoding_errors.log', date('Y-m-d H:i:s') . ' - ' . $msg . PHP_EOL, FILE_APPEND);
}

// ===== SETTINGS =====
define('ADMIN_LAT_SERVER', 12.935160);   // West Tambaram, Chennai
define('ADMIN_LON_SERVER', 80.096417);
define('MAX_DISTANCE_KM', 35);
define('OPENCAGE_API_KEY', 'myap');
// ====================

function haversineGreatCircleDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371) {
    $latFrom = deg2rad($latFrom);
    $lonFrom = deg2rad($lonFrom);
    $latTo   = deg2rad($latTo);
    $lonTo   = deg2rad($lonTo);
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

$response = [
    'status'      => 'error',
    'message'     => 'Location verification failed. Please try again.',
    'distance_km' => null,
];

$address = trim($_POST['address'] ?? '');
$pin     = trim($_POST['pin'] ?? '');

if ($address === '') {
    $response['message'] = 'Please provide your complete address.';
    sendJson($response);
}
if ($pin === '') {
    $response['message'] = 'Please provide your 6-digit PIN code.';
    sendJson($response);
}
if (!preg_match('/^\d{6}$/', $pin)) {
    $response['message'] = 'Invalid PIN format. Must be 6 digits.';
    sendJson($response);
}
if (substr($pin, 0, 3) !== '600') {
    $response['status']  = 'rejected';
    $response['message'] = 'Service is only available for Chennai PIN codes (600xxx)';
    sendJson($response);
}

if (!function_exists('curl_init')) {
    $response['message'] = 'cURL is not enabled in PHP. Enable extension=curl in php.ini and restart Apache.';
    sendJson($response);
}

// Remove PIN from address, then add India
$cleanAddress = preg_replace('/\b\d{6}\b/', '', $address);
$cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress), " ,");
$query = $cleanAddress . ', India';

$url = 'https://api.opencagedata.com/geocode/v1/json?q=' . urlencode($query)
     . '&key=' . OPENCAGE_API_KEY . '&countrycode=in&limit=1';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
// If you put cacert.pem in this folder, it is used automatically
if (file_exists(__DIR__ . '/cacert.pem')) {
    curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
}
$apiResponse = curl_exec($ch);
$httpStatus  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError   = curl_errno($ch) ? curl_error($ch) : '';
curl_close($ch);

if ($curlError !== '') {
    safeLog("OpenCage cURL error: $curlError");
    // Message shows the real reason so you can fix it
    $response['message'] = 'Cannot reach geocode service: ' . $curlError;
    sendJson($response);
}
if ($httpStatus == 401 || $httpStatus == 403) {
    $response['message'] = 'API key problem (HTTP ' . $httpStatus . '). Check your OpenCage key.';
    sendJson($response);
}
if ($httpStatus == 402 || $httpStatus == 429) {
    $response['message'] = 'API daily limit reached (HTTP ' . $httpStatus . '). Try again later.';
    sendJson($response);
}
if ($httpStatus != 200) {
    safeLog("OpenCage HTTP error $httpStatus: $apiResponse");
    $response['message'] = 'Geocode service error (HTTP ' . $httpStatus . ').';
    sendJson($response);
}

$data = json_decode($apiResponse, true);
if (empty($data['results'])) {
    $response['message'] = 'Could not verify location. Please check your address.';
    sendJson($response);
}

$result  = $data['results'][0];
$userLat = (float) $result['geometry']['lat'];
$userLon = (float) $result['geometry']['lng'];
$country = $result['components']['country_code'] ?? '';
$area    = $result['components']['county'] ?? $result['components']['city'] ?? 'your area';

$distance = haversineGreatCircleDistance(ADMIN_LAT_SERVER, ADMIN_LON_SERVER, $userLat, $userLon);
$response['distance_km'] = round($distance, 1);

if ($distance > MAX_DISTANCE_KM) {
    $response['status']  = 'rejected';
    $response['message'] = "Oops! We are sorry, but our services are currently unavailable in this location.\nWe're working to expand soon!";
    sendJson($response);
}
if (strtolower($country) !== 'in') {
    $response['status']  = 'rejected';
    $response['message'] = 'Service is only available within India.';
    sendJson($response);
}

$response['status']    = 'success';
$response['message']   = "Welcome to our Service Center!\nWe are glad to serve you at our Tamil Nadu location.\nFeel free to proceed with your request.";
$response['user_area'] = $area;
sendJson($response);