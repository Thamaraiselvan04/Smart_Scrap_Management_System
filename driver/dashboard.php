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
                            <h6>Total Assign Request</h6>
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
                            <h6>Inprogress Request</h6>
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
                            <h6>Completed Request</h6>
                           
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


<script src="../assets/js/theme.js"></script>
</body>
</html><?php } ?>