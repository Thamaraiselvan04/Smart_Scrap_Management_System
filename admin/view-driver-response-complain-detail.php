<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
}
else{
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
    </style>
</head>
<body class="theme-indigo">
<?php include_once('includes/header.php');?>
<div class="main_content" id="main-content">
   <?php include_once('includes/sidebar.php');?>
    <div class="page">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="javascript:void(0);">View Lodged Request</a>
        </nav>
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2><strong>View Lodged</strong> Request </h2>
                        </div>
                        <div class="body table-responsive">
<?php
$eid = $_GET['editid'];
$sql = "SELECT lc.*, u.FullName, u.MobileNumber, u.Email
        FROM tbllodgedcomplain lc
        JOIN tbluser u ON u.ID = lc.UserID
        WHERE lc.ID = :eid";
$query = $dbh->prepare($sql);
$query->bindParam(':eid', $eid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() > 0) {
    foreach ($results as $row) {
        $status = $row->Status;
        $comid = $row->ComplainNumber;
?>
<table id="datatable" class="table table-bordered dt-responsive nowrap" style="width:100%;">
  <tr>
    <th style="color: orange;">Request Number</th>
    <td colspan="3" style="color: orange;font-weight: bold;"><?php echo $row->ComplainNumber;?></td>
  </tr>
  <tr>
    <th>Name</th><td><?php echo $row->FullName;?></td>
    <th>Email</th><td><?php echo $row->Email;?></td>
  </tr>
  <tr>
    <th>Mobile Number</th><td><?php echo $row->MobileNumber;?></td>
    <th>Address of Scrap</th><td><?php echo $row->Address;?></td>
  </tr>
  <tr>
    <th>Area</th><td><?php echo $row->Area;?></td>
    <th>Locality</th><td><?php echo $row->Locality;?></td>
  </tr>
  <tr>
    <th>Landmark</th><td><?php echo $row->Landmark;?></td>
    <th>Note</th>
    <td><?php echo empty($row->Note) ? 'No Notes' : htmlentities($row->Note);?></td>
  </tr>
    <tr>
    <th>UPI Mobile No.</th><td><?php echo htmlentities($row->Upi);?></td>
    <th>Pin Number</th><td><?php echo htmlentities($row->Pin);?></td>
  </tr>
   <tr>
    <th>Type of Scrap</th><td><?php echo htmlentities($row->Scraptype);?></td>
    <th>Pick-up Date & Time</th>
    <td><?php echo $row->Date . ' ' . $row->Time;?></td>
  </tr>
  <tr>
    <th>Image</th>
    <td colspan="3"><img src="../user/images/<?php echo $row->Photo;?>" width="200" height="150"></td>
  </tr>
  <tr>
     <th>Assign To</th>                                           
     <td><?php echo htmlentities($row->AssignTo) ? htmlentities($row->AssignTo) : 'Not provided';?></td>
  </tr>
  <tr>
    <th>Payment Mode</th><td><?php echo !empty($row->Paymentmode) ? htmlentities($row->Paymentmode) : 'Not provided';?></td>
    <th>Driver Name</th><td><?php echo !empty($row->DriverName) ? htmlentities($row->DriverName) : 'Not assigned';?></td>
  </tr>
  <tr>
    <th>Driver Phone</th><td><?php echo !empty($row->DriverMobile) ? htmlentities($row->DriverMobile) : 'Not assigned';?></td>
    <th>Request Date</th><td><?php echo $row->ComplainDate;?></td>
  </tr>
  <tr>
    <th>Request Final Status</th>
    <td colspan="3"><?php
      switch($status) {
        case 'Approved': echo 'Your request has been approved'; break;
        case 'Rejected': echo 'Your request has been cancelled'; break;
        case 'On the way': echo 'Driver is on the way'; break;
        case 'Completed': echo 'Scrap has been collected'; break;
        default: echo 'Not responded yet';
      }
    ?></td>
  </tr>
  <tr>
    
    <th>Remark</th>
              <?php if ($row->Status == "") { ?>
                                                <td colspan="4"><?php echo "Not Updated Yet"; ?></td>
                                            <?php } else { ?>
                                                <td><?php echo htmlentities($row->Status); ?></td>
                                            <?php } ?>
  </tr>
</table>

<?php
    // Tracking history
    if (!empty($status)) {
        $ret="SELECT Remark, Status, RemarkDate FROM tblcomtracking WHERE ComplainNumber=:comid ORDER BY RemarkDate DESC";
        $trq = $dbh->prepare($ret);
        $trq->bindParam(':comid', $row->ComplainNumber, PDO::PARAM_STR);
        $trq->execute();
        $history = $trq->fetchAll(PDO::FETCH_OBJ);
?>
<table class="table table-bordered dt-responsive nowrap" style="width:100%;">
  <tr align="center"><th colspan="4" style="color: blue;">Tracking History</th></tr>
  <tr><th>#</th><th>Remark</th><th>Status</th><th>Time</th></tr>
  <?php $i=1; foreach($history as $h) { ?>
  <tr>
    <td><?php echo $i++;?></td>
    <td><?php echo htmlentities($h->Remark);?></td>
    <td><?php echo htmlentities($h->Status);?></td>
    <td><?php echo htmlentities($h->RemarkDate);?></td>
  </tr>
  <?php } ?>
</table>
<?php } ?>

<!-- Scrap Items Collected and Payment Receipt (if completed) -->
<?php if ($status === 'Completed') { 
    // Get scrap items from payment_invoice table
    $items_sql = "SELECT * FROM payment_invoice WHERE ComplainID = :eid";
    $items_query = $dbh->prepare($items_sql);
    $items_query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $items_query->execute();
    $scrap_items = $items_query->fetchAll(PDO::FETCH_OBJ);
    
    if ($items_query->rowCount() > 0) {
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
<?php } ?>

<div class="payment-section">
<h4 style="color: green;">Payment Receipt</h4>
<table class="table table-bordered">
    <tr>
        <th>Payment Mode</th>
        <td><?php echo htmlentities($row->Paymentmode); ?></td>
    </tr>
    <tr>
        <th>Total Amount</th>
        <td>₹<?php echo isset($row->Amount) ? htmlentities($row->Amount) : '—'; ?></td>
    </tr>
    <tr>
        <th>Driver Remark</th>
        <td><?php echo isset($row->Remark) ? htmlentities($row->Remark) : '—'; ?></td>
    </tr>
    <tr>
        <th>Payment Date/Time</th>
        <td><?php echo isset($row->PaymentDateTime) ? htmlentities($row->PaymentDateTime) : '—'; ?></td>
    </tr>
</table>
</div>
<?php } ?>

<?php } // endforeach ?>
<?php } // rowCount ?>
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
<?php } ?>