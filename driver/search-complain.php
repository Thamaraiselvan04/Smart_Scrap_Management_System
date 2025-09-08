<?php
session_start();
// error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vamsid']) == 0) {
    header('location:logout.php');
} else {
?>
<!doctype html>
<html lang="en">

<head>
    <title>Search Assign Lodged Request</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
</head>

<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Search Assign Lodged Request</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2>Search Assign Lodged Request</h2>
                            </div>
                            <div class="body">
                                <form id="basic-form" method="post">
                                    <div class="form-group">
                                        <input id="searchdata" type="text" name="searchdata" required="true" class="form-control" placeholder="Enter Request Number">
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary" name="search" id="submit">Search</button>
                                </form>
                                <br>
                                <div class="table-responsive">
                                    <?php
                                    if (isset($_POST['search'])) {
                                        $sdata = $_POST['searchdata'];
                                    ?>
                                        <h4 align="center">Result against "<?php echo $sdata; ?>" keyword </h4>
                                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Request Number</th>
                                                    <th>Name</th>
                                                    <th>Mobile Number</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
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
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php
                                                $did = $_SESSION['vamsdid'];
                                                $sql = "SELECT tbllodgedcomplain.ComplainNumber, tbllodgedcomplain.AssignTo, tbllodgedcomplain.ID as compid, tbllodgedcomplain.Status, tbluser.ID as uid, tbluser.FullName, tbluser.MobileNumber, tbluser.Email 
                                                    FROM tbllodgedcomplain 
                                                    JOIN tbluser ON tbluser.ID = tbllodgedcomplain.UserID 
                                                    WHERE tbllodgedcomplain.ComplainNumber LIKE :searchdata AND tbllodgedcomplain.AssignTo = :did";
                                                $query = $dbh->prepare($sql);
                                                $searchparam = "%" . $sdata . "%";
                                                $query->bindParam(':searchdata', $searchparam, PDO::PARAM_STR);
                                                $query->bindParam(':did', $did, PDO::PARAM_STR);
                                                $query->execute();
                                                $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                if ($query->rowCount() > 0) {
                                                    $cnt = 1;
                                                    foreach ($results as $row) {
                                                ?>
                                                        <tr>
                                                            <td><?php echo htmlentities($cnt); ?></td>
                                                            <td><?php echo htmlentities($row->ComplainNumber); ?></td>
                                                            <td><?php echo htmlentities($row->FullName); ?></td>
                                                            <td><?php echo htmlentities($row->MobileNumber); ?></td>
                                                            <td><?php echo htmlentities($row->Email); ?></td>
                                                            <td>
                                                                <?php
                                                                if ($row->Status == "") {
                                                                    echo "Not Updated Yet";
                                                                } else {
                                                                    echo htmlentities($row->Status) . " (Assign to " . htmlentities($row->AssignTo) . ")";
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <a href="view-complain-detail.php?editid=<?php echo htmlentities($row->compid); ?>&comid=<?php echo htmlentities($row->ComplainNumber); ?>" class="btn btn-primary">View</a>
                                                            </td>
                                                        </tr>
                                                <?php
                                                        $cnt++;
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="7">No record found against this search</td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
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
