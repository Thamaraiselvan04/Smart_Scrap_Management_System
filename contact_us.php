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
    <title>Scrap Price List | Trashman</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

       <!-- Bootstrap CSS -->
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }

        .contact-section {
            max-width: 700px;
            margin: 60px auto;
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .contact-section h2 {
            font-weight: 500;
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }

        footer {
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            background: #eaeaea;
        }
    



  
    
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f6fa;
        }

        .navbar {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand img {
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            color: #333;
            transition: color 0.3s;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0d6efd;
        }

        .btn-primary {
            border-radius: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 40px 15px;
        }

        .price-list {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
        }

        .card {
            background: #fff;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            width: 270px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
        }

        .card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border: 2px solid #e0e0e0;     /* Light gray border */
            border-radius: 8px;            /* Rounded corners */
            padding: 6px;                  /* Padding inside border */
            background-color: #fff;        /* Optional: makes padding visible */
        }

        .card-body {
            padding: 15px 20px;
        }

        .card h5 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #333;
        }

        .price {
            font-weight: bold;
            color: #28a745;
        }

        footer {
            text-align: center;
            padding: 30px 0;
            background: #f1f1f1;
            margin-top: 50px;
        }

    </style>
</head>

<body>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="img1/trash.png" width="40" height="40" alt="Logo">
            <span class="fs-4 fw-bold text-success">Trashman</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="contact_us.php" class="nav-item nav-link active">Contact US</a></li>
                <li class="nav-item"><a href="price_list.php" class="nav-link">Price List</a></li>
                <li class="nav-item"><a href="driver/login.php" class="nav-link">Driver</a></li>
                <li class="nav-item"><a href="user/login.php" class="nav-link">User</a></li>
               
                <li class="nav-item ms-2">
                    <a href="admin/login.php" class="btn btn-success px-4">Admin Panel</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container contact-section">
    <h2>Contact Us</h2>
    <form method="POST" action="mail.php">
        <div class="mb-3">
            <label for="name" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Your Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your mail" required>
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject of your message" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Your Message</label>
            <textarea class="form-control" id="message" name="message" rows="6" placeholder="Write your message here..." required></textarea>
        </div>

        <button type="submit" class="btn btn-success px-4">Send Message</button>
    </form>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Trashman. All rights reserved.</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>