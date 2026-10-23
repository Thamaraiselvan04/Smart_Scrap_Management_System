<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if(isset($_POST['submit']))
  {
    $fname=$_POST['fname'];
    $uname=$_POST['uname'];
    $mobno=$_POST['mobno'];
    $email=$_POST['email'];
    $password=md5($_POST['password']);
    $ret="select Email,UserName from tbluser where Email=:email || UserName=:uname";
    $query= $dbh -> prepare($ret);
    $query-> bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':uname',$uname,PDO::PARAM_STR);
    $query-> execute();
    $results = $query -> fetchAll(PDO::FETCH_OBJ);
if($query -> rowCount() == 0)
{
$sql="Insert Into tbluser(FullName,UserName,MobileNumber,Email,Password)Values(:fname,:uname,:mobno,:email,:password)";
$query = $dbh->prepare($sql);
$query->bindParam(':fname',$fname,PDO::PARAM_STR);
$query->bindParam(':uname',$uname,PDO::PARAM_STR);
$query->bindParam(':email',$email,PDO::PARAM_STR);
$query->bindParam(':mobno',$mobno,PDO::PARAM_INT);
$query->bindParam(':password',$password,PDO::PARAM_STR);
$query->execute();
$lastInsertId = $dbh->lastInsertId();
if($lastInsertId)
{

echo "<script>alert('You have successfully registered with us');</script>";
}
else
{

echo "<script>alert('Something went wrong.Please try again');</script>";
}
}
 else
{

echo "<script>alert('Email-id already exist. Please try again');</script>";
}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Smart Scrap Management System</title>
    <link rel="icon" href="../img1/trash.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Same design as the login page -->
    <link rel="stylesheet" href="../assets/css/ssms-auth.css">
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
            <h1>Create your account</h1>
            <p class="sub">Register once to book and track scrap pickups.</p>
            <form class="form-auth-small" action="" method="post">
                <div class="field">
                    <label for="fname">Full name</label>
                    <i class="fa-solid fa-id-card ic"></i>
                    <input type="text" id="fname" placeholder="Enter your name" required="true" name="fname" value="" >
                </div>
                <div class="field">
                    <label for="uname">Username</label>
                    <i class="fa-solid fa-user ic"></i>
                    <input type="text" id="uname" placeholder="Choose a username" required="true" name="uname" value="" >
                </div>
                <div class="row-2">
                    <div class="field">
                        <label for="mobno">Mobile number</label>
                        <i class="fa-solid fa-phone ic"></i>
                        <input type="text" id="mobno" placeholder="10 digit number" required="true" name="mobno" value="" maxlength="10" pattern="[0-9]{10}">
                    </div>
                    <div class="field">
                        <label for="email">Email address</label>
                        <i class="fa-solid fa-envelope ic"></i>
                        <input type="email" id="email" placeholder="you@example.com" required="true" name="email" value="" >
                    </div>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <i class="fa-solid fa-lock ic"></i>
                    <input type="password" id="password" placeholder="Create a password" name="password" required="true" value="">
                    <button type="button" class="toggle-pw" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                </div>
                <button type="submit" class="btn-login mt-2" name="submit"><i class="fa-solid fa-user-plus me-2"></i>Create Account</button>
            </form>
            <p class="text-center mt-4 mb-0">Already have an account? <a class="link" href="login.php">Log in</a></p>
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
