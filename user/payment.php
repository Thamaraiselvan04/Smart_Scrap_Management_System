<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['uuid']) == 0) {
    header('location:logout.php');
} else {
?>

<!doctype html>
<html lang="en">

<head>
    <title>Request payment History</title>
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
    <a class="navbar-brand" href="javascript:void(0);">payment list</a>
</nav>
<div class="container-fluid">
<div class="row clearfix">
<div class="col-lg-12">
<div class="card">
<div class="header">
<h2><strong>Request</strong> Payment History</h2>
</div>
<div class="body">
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
<th>Action</th>
</tr>
</thead>
<tbody>

<?php
$uid = $_SESSION['uuid'];
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // Get type from URL

if ($type == 'new') {
    $sql = "SELECT * FROM tbllodgedcomplain WHERE UserID=:uid AND (Status IS NULL OR Status='')";
} elseif ($type == 'completed') {
    $sql = "SELECT * FROM tbllodgedcomplain WHERE UserID=:uid AND Status='Completed'";
} else {
    $sql = "SELECT * FROM tbllodgedcomplain WHERE UserID=:uid";
}

$query = $dbh->prepare($sql);
$query->bindParam(':uid', $uid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$cnt = 1;
if ($query->rowCount() > 0) {
    foreach ($results as $row) {

        $userSql = "SELECT FullName, MobileNumber, Email FROM tbluser WHERE ID=:uid";
        $userQuery = $dbh->prepare($userSql);
        $userQuery->bindParam(':uid', $uid, PDO::PARAM_STR);
        $userQuery->execute();
        $userInfo = $userQuery->fetch(PDO::FETCH_OBJ);
?>

<tr>
<td><?php echo htmlentities($cnt);?></td>
<td><?php echo htmlentities($row->ComplainNumber);?></td>
<td><?php echo htmlentities($userInfo->FullName);?></td>
<td><?php echo htmlentities($userInfo->MobileNumber);?></td>
<td><?php echo htmlentities($userInfo->Email);?></td>
<td><?php echo $row->Status ? htmlentities($row->Status) : "Not Updated Yet"; ?></td>
<td><a href="payment_receipt.php?editid=<?php echo htmlentities($row->ID);?>&comid=<?php echo htmlentities($row->ComplainNumber);?>" class="btn btn-primary">View</a></td>
</tr>

<?php 
$cnt++;
    }
} else {
    echo "<tr><td colspan='7'>No record found</td></tr>";
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

<script src="../assets/bundles/libscripts.bundle.js"></script>
<script src="../assets/bundles/vendorscripts.bundle.js"></script>
<script src="../assets/bundles/datatablescripts.bundle.js"></script>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>
<?php } ?>
