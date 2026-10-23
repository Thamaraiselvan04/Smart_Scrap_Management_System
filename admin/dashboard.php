<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']==0)) {
  header('location:logout.php');
  } else{



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
                <div class="dashboard-header">
                    <h1 class="page-title">Admin Dashboard</h1>
                    <p class="welcome-message">Welcome back! Here's what's happening with your system today.</p>
                </div>
                
                <div class="row clearfix">
                    <!-- First Row of Stats -->
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card traffic">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain where Status is null";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totnewreq = $query2->rowCount();
                                ?>
                                <h6>New Lodged Request</h6>
                                <h2><?php echo htmlentities($totnewreq);?></h2>
                                <a href="new-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card sales">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain where Status='Approved'";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totappreq = $query2->rowCount();
                                ?>
                                <h6>Assign Lodged Request</h6>
                                <h2><?php echo htmlentities($totappreq);?></h2>
                                <a href="assign-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card email">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain where Status='Rejected'";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totrejreq = $query2->rowCount();
                                ?>
                                <h6>Rejected Lodged Request</h6>
                                <h2><?php echo htmlentities($totrejreq);?></h2>
                                <a href="rejected-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card domains">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain where Status='On The Way'";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $tototwreq = $query2->rowCount();
                                ?>
                                <h6>Inprogress Lodged Request</h6>
                                <h2><?php echo htmlentities($tototwreq);?></h2>
                                <a href="ontheway-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row of Stats -->
                <div class="row clearfix">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card traffic">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain where Status='Completed'";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totcomreq = $query2->rowCount();
                                ?>
                                <h6>Completed Lodged Request</h6>
                                <h2><?php echo htmlentities($totcomreq);?></h2>
                                <a href="completed-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card sales">
                            <div class="card-body">
                                <?php 
                                $sql1 = "SELECT * from tbldriver";
                                $query1 = $dbh->prepare($sql1);
                                $query1->execute();
                                $totdriver = $query1->rowCount();
                                ?>
                                <h6>Total Drivers</h6>
                                <h2><?php echo htmlentities($totdriver);?></h2>
                                <a href="manage-driver.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card email">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from tbllodgedcomplain";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totrejreq = $query2->rowCount();
                                ?>
                                <h6>Total Request</h6>
                                <h2><?php echo htmlentities($totrejreq);?></h2>
                                <a href="all-complain.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card stat-card domains">
                            <div class="card-body">
                                <?php 
                                $sql2 = "SELECT * from recycle_tb";
                                $query2 = $dbh->prepare($sql2);
                                $query2->execute();
                                $totrejreq = $query2->rowCount();
                                ?>
                                <h6>Total Recyclers</h6>
                                <h2><?php echo htmlentities($totrejreq);?></h2>
                                <a href="recycle_center.php">View Detail <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



<!-- Core -->
<script src="../assets/bundles/libscripts.bundle.js"></script>
<script src="../assets/bundles/vendorscripts.bundle.js"></script>


<script src="../assets/js/theme.js"></script>
</body>
</html><?php } ?>