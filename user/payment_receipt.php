<?php 
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['uuid']==0)) {
    header('location:logout.php');
} else {
?>
<!doctype html>
<html lang="en">

<head>
    <title> View Request</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        /* Receipt page: only small extras, main look comes from ssms-panel.css */
        .scrap-items-table .total-row td{background:var(--g-50);font-weight:700;color:var(--g-800)}
        .receipt-total{font:700 1.1rem 'Poppins',sans-serif;color:var(--g-800)}
    </style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img1/trash.png">
    <!-- SSMS theme: same colours and fonts as the home page -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/ssms-panel.css">
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
                                    $sql = "SELECT tbllodgedcomplain.ComplainNumber,tbllodgedcomplain.Area,tbllodgedcomplain.Locality,tbllodgedcomplain.Landmark,tbllodgedcomplain.Address,tbllodgedcomplain.Upi,tbllodgedcomplain.Paymentmode,tbllodgedcomplain.Date,tbllodgedcomplain.Scraptype,tbllodgedcomplain.Pin,tbllodgedcomplain.Time, tbllodgedcomplain.Photo,tbllodgedcomplain.DriverName,tbllodgedcomplain.DriverMobile,tbllodgedcomplain.ID as compid,tbllodgedcomplain.Status,tbllodgedcomplain.ComplainDate,tbllodgedcomplain.Remark,tbllodgedcomplain.AssignTo,tbluser.ID as uid,tbluser.FullName,tbluser.MobileNumber,tbluser.Email from tbllodgedcomplain join tbluser on tbluser.ID=tbllodgedcomplain.UserID where tbllodgedcomplain.ID=:eid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $row) {
                                    ?>
                                    <table id="datatable" class="table table-bordered ss-detail">
                                        <!-- Existing complaint details table remains the same -->
                                     <tr>
                                            <th>Request Number</th>
                                            <td colspan="4"><span class="request-no"><?php echo $bookingno = ($row->ComplainNumber); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td><?php echo $row->FullName; ?></td>
                                            <th>Email</th>
                                            <td><?php echo $row->Email; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mobile Number</th>
                                            <td><?php echo $row->MobileNumber; ?></td>
                                            <th>Address of Scrap</th>
                                            <td><?php echo $row->Address; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Area</th>
                                            <td><?php echo $row->Area; ?></td>
                                            <th>Locality</th>
                                            <td><?php echo $row->Locality; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Landmark</th>
                                            <td><?php echo $row->Landmark; ?></td>
                                            <th>Note</th>
                                            <?php if ($row->Note == "") { ?>
                                                <td><?php echo "No Notes"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Note); ?></td>
                                            <?php } ?>
                                        </tr>

                                        <tr>
                                            <th>UPI-linked mobile No</th>
                                            <td><?php echo $row->Upi; ?></td>
                                            <th>Pin Number</th>
                                            <td><?php echo $row->Pin; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type of Scrap</th>
                                            <td><?php echo $row->Scraptype; ?></td>
                                            <th>Pick-up date</th>
                                            <td><?php echo $row->Date; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Pick-up Timing</th>
                                            <td><?php echo $row->Time; ?></td>
                                            <th>Payment Mode</th>
                                            <td colspan="3"><?php echo !empty($row->Paymentmode) ? htmlentities($row->Paymentmode) : "Not provided"; ?></td>
                                        </tr>

                                        <tr>
                                            <th>Image</th>
                                            <td colspan="4">
                                                <a href="../user/images/<?php echo $row->Photo; ?>">
                                                    <img src="../user/images/<?php echo $row->Photo; ?>" width="200" height="150" alt="Scrap photo">
                                                </a>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Driver Name</th>
                                            <td>
                                                <?php
                                                if (!empty($row->DriverName)) {
                                                    echo htmlentities($row->DriverName);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Driver Phone</th>
                                            <td>
                                                <?php
                                                if (!empty($row->DriverMobile)) {
                                                    echo htmlentities($row->DriverMobile);
                                                } else {
                                                    echo "Not assigned yet";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Assign To</th>
                                            <?php if ($row->AssignTo == "") { ?>
                                                <td><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->AssignTo); ?></td>
                                            <?php } ?>
                                            <th>Request Date</th>
                                            <td><?php echo $row->ComplainDate; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Request Final Status</th>
                                            <td>
                                                <?php
                                                $status = $row->Status;

                                                if ($row->Status == "Approved") {
                                                    echo "Your request has been approved";
                                                }

                                                if ($row->Status == "Rejected") {
                                                    echo "Your request has been cancelled";
                                                }
                                                if ($row->Status == "On the way") {
                                                    echo "Driver is on the way";
                                                }
                                                if ($row->Status == "Completed") {
                                                    echo "Scrap has been collected";
                                                }

                                                if ($row->Status == "") {
                                                    echo "Not Response Yet";
                                                }
                                                ?>
                                            </td>
                                            <th>Remark</th>
                                            <?php if ($row->Status == "") { ?>
                                                <td colspan="4"><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Status); ?></td>
                                            <?php } ?>
                                        </tr>

                                        <?php $cnt = $cnt + 1; } } ?>

                                    </table>

                               

                                    <!-- Tracking History Section -->
                                    <?php
                                    $comid = $_GET['comid'];
                                    if ($status != "") {
                                        $ret = "select tblcomtracking.Remark,tblcomtracking.Status,tblcomtracking.RemarkDate from tblcomtracking where tblcomtracking.ComplainNumber=:comid";
                                        $query = $dbh->prepare($ret);
                                        $query->bindParam(':comid', $comid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        $cnt = 1;
                                    ?>
                                    <h4 class="ss-section-title">Tracking History</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>#</th>
                                            <th>Remark</th>
                                            <th>Status</th>
                                            <th>Time</th>
                                        </tr>
                                        <?php
                                        foreach ($results as $row) { ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><?php echo $row->Remark; ?></td>
                                            <td><?php echo $row->Status; ?></td>
                                            <td><?php echo $row->RemarkDate; ?></td>
                                        </tr>
                                        <?php $cnt = $cnt + 1; } ?>
                                    </table>
                                    <?php } ?>

                                    <!-- Payment Receipt and Scrap Items Section -->
                                    <?php
                                    if ($status == "Completed") {
                                        // Get payment details
                                        $sql = "SELECT Paymentmode, Amount, Remark, PaymentDateTime FROM tbllodgedcomplain WHERE ComplainNumber = :comid";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':comid', $comid, PDO::PARAM_STR);
                                        $query->execute();
                                        $payment = $query->fetch(PDO::FETCH_OBJ);
                                        
                                        // Get scrap items from payment_invoice table
                                        $items_sql = "SELECT * FROM payment_invoice WHERE ComplainID = :eid";
                                        $items_query = $dbh->prepare($items_sql);
                                        $items_query->bindParam(':eid', $eid, PDO::PARAM_INT);
                                        $items_query->execute();
                                        $scrap_items = $items_query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if ($payment) {
                                    ?>
                                    <!-- Scrap Items Collected Table -->
                                    <h4 class="ss-section-title">Scrap Items Collected</h4>
                                    <table class="table table-bordered scrap-items-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Scrap Type</th>
                                                <th>Rate (₹)</th>
                                                <th>Quantity</th>
                                                <th>Total (₹)</th>
                                                <th>Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $item_count = 1;
                                            $grand_total = 0;
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
                                            <?php } ?>
                                            <tr class="total-row">
                                                <td colspan="4" class="text-right">Grand Total:</td>
                                                <td colspan="2">₹<?php echo htmlentities($grand_total); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Payment Receipt Table -->
                                    <h4 class="ss-section-title">Payment Receipt</h4>
                                    <table class="table table-bordered ss-detail">
                                        <tr>
                                            <th>Payment Mode</th>
                                            <td><?php echo htmlentities($payment->Paymentmode); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td class="receipt-total">₹<?php echo htmlentities($payment->Amount); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Driver Remark</th>
                                            <td><?php echo htmlentities($payment->Remark); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date/Time</th>
                                            <td><?php echo htmlentities($payment->PaymentDateTime); ?></td>
                                        </tr>
                                    </table>
                                    <?php } } ?>
                                                                <!-- Print Button -->
                                    <div class="print-btn ss-actions">
                                        <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Print Receipt</button>
                                        <a href="payment.php?type=completed" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
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
    <script src="../assets/js/theme.js"></script>
</body>
</html>
<?php } ?>