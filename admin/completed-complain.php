<?php
session_start();
//error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
} else {
    // Get filter dates if submitted
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
?>
<!doctype html>
<html lang="en">

<head>
    <title>Resolved Request</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body class="theme-indigo">
    <!-- Page Loader -->
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Resolved Request</a>
            </nav>
            <div class="container-fluid">            
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2>Resolved Request </h2>
                            </div>
                            <div class="body">
                                <!-- Date Filter Form -->
                                <form method="post" action="">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                                        </div>
                                        <div class="col-md-2" style="margin-top: 25px;">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                                 <?php if($start_date || $end_date): ?>
                                                <a href="?clear=1" class="btn btn-secondary">Clear</a>
                                            <?php endif; ?>
                                        </div>
                                               

                                    </div>
                                </form>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Request Number</th>
                                                <th>Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Payment Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Request Number</th>
                                                <th>Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Payment Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php
                                            // Base SQL query
                                            $sql = "SELECT tbllodgedcomplain.ComplainNumber, tbllodgedcomplain.AssignTo, 
                                                    tbllodgedcomplain.ID as compid, tbllodgedcomplain.Status, 
                                                    tbllodgedcomplain.PaymentDateTime, tbluser.ID as uid, 
                                                    tbluser.FullName, tbluser.MobileNumber, tbluser.Email 
                                                    FROM tbllodgedcomplain 
                                                    JOIN tbluser ON tbluser.ID = tbllodgedcomplain.UserID 
                                                    WHERE tbllodgedcomplain.Status = 'Completed'";
                                            
                                            // Add date conditions if dates are provided
                                            if (!empty($start_date) && !empty($end_date)) {
                                                $sql .= " AND DATE(tbllodgedcomplain.PaymentDateTime) BETWEEN :start_date AND :end_date";
                                            } elseif (!empty($start_date)) {
                                                $sql .= " AND DATE(tbllodgedcomplain.PaymentDateTime) >= :start_date";
                                            } elseif (!empty($end_date)) {
                                                $sql .= " AND DATE(tbllodgedcomplain.PaymentDateTime) <= :end_date";
                                            }
                                            
                                            $query = $dbh->prepare($sql);
                                            
                                            // Bind parameters if dates are provided
                                            if (!empty($start_date)) {
                                                $query->bindParam(':start_date', $start_date, PDO::PARAM_STR);
                                            }
                                            if (!empty($end_date)) {
                                                $query->bindParam(':end_date', $end_date, PDO::PARAM_STR);
                                            }
                                            
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt = 1;
                                            
                                            if($query->rowCount() > 0) {
                                                foreach($results as $row) { 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($row->ComplainNumber);?></td>
                                                <td><?php echo htmlentities($row->FullName);?></td>
                                                <td><?php echo htmlentities($row->MobileNumber);?></td>
                                                <td><?php echo htmlentities($row->Email);?></td>
                                                <?php if($row->Status=="") { ?>
                                                    <td><?php echo "Not Updated Yet"; ?></td>
                                                <?php } else { ?>
                                                    <td><?php echo htmlentities($row->Status);?> (Assign to <?php echo htmlentities($row->AssignTo);?>)</td>
                                                <?php } ?>
                                                <td><?php 
                                                    if (!empty($row->PaymentDateTime)) {
                                                        $paymentDate = new DateTime($row->PaymentDateTime);
                                                        echo htmlentities($paymentDate->format('d-m-Y H:i:s'));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?></td>
                                                <td><a href="view-driver-response-complain-detail.php?editid=<?php echo htmlentities($row->compid);?>&&comid=<?php echo htmlentities($row->ComplainNumber);?>" class="btn btn-primary">View</a></td>
                                            </tr>
                                            <?php 
                                                $cnt++;
                                                }
                                            } else {
                                                echo '<tr><td colspan="8" class="text-center">No resolved requests found</td></tr>';
                                            }
                                            ?> 
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery Core Js --> 
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