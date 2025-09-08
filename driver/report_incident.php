<?php
session_start();
error_reporting(0); // For production, consider enabling error reporting for debugging.
include('includes/dbconnection.php'); // Your database connection file

// PHPMailer includes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Adjust path if autoload.php is in a different location

// Initialize message variables
$page_message = '';
$message_class = ''; // 'success-message' or 'error-message'

// Define the directory where incident photos will be stored
$upload_directory = '../uploads/incident_photos/'; 

// --- Email Notification Function for Incident Reports ---
function sendIncidentEmailNotification($incidentData, $adminEmail, $adminPassword, $dbh) {
    $mail = new PHPMailer(true); // Passing true enables exceptions

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Or your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail; // Admin's Email (Sender)
        $mail->Password = $adminPassword; // Admin's Email Password (Sender) - DANGER: REPLACE WITH SECURE METHOD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
        $mail->Port = 587; // TLS port

        // Recipients
        $mail->setFrom($adminEmail, 'Incident Reporting System');
        $mail->addAddress($adminEmail, 'Admin'); // Send to Admin

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Incident Reported by Driver: ' . $incidentData['driverName'];
        
        $emailBody = "
            <html>
            <head>
                <title>New Incident Report</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
                    h2 { color: #d9534f; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .photo-section { margin-top: 20px; }
                    .photo-section img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; border: 1px solid #eee; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>🚨 New Incident Report 🚨</h2>
                    <p>An incident has been reported by a driver. Please review the details below:</p>
                    
                    <h3>Driver Information:</h3>
                    <table>
                        <tr><th>Driver ID</th><td>" . htmlspecialchars($incidentData['driverLoginID']) . "</td></tr>
                        <tr><th>Driver Name</th><td>" . htmlspecialchars($incidentData['driverName']) . "</td></tr>
                        <tr><th>Mobile Number</th><td>" . htmlspecialchars($incidentData['driverMobileNumber']) . "</td></tr>
                    </table>

                    <h3>Incident Details:</h3>
                    <table>
                        <tr><th>Incident Type</th><td>" . htmlspecialchars($incidentData['incidentType']) . "</td></tr>
                        <tr><th>Incident Date & Time</th><td>" . htmlspecialchars($incidentData['incidentDateTime']) . "</td></tr>
                        <tr><th>Related Complain ID</th><td>" . ($incidentData['relatedComplainID'] ? htmlspecialchars($incidentData['relatedComplainID']) : 'N/A') . "</td></tr>
                        <tr><th>Location Description</th><td>" . ($incidentData['incidentLocationDescription'] ? htmlspecialchars($incidentData['incidentLocationDescription']) : 'N/A') . "</td></tr>
                        <tr><th>Incident Details</th><td>" . htmlspecialchars($incidentData['incidentDetails']) . "</td></tr>
                    </table>";

        // Add photo links if available
        if (!empty($incidentData['photoPaths'])) {
            $emailBody .= "<div class='photo-section'><h3>Incident Photos:</h3><ul>";
            foreach ($incidentData['photoPaths'] as $path) {
                // Assuming your base URL is available. Adjust this base URL as needed.
                // For example, if your application is at 'http://yourdomain.com/myapp/',
                // and photos are in 'your_app/uploads/incident_photos/', then the base URL should be 'http://yourdomain.com/myapp/'
                // For local development, it might be 'http://localhost/your_app/'
                $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/"; // Adjust this base URL if your app is in a subfolder
                
                // Example: If your app is in a subfolder like 'scrap_management_system/', then:
                // $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/scrap_management_system/";
                
                $full_photo_url = $base_url . $path; // Path from DB is like 'uploads/incident_photos/...'
                $emailBody .= "<li><a href='" . htmlspecialchars($full_photo_url) . "' target='_blank'>" . basename($path) . "</a></li>";
            }
            $emailBody .= "</ul><small>Note: Photos are linked. You may need to be logged in to view them if access is restricted.</small></div>";
        }

        $emailBody .= "
                </div>
            </body>
            </html>";
        
        $mail->Body = $emailBody;

        $mail->send();
        error_log("Incident email notification sent successfully to " . $adminEmail);
        return true;
    } catch (Exception $e) {
        error_log("Incident Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        // You might want to return false here or handle differently
        return false;
    }
}


// Check if driver is logged in using a robust method
if (!isset($_SESSION['vamsid']) || empty($_SESSION['vamsid'])) {
    header('location:logout.php');
    exit(); // Always exit after redirect to prevent further code execution
} else {
    $did = $_SESSION['vamsid']; // The 'ID' from tbldriver table for the logged-in driver

    // --- Fetch driver details from database ---
    $driverLoginID = '';
    $driverName = '';
    $driverMobileNumber = '';

    try {
        $sql_driver = "SELECT DriverID, Name, MobileNumber FROM tbldriver WHERE ID = :did";
        $query_driver = $dbh->prepare($sql_driver);
        $query_driver->bindParam(':did', $did, PDO::PARAM_INT);
        $query_driver->execute();
        $driverDetails = $query_driver->fetch(PDO::FETCH_OBJ);

        if ($driverDetails) {
            $driverLoginID = $driverDetails->DriverID;
            $driverName = $driverDetails->Name;
            $driverMobileNumber = $driverDetails->MobileNumber;
        } else {
            error_log("Driver details not found for session ID: " . $did);
            $page_message = "Your driver account details could not be found. Please login again.";
            $message_class = "error-message";
        }
    } catch (PDOException $e) {
        error_log("Database error fetching driver details: " . $e->getMessage());
        $page_message = "A database error occurred. Please try again later.";
        $message_class = "error-message";
    }

    // --- Handle form submission ---
    if (isset($_POST['submit_incident'])) {
        $related_complain_id = empty($_POST['relatedComplainID']) ? NULL : (int)$_POST['relatedComplainID'];
        $incident_type = htmlspecialchars($_POST['incidentType'], ENT_QUOTES, 'UTF-8');
        $incident_location_desc = htmlspecialchars($_POST['incidentLocationDescription'], ENT_QUOTES, 'UTF-8');
        $incident_details = htmlspecialchars($_POST['incidentDetails'], ENT_QUOTES, 'UTF-8');
        $incident_datetime_str = $_POST['incidentDateTime']; // Date/time from input

        // Basic validation for required fields
        if (empty($incident_type) || empty($incident_details) || empty($incident_datetime_str)) {
            $page_message = "Please fill in all required fields (Incident Type, Details, and Date/Time).";
            $message_class = "error-message";
        } else {
            // Format for MySQL DATETIME
            $incident_datetime = date('Y-m-d H:i:s', strtotime($incident_datetime_str));

            $uploaded_photo_paths = []; // Array to store paths of successfully uploaded photos
            $upload_success = true; // Flag to track overall upload status

            // Handle photo uploads ONLY from 'incidentPhotos[]'
            if (isset($_FILES['incidentPhotos']) && is_array($_FILES['incidentPhotos']['name'])) {
                // Ensure upload directory exists and is writable
                if (!is_dir($upload_directory)) {
                    if (!mkdir($upload_directory, 0755, true)) { // Create recursively with permissions
                        error_log("Failed to create upload directory: " . $upload_directory . ". Check parent directory permissions.");
                        $page_message = "Server error: Could not create upload directory. Please contact support.";
                        $message_class = "error-message";
                        $upload_success = false; // Mark upload as failed
                    }
                }

                // Proceed with file upload only if directory exists and is writable
                if ($upload_success && is_dir($upload_directory) && is_writable($upload_directory)) {
                    $total_files = count($_FILES['incidentPhotos']['name']);
                    for ($i = 0; $i < $total_files; $i++) {
                        // Check if file was actually selected for this specific index and if it's a valid upload
                        if (!empty($_FILES['incidentPhotos']['name'][$i]) && $_FILES['incidentPhotos']['error'][$i] === UPLOAD_ERR_OK) {
                            $file_name = $_FILES['incidentPhotos']['name'][$i];
                            $file_tmp_name = $_FILES['incidentPhotos']['tmp_name'][$i];
                            $file_size = $_FILES['incidentPhotos']['size'][$i];
                            $file_error = $_FILES['incidentPhotos']['error'][$i];
                            $file_type = $_FILES['incidentPhotos']['type'][$i];

                            // Define allowed extensions for validation
                            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf']; // Added PDF as an example for documents
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                            // Basic file type and size validation
                            if (in_array($file_ext, $allowed_extensions) && $file_size < 5000000) { // Max 5MB
                                $new_file_name = uniqid('incident_', true) . '.' . $file_ext;
                                // Store path in DB relative to website root for consistent access
                                $db_file_path = 'uploads/incident_photos/' . $new_file_name; 
                                $destination_on_server = $upload_directory . $new_file_name;

                                if (move_uploaded_file($file_tmp_name, $destination_on_server)) {
                                    $uploaded_photo_paths[] = $db_file_path; // Store relative path to web root
                                } else {
                                    error_log("Failed to move uploaded file: " . $file_tmp_name . " to " . $destination_on_server);
                                    $page_message = "Error uploading some files. Please try again.";
                                    $message_class = "error-message";
                                    $upload_success = false;
                                }
                            } else {
                                $page_message = "Invalid file type or file too large (max 5MB). Allowed: JPG, JPEG, PNG, GIF, PDF.";
                                $message_class = "error-message";
                                $upload_success = false; // Mark upload as failed
                            }
                        } else if ($_FILES['incidentPhotos']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                            // Handle other upload errors (e.g., UPLOAD_ERR_INI_SIZE) for specific files
                            error_log("File upload error: " . $_FILES['incidentPhotos']['error'][$i] . " for file " . $_FILES['incidentPhotos']['name'][$i]);
                            $page_message = "An unexpected error occurred during file upload for one or more files.";
                            $message_class = "error-message";
                            $upload_success = false; // Mark upload as failed
                        }
                    }
                } else if ($upload_success) { // Only if $upload_success is still true (meaning mkdir didn't fail)
                    $page_message = "Upload directory is not writable or does not exist. Please check server permissions.";
                    $message_class = "error-message";
                    error_log("Upload directory issues: " . $upload_directory . " is not a directory or not writable.");
                    $upload_success = false;
                }
            }

            // Only proceed with DB insertion and email if general validation and uploads were successful
            if ($upload_success && empty($page_message)) { // Check $page_message again in case file upload errors set it
                // Convert photo paths array to JSON string for database storage
                $photo_paths_json = json_encode($uploaded_photo_paths);

                // Insert into tblincident_reports table
                try {
                    $sql_insert = "INSERT INTO tblincident_reports (DriverDBID, DriverLoginID, DriverName, DriverMobileNumber,
                                    RelatedComplainID, IncidentType, IncidentLocationDescription, IncidentDetails, PhotoPaths, IncidentDateTime)
                                    VALUES (:driverdbid, :driverloginid, :drivername, :drivermobilenumber,
                                    :related_complain_id, :incident_type, :incident_location_desc, :incident_details, :photo_paths, :incident_datetime)";

                    $query_insert = $dbh->prepare($sql_insert);

                    // Bind parameters
                    $query_insert->bindParam(':driverdbid', $did, PDO::PARAM_INT);
                    $query_insert->bindParam(':driverloginid', $driverLoginID, PDO::PARAM_STR);
                    $query_insert->bindParam(':drivername', $driverName, PDO::PARAM_STR);
                    $query_insert->bindParam(':drivermobilenumber', $driverMobileNumber, PDO::PARAM_INT);
                    $query_insert->bindParam(':related_complain_id', $related_complain_id, PDO::PARAM_INT);
                    $query_insert->bindParam(':incident_type', $incident_type, PDO::PARAM_STR);
                    $query_insert->bindParam(':incident_location_desc', $incident_location_desc, PDO::PARAM_STR);
                    $query_insert->bindParam(':incident_details', $incident_details, PDO::PARAM_STR);
                    $query_insert->bindParam(':photo_paths', $photo_paths_json, PDO::PARAM_STR); // Bind JSON string
                    $query_insert->bindParam(':incident_datetime', $incident_datetime, PDO::PARAM_STR);

                    if ($query_insert->execute()) {
                        // Prepare data for email notification
                        $incident_data_for_email = [
                            'driverLoginID' => $driverLoginID,
                            'driverName' => $driverName,
                            'driverMobileNumber' => $driverMobileNumber,
                            'relatedComplainID' => $related_complain_id,
                            'incidentType' => $incident_type,
                            'incidentLocationDescription' => $incident_location_desc,
                            'incidentDetails' => $incident_details,
                            'incidentDateTime' => $incident_datetime_str,
                            'photoPaths' => $uploaded_photo_paths // Pass the array of paths
                        ];

                   
                        // DANGER: Replace with your actual admin email and a secure way to get the password
                        $adminEmailForNotification = 'removed'; // !!! IMPORTANT: Replace with your admin's email !!!
                        $adminEmailPassword = 'removed'; // !!! IMPORTANT: Replace with your admin's email password or fetch securely !!!

                        // Send email notification
                        if (sendIncidentEmailNotification($incident_data_for_email, $adminEmailForNotification, $adminEmailPassword, $dbh)) {
                            // Redirect with success message
                            header('location: report_incident.php?status=success');
                            exit();
                        } else {
                            // Incident reported but email failed. Log and inform user.
                            $page_message = "Incident reported successfully, but the notification email could not be sent. Please inform the admin manually.";
                            $message_class = "error-message"; // Still an error for the user
                            // Do not redirect as the message needs to be shown
                        }
                    } else {
                        $page_message = "Error reporting incident. Please try again.";
                        $message_class = "error-message";
                        error_log("Database insertion failed for incident report: " . implode(" ", $query_insert->errorInfo()));
                    }
                } catch (PDOException $e) {
                    error_log("Database insertion PDO error for incident report: " . $e->getMessage());
                    $page_message = "An error occurred while saving your report. Please try again.";
                    $message_class = "error-message";
                }
            } else if (!$upload_success && empty($page_message)) {
                // If upload_success is false but page_message was not set, set a generic error
                $page_message = "An error occurred during file upload. Please check file types and sizes.";
                $message_class = "error-message";
            }
        }
    }

    // Check for success status from redirect
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        $page_message = "Incident reported successfully! We will review it shortly. ✅";
        $message_class = "success-message";
    }
?>
<!doctype html>
<html lang="en">
<head>
    <title>Report Incident</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css" type="text/css">
    <style>
        /* General form styling */
        .container-custom-form {
            padding: 20px;
        }
        .form-title {
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        /* Read-only inputs */
        .form-group input[readonly] {
            background-color: #f8f8f8;
            cursor: not-allowed;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 5px;
            color: #4a5568;
        }
        /* Styling for all form controls */
        textarea.form-control, input.form-control, select.form-control {
            min-height: 40px;
            resize: vertical;
            border-radius: 8px;
            padding: 12px 15px;
            border: 1.5px solid#000000;
            width: 100%; /* Ensure inputs take full width of their column */
        }
        textarea.form-control:focus, input.form-control:focus, select.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            outline: none;
        }
        /* Submit button custom styling */
        .btn-submit-custom {
            background: linear-gradient(to right, #ef4444, #dc2626); /* Red gradient for incident reporting */
            color: white;
            padding: 14px 25px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.1s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
        }
        .btn-submit-custom:hover {
            background: linear-gradient(to right, #dc2626, #ef4444);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
        }
        .btn-submit-custom:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }
        /* Message display styles */
        .message-area {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Report Incident</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <div class="form-title">Report a Trip Incident 🚨</div>
                            </div>
                            <div class="body">
                                <div class="container-custom-form">
                                    <?php if (!empty($page_message)) { ?>
                                        <div class="message-area <?php echo $message_class; ?>">
                                            <?php echo $page_message; ?>
                                        </div>
                                    <?php } ?>

                                    <form id="incidentReportForm" method="post" class="form-horizontal" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="driverLoginID" class="col-md-3 control-label">Driver ID:</label>
                                            <div class="col-md-9">
                                                <input type="text" id="driverLoginID" name="driverLoginID" class="form-control" value="<?php echo htmlspecialchars($driverLoginID); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="driverName" class="col-md-3 control-label">Driver Name:</label>
                                            <div class="col-md-9">
                                                <input type="text" id="driverName" name="driverName" class="form-control" value="<?php echo htmlspecialchars($driverName); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="driverMobile" class="col-md-3 control-label">Mobile Number:</label>
                                            <div class="col-md-9">
                                                <input type="tel" id="driverMobile" name="driverMobile" class="form-control" value="<?php echo htmlspecialchars($driverMobileNumber); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="relatedComplainID" class="col-md-3 control-label">Related Trip/Complain ID (Optional):</label>
                                            <div class="col-md-9">
                                                <input type="number" id="relatedComplainID" name="relatedComplainID" class="form-control" placeholder="E.g., 12345">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="incidentType" class="col-md-3 control-label">Incident Type <span style="color: red;">*</span>:</label>
                                            <div class="col-md-9">
                                                <select id="incidentType" name="incidentType" class="form-control" required>
                                                    <option value="">-- Select Type --</option>
                                                    <option value="Accident - Minor">Accident - Minor 🚗💨</option>
                                                    <option value="Accident - Major">Accident - Major 💥</option>
                                                    <option value="Vehicle Breakdown">Vehicle Breakdown 🛠️</option>
                                                    <option value="Passenger Misconduct">Passenger Misconduct 😡</option>
                                                    <option value="Weather Related">Weather Related (e.g., Heavy Rain) 🌧️</option>
                                                    <option value="Road Hazard">Road Hazard (e.g., Pothole, Debris) 🚧</option>
                                                    <option value="Fire Incident">Fire Incident 🔥</option>
                                                    <option value="Other">Other (Please specify in details)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="incidentDateTime" class="col-md-3 control-label">Incident Date & Time <span style="color: red;">*</span>:</label>
                                            <div class="col-md-9">
                                                <input type="datetime-local" id="incidentDateTime" name="incidentDateTime" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="incidentLocationDescription" class="col-md-3 control-label">Incident Location Description:</label>
                                            <div class="col-md-9">
                                                <input type="text" id="incidentLocationDescription" name="incidentLocationDescription" class="form-control" placeholder="E.g., Near ABC Junction, XYZ Road">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="incidentDetails" class="col-md-3 control-label">Incident Details <span style="color: red;">*</span>:</label>
                                            <div class="col-md-9">
                                                <textarea id="incidentDetails" name="incidentDetails" rows="5" class="form-control" placeholder="Describe what happened in detail..." required></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="incidentPhotos" class="col-md-3 control-label">Upload Files (Optional):</label>
                                            <div class="col-md-9">
                                                <input type="file" id="incidentPhotos" name="incidentPhotos[]" multiple class="form-control">
                                                <small class="text-muted">Max 5MB per file. JPG, JPEG, PNG, GIF, PDF allowed.</small>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" name="submit_incident" class="btn btn-primary btn-submit-custom">
                                                    Report Incident Now 🚨
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
<?php } ?>