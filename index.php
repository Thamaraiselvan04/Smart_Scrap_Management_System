<?php
session_start();
error_reporting(0);

include('includes/dbconnection.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Smart Scrap Management System | Eco-Friendly Waste Recycling</title>
    <meta name="description" content="Door-to-door scrap collection service in Chennai. We recycle paper, plastic, metal and more with accurate weights and fair pricing.">
    <meta name="keywords" content="scrap collection, recycling, waste management, Chennai, paper recycling, plastic recycling">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <!-- Favicon -->
    <link href="img1/trash.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;500&family=Roboto:wght@500;700;900&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib1/animate/animate.min.css" rel="stylesheet">
    <link href="lib1/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib1/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css1/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css1/style.css" rel="stylesheet">

    <style>
        :root {
            --primary: #AB7442;
            --secondary: #AB7442;
            --dark: #1D1D1D;
            --light: #F8F9FA;
            --accent: #F39C12;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background-color: #f9f9f9;
        }
        
        .bg-primary {
            background-color: var(--primary) !important;
        }
        
        .text-primary {
            color: var(--primary) !important;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        /* How It Works Section Styling */
        .how-it-works {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        
        .step-container {
            position: relative;
            padding-top: 50px;
        }
        
        .step-line {
            position: absolute;
            height: 2px;
            width: 80%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            top: 100px;
            left: 10%;
            z-index: 1;
        }
        
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            margin-bottom: 30px;
            border-top: 5px solid var(--primary);
        }
        
        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            margin: -50px auto 20px;
            border: 5px solid white;
            box-shadow: 0 0 0 2px var(--primary);
        }
        
        .step-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .step-line {
                display: none;
            }
            
            .step-card {
                margin-bottom: 50px;
            }
        }
        .notice-bar {
  background: linear-gradient(90deg,rgb(136, 132, 122),rgb(108, 216, 30));
  color: #fff;
  padding: 2px 0;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  
  border-radius: 5px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin: 10px 0;
  overflow: hidden;
}

marquee {
  font-weight: 100;
  letter-spacing: 0.5px;
}
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->
<div class="notice-bar">
  <marquee behavior="scroll" direction="left" scrollamount="5">
    Minimum Pickup Value Must be 8kg and above ₹500
  </marquee>
</div>
    <!-- Topbar Start -->
    <div class="container-fluid bg-dark text-white p-0">
        <?php
        $sql="SELECT * from tblpage where PageType='contactus'";
        $query = $dbh->prepare($sql);
        $query->execute();
        $results=$query->fetchAll(PDO::FETCH_OBJ);
        $cnt=1;
        if($query->rowCount() > 0) {
            foreach($results as $row) {               
        ?>      
        <div class="row gx-0 d-none d-lg-flex">
            <div class="col-lg-7 px-5 text-start">
                <div class="h-100 d-inline-flex align-items-center py-3 me-4">
                    <small class="fa fa-map-marker-alt text-primary me-2"></small>
                    <small>Chennai</small>
                </div>
                <div class="h-100 d-inline-flex align-items-center py-3">
                    <small class="far fa-envelope text-primary me-2"></small>
                    <small><?php echo $row->Email;?></small>
                </div>
            </div>
            <div class="col-lg-5 px-5 text-end">
                <div class="h-100 d-inline-flex align-items-center py-3 me-4">
                    <small class="fa fa-phone-alt text-primary me-2"></small>
                    <small>+<?php echo $row->MobileNumber;?></small>
                </div>
                <div class="h-100 d-inline-flex align-items-center">
                    <a class="btn btn-sm-square bg-white text-primary me-1" href="https://www.facebook.com/people/Trashmangogreen/100064159024415/#"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-sm-square bg-white text-primary me-1" href="https://www.youtube.com/channel/UCqOB-UuDLt5RSf1tNHF1p4Q/featured"><i class="fab fa-youtube"></i></a>
                    <a class="btn btn-sm-square bg-white text-primary me-0" href="https://www.instagram.com/trashman_greentechnology/?hl=en"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <?php $cnt=$cnt+1;}} ?>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
 <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h2 class="m-0 text-primary"><img src="img1/trash.png" width="50" height="50">Trashman</h2>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link active">Home</a>
                <a href="contact_us.php" class="nav-item nav-link">Contact US</a>
                <a href="price_list.php" class="nav-item nav-link ">Price List</a>
                <a href="driver/login.php" class="nav-item nav-link">Driver</a>
                <a href="user/login.php" class="nav-item nav-link">User</a>
                <a href="admin/login.php"  class="btn btn-primary py-4 px-lg-5 d-none d-lg-block">Admin<i class="fa fa-arrow-right ms-3"></i></a>

            </div>
            
        </div>
        
    </nav>
    <!-- Navbar End -->


    <!-- Carousel Start -->
    <div class="container-fluid p-0 pb-5">
        <div class="owl-carousel header-carousel position-relative">
            <div class="owl-carousel-item position-relative">
                <img class="img-fluid" src="img1/newcarousel-1.jpg" alt="">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(53, 53, 53, .7);">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12 col-lg-8 text-center">
                                <h5 class="text-white text-uppercase mb-3 animated slideInDown">Welcome To Smart Scrap Management System</h5>
                                <h1 class="display-3 text-white animated slideInDown mb-4">A Door To Door, Online Scrap collection Service In Chennai</h1>
                                <p class="fs-5 fw-medium text-white mb-4 pb-2">Customer's Happiness is our Aim</p>
                                <a href="user/login.php" class="btn btn-primary py-3 px-5 animated slideInRight">Schedule Pickup Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="owl-carousel-item position-relative">
                <img class="img-fluid" src="img1/newcarousel-4.jpg" alt="">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(53, 53, 53, .7);">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-12 col-lg-8 text-center">
                                <h5 class="text-white text-uppercase mb-3 animated slideInDown">Welcome To Smart Scrap Management System</h5>
                                <h1 class="display-3 text-white animated slideInDown mb-4">Get Best Prices For Your Scrap Materials</h1>
                                <p class="fs-5 fw-medium text-white mb-4 pb-2">Transparent weighing and instant payment</p>
                                <a href="price_list.php" class="btn btn-primary py-3 px-5 animated slideInRight">View Price List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Features Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="text-primary">Our Services</h6>
                <h2 class="mb-4">Why Choose Trashman</h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                            <img src="img1/weighingmachine.png" width="60" height="60">
                        </div>
                        <h1 class="display-1 text-light mb-0">01</h1>
                    </div>
                    <h5><b>Accurate measurement</b></h5>
                    <p>Digital weighing for precise measurements</p>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                            <img src="img1/trashtruck.png" width="60" height="60">
                        </div>
                        <h1 class="display-1 text-light mb-0">02</h1>
                    </div>
                    <h5>Door step recycling</h5>
                    <p>We come to your location at your convenience</p>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                            <img src="img1/plant.png" width="60" height="60">
                        </div>
                        <h1 class="display-1 text-light mb-0">03</h1>
                    </div>
                    <h5>Plants for trash</h5>
                    <p>Get free plants when you recycle with us</p>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center justify-content-center bg-light" style="width: 60px; height: 60px;">
                            <i class="fa fa-headphones fa-2x text-black"></i>
                        </div>
                        <h1 class="display-1 text-light mb-0">04</h1>
                    </div>
                    <h5>24/7 Support</h5>
                    <p>Dedicated customer support team</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->
<hr>
    <!-- How It Works Start -->
    <div class="how-it-works">
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <h6 class="text-primary">Process</h6>
                    <h2 class="mb-4">How It Works</h2>
                    <p class="w-75 mx-auto">Selling your scrap has never been easier with our simple 4-step process</p>
                </div>
                
                <div class="step-container">
                    <div class="step-line"></div>
                    
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="step-card">
                                <div class="step-number">1</div>
                                <div class="step-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <h5 class="mb-3">Schedule Pickup</h5>
                                <p >
<h5 style="color:green";>Just Click & Order Us!!</h5>
<p style="color:green";>It’s easy to order us for picking your scrap. You just need to fill the details of your order, and make a click. That is it and we will do the rest.</p>
<br>
<h6 style="color:green";> ->Go to pick up request  page </h6>
 <h6 style="color:green";>->Fill the Details of your Pick  request</h6>
 <h6 style="color:green";>->Click the request  Button</h6></p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                            <div class="step-card">
                                <div class="step-number">2</div>
                                <div class="step-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <h5 class="mb-3">We Collect</h5>
                                                               <p >
<h5 style="color:red";>Here Comes, Trashman</h5>
<p style="color:red";>On your order, the our team will come to pick your scrap on your desired and scheduled time and location</p>
<br>
<h6 style="color:RED";> ->We will come to Pick your scraps </h6>
 <h6 style="color:red";>->You just be comfortable and Don’t worry</h6>
</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                            <div class="step-card">
                                <div class="step-number">3</div>
                                <div class="step-icon">
                                    <i class="fas fa-weight"></i>
                                </div>
                                <h5 class="mb-3">We Weigh</h5>
                                                                                              <p >
<h5 style="color:darkviolet";>Segregate & Pick Up</h5>
<p style="color:darkviolet";>All you need to do is to show Trashman_guy the scrap. Trashman_guy  will segregate , weigh and collect the scrap.</p>
<br>
<h6 style="color:darkviolet";> -> Show him the scrap.</h6>
 <h6 style="color:darkviolet";>-> He will segregate.</h6>
 <h6 style="color:darkviolet";>-> Weigh, pack and Pick.</h6>
</p>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                            <div class="step-card">
                                <div class="step-number">4</div>
                                <div class="step-icon">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <h5 class="mb-3">Get Paid</h5>
                                                                                                                              <p >
<h5 style="color:darkorange";>Get Paid</h5>
<p style="color:darkorange";>Once done with all, the Trashman_guy  will pay you the prices for what you just sold to us, instantly. You don’t need to bargain and face a mess with the vendor, as the prices are already fixed just when you placed the order.</p>
<br>
<h6 style="color:darkorange";> -> You will be paid for what you sell at the best prices, even instantly</h6>
 
</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <a href="user/login.php" class="btn btn-primary py-3 px-5">Schedule Your Pickup Now</a>
                </div>
            </div>
        </div>
    </div>
    <!-- How It Works End -->
<hr>
    <!-- Materials We Accept Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                <h6 class="text-primary">Recycling</h6>
                <h2 class="mb-4">What We Recycle</h2>
                <p class="w-75 mx-auto">We accept a wide variety of recyclable materials to help reduce landfill waste</p>
            </div>
            <div class="owl-carousel testimonial-carousel wow fadeInUp" data-wow-delay="0.3s">
                <div class="testimonial-item text-center">
                    <img class="img-fluid bg-light p-2 mx-auto mb-3" src="img1/1_!.jpg" style="width: 200px; height: 250px; border:3px solid black">
                    <div class="testimonial-text text-center p-4">
                        <p>Paper Scrap: Paper scrap consists of discarded paper products such as newspapers, cardboard, and office paper, which can be recycled to create new paper goods, saving trees and reducing landfill waste.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="img-fluid bg-light p-2 mx-auto mb-3" src="img1/2_!.jpg" style="width: 200px; height: 250px; border:3px solid black">
                    <div class="testimonial-text text-center p-4">
                        <p>Plastic Scrap: Plastic scrap includes used plastic bottles, containers, and packaging, which can be recycled into new plastic products, helping to reduce plastic pollution and conserve petroleum resources.</p>
                    </div>
                </div>
                <div class="testimonial-item text-center">
                    <img class="img-fluid bg-light p-2 mx-auto mb-3" src="img1/3_!.jpg" style="width: 200px; height: 250px; border:3px solid black">
                    <div class="testimonial-text text-center p-4">
                        <p>Metal Scrap: Metal scrap includes materials like aluminum, steel, and copper, which can be recycled and repurposed for new manufacturing processes, reducing environmental impact and conserving resources.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Materials We Accept End -->

    <!-- About Start -->
    <div class="container-fluid bg-light overflow-hidden my-5 px-lg-0">
        <div class="container about px-lg-0">
            <div class="row g-0 mx-lg-0">
                <div class="col-lg-6 ps-lg-0" style="min-height: 400px; padding: 0 20px; display: flex; justify-content: center; align-items: center;">
                    <div class="position-relative">
                        <img class="img-fluid" src="img1/aboutus.jpg" style="max-width: 79%; height: auto; margin: 40px; border:3px solid black" alt="">
                    </div>
                </div>
                <div class="col-lg-6 about-text py-5 wow fadeIn" data-wow-delay="0.5s">
                    <div class="p-lg-5 pe-lg-0">
                        <div class="section-title text-start">
                            <h1 class="display-5 mb-4">About Us</h1>
                        </div>
                        <p class="mb-4 pb-2">
                            Trashman Waste Management Services Private Limited is a company based in Chennai, India, focused on providing waste collection and recycling services. The company was founded in November 2021 by a group of mechanical engineers with the goal of promoting eco-friendly waste disposal and creating a more sustainable environment.
                        </p>
                        <br>
                        <h6>What we do:</h6>
                        <p>
                            <b>Waste Collection:</b> Trashman offers door-to-door collection services for various recyclable materials such as paper, plastic, cardboard, metals, e-waste, and home appliances. This service allows individuals and businesses to recycle their waste responsibly, helping to reduce landfill waste.
                            <br><br>
                            <b>Convenience through Technology:</b> Trashman makes it easy for people to schedule pickups via their website, or through WhatsApp. This app offers a convenient way to request pickup, track orders, and even receive payment for the recyclables they hand over.
                        </p>
                        <div class="row g-4 mb-4 pb-2">
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.1s">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex flex-shrink-0 align-items-center justify-content-center bg-white" style="width: 60px; height: 60px;">
                                        <i class="fa fa-users fa-2x text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h2 class="text-primary mb-1" data-toggle="counter-up">1200</h2>
                                        <p class="fw-medium mb-0">Happy Clients</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 wow fadeIn" data-wow-delay="0.3s">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex flex-shrink-0 align-items-center justify-content-center bg-white" style="width: 60px; height: 60px;">
                                        <i class="fa fa-check fa-2x text-primary"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h2 class="text-primary mb-1" data-toggle="counter-up">1501</h2>
                                        <p class="fw-medium mb-0">Projects Done</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- CTA Start -->
    <div class="container-fluid bg-primary text-white py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Ready to Recycle Your Scrap?</h2>
                    <p class="mb-5">Join thousands of Chennai residents who are turning their waste into wealth while protecting the environment.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="user/login.php" class="btn btn-light py-3 px-5">Schedule Pickup Now</a>
                        <a href="contact_us.php" class="btn btn-outline-light py-3 px-5">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CTA End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer mt-5 pt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-light mb-4">Address</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i><?php echo $row->PageDescription;?></p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+<?php echo $row->MobileNumber;?></p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i><?php echo $row->Email;?></p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-sm-square bg-white text-primary me-1" href="https://www.facebook.com/people/Trashmangogreen/100064159024415/#"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-sm-square bg-white text-primary me-1" href="https://www.youtube.com/channel/UCqOB-UuDLt5RSf1tNHF1p4Q/featured"><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-sm-square bg-white text-primary me-0" href="https://www.instagram.com/trashman_greentechnology/?hl=en"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <?php
                    $sql1="SELECT * from tblpage where PageType='aboutus'";
                    $query1 = $dbh->prepare($sql1);
                    $query1->execute();
                    $results1=$query1->fetchAll(PDO::FETCH_OBJ);
                    $cnt=1;
                    if($query1->rowCount() > 0) {
                        foreach($results1 as $row1) {
                    ?>
                    <h4 class="text-light mb-4"><?php echo $row1->PageTitle;?></h4>
                    <p><?php echo $row1->PageDescription;?></p>
                    <?php $cnt=$cnt+1;}} ?>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-light mb-4">Quick Links</h4>
                    <a class="btn btn-link" href="index.php">Home</a>
                    <a class="btn btn-link" href="contact_us.php">Contact Us</a>
                    <a class="btn btn-link" href="price_list.php">Price List</a>
                    <a class="btn btn-link" href="driver/login.php">Driver Login</a>
                    <a class="btn btn-link" href="user/login.php">User Login</a>
                    <a class="btn btn-link" href="admin/login.php">Admin Login</a>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="border-bottom" href="#">Smart Scrap Management System</a>, All Right Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        Designed By <a class="border-bottom" href="#">Trashman Team</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-0 back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib1/wow/wow.min.js"></script>
    <script src="lib1/easing/easing.min.js"></script>
    <script src="lib1/waypoints/waypoints.min.js"></script>
    <script src="lib1/counterup/counterup.min.js"></script>
    <script src="lib1/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib1/isotope/isotope.pkgd.min.js"></script>
    <script src="lib1/lightbox/js/lightbox.min.js"></script>

    <!-- Template Javascript -->
    <script src="js1/main.js"></script>
    
    <script>
        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });
        
        // Initialize WOW.js for animations
        new WOW().init();
    </script>
</body>
</html>