    <?php
    // check_distance.php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header('Content-Type: application/json');
    ob_start();

    // Service center coordinates (West Tambaram, Chennai)
    define('ADMIN_LAT_SERVER', 12.935160);
    define('ADMIN_LON_SERVER', 80.096417);
    define('MAX_DISTANCE_KM', 35);
    
    function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371) {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    $response = [
        'status' => 'error',
        'message' => 'Location verification failed. Please try again.',
        'distance_km' => null
    ];

    try {
        $address = trim($_POST['address'] ?? '');
        $pin = trim($_POST['pin'] ?? '');

        // Basic validation
        if (empty($address)) {
            $response['message'] = 'Please provide your complete address.';
            echo json_encode($response);
            exit();
        }

        if (empty($pin)) {
            $response['message'] = 'Please provide your 6-digit PIN code.';
            echo json_encode($response);
            exit();
        }

        if (!preg_match('/^\d{6}$/', $pin)) {
            $response['message'] = 'Invalid PIN format. Must be 6 digits.';
            echo json_encode($response);
            exit();
        }

        // Prepare address for OpenCage (don't include PIN in the geocode query)
        $cleanAddress = preg_replace('/\b\d{6}\b/', '', $address); // Remove PIN if present
        $cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress), " ,"); // Clean extra spaces
        $fullAddressForOpenCage = "$cleanAddress, India";

        $encodedAddress = urlencode($fullAddressForOpenCage);
        
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $opencageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $apiResponse = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $response['message'] = 'Temporary service issue. Please try again.';
            error_log("OpenCage cURL error: " . curl_error($ch));
        } elseif ($httpStatus != 200) {
            $response['message'] = 'Temporary service issue. Please try again later.';
            error_log("OpenCage HTTP error $httpStatus: $apiResponse");
        } else {
            $data = json_decode($apiResponse, true);
            
            if (empty($data['results'])) {
                $response['message'] = 'Could not verify location. Please check your address.';
            } else {
                $result = $data['results'][0];
                $userLat = (float) $result['geometry']['lat'];
                $userLon = (float) $result['geometry']['lng'];
                $geocodedPin = $result['components']['postcode'] ?? null;
                $geocodedCountry = $result['components']['country_code'] ?? null;
                $areaName = $result['components']['county'] ?? $result['components']['city'] ?? 'your area';

                // Calculate distance first (more important than PIN match)
                $distance = haversineGreatCircleDistance(ADMIN_LAT_SERVER, ADMIN_LON_SERVER, $userLat, $userLon);
                $response['distance_km'] = round($distance, 1);
                
                if ($distance > MAX_DISTANCE_KM) {
                    $response['status'] = 'rejected';
                    $response['message'] = 'Oops!--- We are sorry, but our services are currently unavailable in this location.
    We’re working to expand soon!';
                    echo json_encode($response);
                    exit();
                }
                // Define valid PIN code prefixes for your service area
$validPinPrefixes = ['600']; // Chennai PIN prefixes
$providedPinPrefix = substr($pin, 0, 3);

if (!in_array($providedPinPrefix, $validPinPrefixes)) {
    $response['status'] = 'rejected';
    $response['message'] = 'Service is only available for Chennai PIN codes (600xxx)';
    echo json_encode($response);
    exit();
}

                // Then verify PIN (but don't reject if PIN doesn't match but distance is OK)
                if (!empty($geocodedPin) && (string)$geocodedPin !== (string)$pin) {
                    // Log the mismatch but don't reject if within service area
                    error_log("PIN mismatch for $cleanAddress: Provided $pin vs Geocoded $geocodedPin");
                }

                if (strtolower($geocodedCountry) !== 'in') {
                    $response['status'] = 'rejected';
                    $response['message'] = 'Service is only available within India.';
                } else {
                    $response['status'] = 'success';
                    $response['message'] = 'Welcome to our Service Center!
    We are glad to serve you at our Tamil Nadu location.
    Feel free to proceed with your request.';
                    $response['user_area'] = $areaName;
                }
            }
        }
        
        curl_close($ch);
    } catch (Exception $e) {
        $response['message'] = 'System error. Please try again.';
        error_log("Exception in check_distance.php: " . $e->getMessage());
    }

    $response['debug']['output'] = ob_get_clean();
    echo json_encode($response);