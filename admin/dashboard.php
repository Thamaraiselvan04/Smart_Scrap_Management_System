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

<link rel="stylesheet" href="../assets/vendor/charts-c3/plugin.css"/>
<link rel="stylesheet" href="../assets/vendor/jvectormap/jquery-jvectormap-2.0.3.css"/>
<link rel="stylesheet" href="../assets/css/main.css" type="text/css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2E7D32;
            --accent-color: #8BC34A;
            --dark-color: #263238;
            --light-color: #f5f7fa;
            --danger-color: #f44336;
            --warning-color: #ff9800;
            --info-color: #2196F3;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .stat-card .card-body {
            padding: 20px;
            position: relative;
        }
        
        .stat-card h6 {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-card h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .stat-card a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .stat-card a:hover {
            text-decoration: underline;
        }
        
        .stat-card a i {
            margin-left: 5px;
            font-size: 12px;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card.traffic::before {
            background-color: var(--info-color);
        }
        
        .stat-card.sales::before {
            background-color: var(--primary-color);
        }
        
        .stat-card.email::before {
            background-color: var(--warning-color);
        }
        
        .stat-card.domains::before {
            background-color: var(--danger-color);
        }
        
        .page-title {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .welcome-message {
            font-size: 16px;
            opacity: 0.9;
        }
    </style>

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
                                <a href="new-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="assign-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="rejected-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="ontheway-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="completed-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="manage-driver.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="all-complain.php">View Detail <i class="fas fa-arrow-right"></i></a>
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
                                <a href="recycle_center.php">View Detail <i class="fas fa-arrow-right"></i></a>
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

<script src="../assets/bundles/c3.bundle.js"></script>
<script src="../assets/bundles/jvectormap.bundle.js"></script> <!-- JVectorMap Plugin Js -->

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/pages/index.js"></script>
<script src="../assets/js/pages/todo-js.js"></script>
</body>
</html><?php } ?>