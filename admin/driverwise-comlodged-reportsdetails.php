<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
} else {
?>
<!doctype html>
<html lang="en">

<head>
    <title>Driverwise Between Dates Report of User Requests</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Driverwise Between Dates Report of User Requests</a>
            </nav>
            <div class="container-fluid">            
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <?php
                                $fdate=$_POST['fromdate'];
                                $tdate=$_POST['todate'];
                                ?>
                                <h5 align="center" style="color:blue">Report from <?php echo $fdate?> to <?php echo $tdate?></h5>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Work Assign</th>
                                                <th>Completed Work</th>
                                                <th>Remaining Work</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT 
                                                    d.DriverID,
                                                    d.Name,
                                                    COUNT(lc.ID) AS assigned,
                                                    SUM(CASE WHEN lc.Status = 'Completed' THEN 1 ELSE 0 END) AS completed
                                                FROM 
                                                    tbldriver d
                                                LEFT JOIN 
                                                    tbllodgedcomplain lc ON d.DriverID = lc.AssignTo 
                                                    AND DATE(lc.ComplainDate) BETWEEN :fdate AND :tdate
                                                GROUP BY 
                                                    d.DriverID, d.Name
                                                HAVING 
                                                    COUNT(lc.ID) > 0";
                                            
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':fdate', $fdate, PDO::PARAM_STR);
                                            $query->bindParam(':tdate', $tdate, PDO::PARAM_STR);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            $cnt = 1;
                                            if($query->rowCount() > 0) {
                                                foreach($results as $row) { 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($row->DriverID);?></td>
                                                <td><?php echo htmlentities($row->Name);?></td>
                                                <td><?php echo htmlentities($row->assigned);?></td>
                                                <td><?php echo htmlentities($row->completed);?></td>
                                                <td><?php echo htmlentities($row->assigned - $row->completed);?></td>
                                            </tr>
                                            <?php 
                                                $cnt++;
                                                }
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="6" align="center">No records found for the selected date range</td>
                                            </tr>
                                            <?php } ?> 
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