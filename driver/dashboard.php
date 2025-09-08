<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsid']==0)) {
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
        
        .welcome-user {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 20px;
        }
        
        .profile-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .profile-btn:hover {
            background-color: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>




</head>
<body class="theme-indigo">

<?php include_once('includes/header.php');?>

<div class="main_content" id="main-content">

    <?php include_once('includes/sidebar.php');?>

    

   <div class="page">
            <div class="container-fluid">
              <?php
                     $did=$_SESSION['vamsdid'];
                    $sql="SELECT Name,Email from  tbldriver where DriverID=:did";
$query = $dbh -> prepare($sql);
$query->bindParam(':did',$did,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

foreach($results as $row)
{    
$email=$row->Email;   
$fname=$row->Name;     
}   ?>
                
                <div class="dashboard-header">
                    <h1 class="page-title">Driver Dashboard</h1>
                    <p class="welcome-message">Welcome back, <?php echo htmlentities($fname); ?>! Here's your activity summary.</p>
                </div>
                
                <div class="row clearfix">
                    <!-- Total Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card traffic">
                            <div class="card-body">
                                <?php 
                         $did=$_SESSION['vamsdid'];
$sql1 ="SELECT * from  tbllodgedcomplain where AssignTo=:did ";
$query1 = $dbh -> prepare($sql1);
$query1-> bindParam(':did', $did, PDO::PARAM_STR);
$query1->execute();
$results1=$query1->fetchAll(PDO::FETCH_OBJ);
$totassrequest=$query1->rowCount();
?>
                            <h6 style="color: red;">Total Assign Request</h6>
                            <h2><?php echo htmlentities($totassrequest);?></h2>
                            <a href="all-complain.php"><small> View Detail</small></a>
                          
                        </div>
                    </div>
                </div>
                    
                    <!-- New Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card sales">
                            <div class="card-body">
                                <?php 
                         $did=$_SESSION['vamsdid'];
$sql ="SELECT * from  tbllodgedcomplain where Status='On The Way' && AssignTo=:did ";
$query = $dbh -> prepare($sql);
$query-> bindParam(':did', $did, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$tototwcomp=$query->rowCount();
?>
                            <h6 style="color: orange;">Inprogress Request</h6>
                           <h2><?php echo htmlentities($tototwcomp);?></h2>
                            <a href="ontheway-complain.php"><small> View Detail</small></a>
                        </div>
                    </div>
                </div>
                    <!-- Completed Request -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card email">
                            <div class="card-body">
                                 <?php 
                         $did=$_SESSION['vamsdid'];
$sql ="SELECT * from  tbllodgedcomplain where Status='Completed' && AssignTo=:did ";
$query = $dbh -> prepare($sql);
$query-> bindParam(':did', $did, PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$totcompcomplain=$query->rowCount();
?>
                            <h6 style="color: green;">Completed Request</h6>
                           
                            <h2><?php echo htmlentities($totcompcomplain);?></h2>
                            <a href="completed-complain.php"><small> View Detail</small></a>
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