<?php
session_start();
error_reporting(0); // For production, consider enabling error reporting for debugging.
include('includes/dbconnection.php'); // Your database connection file

// Initialize message variables
$page_message = '';
$message_class = ''; // 'success-message' or 'error-message'
$precheck_today_submitted = false; // Flag to check if pre-check for today is already submitted

// Check if driver is logged in using a robust method
if (!isset($_SESSION['vamsid']) || empty($_SESSION['vamsid'])) { // CORRECTED SESSION CHECK
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

            // --- Check if pre-check already submitted for today ---
            $sql_check_today = "SELECT ID FROM tbldailyprecheck WHERE DriverDBID = :did AND DATE(CheckDate) = CURRENT_DATE()";
            $query_check_today = $dbh->prepare($sql_check_today);
            $query_check_today->bindParam(':did', $did, PDO::PARAM_INT);
            $query_check_today->execute();
            if ($query_check_today->rowCount() > 0) {
                $precheck_today_submitted = true;
                $page_message = "You have already completed your daily pre-check for today. Come back tomorrow! 🎉";
                $message_class = "success-message"; // Using success-message class for positive feedback
            }

        } else {
            // Log if driver details not found, then redirect/handle gracefully
            error_log("Driver details not found for session ID: " . $did);
            $page_message = "Your driver account details could not be found. Please login again.";
            $message_class = "error-message";
            // No exit here, let the page render with the error, then redirect if needed by admin
        }
    } catch (PDOException $e) {
        error_log("Database error fetching driver details or checking pre-check: " . $e->getMessage());
        $page_message = "A database error occurred. Please try again later.";
        $message_class = "error-message";
        // No exit here, let the page render with the error, then redirect if needed by admin
    }

    // --- Handle form submission ---
    if (isset($_POST['submit_precheck']) && !$precheck_today_submitted) { // Only process if not already submitted today
        // Collect all checkbox values (1 if checked, 0 if not)
        $tires_checked = isset($_POST['checkTires']) ? 1 : 0;
        $lights_working = isset($_POST['checkLights']) ? 1 : 0;
        $brakes_functioning = isset($_POST['checkBrakes']) ? 1 : 0;
        $fluid_levels_checked = isset($_POST['checkFluids']) ? 1 : 0;
        $windshield_mirrors_checked = isset($_POST['checkWindshieldMirrors']) ? 1 : 0;
        $horn_working = isset($_POST['checkHorn']) ? 1 : 0;
        $wipers_functioning = isset($_POST['checkWipers']) ? 1 : 0;
        $safety_gear_present = isset($_POST['checkSafetyGear']) ? 1 : 0;
        $issues_comments = htmlspecialchars($_POST['comments'], ENT_QUOTES, 'UTF-8'); // Sanitize user input

        // Determine if all checks are OK (1 if all are checked, 0 otherwise)
        $is_all_ok = ($tires_checked && $lights_working && $brakes_functioning && $fluid_levels_checked &&
                      $windshield_mirrors_checked && $horn_working && $wipers_functioning && $safety_gear_present) ? 1 : 0;

        // Insert into tbldailyprecheck table
        try {
            $sql_insert = "INSERT INTO tbldailyprecheck (DriverDBID, DriverLoginID, DriverName, DriverMobileNumber,
                          TiresChecked, LightsWorking, BrakesFunctioning, FluidLevelsChecked,
                          WindshieldMirrorsChecked, HornWorking, WipersFunctioning, SafetyGearPresent,
                          IssuesComments, IsAllOk)
                          VALUES (:driverdbid, :driverloginid, :drivername, :drivermobilenumber,
                          :tires_checked, :lights_working, :brakes_functioning, :fluid_levels_checked,
                          :windshield_mirrors_checked, :horn_working, :wipers_functioning, :safety_gear_present,
                          :issues_comments, :is_all_ok)";

            $query_insert = $dbh->prepare($sql_insert);

            // Bind parameters
            $query_insert->bindParam(':driverdbid', $did, PDO::PARAM_INT);
            $query_insert->bindParam(':driverloginid', $driverLoginID, PDO::PARAM_STR);
            $query_insert->bindParam(':drivername', $driverName, PDO::PARAM_STR);
            $query_insert->bindParam(':drivermobilenumber', $driverMobileNumber, PDO::PARAM_INT);
            $query_insert->bindParam(':tires_checked', $tires_checked, PDO::PARAM_INT);
            $query_insert->bindParam(':lights_working', $lights_working, PDO::PARAM_INT);
            $query_insert->bindParam(':brakes_functioning', $brakes_functioning, PDO::PARAM_INT);
            $query_insert->bindParam(':fluid_levels_checked', $fluid_levels_checked, PDO::PARAM_INT);
            $query_insert->bindParam(':windshield_mirrors_checked', $windshield_mirrors_checked, PDO::PARAM_INT);
            $query_insert->bindParam(':horn_working', $horn_working, PDO::PARAM_INT);
            $query_insert->bindParam(':wipers_functioning', $wipers_functioning, PDO::PARAM_INT);
            $query_insert->bindParam(':safety_gear_present', $safety_gear_present, PDO::PARAM_INT);
            $query_insert->bindParam(':issues_comments', $issues_comments, PDO::PARAM_STR);
            $query_insert->bindParam(':is_all_ok', $is_all_ok, PDO::PARAM_INT);

            if ($query_insert->execute()) {
                // Redirect to the same page with a success status to show message and reset form
                header('location: daily_pre_check.php?status=success');
                exit();
            } else {
                $page_message = "Error submitting pre-check. Please try again.";
                $message_class = "error-message";
                error_log("Database insertion failed: " . implode(" ", $query_insert->errorInfo()));
            }
        } catch (PDOException $e) {
            error_log("Database insertion PDO error: " . $e->getMessage());
            $page_message = "An error occurred while saving your check. Please try again.";
            $message_class = "error-message";
        }
    }

    // Check for success status from redirect or if already submitted today
    if (isset($_GET['status']) && $_GET['status'] == 'success') {
        $page_message = "Your daily pre-check has been submitted successfully! 🎉";
        $message_class = "success-message";
        $precheck_today_submitted = true; // Ensure form is disabled after successful submission and redirect
    }
?>
<!doctype html>
<html lang="en">
<head>
    <title>Daily Vehicle Pre-Check</title>
    <!-- Existing CSS includes from your template -->
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css" type="text/css">
    <!-- Custom styling for the form elements and messages -->
    <style>
        /* General container styling to center the form content within the card body */
        .container-custom-form {
            padding: 20px; /* Add some padding inside the form area */
        }
        /* Style for the header/title */
        .form-title {
            font-size: 1.8rem; /* Equivalent to text-3xl from Tailwind */
            font-weight: bold; /* Equivalent to font-bold */
            text-align: center;
            color: #333; /* Dark gray for text */
            margin-bottom: 25px; /* Spacing below title */
        }
        /* Style for read-only input fields */
        .form-group input[readonly] {
            background-color: #f8f8f8; /* Light gray background */
            cursor: not-allowed;
            border: 1px solid #e2e8f0; /* Light border */
            padding: 10px;
            border-radius: 5px; /* Slightly rounded corners */
            color: #4a5568; /* Darker gray text */
        }
        /* Checkbox group styling */
        .checkbox-group {
            margin-bottom: 30px; /* Space before comments section */
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 1.05rem;
            color: #333;
            cursor: pointer; /* Indicate interactivity */
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            border-radius: 4px; /* Rounded corners for checkboxes */
            border: 2px solid #cbd5e0; /* Border color */
            appearance: none; /* Hide default checkbox */
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            position: relative;
            flex-shrink: 0; /* Prevent checkbox from shrinking */
        }
        /* Custom checkmark for selected checkbox */
        .checkbox-group input[type="checkbox"]:checked {
            background-color: #22c55e; /* Green-500 */
            border-color: #22c55e;
        }
        .checkbox-group input[type="checkbox"]:checked::after {
            content: '✔'; /* Unicode checkmark */
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
        }
        /* Textarea styling */
        textarea.form-control { /* Targeting existing form-control class */
            min-height: 100px; /* Ensure a decent height */
            resize: vertical; /* Allow vertical resizing */
            border-radius: 8px; /* Rounded corners */
            padding: 12px 15px; /* Padding */
            border: 1px solid #e2e8f0; /* Light border */
        }
        textarea.form-control:focus {
            border-color: #3b82f6; /* Blue-500 on focus */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3); /* Focus ring */
            outline: none;
        }
        /* Submit button custom styling */
        .btn-submit-custom {
            background: linear-gradient(to right, #22c55e, #10b981); /* Green gradient */
            color: white;
            padding: 14px 25px;
            border-radius: 8px; /* Rounded corners */
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            width: 100%; /* Full width */
            transition: background-color 0.3s ease, transform 0.1s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3); /* Soft shadow */
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px; /* Space above button */
        }
        .btn-submit-custom:hover {
            background: linear-gradient(to right, #10b981, #22c55e); /* Reverse gradient on hover */
            transform: translateY(-2px); /* Slight lift effect */
            box-shadow: 0 6px 15px rgba(34, 197, 94, 0.4); /* Enhanced shadow on hover */
        }
        .btn-submit-custom:active {
            transform: translateY(0); /* Press effect */
            box-shadow: 0 2px 5px rgba(34, 197, 94, 0.3); /* Smaller shadow on click */
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
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green */
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red */
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
                <a class="navbar-brand" href="javascript:void(0);">Daily Vehicle Pre-Check</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <div class="form-title">Daily Vehicle Pre-Check 🚚📋</div>
                            </div>
                            <div class="body">
                                <div class="container-custom-form">
                                    <?php if (!empty($page_message)) { ?>
                                        <div class="message-area <?php echo $message_class; ?>">
                                            <?php echo $page_message; ?>
                                        </div>
                                    <?php } ?>

                                    <form id="preCheckForm" method="post" class="form-horizontal">
                                        <!-- Driver Info Fields - Pre-filled from DB and Read-only -->
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

                                        <!-- Pre-Check Checkboxes -->
                                        <div class="checkbox-group mt-5">
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkTires" name="checkTires" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Tires (pressure, tread, damage) checked
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkLights" name="checkLights" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Lights (headlights, tail lights, indicators) working
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkBrakes" name="checkBrakes" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Brakes functioning properly
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkFluids" name="checkFluids" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Fluid levels (oil, coolant, washer fluid) checked
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkWindshieldMirrors" name="checkWindshieldMirrors" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Windshield and mirrors clean and undamaged
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkHorn" name="checkHorn" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Horn working
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkWipers" name="checkWipers" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Wipers functioning
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <label>
                                                        <input type="checkbox" id="checkSafetyGear" name="checkSafetyGear" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                        Safety gear (first-aid kit, fire extinguisher, etc.) present
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Issues/Comments Textarea -->
                                        <div class="form-group mt-4">
                                            <label for="comments" class="col-md-3 control-label">
                                                Any Issues / Comments? (Optional) 📝
                                            </label>
                                            <div class="col-md-9">
                                                <textarea id="comments" name="comments" rows="4" class="form-control" placeholder="Enter any issues found or additional comments..." <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-md-offset-3 col-md-9">
                                                <button type="submit" name="submit_precheck" class="btn btn-primary btn-submit-custom" <?php echo $precheck_today_submitted ? 'disabled' : ''; ?>>
                                                    Submit Daily Pre-Check 🚀
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

    <!-- Core Javascript files from your template -->
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/js/theme.js"></script>
    <!-- Removed Bootstrap Multiselect and Parsley JS as they are not used on this form -->
</body>
</html>
<?php } ?>
