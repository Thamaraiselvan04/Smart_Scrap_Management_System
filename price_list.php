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
    <title>Scrap Price List | SSMS</title>
    <meta name="description" content="Today's scrap prices in Chennai: paper, plastic, metal, e-waste and more.">
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
                <li class="nav-item"><a class="nav-link active" href="price_list.php">Price List</a></li>
                <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact</a></li>
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
        <span class="eyebrow"><i class="fa-solid fa-tags"></i> Today's rates</span>
        <h1>Scrap Price List</h1>
        <p>Fixed, transparent prices for every material. The price is locked when you book, so there is no bargaining at your door.</p>
        <!-- Search Box -->
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search scrap items, e.g. paper, copper...">
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="price-list" id="scrapItemsContainer">
            <?php foreach ($result as $row): ?>
                <div class="scrap-item" data-name="<?= strtolower(htmlspecialchars($row['scrap_name'])) ?>">
                    <?php
                        // Retrieve image filename from the database (stored in 'image' field)
                        $img = $row['image']; // image filename stored in the database

                        // Construct the full image path
                        if (!empty($img) && file_exists("admin/images/" . $img)) {
                            $imgPath = "admin/images/" . $img;
                        } else {
                            // Use default image if not found
                            $imgPath = "admin/images/default.png";
                        }
                    ?>
                    <div class="img-box">
                        <img src="<?= $imgPath ?>" alt="Scrap Image" loading="lazy" onerror="this.onerror=null; this.src='admin/images/default.png';">
                    </div>
                    <h5><?= htmlspecialchars($row['scrap_name']) ?></h5>
                    <span class="price">₹<?= htmlspecialchars($row['price']) ?> <?= htmlspecialchars($row['unit_type']) ?>KG</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResults" class="no-results" style="display: none;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <p class="mb-0">No scrap items found matching your search.</p>
        </div>

        <div class="notes mt-5">
            <h3><i class="fa-solid fa-circle-info me-2"></i>Important Notes</h3>
            <ul>
                <li><i class="fa-solid fa-check"></i>Prices are subject to change based on market conditions</li>
                <li><i class="fa-solid fa-check"></i>Minimum quantities may apply for certain materials</li>
                <li><i class="fa-solid fa-check"></i>Contamination may affect pricing - please separate materials properly</li>
                <li><i class="fa-solid fa-check"></i>We offer premium prices for large quantities (over 10kg)</li>
                <li><i class="fa-solid fa-check"></i>Special pricing available for commercial accounts</li>
            </ul>
        </div>

        <div class="text-center mt-5">
            <a href="user/login.php" class="btn btn-green"><i class="fa-solid fa-truck-fast me-2"></i>Schedule a Pickup</a>
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
            <span>&copy; <?= date('Y') ?> Smart Scrap Management System. All rights reserved.</span>
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

<!-- Search Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const scrapItems = document.querySelectorAll('.scrap-item');
    const noResults = document.getElementById('noResults');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;
        
        scrapItems.forEach(item => {
            const itemName = item.getAttribute('data-name');
            if (itemName.includes(searchTerm)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (hasResults || searchTerm === '') {
            noResults.style.display = 'none';
        } else {
            noResults.style.display = 'block';
        }
    });
});
</script>

</body>
</html>