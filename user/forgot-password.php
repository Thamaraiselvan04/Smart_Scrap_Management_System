<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if(isset($_POST['submit']))
  {
    $email=$_POST['email'];
$mobile=$_POST['mobile'];
$newpassword=md5($_POST['newpassword']);
  $sql ="SELECT Email FROM tbluser WHERE Email=:email and MobileNumber=:mobile";
$query= $dbh -> prepare($sql);
$query-> bindParam(':email', $email, PDO::PARAM_STR);
$query-> bindParam(':mobile', $mobile, PDO::PARAM_STR);
$query-> execute();
$results = $query -> fetchAll(PDO::FETCH_OBJ);
if($query -> rowCount() > 0)
{
$con="update tbluser set Password=:newpassword where Email=:email and MobileNumber=:mobile";
$chngpwd1 = $dbh->prepare($con);
$chngpwd1-> bindParam(':email', $email, PDO::PARAM_STR);
$chngpwd1-> bindParam(':mobile', $mobile, PDO::PARAM_STR);
$chngpwd1-> bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
$chngpwd1->execute();
// echo "<script>alert('Your Password succesfully changed');</script>";
 echo "<script>alert('Your Password succesfully changed'); window.location='login.php';</script>";
}
else {
echo "<script>alert('Email id or Mobile no is invalid');</script>"; 
}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Smart Scrap Management System</title>
    <link rel="icon" href="../img1/trash.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Same design as the login page -->
    <link rel="stylesheet" href="../assets/css/ssms-auth.css">
    <script type="text/javascript">
function valid()
{
if(document.chngpwd.newpassword.value!= document.chngpwd.confirmpassword.value)
{
alert("New Password and Confirm Password Field do not match  !!");
document.chngpwd.confirmpassword.focus();
return false;
}
return true;
}
</script>
</head>
<body>
<div class="auth-wrap" style="--bg:url('https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=1400&q=70')">

    <!-- Mobile header -->
    <div class="mobile-head">
        <a class="brand" href="../index.php"><img src="../img1/trash.png" alt="">SSMS</a>
        <p>Smart Scrap Management System</p>
    </div>

    <!-- Left panel (desktop) -->
    <div class="auth-visual">
        <a class="brand" href="../index.php"><img src="../img1/trash.png" alt="">SSMS</a>
        <div>
            <h2>Sell your scrap from home</h2>
            <p>Book a doorstep pickup in a minute and get paid instantly at fixed prices.</p>
            <div class="perk"><i class="fa-solid fa-calendar-check"></i><div><b>Book a pickup</b><small>Choose your date, time and address</small></div></div>
            <div class="perk"><i class="fa-solid fa-location-crosshairs"></i><div><b>Track your request</b><small>See status updates from the dashboard</small></div></div>
            <div class="perk"><i class="fa-solid fa-seedling"></i><div><b>Get paid and a plant</b><small>Instant payment plus a free sapling</small></div></div>
        </div>
        <div class="foot">Smart Scrap Management System</div>
    </div>

    <!-- Form -->
    <div class="auth-form">
        <div class="form-box">
            <span class="role-badge"><i class="fa-solid fa-user"></i> User Area</span>
            <h1>Reset your password</h1>
            <p class="sub">Enter your registered email and mobile number, then choose a new password.</p>
            <form class="form-auth-small" action="" method="post" name="chngpwd" onSubmit="return valid();">
                <div class="field">
                    <label for="email">Email address</label>
                    <i class="fa-solid fa-envelope ic"></i>
                    <input type="text" id="email" placeholder="Enter your email" required="true" name="email">
                </div>
                <div class="field">
                    <label for="mobile">Mobile number</label>
                    <i class="fa-solid fa-phone ic"></i>
                    <input type="text" id="mobile" name="mobile" placeholder="Registered mobile number" required="true">
                </div>
                <div class="field">
                    <label for="newpassword">New password</label>
                    <i class="fa-solid fa-lock ic"></i>
                    <input type="password" id="newpassword" name="newpassword" placeholder="New password" required="true"/>
                    <button type="button" class="toggle-pw" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                </div>
                <div class="field">
                    <label for="confirmpassword">Confirm password</label>
                    <i class="fa-solid fa-lock ic"></i>
                    <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm password" required="true" />
                    <button type="button" class="toggle-pw" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                </div>
                <button type="submit" class="btn-login mt-2" name="submit"><i class="fa-solid fa-key me-2"></i>Reset Password</button>
            </form>
            <p class="text-center mt-4 mb-0">Remembered it? <a class="link" href="login.php">Back to login</a></p>
            <div class="divider">Other logins</div>
            <div class="other-roles">
                <a href="../driver/login.php"><i class="fa-solid fa-truck me-1"></i> Driver</a>
                <a href="../admin/login.php"><i class="fa-solid fa-user-shield me-1"></i> Admin</a>
            </div>
            <a class="back-home" href="../index.php"><i class="fa-solid fa-arrow-left"></i> Back to home</a>
        </div>
    </div>
</div>

<script>
    // Show / hide password
    document.querySelectorAll('.toggle-pw').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
    }));
</script>
</body>
</html>
