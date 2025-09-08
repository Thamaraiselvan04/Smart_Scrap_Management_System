<?php
// lodged-complain.php
ini_set('display_errors', 1); // For debugging: Display all PHP errors
ini_set('display_startup_errors', 1); // For debugging: Report startup errors
error_reporting(E_ALL); // For debugging: Report all error types

session_start();
include('includes/dbconnection.php'); // Your database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Path to your Composer autoloader

// Define Admin Godown Coordinates (YOUR PRECISE GODOWN LOCATION from Google Maps)
// These constants are duplicated from check_distance.php for server-side validation here.
define('ADMIN_LAT_SERVER', 12.935160); // West Tambaram, Chennai - VERIFY THIS IS YOUR EXACT PLOT COORDINATE
define('ADMIN_LON_SERVER', 80.096417); // West Tambaram, Chennai - VERIFY THIS IS YOUR EXACT PLOT COORDINATE
define('MAX_DISTANCE_KM', 35); // Max service distance in KM
define('OPENCAGE_API_KEY', '65c22dc1e71847bab95da587d2060c24'); // Your OpenCage API Key

// Haversine distance function - duplicated for server-side validation here.
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


// Handle reset functionality
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    unset($_SESSION['temp_form']);
    unset($_SESSION['selected_scraps']);
    header("Location: lodged-complain.php");
    exit();
}

// Restore form data from session if available (e.g., after returning from price_list.php)
if (isset($_SESSION['temp_form'])) {
    // Populate $_POST from $_SESSION['temp_form']
    foreach ($_SESSION['temp_form'] as $key => $value) {
        $_POST[$key] = $value;
    }
}

// Save form data to session before redirecting to price_list.php
// This must happen BEFORE the main form submission logic for 'submit'
if (isset($_GET['save']) && $_GET['save'] === '1') {
    // Explicitly capture all relevant form fields that should persist
    $_SESSION['temp_form'] = [
        'area' => $_POST['area'] ?? '',
        'locality' => $_POST['locality'] ?? '',
        'landmark' => $_POST['landmark'] ?? '',
        'address' => $_POST['address'] ?? '',
        'pin' => $_POST['pin'] ?? '',
        'note' => $_POST['note'] ?? '',
        'paymentmode' => $_POST['paymentmode'] ?? '',
        'upi' => $_POST['upi'] ?? '', // Crucially, capture UPI here
        'date' => $_POST['date'] ?? '',
        'time' => $_POST['time'] ?? '',
        'location_status_flag' => $_POST['location_status_flag'] ?? 'unverified',
        // 'garbagephoto' is a file, cannot be directly saved to session like this.
        // It will need to be re-uploaded if the form is fully reset.
        // For persistence across price_list.php redirect, it's generally fine
        // as the actual upload only happens on final 'submit'.
    ];
    header("Location: price_list.php");
    exit();
}

// Handle main form submission
if (isset($_POST['submit'])) {
    $area = $_POST['area'] ?? '';
    $locality = $_POST['locality'] ?? '';
    $landmark = $_POST['landmark'] ?? '';
    $address = $_POST['address'] ?? '';
    $pin = $_POST['pin'] ?? '';

    // --- Server-Side Location Verification (mirroring check_distance.php logic) ---
    $serverLocationStatus = 'unverified'; // Default to unverified
    $serverMessage = '';

    if (empty($address) || empty($pin)) {
        $serverMessage = 'Address and Pin Number must be provided.';
    } elseif (!preg_match('/^\d{6}$/', $pin)) {
        $serverMessage = 'Invalid PIN format. Must be 6 digits.';
    } else {
        // Prepare address for OpenCage (don't include PIN in the geocode query)
        $cleanAddress = preg_replace('/\b\d{6}\b/', '', $address); // Remove PIN if present
        $cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress), " ,"); // Clean extra spaces
        $fullAddressForOpenCage = "$cleanAddress, India";

        $encodedAddress = urlencode($fullAddressForOpenCage);
        $opencageUrl = "https://api.opencagedata.com/geocode/v1/json?q=$encodedAddress&key=" . OPENCAGE_API_KEY . "&countrycode=in&limit=1";
        
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
            $serverMessage = 'Temporary service issue. Please try again.';
            error_log("OpenCage cURL error in lodged-complain.php: " . curl_error($ch));
        } elseif ($httpStatus != 200) {
            $serverMessage = 'Temporary service issue. Please try again later.';
            error_log("OpenCage HTTP error $httpStatus in lodged-complain.php: $apiResponse");
        } else {
            $data = json_decode($apiResponse, true);
            
            if (empty($data['results'])) {
                $serverMessage = 'Could not verify location. Please check your address.';
            } else {
                $result = $data['results'][0];
                $userLat = (float) $result['geometry']['lat'];
                $userLon = (float) $result['geometry']['lng'];
                $geocodedPin = $result['components']['postcode'] ?? null;
                $geocodedCountry = $result['components']['country_code'] ?? null;

                $distance = haversineGreatCircleDistance(ADMIN_LAT_SERVER, ADMIN_LON_SERVER, $userLat, $userLon);
                
                if ($distance > MAX_DISTANCE_KM) {
                    $serverLocationStatus = 'rejected';
                    $serverMessage = 'Oops!--- We are sorry, but our services are currently unavailable in this location. We’re working to expand soon!';
                } elseif (strtolower($geocodedCountry) !== 'in') {
                    $serverLocationStatus = 'rejected';
                    $serverMessage = 'Service is only available within India.';
                } else {
                    $serverLocationStatus = 'verified';
                    $serverMessage = 'Location successfully verified.';
                    // Log PIN mismatch, but don't reject if distance is fine.
                    if (!empty($geocodedPin) && (string)$geocodedPin !== (string)$pin) {
                        error_log("Server-side PIN mismatch for $fullAddressForOpenCage: Provided $pin vs Geocoded $geocodedPin");
                    }
                }
            }
        }
        curl_close($ch);
    }
    
    if ($serverLocationStatus !== 'verified') {
        $_SESSION['swal_message'] = ['icon' => 'error', 'title' => 'Submission Blocked', 'text' => $serverMessage];
        header("Location: lodged-complain.php");
        exit();
    }
    // --- END Server-Side Location Verification ---

    // Validate scrap selection
    if (empty($_SESSION['selected_scraps']) || !is_array($_SESSION['selected_scraps'])) {
        $_SESSION['swal_message'] = ['icon' => 'error', 'title' => 'Selection Missing', 'text' => 'Please select at least one scrap type'];
        header("Location: lodged-complain.php");
        exit();
    }

    // Validate photo upload
    if (!isset($_FILES['garbagephoto']) || $_FILES['garbagephoto']['error'] != UPLOAD_ERR_OK) {
        $_SESSION['swal_message'] = ['icon' => 'error', 'title' => 'Photo Missing', 'text' => 'Please upload a photo of your scrap'];
        header("Location: lodged-complain.php");
        exit();
    }

    $uid = $_SESSION['uuid'];
    $note = $_POST['note'];
    $upi = $_POST['upi'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $paymentmode = $_POST['paymentmode'];

    $garbagephoto = $_FILES["garbagephoto"]["name"];
    $extension = strtolower(pathinfo($garbagephoto, PATHINFO_EXTENSION)); 
    $allowed_extensions = array("jpg", "jpeg", "png", "gif");

    if (!in_array($extension, $allowed_extensions)) {
        $_SESSION['swal_message'] = ['icon' => 'error', 'title' => 'Invalid Photo', 'text' => 'Garbage photo has invalid format. Only jpg / jpeg / png / gif allowed'];
    } else {
        $garbagephoto = md5($garbagephoto . time()) . "." . $extension; // Ensure unique filename
        move_uploaded_file($_FILES["garbagephoto"]["tmp_name"], "images/" . $garbagephoto);

        $complainnum = mt_rand(100000000, 999999999); // Generate a random complain number
        $scraptype = implode(", ", $_SESSION['selected_scraps']); // Convert array of scrap types to comma-separated string

        // Prepare and execute the INSERT statement
        // Removed UserLat, UserLon, and LocationStatus from the field list and bind parameters
        $sql = "INSERT INTO tbllodgedcomplain(UserID, ComplainNumber, Area, Locality, Landmark, Address, Photo, Note, Upi, Date, Scraptype, Pin, Time, Paymentmode)
                VALUES (:uid, :complainnum, :area, :locality, :landmark, :address, :garbagephoto, :note, :upi, :date, :scraptype, :pin, :time, :paymentmode)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':uid', $uid, PDO::PARAM_INT);
        $query->bindParam(':complainnum', $complainnum, PDO::PARAM_INT);
        $query->bindParam(':area', $area, PDO::PARAM_STR); 
        $query->bindParam(':locality', $locality, PDO::PARAM_STR); 
        $query->bindParam(':landmark', $landmark, PDO::PARAM_STR); 
        $query->bindParam(':address', $address, PDO::PARAM_STR); 
        $query->bindParam(':garbagephoto', $garbagephoto, PDO::PARAM_STR);
        $query->bindParam(':note', $note, PDO::PARAM_STR);
        $query->bindParam(':upi', $upi, PDO::PARAM_STR);
        $query->bindParam(':date', $date, PDO::PARAM_STR);
        $query->bindParam(':scraptype', $scraptype, PDO::PARAM_STR);
        $query->bindParam(':pin', $pin, PDO::PARAM_STR);
        $query->bindParam(':time', $time, PDO::PARAM_STR);
        $query->bindParam(':paymentmode', $paymentmode, PDO::PARAM_STR);

        if ($query->execute()) {
            $lastInsertId = $dbh->lastInsertId();

            // Fetch user and complaint details for email notifications
            $stmt = $dbh->prepare("
                SELECT u.Email, u.FullName, u.MobileNumber, lc.ComplainNumber, lc.Area, lc.Locality, lc.Landmark, lc.Address, lc.Date, lc.Time, lc.Scraptype, lc.Pin
                FROM tbllodgedcomplain lc
                JOIN tbluser u ON u.ID = lc.UserID
                WHERE lc.ID = :eid
                LIMIT 1
            ");
            $stmt->bindParam(':eid', $lastInsertId, PDO::PARAM_INT);
            $stmt->execute();
            $requestData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($requestData) {
                $mail = new PHPMailer(true);
                try {
                    // Configure SMTP settings for PHPMailer
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host      = 'smtp.gmail.com';
                    $mail->SMTPAuth  = true;
                    $mail->Username  = 'removed';
                    $mail->Password  = 'removed';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port      = 587;
                    $mail->SMTPSuccessStatusCodes = array(200, 220); // Add these for some servers
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );

                    // Send email to USER
                    $mail->setFrom('removed', 'Smart Scrap System');
                    $mail->addAddress($requestData['Email'], $requestData['FullName']);

                    $mail->isHTML(false);
                    $mail->Subject = "Your Request #{$requestData['ComplainNumber']} Is Received";
                    $mail->Body     =
                        "Dear {$requestData['FullName']},\n\n".
                        "Thank you! Your scrap pickup request (#{$requestData['ComplainNumber']}) has been received successfully.\n\n".
                        "Request Details:\n".
                        "- Scrap Type: {$requestData['Scraptype']}\n".
                        "- Pickup Date: {$requestData['Date']}\n".
                        "- Pickup Time: {$requestData['Time']}\n".
                        "- Location: {$requestData['Address']}, PIN: {$requestData['Pin']}\n\n";

                    $mail->Body .= "We will contact you shortly to confirm the pickup details.\n\n".
                                    "— The Smart Scrap Team";

                    $userEmailSent = $mail->send();
                    $mail->clearAddresses();

                    // Send email to ADMIN
                    $adminEmail = 'removed'; // Replace with your actual admin email
                    $mail->addAddress($adminEmail, 'Admin');

                    $mail->Subject = "New Scrap Pickup Request #{$requestData['ComplainNumber']}";
                    $mail->Body     =
                        "New scrap pickup request received:\n\n".
                        "Request #: {$requestData['ComplainNumber']}\n".
                        "Customer: {$requestData['FullName']}\n".
                        "Contact: {$requestData['MobileNumber']}\n".
                        "Email: {$requestData['Email']}\n".
                        "Scrap Type: {$requestData['Scraptype']}\n".
                        "Pickup Date: {$requestData['Date']}\n".
                        "Pickup Time: {$requestData['Time']}\n".
                        "Location:\n".
                        "   Area: {$requestData['Area']}\n".
                        "   Locality: {$requestData['Locality']}\n".
                        "   Landmark: {$requestData['Landmark']}\n".
                        "   Address: {$requestData['Address']}\n".
                        "   Pin: {$requestData['Pin']}\n";

                    $mail->Body .= "Payment Mode: {$paymentmode}\n".
                        (($paymentmode == 'UPI') ? "UPI: {$upi}\n" : "") .
                        (($note) ? "Additional Notes: {$note}\n" : "") .
                        "\nPlease process this request promptly.";

                    $adminEmailSent = $mail->send();

                    $emailStatus = "";
                    if ($userEmailSent && $adminEmailSent) {
                        $emailStatus = "Notification emails sent to you and admin.";
                    } elseif ($userEmailSent) {
                        $emailStatus = "Notification email sent to you (admin notification failed).";
                    } elseif ($adminEmailSent) {
                        $emailStatus = "Admin notified (your notification failed).";
                    } else {
                        $emailStatus = "Email notifications failed.";
                    }

                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                    $emailStatus = "Email error: " . $e->getMessage();
                }
            } else {
                $emailStatus = "No user data found for notification";
            }

            // Clear session data after successful submission
            unset($_SESSION['temp_form']);
            unset($_SESSION['selected_scraps']);

            // Set success message for SweetAlert and redirect
            $_SESSION['swal_message'] = ['icon' => 'success', 'title' => 'Request Lodged!', 'text' => 'Your Request has been lodged successfully. ' . $emailStatus];
            header("Location: lodged-complain.php");
            exit();
        } else {
            // Error in database insertion
            $_SESSION['swal_message'] = ['icon' => 'error', 'title' => 'Error', 'text' => 'Error saving your request. Please try again.'];
            header("Location: lodged-complain.php");
            exit();
        }
    }
}

// Get selected scraps from session for display
$selectedScraps = isset($_SESSION['selected_scraps']) && is_array($_SESSION['selected_scraps'])
    ? implode(', ', $_SESSION['selected_scraps']) : '';
?>


<!doctype html>
<html class="no-js" lang="en">

<head>
    <title>Smart Scrap System - Lodged Complain</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/color_skins.css">
    
    <link rel="icon" href="favicon.ico" type="image/x-icon" />

    <style>
        select, input, textarea {
            width: 100%;
            padding: .5rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }
        label { 
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        .required:after {
            content: " *";
            color: #dc3545;
        }
        #scrapError, #photoError { 
            display: none;
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        #garbagephoto[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #submitButton[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .input-group-append .input-group-text {
            background-color: #e9ecef;
            pointer-events: none;
        }
        #locationStatusMessage {
            font-weight: bold;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        #locationStatusMessage.success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        #locationStatusMessage.error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .optional-field:after {
            content: " (Optional)";
            color: #6c757d;
            font-weight: normal;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .btn-select-scrap {
            white-space: nowrap;
            margin-left: 10px;
        }
        #previewArea img {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .time-input-group {
            display: flex;
            align-items: center;
        }
        .time-input-group input {
            flex: 1;
        }
        .time-input-group .input-group-text {
            width: 100px;
        }
        .scrap-selection-container {
            display: flex;
            align-items: center;
        }
        .scrap-selection-container input {
            margin-right: 10px;
        }
    </style>
</head>

<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="page">
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2>Lodged Request</h2>
                            </div>
                            <div class="body">
                                <form method="POST" enctype="multipart/form-data" id="complaintForm">
                                    <input type="hidden" id="locationStatusFlag" name="location_status_flag" value="<?= htmlspecialchars($_POST['location_status_flag'] ?? 'unverified') ?>">

                                    <div class="form-section">
                                        <div class="form-section-title">Location Details</div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="optional-field">Area</label>
                                                    <input type="text" name="area" id="areaInput" class="form-control" value="<?= htmlspecialchars($_POST['area'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="optional-field">Locality</label>
                                                    <input type="text" name="locality" id="localityInput" class="form-control" value="<?= htmlspecialchars($_POST['locality'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="optional-field">Landmark</label>
                                            <input type="text" name="landmark" id="landmarkInput" class="form-control" value="<?= htmlspecialchars($_POST['landmark'] ?? '') ?>">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="required">Address</label>
                                            <textarea name="address" id="addressInput" class="form-control" rows="3" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required">Pin Number</label>
                                                    <input type="text" name="pin" id="pinInput" class="form-control" required maxlength="6" pattern="\d{6}" title="Please enter a 6-digit PIN code" value="<?= htmlspecialchars($_POST['pin'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="locationStatusMessage" class="mt-2"></div>
                                    </div>

                                    <div class="form-section">
                                        <div class="form-group">
                                            <label class="optional-field">Note (Optional)</label>
                                            <textarea name="note" id="noteInput" class="form-control" placeholder="Any additional instructions or message" rows="3"><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <div class="form-group">
                                            <label class="required">Payment Mode</label>
                                            <select name="paymentmode" id="paymentmode" class="form-control" required>
                                                <option value="">-- Select Payment Mode --</option>
                                                <option value="UPI" <?= (isset($_POST['paymentmode']) && $_POST['paymentmode'] == 'UPI') ? 'selected' : '' ?>>UPI</option>
                                                <option value="Cash" <?= (isset($_POST['paymentmode']) && $_POST['paymentmode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group" id="upi-group" style="display:none;">
                                            <label class="required">UPI-linked Mobile No</label>
                                            <input type="tel" name="upi" id="upiField" class="form-control" placeholder="Enter UPI-linked number" value="<?= htmlspecialchars($_POST['upi'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="form-section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required">Pickup Date</label>
                                                    <input type="date" name="date" id="pickupDate" class="form-control" required value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="required">Pickup Time</label>
                                                    <div class="time-input-group">
                                                        <input type="time" name="time" id="pickupTime" class="form-control" required value="<?= htmlspecialchars($_POST['time'] ?? '') ?>">
                                                        <span class="input-group-text" id="ampmIndicator"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="scrap-selection-container">
                                        <input type="text" id="scraptype" name="scraptype" class="form-control" value="<?= htmlspecialchars($selectedScraps) ?>" readonly required>
                                        <button type="button" id="selectScrapButton" class="btn btn-info bg-white btn-select-scrap">Select Scrap Types</button>
                                    </div><br>

                                                <hr>




                                    <div class="form-section">
                                        <div class="form-group">
                                            <label class="required">Photo</label>
                                            <input type="file" name="garbagephoto" id="garbagephoto" class="form-control-file" required>
                                            <small id="photoError" class="text-danger"></small>
                                            <div id="previewArea" style="margin-top:10px;"></div>
                                        </div>
                                    </div>

                                    <div class="form-group text-left">
                                        <button type="submit" name="submit" id="submitButton" class="btn btn-danger" disabled>Submit</button>
                                        <a href="lodged-complain.php?reset=1" class="btn btn-secondary ml-2">Reset</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <script src="../assets/vendor/parsleyjs/js/parsley.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
        // SweetAlert helper function
        function showSwal(options) {
            Swal.fire(options);
        }

        // Display PHP session messages (e.g., after redirects)
        <?php if (isset($_SESSION['swal_message'])) {
            $msg = $_SESSION['swal_message'];
            echo "showSwal(" . json_encode($msg) . ");";
            unset($_SESSION['swal_message']); // Clear the message after displaying
        } ?>

        document.addEventListener('DOMContentLoaded', function() {
            // --- References to input fields and form sections ---
            const complaintForm = document.getElementById('complaintForm');
            const areaInput = document.getElementById('areaInput');
            const localityInput = document.getElementById('localityInput');
            const landmarkInput = document.getElementById('landmarkInput');
            const addressInput = document.getElementById('addressInput');
            const pinInput = document.getElementById('pinInput');
            const locationStatusMessage = document.getElementById('locationStatusMessage');
            const locationStatusFlag = document.getElementById('locationStatusFlag'); // Hidden input

            const noteInput = document.getElementById('noteInput');
            const pickupDateInput = document.getElementById('pickupDate');
            const pickupTimeInput = document.getElementById('pickupTime');
            const ampmIndicator = document.getElementById('ampmIndicator');
            const scraptypeInput = document.getElementById('scraptype');
            const selectScrapButton = document.getElementById('selectScrapButton'); // Corrected ID
            const garbagephotoInput = document.getElementById('garbagephoto');
            const paymentmodeSelect = document.getElementById('paymentmode');
            const upiField = document.getElementById('upiField');
            const upiGroup = document.getElementById('upi-group');
            const submitButton = document.getElementById('submitButton');

            const scrapError = document.getElementById('scrapError');
            const photoError = document.getElementById('photoError');

            // --- Function to enable/disable all main form elements ---
            function setFormElementsEnabled(enable) {
                // Location input fields always remain enabled for user input
                areaInput.disabled = false;
                localityInput.disabled = false;
                landmarkInput.disabled = false;
                addressInput.disabled = false;
                pinInput.disabled = false;

                // Other form elements are toggled based on location verification
                noteInput.disabled = !enable;
                pickupDateInput.disabled = !enable;
                pickupTimeInput.disabled = !enable;
                paymentmodeSelect.disabled = !enable;
                scraptypeInput.disabled = !enable; // Disable the text input, not the button
                garbagephotoInput.disabled = !enable;
                submitButton.disabled = !enable;
                
                // Select Scrap button itself is always enabled
                if (selectScrapButton) {
                    selectScrapButton.disabled = false;
                }

                // Handle UPI field visibility and required state
                toggleUPI(); 
            }

            // --- Toggle UPI field visibility based on payment mode ---
            function toggleUPI() {
                const paymentMode = paymentmodeSelect.value;
                // Only show/require UPI if payment mode is UPI AND the paymentmodeSelect is itself enabled
                if (paymentMode === 'UPI' && !paymentmodeSelect.disabled) {
                    upiGroup.style.display = 'block';
                    upiField.required = true;
                } else {
                    upiGroup.style.display = 'none';
                    upiField.required = false;
                }
            }
          
            // --- Location Verification Logic (using check_distance.php) ---
            let verificationTimeout; // Debounce timer

            async function verifyLocation() {
                const address = addressInput.value.trim();
                const pin = pinInput.value.trim();
                let previousLocationStatus = locationStatusFlag.value; // Capture current status before update

                // Clear previous messages and disable fields if inputs are empty
                if (address === '' || pin === '') {
                    locationStatusMessage.style.display = 'block';
                    locationStatusMessage.classList.remove('success', 'error');
                    locationStatusMessage.textContent = 'Please enter your Address and Pin Number to check service availability.';
                    setFormElementsEnabled(false); // Disable dependent fields
                    locationStatusFlag.value = 'unverified';
                    return;
                }

                // Basic client-side PIN format validation
                if (!/^\d{6}$/.test(pin)) {
                    locationStatusMessage.style.display = 'block';
                    locationStatusMessage.classList.remove('success');
                    locationStatusMessage.classList.add('error');
                    locationStatusMessage.textContent = 'Invalid PIN format. Must be 6 digits.';
                    setFormElementsEnabled(false); // Disable dependent fields
                    locationStatusFlag.value = 'invalid_pin_format';
                    return;
                }

                locationStatusMessage.style.display = 'block';
                locationStatusMessage.classList.remove('success', 'error');
                locationStatusMessage.textContent = 'Verifying location...';
                // Temporarily disable *dependent* fields while verifying, if not already verified
                if (locationStatusFlag.value !== 'verified') {
                     setFormElementsEnabled(false);
                }

                try {
                    const formData = new FormData();
                    formData.append('address', address);
                    formData.append('pin', pin);

                    const response = await fetch('check_distance.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        console.log('Client-side: Location verified successfully via check_distance.php');
                        locationStatusMessage.classList.remove('error');
                        locationStatusMessage.classList.add('success');
                        locationStatusMessage.textContent = result.message;
                        setFormElementsEnabled(true);
                        locationStatusFlag.value = 'verified';
                        if (previousLocationStatus !== 'verified') { // Only show SweetAlert if status was not already verified
                            showSwal({
                                icon: 'success',
                                title: 'Location Verified',
                                text: result.message,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    } else if (result.status === 'rejected') {
                        console.log('Client-side: Location rejected via check_distance.php');
                        locationStatusMessage.classList.remove('success');
                        locationStatusMessage.classList.add('error');
                        locationStatusMessage.textContent = result.message;
                        setFormElementsEnabled(false);
                        locationStatusFlag.value = 'rejected';
                        showSwal({
                            icon: 'error',
                            title: 'Location Rejected',
                            text: result.message,
                            confirmButtonText: 'OK'
                        });
                    } else { // status === 'error' or any other unexpected status
                        console.error('Client-side: Error from check_distance.php:', result.message);
                        locationStatusMessage.classList.remove('success');
                        locationStatusMessage.classList.add('error');
                        locationStatusMessage.textContent = result.message || 'Error verifying location. Please try again.';
                        setFormElementsEnabled(false);
                        locationStatusFlag.value = 'error';
                        showSwal({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: result.message || 'An unexpected error occurred during location verification. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (error) {
                    console.error('Client-side: Fetch error during location verification:', error);
                    locationStatusMessage.classList.remove('success');
                    locationStatusMessage.classList.add('error');
                    locationStatusMessage.textContent = 'Network or server error. Please try again.';
                    setFormElementsEnabled(false);
                    locationStatusFlag.value = 'error';
                    showSwal({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: 'Unable to connect to verification service. Please check your internet connection or try again later.',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    // Ensure UPI field's required status is correctly set after enabling
                    toggleUPI(); 
                }
            }


            document.getElementById('selectScrapButton').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('complaintForm'));
                
                fetch('lodged-complain.php?save=1', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = 'price_list.php';
                    } else {
                        throw new Error('Failed to save form data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showSwal({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not save form data. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });
            });

            // Attach event listeners to address and pin input fields to trigger verification
            const primaryAddressFields = [addressInput, pinInput];
            primaryAddressFields.forEach(field => {
                field.addEventListener('input', () => {
                    clearTimeout(verificationTimeout);
                    verificationTimeout = setTimeout(verifyLocation, 800); // 800ms debounce delay
                });
                field.addEventListener('blur', () => {
                    clearTimeout(verificationTimeout);
                    verifyLocation(); // Verify immediately on blur
                });
            });

            // Initial location verification call if address and pin are pre-filled (e.g., from session or PHP reload)
            if (addressInput.value.trim() && pinInput.value.trim()) {
                console.log('Client-side: Pre-filled address/pin detected, initiating verification.');
                verifyLocation();
            } else {
                console.log('Client-side: No pre-filled address/pin, setting initial prompt.');
                setFormElementsEnabled(false); // All dependent fields disabled
                locationStatusMessage.style.display = 'block';
                locationStatusMessage.classList.add('error');
                locationStatusMessage.textContent = 'Please enter your Address and Pin Number to check service availability.'; // Initial prompt
            }

            // --- DATE PICKER SETUP (ensure this works with disabled state) ---
            if (pickupDateInput) {
              const today = new Date();
const maxDate = new Date();
maxDate.setDate(today.getDate() + 30); // 31 days from today

pickupDateInput.min = today.toISOString().split('T')[0]; // Set minimum date to today
pickupDateInput.max = maxDate.toISOString().split('T')[0]; // Set minimum date to today

                // Validate date and time selection
                const updateAmPmDisplay = () => {
                    const time = pickupTimeInput.value;
                    if (time) {
                        const [hours, minutes] = time.split(':').map(Number);
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const displayHours = hours % 12 || 12; // Convert 24hr to 12hr format
                        ampmIndicator.textContent = `${String(displayHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')} ${ampm}`;
                    } else {
                        ampmIndicator.textContent = '';
                    }
                };

                const validateDateTime = () => {
                    if (pickupDateInput.disabled || pickupTimeInput.disabled) return; // Do not validate if fields are disabled

                    const selectedDateStr = pickupDateInput.value;
                    const selectedTimeStr = pickupTimeInput.value;

                    if (!selectedDateStr || !selectedTimeStr) return;

                    const selectedDateTime = new Date(`${selectedDateStr}T${selectedTimeStr}:00`);
                    const now = new Date();

                    // If selected date is today, ensure time is in the future or valid current slot
                    const today = new Date();
                    today.setHours(0, 0, 0, 0); // Normalize today to start of day
                    const selectedDateOnly = new Date(selectedDateStr);
                    selectedDateOnly.setHours(0, 0, 0, 0); // Normalize selectedDate to start of day

                    if (selectedDateOnly.getTime() === today.getTime()) {
                        // For today, ensure time is not in the past
                        if (selectedDateTime < now) {
                            showSwal({
                                icon: 'warning',
                                title: 'Past Time',
                                text: "Selected time is in the past for today's date. Please select a future time.",
                                confirmButtonText: 'OK'
                            });

                            // Adjust time to nearest future half-hour, or next day if past 6 PM
                            let hours = now.getHours();
                            let minutes = now.getMinutes();
                            minutes = minutes < 30 ? 30 : 0; // Round up to next half hour
                            if (minutes === 0) hours += 1; // If rounded to 0, it means next hour

                            // If current time is past 6 PM, set date to tomorrow and time to 8 AM
                            if (hours >= 18) { // 6 PM
                                const tomorrow = new Date();
                                tomorrow.setDate(now.getDate() + 1);
                                pickupDateInput.value = tomorrow.toISOString().split('T')[0];
                                hours = 8; // 8 AM
                                minutes = 0;
                            }
                            pickupTimeInput.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                            updateAmPmDisplay();
                        }
                    } else if (selectedDateOnly < today) {
                        // If selected date is in the past, reset to today
                        showSwal({
                            icon: 'warning',
                            title: 'Past Date',
                            text: "Selected date is in the past. Please select today's date or a future date.",
                            confirmButtonText: 'OK'
                        });
                        pickupDateInput.value = today.toISOString().split('T')[0];
                        // Also reset time to current time or default
                        let hours = now.getHours();
                        let minutes = now.getMinutes();
                        minutes = minutes < 30 ? 30 : 0;
                        if (minutes === 0) hours += 1;
                        pickupTimeInput.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                        updateAmPmDisplay();
                    }
                };

                pickupDateInput.addEventListener('change', validateDateTime);
                pickupTimeInput.addEventListener('change', validateDateTime);
                pickupTimeInput.addEventListener('input', updateAmPmDisplay); // Update AM/PM as time is typed/changed
                updateAmPmDisplay(); // Initial display if time is pre-filled
            }

            // --- Event Listeners for other form elements ---
            paymentmodeSelect.addEventListener('change', toggleUPI);

            // --- Initial state setup ---
            toggleUPI(); // Ensure UPI state is correct on load based on $_POST['paymentmode']
            // setFormElementsEnabled(false) is called in the initial verifyLocation check if fields are empty.

            // --- Form Submission Validation ---
            complaintForm.addEventListener('submit', function(e) {
                // Ensure the form is submitted only if location is verified
                if (locationStatusFlag.value !== 'verified') {
                    e.preventDefault();
                    console.log('Client-side: Submission blocked - location not verified. Current status:', locationStatusFlag.value);

                    locationStatusMessage.style.display = 'block';
                    locationStatusMessage.classList.remove('success');
                    locationStatusMessage.classList.add('error');
                    let message = '';
                    if (locationStatusFlag.value === 'invalid_pin_format') {
                        message = 'Invalid Pin Number format.';
                    } else if (locationStatusFlag.value === 'rejected') {
                        message = 'Your location is outside our service area. Please correct your address or contact support.';
                    }
                    else {
                        message = 'Please verify your location to proceed with the request.';
                    }
                    locationStatusMessage.textContent = message;

                    showSwal({
                        icon: 'error',
                        title: 'Submission Blocked',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                    locationStatusMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                // Client-side validation for scrap and photo
                let isValid = true;

                // Validate scrap selection
                if (!scraptypeInput.value.trim()) {
                    scrapError.style.display = 'block';
                    scrapError.textContent = 'Please select at least one scrap type';
                    isValid = false;
                } else {
                    scrapError.style.display = 'none';
                    scrapError.textContent = '';
                }

                // Validate photo upload
                if (!garbagephotoInput.files || garbagephotoInput.files.length === 0) {
                    photoError.style.display = 'block';
                    photoError.textContent = 'Please upload a photo of your scrap';
                    isValid = false;
                } else {
                    photoError.style.display = 'none';
                    photoError.textContent = '';
                }

                // If any client-side validation failed, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                    console.log('Client-side: Other form validations failed.');
                    const firstErrorField = !scraptypeInput.value.trim() ? scraptypeInput : (!garbagephotoInput.files || garbagephotoInput.files.length === 0 ? garbagephotoInput : null);
                    if (firstErrorField) {
                        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    showSwal({
                        icon: 'error',
                        title: 'Missing Information',
                        text: 'Please fill in all required fields (marked with *) and upload a photo.',
                        confirmButtonText: 'OK'
                    });
                }
                console.log('Client-side: All client-side validations passed. Form will now attempt to submit.');
            });
            
            // Photo preview functionality
            const previewArea = document.getElementById('previewArea');
            if (garbagephotoInput) {
                garbagephotoInput.addEventListener('change', function() {
                    previewArea.innerHTML = ''; // Clear previous preview

                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.maxWidth = '200px';
                            img.style.maxHeight = '200px';
                            img.style.marginTop = '10px';
                            previewArea.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>