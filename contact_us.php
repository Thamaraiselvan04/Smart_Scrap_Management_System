<?php
session_start();
error_reporting(E_ALL); // Enable all error reporting for debugging
include('admin/includes/dbconnection.php');

$query = "SELECT * FROM scrap_price_list";
$stmt = $dbh->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | SSMS</title>
    <meta name="description" content="Contact the Smart Scrap Management System team in Chennai.">
    <meta name="theme-color" content="#1b5e20">
    <link href="img1/trash.png" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Same navbar, footer and colours as the home page -->
    <link href="assets/css/ssms-site.css" rel="stylesheet">
</head>

<body>

<!-- Navbar (same as the home page) -->
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="img1/trash.png" alt="SSMS logo">
            <span>SSMS<small>Smart Scrap System</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Toggle menu">
            <i class="fa-solid fa-bars fs-3 text-green"></i>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 py-3 py-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#how">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#materials">What We Buy</a></li>
                <li class="nav-item"><a class="nav-link" href="price_list.php">Price List</a></li>
                <li class="nav-item"><a class="nav-link active" href="contact_us.php">Contact</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Login</a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 p-2">
                        <li><a class="dropdown-item rounded-3 py-2" href="user/login.php"><i class="fa-solid fa-user text-green me-2"></i>User</a></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="driver/login.php"><i class="fa-solid fa-truck text-green me-2"></i>Driver</a></li>
                        <li><a class="dropdown-item rounded-3 py-2" href="admin/login.php"><i class="fa-solid fa-user-shield text-green me-2"></i>Admin</a></li>
                    </ul>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="btn btn-green py-2 px-4" href="user/login.php"><i class="fa-solid fa-truck-fast me-2"></i>Book Pickup</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page header -->
<section class="page-hero">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-headset"></i> We're here to help</span>
        <h1>Contact Us</h1>
        <p>Questions about a pickup, prices or payment? Send us a message and our team will get back to you.</p>
        <div class="crumb"><a href="index.php">Home</a><i class="fa-solid fa-chevron-right small"></i><span>Contact</span></div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="info-side">
                    <span class="eyebrow" style="background:rgba(156,204,101,.15);color:var(--lime)">Get in touch</span>
                    <h3 class="mb-2">Let's make recycling easy</h3>
                    <p class="mb-0">Doorstep scrap pickup across Chennai with digital weighing and instant payment.</p>
                    <div class="info-line"><span class="ic"><i class="fa-solid fa-location-dot"></i></span><div><b>Service area</b>Chennai, Tamil Nadu</div></div>
                    <div class="info-line"><span class="ic"><i class="fa-solid fa-clock"></i></span><div><b>Pickup hours</b>Every day, 9:00 AM – 7:00 PM</div></div>
                    <div class="info-line"><span class="ic"><i class="fa-solid fa-truck-fast"></i></span><div><b>Book a pickup</b><a href="user/login.php" style="color:var(--lime)">Log in as a user</a> to schedule</div></div>
                    <i class="fa-solid fa-envelope-open-text big-ic"></i>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card">
                    <h3 class="mb-1">Send us a message</h3>
                    <p class="text-muted mb-4">We usually reply within one working day.</p>
                    <form method="POST" action="mail.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your mail" required>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your message" required>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Your Message</label>
                                <textarea class="form-control" id="message" name="message" rows="6" placeholder="Write your message here..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-green"><i class="fa-solid fa-paper-plane me-2"></i>Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer (same as the home page) -->
<footer>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand text-white mb-3" href="index.php" style="color:#fff!important"><img src="img1/trash.png" alt="" style="width:44px">SSMS</a>
                <p>A smart, transparent way to sell your scrap in Chennai. Door-to-door pickup, digital weighing and instant payment.</p>
            </div>
            <div class="col-lg-2 col-md-6 col-6 f-links">
                <h5>Explore</h5>
                <a href="index.php"><i class="fa-solid fa-chevron-right"></i>Home</a>
                <a href="index.php#how"><i class="fa-solid fa-chevron-right"></i>How It Works</a>
                <a href="price_list.php"><i class="fa-solid fa-chevron-right"></i>Price List</a>
                <a href="contact_us.php"><i class="fa-solid fa-chevron-right"></i>Contact Us</a>
            </div>
            <div class="col-lg-2 col-md-6 col-6 f-links">
                <h5>Login</h5>
                <a href="user/login.php"><i class="fa-solid fa-chevron-right"></i>User</a>
                <a href="driver/login.php"><i class="fa-solid fa-chevron-right"></i>Driver</a>
                <a href="admin/login.php"><i class="fa-solid fa-chevron-right"></i>Admin</a>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5>Service Area</h5>
                <div class="contact-line"><i class="fa-solid fa-location-dot"></i><span>Chennai, Tamil Nadu</span></div>
                <div class="contact-line"><i class="fa-solid fa-clock"></i><span>Pickups every day, 9:00 AM – 7:00 PM</span></div>
                <div class="contact-line"><i class="fa-solid fa-scale-balanced"></i><span>Digital weighing and instant payment</span></div>
            </div>
        </div>
        <div class="copy d-flex flex-column flex-md-row justify-content-between gap-2 text-center">
            <span>&copy; <?php echo date("Y"); ?> Smart Scrap Management System. All rights reserved.</span>
            <span>Designed by SSMS</span>
        </div>
    </div>
</footer>

<a href="#" class="back-top" id="backTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Navbar shadow + back-to-top button (same as the home page)
    const nav = document.getElementById('mainNav'), topBtn = document.getElementById('backTop');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', scrollY > 60);
        topBtn.classList.toggle('show', scrollY > 500);
    });
</script>
</body>
</html>