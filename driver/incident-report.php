<?php
// driver-trip-start.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('includes/dbconnection.php'); // Your database connection

// IMPORTANT: Get the actual DriverID from your session after login
// For testing, default to 1. In production, this must be secure.
$driver_id = isset($_SESSION['driverid']) ? $_SESSION['driverid'] : 1; 

$message = '';
$can_submit = true; // Flag to control button visibility

// Check if driver has already submitted for today
try {
    $today = date('Y-m-d');
    $stmt_check = $con->prepare("SELECT ID FROM tbl_driver_daily_status WHERE DriverID = :driver_id AND DATE(Timestamp) = :today");
    $stmt_check->bindParam(':driver_id', $driver_id);
    $stmt_check->bindParam(':today', $today);
    $stmt_check->execute();
    if ($stmt_check->rowCount() > 0) {
        $message = "You have already confirmed your trip start for today! ✅";
        $can_submit = false; // Disable button if already submitted
    }
} catch (PDOException $e) {
    $message = "Database error checking status. ❌";
    error_log("Trip Start Check PDO Error: " . $e->getMessage());
    $can_submit = false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_trip'])) {
    if (!$can_submit) { // Re-check if already submitted
        $message = "You have already confirmed your trip start for today! ✅";
    } else {
        try {
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $statusType = "Trip Started";
            $customMessage = $_POST['custom_message'] ?? ''; // Driver's optional message

            $stmt = $con->prepare("INSERT INTO tbl_driver_daily_status (DriverID, StatusType, Message) VALUES (:driver_id, :status_type, :message)");
            $stmt->bindParam(':driver_id', $driver_id);
            $stmt->bindParam(':status_type', $statusType);
            $stmt->bindParam(':message', $customMessage);

            if ($stmt->execute()) {
                $message = "Trip Start Confirmed! 🎉 Ready to go! 🚀";
                $can_submit = false; // Disable button after successful submission

                // Optional: Send an email notification to admin
                // You'll need to include PHPMailer setup here or in a function
                // For simplicity, I'm omitting the full email code here, but you can
                // adapt it from your lodged-complain.php file.
                /*
                use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\Exception;
                require '../vendor/autoload.php'; // Adjust path if needed

                $mail = new PHPMailer(true);
                try {
                    // ... PHPMailer configuration (Host, Username, Password, Port, SMTPSecure) ...
                    $mail->setFrom('your_email@example.com', 'Smart Scrap System');
                    $mail->addAddress('admin_email@example.com', 'Admin');
                    $mail->isHTML(false);
                    $mail->Subject = "Driver {$driver_id} has started their trip!";
                    $mail->Body    = "Driver ID: {$driver_id}\nDate: {$today}\nTime: " . date('H:i:s') . "\nMessage: {$customMessage}";
                    $mail->send();
                    $message .= " Admin notified via email. 📧";
                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                    $message .= " (Email notification failed.) 😔";
                }
                */

            } else {
                $message = "Error confirming trip start. Please try again. 😔";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage() . " ❌";
            error_log("Trip Start PDO Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Trip Start Confirmation 🚦</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.15); text-align: center; }
        h2 { color: #007bff; margin-bottom: 25px; font-size: 2.2em; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 20px; }
        .form-group textarea {
            width: calc(100% - 24px); /* Account for padding */
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            resize: vertical;
            min-height: 80px;
            font-size: 1em;
        }
        button {
            padding: 15px 30px;
            background-color: #28a745; /* Green for go */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.3em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 0 auto; /* Center button */
        }
        button:hover:not(:disabled) { background-color: #218838; transform: translateY(-2px); }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        .info-text { margin-top: 20px; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daily Trip Status 🚦</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Successfully') !== false || strpos($message, 'already confirmed') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <?php if ($can_submit): ?>
                <div class="form-group">
                    <label for="custom_message" style="display: block; margin-bottom: 10px; font-weight: normal;">Optional message for admin: 💬</label>
                    <textarea id="custom_message" name="custom_message" placeholder="e.g., 'All good to go!', 'Slight delay, starting soon.'"></textarea>
                </div>
                <button type="submit" name="start_trip">Start My Trip! 🚀</button>
            <?php else: ?>
                <p class="info-text">You've already checked in for today. 👍</p>
            <?php endif; ?>
        </form>
        <div class="info-text">
            Current Date: **<?php echo date('Y-m-d'); ?>**
        </div>
    </div>
</body>
</html>
