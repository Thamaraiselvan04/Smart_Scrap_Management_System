<?php
session_start();
error_reporting(E_ALL); // Keep error reporting for development, change to 0 for production
ini_set('display_errors', 1);
include('includes/dbconnection.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Helper function to safely handle null values
function safeHtml($value, $default = '') {
    return $value !== null ? htmlentities($value) : $default;
}

// --- Function to send email notifications (MOVED TO GLOBAL SCOPE and corrected to use global $dbh) ---
// Added $assignedDriverName, $assignedDriverMobile, $assignedDriverEmail for explicit driver details
function sendEmailNotifications($comid, $status, $remark, $requestData, $assignedPickupDate = null, $email_type = 'status_update', $assignedDriverName = null, $assignedDriverMobile = null, $assignedDriverEmail = null, $paid_amount = null) {
    $mail = new PHPMailer(true);
    global $dbh; // This makes the database connection available inside the function
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'removed';
        $mail->Password = 'removed';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Common email content parts
        // Use $assignedPickupDate if provided, otherwise fallback to requestData's pickup_date
        $pickupDateForEmail = $assignedPickupDate ? date("Y-m-d", strtotime($assignedPickupDate)) : date("Y-m-d", strtotime($requestData['pickup_date']));
        $pickupTimeForEmail = date("H:i", strtotime($requestData['pickup_time'])); // Ensure time is formatted correctly

        $requestDetails = "
            <h3>Request Details:</h3>
            <p><strong>Request #:</strong> {$comid}</p>
            <p><strong>Scrap Type:</strong> {$requestData['Scraptype']}</p>
            <p><strong>Pickup Date:</strong> {$pickupDateForEmail}</p>
            <p><strong>Pickup Time:</strong> {$pickupTimeForEmail}</p>
            <p><strong>Location:</strong> {$requestData['Address']}, {$requestData['Locality']}, {$requestData['Area']}</p>
            <p><strong>Customer:</strong> {$requestData['user_name']} ({$requestData['user_phone']})</p>
        ";
        
        if($email_type == 'status_update') {
            $requestDetails .= "<p><strong>Status:</strong> {$status}</p>";
            $requestDetails .= "<p><strong>Remark:</strong> {$remark}</p>";
            if($status == 'Completed' && $paid_amount) {
                $requestDetails .= "<p><strong>Amount Paid:</strong> ₹{$paid_amount}</p>";
            }
        } else if ($email_type == 'reschedule') {
            // For reschedule emails, the remark indicates the reason for rescheduling
            $requestDetails .= "<p><strong>Reason for Reschedule:</strong> {$remark}</p>"; 
        }


        // 1. Send email to USER
        if (!empty($requestData['user_email'])) {
            $mail->setFrom('removed', 'Smart Scrap Management');
            $mail->addAddress($requestData['user_email'], $requestData['user_name']);
            
            $mail->isHTML(true);
            
            if ($email_type == 'reschedule') {
                $mail->Subject = "IMPORTANT: Your Scrap Pickup Request (#{$comid}) Has Been Rescheduled!";
                $emailBody = "
                    <h3>Dear {$requestData['user_name']},</h3>
                    <p>We need to inform you that your scrap pickup request <strong>#{$comid}</strong> has been rescheduled.</p>
                    <p><strong>New Pickup Date:</strong> {$pickupDateForEmail}</p>
                    <p><strong>Original Pickup Time:</strong> {$pickupTimeForEmail}</p>
                    <p>Our apologies for any inconvenience this may cause.</p>
                    {$requestDetails}
                    <br><p>Regards,<br>Smart Scrap Management Team</p>
                ";
            } else { // status_update
                $mail->Subject = "Update on Your Scrap Request (#{$comid})";
                $emailBody = "
                    <h3>Dear {$requestData['user_name']},</h3>
                    <p>Your request <strong>#{$comid}</strong> has been updated. The current status is: <strong>{$status}</strong>.</p>
                    {$requestDetails}
                ";
                // Add driver details if status is Approved and driver info exists
                if ($status == 'Approved' && !empty($assignedDriverName) && !empty($assignedDriverMobile)) {
                    $emailBody .= "
                        <p>Your pickup will be handled by <strong>{$assignedDriverName}</strong>. You can contact them at <strong>{$assignedDriverMobile}</strong>.</p>
                    ";
                }
                $emailBody .= "<br><p>Regards,<br>Smart Scrap Management Team</p>";
            }
            
            $mail->Body = $emailBody;
            if(!$mail->send()) {
                error_log("Failed to send email to user: " . $requestData['user_email'] . " - " . $mail->ErrorInfo);
            }
            $mail->clearAddresses();
        }

        // 2. Send email to DRIVER (if assigned and not rejected, for status update or reschedule)
        // Ensure driver_email is populated and driver info is not empty
        // PRIORITIZE assignedDriverEmail if it's set (from POST/current action)
        $driverEmailToSend = !empty($assignedDriverEmail) ? $assignedDriverEmail : $requestData['driver_email'];

        if ($status != 'Rejected' && !empty($driverEmailToSend)) {
            $mail->setFrom('removed', 'Smart Scrap Management');
            // Use assignedDriverName if available, else fallback to requestData's driver_name
            $driverNameToUse = !empty($assignedDriverName) ? $assignedDriverName : $requestData['driver_name'];
            $mail->addAddress($driverEmailToSend, $driverNameToUse);
            
            $mail->isHTML(true);
            
            if ($email_type == 'reschedule') {
                $mail->Subject = "ACTION REQUIRED: Pickup Rescheduled for Request (#{$comid})";
                $emailBody = "
                    <h3>Dear {$driverNameToUse},</h3>
                    <p>The pickup date for request <strong>#{$comid}</strong> has been updated. Please check the new details.</p>
                    <p><strong>New Pickup Date:</strong> {$pickupDateForEmail}</p>
                    <p><strong>Original Pickup Time:</strong> {$pickupTimeForEmail}</p>
                    {$requestDetails}
                    <p><strong>Customer Contact:</strong> {$requestData['user_phone']}</p>
                    <br><p>Please acknowledge the new schedule and contact the customer if necessary.</p>
                    <p>Regards,<br>Smart Scrap Management Team</p>
                ";
            } else if ($email_type == 'status_update' && $status == 'Approved') { // Specific email for driver on assignment
                $mail->Subject = "NEW ASSIGNMENT: Scrap Pickup Request #{$comid} Assigned to You!";
                $emailBody = "
                    <h3>Dear {$driverNameToUse},</h3>
                    <p>A new scrap pickup request has been assigned to you:</p>
                    {$requestDetails}
                    <p>Please review the details and proceed with the pickup as scheduled. Customer contact: {$requestData['user_phone']}</p>
                    <br><p>Regards,<br>Smart Scrap Management Team</p>
                ";
            }
            else { // Generic status update for driver if not Approved or Reschedule
                $mail->Subject = "Update on Scrap Pickup Assignment (#{$comid}) - Status: {$status}";
                $emailBody = "
                    <h3>Dear {$driverNameToUse},</h3>
                    <p>The status of request <strong>#{$comid}</strong> has been updated to: <strong>{$status}</strong>.</p>
                    {$requestDetails}
                    <p><strong>Customer Contact:</strong> {$requestData['user_phone']}</p>
                    <br><p>Regards,<br>Smart Scrap Management Team</p>
                ";
            }

            $mail->Body = $emailBody;
            if(!$mail->send()) {
                error_log("Failed to send email to driver: " . $driverEmailToSend . " - " . $mail->ErrorInfo);
            }
            $mail->clearAddresses();
        }

        // 3. Send email to ADMIN (always send to admin for records)
        $adminEmail = 'removed';
        $mail->setFrom('removed', 'Smart Scrap Management');
        $mail->addAddress($adminEmail, 'Admin');
        
        $mail->isHTML(true);
        
        if ($email_type == 'reschedule') {
            $mail->Subject = "Request #{$comid} Pickup Rescheduled!";
            $emailBody = "
                <h3>Pickup Reschedule Notification</h3>
                <p>The pickup date for request <strong>#{$comid}</strong> has been rescheduled by an admin.</p>
                {$requestDetails}
                <p><strong>Original Pickup Date (before change):</strong> " . date("Y-m-d", strtotime($requestData['Date'])) . "</p>
                <p><strong>Original Pickup Time:</strong> " . date("H:i", strtotime($requestData['Time'])) . "</p>
                <p><strong>New Pickup Date:</strong> {$pickupDateForEmail}</p>
                <br><p>Regards,<br>Smart Scrap Management System</p>
            ";
        } else { // status_update
            $adminDriverName = !empty($assignedDriverName) ? $assignedDriverName : ($requestData['driver_name'] ?? 'N/A');
            $adminDriverMobile = !empty($assignedDriverMobile) ? $assignedDriverMobile : ($requestData['driver_current_mobile_in_complain'] ?? 'N/A');

            $mail->Subject = "Request #{$comid} Status Updated to {$status}";
            $emailBody = "
                <h3>Request Status Update Notification</h3>
                <p>The following request has been updated:</p>
                {$requestDetails}
                <p><strong>Assigned Driver:</strong> {$adminDriverName} ({$adminDriverMobile})</p>
                <br><p>Regards,<br>Smart Scrap Management System</p>
            ";
        }
        
        $mail->Body = $emailBody;
        if(!$mail->send()) {
            error_log("Failed to send email to admin: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log("Mailer Error in sendEmailNotifications: {$e->getMessage()}");
        // In a real application, you might want to log this to a file or a dedicated error service.
    }
}

if (!isset($_SESSION['vamsaid']) || empty($_SESSION['vamsaid'])) { // Corrected session check
    header('location:logout.php');
    exit();
} else {
    $eid = $_GET['editid']; // Complain ID from URL
    $comid = $_GET['comid']; // Complain Number from URL

    // --- Handle "Take Action" form submission (existing logic) ---
    if(isset($_POST['submit'])) {
        $status = $_POST['status'];
        $remark = $_POST['remark'];
        $paid_amount = null; // Ensure it's null as we're not dealing with payment here

        // Variables to hold driver info from POST for consistent passing to email function
        $postDriverName = '';
        $postDriverMobile = '';
        $postDriverEmail = '';
        $postAssignee = '';
        // Removed $postAssignDate

        // For rejected cases, don't use driver info
        if($status == 'Rejected') {
            $postAssignee = '';
            // Driver name/mobile/email should be cleared in DB, and not sent in emails for rejected
        } else { // Status is 'Approved'
            $postAssignee = $_POST['assignee'];
            $postDriverName = $_POST['drivername']; 
            $postDriverMobile = $_POST['drivermobile']; 
            $postDriverEmail = $_POST['driveremail']; 
        }

        try {
            // Insert into tracking table
            $sql_tracking = "INSERT INTO tblcomtracking(ComplainNumber, Remark, Status) VALUES(:comid, :remark, :status)";
            $query_tracking = $dbh->prepare($sql_tracking);
            $query_tracking->bindParam(':comid', $comid, PDO::PARAM_STR);
            $query_tracking->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query_tracking->bindParam(':status', $status, PDO::PARAM_STR);
            $query_tracking->execute();

            // Update main complain table
            // Removed AssignDate from UPDATE query as it's no longer managed by this form
            $sql_update_complain = "UPDATE tbllodgedcomplain SET AssignTo=:assignee, Status=:status, 
                                    Remark=:remark, DriverName=:drivername, DriverMobile=:drivermobile";
            $sql_update_complain .= " WHERE ID=:eid";
            
            $query_update_complain = $dbh->prepare($sql_update_complain);
            $query_update_complain->bindParam(':assignee', $postAssignee, PDO::PARAM_STR);
            $query_update_complain->bindParam(':status', $status, PDO::PARAM_STR);
            $query_update_complain->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query_update_complain->bindParam(':drivername', $postDriverName, PDO::PARAM_STR);
            $query_update_complain->bindParam(':drivermobile', $postDriverMobile, PDO::PARAM_STR);
            $query_update_complain->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query_update_complain->execute();

            // Fetch the user's email address and request details for email notification
            // Note: This fetch will get the *old* driver details if assignment just changed.
            // We rely on passing explicit driver details to sendEmailNotifications for new assignments.
            $stmt = $dbh->prepare("
                SELECT 
                    u.Email as user_email, 
                    u.FullName as user_name, 
                    u.MobileNumber as user_phone,
                    lc.ComplainNumber, 
                    lc.Area, 
                    lc.Locality, 
                    lc.Address,
                    lc.Date as pickup_date,
                    lc.Time as pickup_time,
                    lc.Scraptype,
                    d.Email as driver_email,
                    d.Name as driver_name,
                    lc.DriverMobile as driver_current_mobile_in_complain -- Use current driver mobile from complain
                FROM tbllodgedcomplain lc
                JOIN tbluser u ON u.ID = lc.UserID
                LEFT JOIN tbldriver d ON d.DriverID = lc.AssignTo
                WHERE lc.ID = :eid
                LIMIT 1
            ");
            $stmt->bindParam(':eid', $eid, PDO::PARAM_STR);
            $stmt->execute();
            $requestData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($requestData) {
                // Call sendEmailNotifications, passing explicit driver details from POST for approved status
                // The pickup date for status update will be the current one from $requestData
                sendEmailNotifications($comid, $status, $remark, $requestData, $requestData['pickup_date'], 'status_update', $postDriverName, $postDriverMobile, $postDriverEmail, $paid_amount); 
            } else {
                error_log("No request data found for ID: $eid during status update.");
            }

            echo '<script>alert("Remark has been updated successfully!")</script>';
            echo "<script>window.location.href ='all-complain.php'</script>";
            exit();
        } catch (PDOException $e) {
            error_log("PDO Error on status update: " . $e->getMessage());
            echo '<script>alert("Database error on status update. Please try again.")</script>';
        }
    }

    // --- Handle "Edit Pickup Date" form submission ---
    if(isset($_POST['update_pickup_datetime'])) { // Name kept for consistency but now only handles date
        $new_pickup_date = $_POST['new_pickup_date'];
        
        // Basic validation - only date required now
        if (empty($new_pickup_date)) {
            echo '<script>alert("Please select a new date.")</script>';
        } else {
            try {
                // Update only Date field
                $sql_update_datetime = "UPDATE tbllodgedcomplain SET Date=:new_date WHERE ID=:eid";
                $query_update_datetime = $dbh->prepare($sql_update_datetime);
                $query_update_datetime->bindParam(':new_date', $new_pickup_date, PDO::PARAM_STR);
                $query_update_datetime->bindParam(':eid', $eid, PDO::PARAM_STR); 
                $query_update_datetime->execute();

                // Fetch updated request details for email notification
                // Fetch Time from DB as it's not being updated by form
                $stmt = $dbh->prepare("
                    SELECT 
                        u.Email as user_email, 
                        u.FullName as user_name, 
                        u.MobileNumber as user_phone,
                        lc.ComplainNumber, 
                        lc.Area, 
                        lc.Locality, 
                        lc.Address,
                        lc.Date as pickup_date, -- This will be the new date
                        lc.Time as pickup_time, -- Original time (not updated by this form)
                        lc.Scraptype,
                        d.Email as driver_email,
                        d.Name as driver_name,
                        lc.DriverMobile as driver_current_mobile_in_complain, -- Driver's mobile from complain record
                        lc.Status -- Get current status
                    FROM tbllodgedcomplain lc
                    JOIN tbluser u ON u.ID = lc.UserID
                    LEFT JOIN tbldriver d ON d.DriverID = lc.AssignTo
                    WHERE lc.ID = :eid
                    LIMIT 1
                ");
                $stmt->bindParam(':eid', $eid, PDO::PARAM_STR);
                $stmt->execute();
                $requestData = $stmt->fetch(PDO::FETCH_ASSOC);

                // --- Debugging for reschedule email ---
                error_log("--- Debugging Reschedule Email ---");
                error_log("Complain ID: " . $comid);
                error_log("Status: " . ($requestData['Status'] ?? 'N/A'));
                error_log("New Pickup Date: " . $new_pickup_date);
                error_log("Driver Name (from requestData): " . ($requestData['driver_name'] ?? 'N/A'));
                error_log("Driver Mobile (from requestData): " . ($requestData['driver_current_mobile_in_complain'] ?? 'N/A'));
                error_log("Driver Email (from requestData): " . ($requestData['driver_email'] ?? 'N/A'));
                error_log("--- End Debugging Reschedule Email ---");
                // --- End Debugging for reschedule email ---


                if ($requestData) {
                    // For reschedule, pass the driver details as they were fetched from the DB
                    sendEmailNotifications(
                        $comid, 
                        $requestData['Status'], 
                        "Pickup date has been rescheduled.", 
                        $requestData, 
                        $new_pickup_date, // Pass the NEW pickup date here
                        'reschedule', 
                        $requestData['driver_name'], // Pass fetched driver name
                        $requestData['driver_current_mobile_in_complain'], // Pass fetched driver mobile
                        $requestData['driver_email'] // Pass fetched driver email
                    ); 
                } else {
                    error_log("No request data found for ID: $eid during pickup date update.");
                }

                echo '<script>alert("Pickup Date updated successfully!")</script>';
                echo "<script>window.location.href ='view-complain-detail.php?editid={$eid}&comid={$comid}'</script>";
                exit();
            } catch (PDOException $e) {
                error_log("PDO Error on pickup date update: " . $e->getMessage());
                echo '<script>alert("Database error on pickup date update. Please try again.")</script>';
            }
        }
    }


    // The rest of the HTML and JavaScript remain the same as previously provided for the Canvas.
?>

<!doctype html>
<html lang="en">
<head>
    <title>View Request</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .scrap-items-table {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .scrap-items-table th {
            background-color: #f8f9fa;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .payment-section {
            margin-top: 20px;
        }
        /* Custom styles for the edit pickup date feature */
        .pickup-date-cell { /* New class for the td containing date and edit button */
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px; /* Space between date text and button */
            flex-wrap: wrap; /* Allow wrapping on smaller screens if content is long */
        }
        .edit-pickup-btn {
            background-color: #007bff; /* Blue */
            color: white;
            border: none;
            padding: 5px 10px; /* Slightly smaller padding for better fit */
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8em; /* Slightly smaller font size */
            transition: background-color 0.2s ease;
            white-space: nowrap; /* Prevent button text from wrapping */
        }
        .edit-pickup-btn:hover {
            background-color: #0056b3; /* Darker blue */
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle driver selection for "Take Action" modal
        $('select[name="assignee"]').change(function() {
            var driverId = $(this).val();
            if(driverId) {
                $.ajax({
                    type: 'POST',
                    url: 'getdriverdetails.php', // Ensure this file exists and works
                    data: { driverid: driverId },
                    dataType: 'json', // Expect JSON response
                    success: function(data) {
                        if (data && data.Name) {
                            $('#drivername').val(data.Name);
                            $('#drivermobile').val(data.MobileNumber);
                            $('#driveremail').val(data.Email); // Populate driver email
                            $('#driverInfo').html('<strong>Driver:</strong> ' + data.Name + ' <strong>Phone:</strong> ' + data.MobileNumber);
                        } else {
                            console.error("Invalid response for driver details:", data);
                            $('#drivername').val('');
                            $('#drivermobile').val('');
                            $('#driveremail').val('');
                            $('#driverInfo').html('Driver details not found.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error fetching driver details:", status, error);
                        $('#drivername').val('');
                        $('#drivermobile').val('');
                        $('#driveremail').val('');
                        $('#driverInfo').html('Error fetching driver details.');
                    }
                });
            } else {
                $('#drivername').val('');
                $('#drivermobile').val('');
                $('#driveremail').val('');
                $('#driverInfo').html('');
            }
        });

        // Show/hide fields based on status for "Take Action" modal
        $('select[name="status"]').change(function() {
            var currentStatus = $(this).val();
            if(currentStatus === 'Rejected') {
                $('.driver-fields').hide();
                $('select[name="assignee"]').removeAttr('required');
                // The payment-fields row is already hidden by default in HTML and remains hidden
            } else if (currentStatus === 'Approved') { // Only show driver fields for Approved
                $('.driver-fields').show();
                $('select[name="assignee"]').attr('required', 'true');
                // The payment-fields row is already hidden by default in HTML and remains hidden
            } else { // For other statuses like 'On the way', 'Completed'
                $('.driver-fields').show(); // Keep driver fields visible as they might be pre-filled
                $('select[name="assignee"]').removeAttr('required'); // Driver assignment isn't strictly required for these states
            }
        });

        // Trigger change event on page load for "Take Action" modal status
        $('select[name="status"]').trigger('change');

        // Date picker setup for "Edit Pickup Date" modal
        const editPickupDateInput = document.getElementById('editPickupDate');
        if (editPickupDateInput) {
            editPickupDateInput.min = new Date().toISOString().split('T')[0];
            editPickupDateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    alert("Please select today's date or a future date for pickup.");
                    this.value = today.toISOString().split('T')[0];
                }
            });
        }
    });
    </script>
</head>

<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">View Request</a>
            </nav>
            <div class="container-fluid">            
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2><strong>View Lodged</strong> Request </h2>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <?php
                                    $eid = $_GET['editid'];
                                    // Fetch the request details to display and to determine status for button visibility
                                    $sql = "SELECT tbllodgedcomplain.ComplainNumber,tbllodgedcomplain.Area,tbllodgedcomplain.Locality,tbllodgedcomplain.Note,tbllodgedcomplain.Landmark,tbllodgedcomplain.Address,tbllodgedcomplain.Upi,tbllodgedcomplain.Paymentmode,tbllodgedcomplain.Date,tbllodgedcomplain.Scraptype,tbllodgedcomplain.Pin,tbllodgedcomplain.Time, tbllodgedcomplain.Photo,tbllodgedcomplain.DriverName,tbllodgedcomplain.DriverMobile,tbllodgedcomplain.ID as compid,tbllodgedcomplain.Status,tbllodgedcomplain.ComplainDate,tbllodgedcomplain.Remark,tbllodgedcomplain.AssignTo,tbluser.ID as uid,tbluser.FullName,tbluser.MobileNumber,tbluser.Email,tbldriver.Email as DriverEmail from tbllodgedcomplain join tbluser on tbluser.ID=tbllodgedcomplain.UserID LEFT JOIN tbldriver ON tbldriver.DriverID = tbllodgedcomplain.AssignTo where tbllodgedcomplain.ID=:eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $current_request_status = ''; // Initialize variable for status check

                                    if($query->rowCount() > 0) {
                                        foreach($results as $row) {
                                            $current_request_status = $row->Status; // Get current status for button logic
                                    ?>
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tr>
                                            <th style="color: orange;">Request Number</th>
                                            <td colspan="4" style="color: orange;font-weight: bold;"><?php echo safeHtml($row->ComplainNumber); ?></td>
                                        </tr>
                                      
                                        <tr>
                                            <th>Name</th>
                                            <td><?php echo safeHtml($row->FullName); ?></td>
                                            <th>Email</th>
                                            <td><?php echo safeHtml($row->Email); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mobile Number</th>
                                            <td><?php echo safeHtml($row->MobileNumber); ?></td>
                                            <th>Address of Scrap</th>
                                            <td><?php echo safeHtml($row->Address); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Area</th>
                                            <td><?php echo safeHtml($row->Area); ?></td>
                                            <th>Locality</th>
                                            <td><?php echo safeHtml($row->Locality); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Landmark</th>
                                            <td><?php echo empty($row->Landmark) ? '—' : safeHtml($row->Landmark); ?></td>
                                            <th>Note</th>
                                            <td><?php echo empty($row->Note) ? 'No Notes' : safeHtml($row->Note); ?></td>
                                        </tr>
                                        <tr>
                                            <th>UPI-linked mobile No</th>
                                            <td><?php echo empty($row->Upi) ? '—' : safeHtml($row->Upi); ?></td>
                                            <th>Pin Number</th>
                                            <td><?php echo empty($row->Pin) ? '—' : safeHtml($row->Pin); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type of Scrap</th>
                                            <td><?php echo safeHtml($row->Scraptype); ?></td>
                                            <th>Pick-up Date</th>
                                            <td class="pickup-date-cell">
                                                <span><?php echo safeHtml($row->Date); ?></span>
                                                <?php if ($current_request_status != "Completed") { // Edit button hides ONLY for "Completed" ?>
                                                    <button type="button" class="edit-pickup-btn" data-toggle="modal" data-target="#editPickupModal">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Pick-up Timing</th>
                                            <td><?php echo safeHtml($row->Time); ?></td>
                                            <th>Payment Mode</th>
                                            <td colspan="3"><?php echo !empty($row->Paymentmode) ? safeHtml($row->Paymentmode) : "Not provided"; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Image</th>
                                            <td colspan="4">
                                            <?php if(!empty($row->Photo)) { ?>
                                                <a href="../user/images/<?php echo safeHtml($row->Photo); ?>" target="_blank">  
                                                <img src="../user/images/<?php echo safeHtml($row->Photo); ?>" width="200" height="150" alt="Scrap Image">
                                                </a>  
                                            <?php } else { echo "No Image"; } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Driver Name</th>
                                            <td>
                                                <?php 
                                                if(!empty($row->DriverName)) {
                                                    echo safeHtml($row->DriverName);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Driver Phone</th>
                                            <td>
                                                <?php 
                                                if(!empty($row->DriverMobile)) {
                                                    echo safeHtml($row->DriverMobile);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Driver Email</th>
                                            <td colspan="3">
                                                <?php 
                                                if(!empty($row->DriverEmail)) {
                                                    echo safeHtml($row->DriverEmail);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Assign To</th>
                                            <td><?php echo empty($row->AssignTo) ? "Not Updated Yet" : safeHtml($row->AssignTo); ?></td>
                                            <th>Request Date</th>
                                            <td><?php echo safeHtml($row->ComplainDate); ?></td>     
                                        </tr>
                                        <tr>
                                            <th>Request Final Status</th>
                                            <td> 
                                                <?php  
                                                $status = $row->Status; // This is the final status from the DB
                                                if($status == "Approved") {
                                                    echo "Your request has been approved";
                                                } elseif($status == "Rejected") {
                                                    echo "Your request has been cancelled";
                                                } elseif($status == "On the way") {
                                                    echo "Driver is on the way";
                                                } elseif($status == "Completed") {
                                                    echo "Scrap has been collected";
                                                } else {
                                                    echo "Not Response Yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Remark</th>
                                            <td><?php echo empty($row->Remark) ? "Not Updated Yet" : safeHtml($row->Remark); ?></td>
                                        </tr>
                                    </table>
                                    
                                    <?php 
                                    // Make sure $status is defined before this block by using $row->Status
                                    if(isset($row->Status) && !empty($row->Status)) {
                                        $ret = "SELECT tblcomtracking.Remark, tblcomtracking.Status, tblcomtracking.RemarkDate 
                                              FROM tblcomtracking 
                                              WHERE tblcomtracking.ComplainNumber = :comid";
                                        $query_tracking_history = $dbh->prepare($ret);
                                        $query_tracking_history->bindParam(':comid', $comid, PDO::PARAM_STR);
                                        $query_tracking_history->execute();
                                        $results_tracking = $query_tracking_history->fetchAll(PDO::FETCH_OBJ);
                                    ?>
                                    <h4 style="margin-top: 20px;">Tracking History</h4>
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tr align="center">
                                            <th colspan="4" style="color: blue">Tracking History</th> 
                                        </tr>
                                        <tr>
                                            <th>#</th>
                                            <th>Remark</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                        <?php  
                                        $cnt_tracking = 1;
                                        foreach($results_tracking as $track_row) {               
                                        ?>
                                        <tr>
                                            <td><?php echo safeHtml($cnt_tracking); ?></td>
                                            <td><?php echo safeHtml($track_row->Remark); ?></td> 
                                            <td><?php echo safeHtml($track_row->Status); ?></td> 
                                            <td><?php echo safeHtml($track_row->RemarkDate); ?></td> 
                                        </tr>
                                        <?php $cnt_tracking++; } ?>
                                    </table>
                                    <?php } ?>
                                
                                    <?php if ($current_request_status != "Completed" && $current_request_status != "Rejected") { ?> 
                                    <p align="center" style="padding-top: 20px">                            
                                        <button class="btn btn-primary waves-effect waves-light w-lg" data-toggle="modal" data-target="#myModal">Take Action</button>
                                    </p>  
                                    <?php } ?>
                                    
                                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Take Action</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered table-hover data-tables">
                                                        <form method="post" name="submit">
                                                            <tr>
                                                                <th>Remark :</th>
                                                                <td>
                                                                    <textarea name="remark" placeholder="Remark" rows="5" class="form-control wd-450" required="true"></textarea>
                                                                </td>
                                                            </tr> 
                                                            <tr class="driver-fields">
                                                                <th>Assign to :</th>
                                                                <td>
                                                                    <select name="assignee" placeholder="Assign To" class="form-control wd-450" required>
                                                                        <option value="">Select Driver</option>
                                                                        <?php 
                                                                        $sql2 = "SELECT * FROM tbldriver";
                                                                        $query2 = $dbh->prepare($sql2);
                                                                        $query2->execute();
                                                                        $result2 = $query2->fetchAll(PDO::FETCH_OBJ);
                                                                        foreach($result2 as $drow) {          
                                                                        ?>  
                                                                        <option value="<?php echo safeHtml($drow->DriverID); ?>"
                                                                            <?php echo ($row->AssignTo == $drow->DriverID) ? 'selected' : ''; ?>>
                                                                            <?php echo safeHtml($drow->DriverID); ?> - <?php echo safeHtml($drow->Name); ?>
                                                                        </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <div id="driverInfo" style="margin-top:10px; padding:5px; background:#f8f9fa;"></div>
                                                                    <input type="hidden" name="drivername" id="drivername">
                                                                    <input type="hidden" name="drivermobile" id="drivermobile">
                                                                    <input type="hidden" name="driveremail" id="driveremail">
                                                                </td>
                                                            </tr> 
                                                            <tr id="payment-fields" style="display:none;">
                                                                <th>Amount Paid :</th>
                                                                <td>
                                                                    <input type="number" name="paid_amount" id="paid_amount" class="form-control wd-450" placeholder="Enter amount in ₹">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Status :</th>
                                                                <td>
                                                                    <select name="status" class="form-control wd-450" required="true">
                                                                        <option value="Approved" <?php echo ($row->Status == "Approved") ? 'selected' : ''; ?>>Approved</option>
                                                                        <option value="Rejected" <?php echo ($row->Status == "Rejected") ? 'selected' : ''; ?>>Rejected</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="submit" class="btn btn-primary">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="editPickupModal" tabindex="-1" role="dialog" aria-labelledby="editPickupModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPickupModalLabel">Edit Pickup Date</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="post" name="update_pickup_datetime_form">
                                                        <input type="hidden" name="editid" value="<?php echo safeHtml($eid); ?>">
                                                        <input type="hidden" name="comid" value="<?php echo safeHtml($comid); ?>">
                                                        <div class="form-group">
                                                            <label for="new_pickup_date">New Pick-up Date:</label>
                                                            <input type="date" name="new_pickup_date" id="editPickupDate" class="form-control" value="<?php echo safeHtml($row->Date); ?>" required>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_pickup_datetime" class="btn btn-primary">Save Changes</button>
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
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/bundles/datatablescripts.bundle.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>

    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>
<?php } // Closes the foreach loop ?>
<?php } // Closes the if($query->rowCount() > 0) block ?>
<?php } // Closes the main session check else block ?>