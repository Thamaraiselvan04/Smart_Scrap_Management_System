<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['uuid']) == 0) {
    header('location:logout.php');
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Dashboard</title>
<link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
<link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
<link rel="stylesheet" href="../assets/css/main.css" type="text/css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img1/trash.png">
    <!-- SSMS theme: same colours and fonts as the home page -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/ssms-panel.css">
</head>
<body class="theme-indigo">

<?php include_once('includes/header.php');?>
<div class="main_content" id="main-content">
<?php include_once('includes/sidebar.php');?>

 <div class="page">
            <div class="container-fluid">
                <?php
                $uid = $_SESSION['uuid'];
                $sql = "SELECT FullName, Email FROM tbluser WHERE ID=:uid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                foreach($results as $row) {
                    $fname = $row->FullName;
                }
                ?>
                
                <div class="dashboard-header">
                    <h1 class="page-title">User Dashboard</h1>
                    <p class="welcome-message">Welcome back, <?php echo htmlentities($fname); ?>! Here's your activity summary.</p>
                </div>
                
                <div class="row clearfix">
                    <!-- Total Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card traffic">
                            <div class="card-body">
                                <?php 
                                $sql ="SELECT ID FROM tbllodgedcomplain WHERE UserID=:uid";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                $query->execute();
                                $totalRequest = $query->rowCount();
                                ?>
                                <h6>Total Requests</h6>
                                <h2><?php echo htmlentities($totalRequest);?></h2>
                                <a href="lodged-complain-history.php?type=all">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- New Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card sales">
                            <div class="card-body">
                                <?php 
                                $sql ="SELECT ID FROM tbllodgedcomplain WHERE UserID=:uid AND (Status IS NULL OR Status='')";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                $query->execute();
                                $newRequest = $query->rowCount();
                                ?>
                                <h6>New Requests</h6>
                                <h2><?php echo htmlentities($newRequest);?></h2>
                                <a href="lodged-complain-history.php?type=new">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completed Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card email">
                            <div class="card-body">
                                <?php 
                                $sql ="SELECT ID FROM tbllodgedcomplain WHERE UserID=:uid AND Status='Completed'";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                $query->execute();
                                $completedRequest = $query->rowCount();
                                ?>
                                <h6>Completed Requests</h6>
                                <h2><?php echo htmlentities($completedRequest);?></h2>
                                <a href="lodged-complain-history.php?type=completed">View Detail <i class="fa fa-arrow-right"></i></a>
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
