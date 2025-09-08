<?php
session_start();
error_reporting(E_ALL); // Enable all error reporting
ini_set('display_errors', 1); // Display errors on the page

include('includes/dbconnection.php');

// Include PHPMailer - assuming this is part of your system for cancellation emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Function to send cancellation emails (moved to the top for better organization)
function sendCancellationEmails($comid, $remark, $complaint) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'removed';
        $mail->Password = 'removed'; // This should be an App Password if using Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Common email content
        $emailContent = "
            <h3>Request #$comid has been cancelled</h3>
            <p><strong>Reason:</strong> $remark</p>
            <p><strong>Request Details:</strong></p>
            <ul>
                <li><strong>Scrap Type:</strong> {$complaint->Scraptype}</li>
                <li><strong>Pickup Date:</strong> {$complaint->Date}</li>
                <li><strong>Pickup Time:</strong> {$complaint->Time}</li>
                <li><strong>Location:</strong> {$complaint->Address}, {$complaint->Locality}, {$complaint->Area}</li>
            </ul>
            <p>Regards,<br>Smart Scrap Management Team</p>
        ";

        // 1. Send email to USER
        if (!empty($complaint->user_email)) {
            $mail->setFrom('removed', 'Smart Scrap Management');
            $mail->addAddress($complaint->user_email, $complaint->user_name);
            $mail->isHTML(true);
            $mail->Subject = "Your Scrap Request #$comid has been cancelled";
            $mail->Body = "
                <h3>Dear {$complaint->user_name},</h3>
                <p>Your scrap pickup request <strong>#$comid</strong> has been cancelled.</p>
                $emailContent
            ";
            $mail->send();
            $mail->clearAddresses();
        }

        // 2. Send email to DRIVER (if assigned)
        if (!empty($complaint->AssignTo) && !empty($complaint->driver_email)) {
            $mail->setFrom('removed', 'Smart Scrap Management');
            $mail->addAddress($complaint->driver_email, $complaint->driver_name);
            $mail->isHTML(true);
            $mail->Subject = "Assigned Request #$comid has been cancelled";
            $mail->Body = "
                <h3>Dear {$complaint->driver_name},</h3>
                <p>The scrap pickup request <strong>#$comid</strong> assigned to you has been cancelled.</p>
                $emailContent
            ";
            $mail->send();
            $mail->clearAddresses();
        }

        // 3. Send email to ADMIN
        $adminEmail = 'removed'; // Admin's email
        $mail->setFrom('removed', 'Smart Scrap Management');
        $mail->addAddress($adminEmail, 'Admin');
        $mail->isHTML(true);
        $mail->Subject = "Request #$comid has been cancelled";
        $mail->Body = "
            <h3>Request Cancellation Notification</h3>
            <p>The following request has been cancelled:</p>
            $emailContent
            <p><strong>Cancelled By:</strong> Admin</p>
        ";
        $mail->send();

    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}

if (strlen($_SESSION['uuid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    // Handle Cancel Request form submission
    if(isset($_POST['cancel_request'])) {
        $eid = $_GET['editid'];
        $comid = $_GET['comid'];
        $remark = $_POST['cancel_remark'];

        // Get current complaint details for email notification
        $sql = "SELECT tbllodgedcomplain.*, tbluser.Email as user_email, tbluser.FullName as user_name,
                       tbldriver.Email as driver_email, tbldriver.Name as driver_name
                FROM tbllodgedcomplain
                JOIN tbluser ON tbluser.ID = tbllodgedcomplain.UserID
                LEFT JOIN tbldriver ON tbldriver.DriverID = tbllodgedcomplain.AssignTo
                WHERE tbllodgedcomplain.ID = :eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
        $complaint = $query->fetch(PDO::FETCH_OBJ);

        if($complaint) {
            // Insert into tracking table
            $sql = "INSERT INTO tblcomtracking(ComplainNumber, Remark, Status) VALUES(:comid, :remark, 'Rejected')";
            $query = $dbh->prepare($sql);
            $query->bindParam(':comid', $comid, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->execute();

            // Update main complaint table
            $sql = "UPDATE tbllodgedcomplain SET Status='Rejected', Remark=:remark WHERE ID=:eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query->execute();

            // Send cancellation emails
            sendCancellationEmails($comid, $remark, $complaint);

            echo '<script>alert("Request has been cancelled successfully.")</script>';
            echo '<script>window.location.href="view-lodged-complain-detail.php?editid='.$eid.'&comid='.$comid.'"</script>';
            exit();
        }
    }

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
        .cancel-btn {
            margin-top: 20px;
        }
    </style>
</head>

<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

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
                                    // Modified SQL query to include Paymentmode and DriverEmail for comprehensive display
                                    $sql = "SELECT tbllodgedcomplain.ComplainNumber,tbllodgedcomplain.Area,tbllodgedcomplain.Locality,tbllodgedcomplain.Note,tbllodgedcomplain.Landmark,tbllodgedcomplain.Address,tbllodgedcomplain.Upi,tbllodgedcomplain.Paymentmode,tbllodgedcomplain.Date,tbllodgedcomplain.Scraptype,tbllodgedcomplain.Pin,tbllodgedcomplain.Time, tbllodgedcomplain.Photo,tbllodgedcomplain.DriverName,tbllodgedcomplain.DriverMobile,tbllodgedcomplain.ID as compid,tbllodgedcomplain.Status,tbllodgedcomplain.ComplainDate,tbllodgedcomplain.Remark,tbllodgedcomplain.AssignTo,tbluser.ID as uid,tbluser.FullName,tbluser.MobileNumber,tbluser.Email ,tbldriver.Email as DriverEmail FROM tbllodgedcomplain JOIN tbluser ON tbluser.ID=tbllodgedcomplain.UserID LEFT JOIN tbldriver ON tbldriver.DriverID = tbllodgedcomplain.AssignTo WHERE tbllodgedcomplain.ID=:eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                    ?>
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tr>
                                            <th style="color: orange;">Request Number</th>
                                            <td colspan="4" style="color: orange;font-weight: bold;"><?php echo htmlentities($row->ComplainNumber);?></td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td><?php echo htmlentities($row->FullName);?></td>
                                            <th>Email</th>
                                            <td><?php echo htmlentities($row->Email);?></td>
                                        </tr>
                                        <tr>
                                            <th>Mobile Number</th>
                                            <td><?php echo htmlentities($row->MobileNumber);?></td>
                                            <th>Address of Scrap</th>
                                            <td><?php echo htmlentities($row->Address);?></td>
                                        </tr>
                                        <tr>
                                            <th>Area</th>
                                            <td><?php echo htmlentities($row->Area);?></td>
                                            <th>Locality</th>
                                            <td><?php echo htmlentities($row->Locality);?></td>
                                        </tr>
                                        <tr>
                                            <th>Landmark</th>
                                            <td><?php echo htmlentities($row->Landmark);?></td>
                                            <th>Note</th>
                                            <?php if($row->Note == ""){ ?>
                                                <td><?php echo "No Notes"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Note);?></td>
                                            <?php } ?>
                                        </tr>

                                        <tr>
                                            <th>UPI-linked mobile No</th>
                                            <td><?php echo htmlentities($row->Upi);?></td> <th>Pin Number</th>
                                            <td><?php echo htmlentities($row->Pin);?></td>
                                        </tr>
                                        <tr>
                                            <th>Type of Scrap</th>
                                            <td><?php echo htmlentities($row->Scraptype);?></td>
                                            <th>Pick-up date</th>
                                            <td><?php echo htmlentities($row->Date);?></td>
                                        </tr>
                                        <tr>
                                            <th>Pick-up Timing</th>
                                            <td><?php echo htmlentities($row->Time);?></td>
                                            <th>Payment Mode</th>
                                            <td colspan="3"><?php echo !empty($row->Paymentmode) ? htmlentities($row->Paymentmode) : "Not provided"; ?></td>
                                        </tr>

                                        <tr>
                                            <th>Image</th>
                                            <td colspan="4">
                                                <a href="../user/images/<?php echo htmlentities($row->Photo);?>" target="_blank">
                                                    <img src="../user/images/<?php echo htmlentities($row->Photo);?>" width="200" height="150" alt="Scrap Image">
                                                </a>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Driver Name</th>
                                            <td>
                                                <?php
                                                if(!empty($row->DriverName)) {
                                                    echo htmlentities($row->DriverName);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Driver Phone</th>
                                            <td>
                                                <?php
                                                if(!empty($row->DriverMobile)) {
                                                    echo htmlentities($row->DriverMobile);
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
                                                    echo htmlentities($row->DriverEmail);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th >Assign To</th>
                                            <?php if($row->AssignTo == ""){ ?>
                                                <td><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->AssignTo);?></td>
                                            <?php } ?>
                                            <th>Request Date</th>
                                            <td><?php echo htmlentities($row->ComplainDate);?></td>
                                        </tr>
                                        <tr>
                                            <th> Request Final Status</th>
                                            <td>
                                                <?php
                                                $status = $row->Status; // Get current status for logic below

                                                if($row->Status == "Approved") {
                                                    echo "Your request has been approved";
                                                } elseif($row->Status == "Rejected") {
                                                    echo "Your request has been cancelled";
                                                } elseif($row->Status == "On the way") {
                                                    echo "Driver is on the way";
                                                } elseif($row->Status == "Completed") {
                                                    echo "Scrap has been collected";
                                                } else {
                                                    echo "Not Response Yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Remark</th>
                                            <?php if($row->Status == ""){ ?>
                                                <td colspan="4"><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Remark);?></td> <?php } ?>
                                        </tr>
                                        <?php $cnt=$cnt+1; }} // End of foreach for main complaint details ?>

                                    </table>

                                    <?php
                                    $comid = $_GET['comid'];
                                    // Only show tracking history if a status has been set
                                    if (!empty($status)) {
                                        $ret = "SELECT tblcomtracking.Remark, tblcomtracking.Status, tblcomtracking.RemarkDate FROM tblcomtracking WHERE tblcomtracking.ComplainNumber=:comid ORDER BY tblcomtracking.RemarkDate DESC"; // Ordered by date
                                        $query = $dbh->prepare($ret);
                                        $query->bindParam(':comid', $comid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                    ?>
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
                                        foreach($results as $row) { ?>
                                        <tr>
                                            <td><?php echo $cnt;?></td>
                                            <td><?php echo htmlentities($row->Remark);?></td>
                                            <td><?php echo htmlentities($row->Status);?></td>
                                            <td><?php echo htmlentities($row->RemarkDate);?></td>
                                        </tr>
                                        <?php $cnt=$cnt+1;} ?>
                                    </table>
                                    <?php  } ?>

                                    <?php
                                    if ($status == "Completed") {
                                        // Get payment details from tbllodgedcomplain (Amount, Paymentmode, Remark, PaymentDateTime)
                                        $sql_payment = "SELECT Paymentmode, Amount, Remark, PaymentDateTime FROM tbllodgedcomplain WHERE ComplainNumber = :comid";
                                        $query_payment = $dbh->prepare($sql_payment);
                                        $query_payment->bindParam(':comid', $comid, PDO::PARAM_STR);
                                        $query_payment->execute();
                                        $payment = $query_payment->fetch(PDO::FETCH_OBJ);

                                        // Get scrap items from payment_invoice table
                                        $items_sql = "SELECT Scrap_name, Fixed_rate, Kg, Total, Remark FROM payment_invoice WHERE ComplainID = :eid";
                                        $items_query = $dbh->prepare($items_sql);
                                        $items_query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                        $items_query->execute();
                                        $scrap_items = $items_query->fetchAll(PDO::FETCH_OBJ);

                                        if ($payment) {
                                    ?>
                                    <h4 style="color: green; margin-top: 20px;">Scrap Items Collected</h4>
                                    <table class="table table-bordered scrap-items-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Scrap Type</th>
                                                <th>Rate (₹)</th>
                                                <th>Quantity</th>
                                                <th>Total (₹)</th>
                                                <th>Remark (Driver)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $item_count = 1;
                                            $grand_total = 0;
                                            if ($scrap_items) {
                                                foreach ($scrap_items as $item) {
                                                    $grand_total += $item->Total;
                                            ?>
                                            <tr>
                                                <td><?php echo $item_count++; ?></td>
                                                <td><?php echo htmlentities($item->Scrap_name); ?></td>
                                                <td><?php echo htmlentities($item->Fixed_rate); ?></td>
                                                <td><?php echo htmlentities($item->Kg); ?> kg</td>
                                                <td>₹<?php echo htmlentities($item->Total); ?></td>
                                                <td><?php echo htmlentities($item->Remark); ?></td>
                                            </tr>
                                            <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="6">No scrap items recorded for this collection.</td></tr>';
                                            }
                                            ?>
                                            <tr class="total-row">
                                                <td colspan="4" class="text-right">Grand Total:</td>
                                                <td colspan="2">₹<?php echo htmlentities($grand_total); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <h4 style="color: green;">Payment Receipt</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Payment Mode</th>
                                            <td><?php echo htmlentities($payment->Paymentmode); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount Paid</th>
                                            <td>₹<?php echo htmlentities($payment->Amount); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Driver's Final Remark</th>
                                            <td><?php echo htmlentities($payment->Remark); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date/Time</th>
                                            <td><?php echo htmlentities($payment->PaymentDateTime); ?></td>
                                        </tr>
                                    </table>
                                    <?php
                                        } // End if ($payment)
                                    } // End if ($status == "Completed")
                                    ?>

                                    <?php if ($status != "Completed" && $status != "Rejected") { ?>
                                    <div class="cancel-btn text-center">
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelModal">
                                            Cancel This Request
                                        </button>
                                    </div>

                                    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="cancelModalLabel">Cancel Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to cancel this request?</p>
                                                        <div class="form-group">
                                                            <label for="cancel_remark">Reason for cancellation:</label>
                                                            <textarea class="form-control" id="cancel_remark" name="cancel_remark" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" name="cancel_request" class="btn btn-danger">Confirm Cancellation</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/bundles/libscripts.bundle.js"></script> <script src="../assets/bundles/vendorscripts.bundle.js"></script> <script src="../assets/bundles/datatablescripts.bundle.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>

    <script src="../assets/js/theme.js"></script><script src="../assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>
<?php }  ?>