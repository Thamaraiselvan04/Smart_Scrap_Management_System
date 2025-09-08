<?php
session_start();
error_reporting(E_ALL); // Enable for debugging, set to 0 in production
include('includes/dbconnection.php');

// Check for valid session - now matching login page's session variable
if (!isset($_SESSION['uuid']) || empty($_SESSION['uuid'])) {
    header('location:logout.php');
    exit();
}

if(isset($_POST['submit'])) {
    $uid = $_SESSION['uuid']; // Now using the correct session variable
    $cpassword = md5($_POST['currentpassword']);
    $newpassword = md5($_POST['newpassword']);
    
    $sql = "SELECT ID FROM tbluser WHERE ID=:uid AND Password=:cpassword";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $uid, PDO::PARAM_INT);
    $query->bindParam(':cpassword', $cpassword, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() > 0) {
        $con = "UPDATE tbluser SET Password=:newpassword WHERE ID=:uid";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':uid', $uid, PDO::PARAM_INT);
        $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
        
        if($chngpwd1->execute()) {
            echo '<script>alert("Your password successfully changed")</script>';
        } else {
            echo '<script>alert("Error updating password")</script>';
        }
    } else {
        echo '<script>alert("Your current password is wrong")</script>';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css">
    <link rel="stylesheet" href="../assets/vendor/parsleyjs/css/parsley.css">
    <link rel="stylesheet" href="../assets/css/main.css" type="text/css">
    <script type="text/javascript">
    function checkpass() {
        if(document.changepassword.newpassword.value != document.changepassword.confirmpassword.value) {
            alert('New Password and Confirm Password field does not match');
            document.changepassword.confirmpassword.focus();
            return false;
        }
        return true;
    }   
    </script>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Change Password</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2>Change Password</h2>
                            </div>
                            <div class="body">
                                <form method="post" onsubmit="return checkpass();" name="changepassword" novalidate>
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password" class="form-control" name="currentpassword" id="currentpassword" required>
                                    </div>
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" name="newpassword" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" required>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary" name="submit">Change</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <script src="../assets/vendor/parsleyjs/js/parsley.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>