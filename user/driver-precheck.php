<?php
// driver-precheck.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('includes/dbconnection.php'); // Make sure this path is correct for your setup

// Check if driver is logged in (you'll need to implement your driver login logic)
// For now, let's assume a driver_id is available or set it for testing.
// In a real application, you'd get this from the session after a driver logs in.
$driver_id = isset($_SESSION['driverid']) ? $_SESSION['driverid'] : 1; // Default to 1 for testing if no session driver ID

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get current date for the check
    $check_date = date('Y-m-d');

    // Retrieve form data
    $tires_checked = isset($_POST['tires']) ? 1 : 0;
    $lights_checked = isset($_POST['lights']) ? 1 : 0;
    $brakes_checked = isset($_POST['brakes']) ? 1 : 0;
    $fluids_checked = isset($_POST['fluids']) ? 1 : 0;
    $windshield_checked = isset($_POST['windshield']) ? 1 : 0;
    $horn_checked = isset($_POST['horn']) ? 1 : 0;
    $wipers_checked = isset($_POST['wipers']) ? 1 : 0;
    $safety_gear_checked = isset($_POST['safety_gear']) ? 1 : 0;
    $comments = $_POST['comments'] ?? '';

    // SQL to insert data into the database
    // YOU WILL NEED TO CREATE A TABLE NAMED 'tbldriverprechecks' IN YOUR DATABASE
    // Example SQL for the table (run this in phpMyAdmin or your SQL client):
    /*
    CREATE TABLE tbldriverprechecks (
        ID INT(11) NOT NULL AUTO_INCREMENT,
        DriverID INT(11) NOT NULL,
        CheckDate DATE NOT NULL,
        TiresChecked TINYINT(1) DEFAULT 0,
        LightsChecked TINYINT(1) DEFAULT 0,
        BrakesChecked TINYINT(1) DEFAULT 0,
        FluidsChecked TINYINT(1) DEFAULT 0,
        WindshieldChecked TINYINT(1) DEFAULT 0,
        HornChecked TINYINT(1) DEFAULT 0,
        WipersChecked TINYINT(1) DEFAULT 0,
        SafetyGearChecked TINYINT(1) DEFAULT 0,
        Comments TEXT,
        SubmissionTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (ID),
        UNIQUE KEY (DriverID, CheckDate) -- Ensures only one submission per driver per day
    );
    */

    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if a record for today already exists for this driver
        $stmt_check = $con->prepare("SELECT ID FROM tbldriverprechecks WHERE DriverID = :driver_id AND CheckDate = :check_date");
        $stmt_check->bindParam(':driver_id', $driver_id);
        $stmt_check->bindParam(':check_date', $check_date);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            $message = "You have already submitted a pre-check for today! ✅";
        } else {
            $stmt = $con->prepare("INSERT INTO tbldriverprechecks (
                DriverID, CheckDate, TiresChecked, LightsChecked, BrakesChecked,
                FluidsChecked, WindshieldChecked, HornChecked, WipersChecked,
                SafetyGearChecked, Comments
            ) VALUES (
                :driver_id, :check_date, :tires_checked, :lights_checked, :brakes_checked,
                :fluids_checked, :windshield_checked, :horn_checked, :wipers_checked,
                :safety_gear_checked, :comments
            )");

            $stmt->bindParam(':driver_id', $driver_id);
            $stmt->bindParam(':check_date', $check_date);
            $stmt->bindParam(':tires_checked', $tires_checked);
            $stmt->bindParam(':lights_checked', $lights_checked);
            $stmt->bindParam(':brakes_checked', $brakes_checked);
            $stmt->bindParam(':fluids_checked', $fluids_checked);
            $stmt->bindParam(':windshield_checked', $windshield_checked);
            $stmt->bindParam(':horn_checked', $horn_checked);
            $stmt->bindParam(':wipers_checked', $wipers_checked);
            $stmt->bindParam(':safety_gear_checked', $safety_gear_checked);
            $stmt->bindParam(':comments', $comments);

            if ($stmt->execute()) {
                $message = "Daily Pre-Check Submitted Successfully! 🥳 Drive safely! 🌟";
            } else {
                $message = "Error submitting pre-check. Please try again. 😔";
            }
        }
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage() . " ❌";
        error_log("Pre-check PDO Error: " . $e->getMessage()); // Log the error for debugging
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Driver Pre-Check 🚚</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input[type="checkbox"] { margin-right: 10px; transform: scale(1.2); }
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; resize: vertical; min-height: 80px; }
        button {
            display: block;
            width: 100%;
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 25px;
        }
        button:hover { background-color: #218838; }
        .message {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daily Vehicle Pre-Check 🚚📋</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="tires" value="1"> Tires (pressure, tread, damage) checked ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="lights" value="1"> Lights (headlights, tail lights, indicators) working ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="brakes" value="1"> Brakes functioning properly ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="fluids" value="1"> Fluid levels (oil, coolant, washer fluid) checked ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="windshield" value="1"> Windshield and mirrors clean and undamaged ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="horn" value="1"> Horn working ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="wipers" value="1"> Wipers functioning ✅
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="safety_gear" value="1"> Safety gear (first-aid kit, etc.) present ✅
                </label>
            </div>
            <div class="form-group">
                <label for="comments">Any Issues / Comments? ✍️</label>
                <textarea id="comments" name="comments" rows="4" placeholder="Enter any issues found or additional comments..."></textarea>
            </div>
            <button type="submit">Submit Daily Pre-Check 🚀</button>
        </form>
    </div>
</body>
</html>