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

    <!-- Custom Styles -->
    <style>
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

        .card-img-container {
            width: 100%;
            height: 140px; /* Reduced height */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fff;
            margin: 10px auto;
        }

        .card img {
            max-width: 90%; /* Smaller image size */
            max-height: 90%;
            object-fit: contain; /* Changed to contain to show full image */
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
        .notes {
            margin-top: 3rem;
            padding: 1.5rem;
            background-color: var(--lighter);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        :root {
            --primary: #2e7d32;
            --primary-light: #4caf50;
            --secondary: #ff9800;
            --accent: #ff5722;
            --dark: #263238;
            --darker: #1a2327;
            --light: #f5f5f5;
            --lighter: #ffffff;
            --gray: #607d8b;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .notes {
            margin-top: 3rem;
            padding: 1.5rem;
            background-color: var(--lighter);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .notes h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .search-container {
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-left: 40px;
            border-radius: 30px;
            height: 50px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #6c757d;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 1.2rem;
        }

    </style>
</head>

<body>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="img1/trash.png" width="40" height="40" alt="Logo">
            <span class="fs-4 fw-bold text-primary">Trashman</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <a href="contact_us.php" class="nav-item nav-link ">Contact us</a>
                <li class="nav-item"><a href="price_list.php" class="nav-link active">Price List</a></li>
                <li class="nav-item"><a href="driver/login.php" class="nav-link">Driver</a></li>
                <li class="nav-item"><a href="user/login.php" class="nav-link">User</a></li>
     
                <li class="nav-item ms-2">
                    <a href="admin/login.php" class="btn btn-primary px-4">Admin Panel</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- Navbar End -->

<!-- Main Content -->

<div class="container">
    <div class="notes">
        <h3>Important Notes</h3>
        <ul>
            <li>Prices are subject to change based on market conditions</li>
            <li>Minimum quantities may apply for certain materials</li>
            <li>Contamination may affect pricing - please separate materials properly</li>
            <li>We offer premium prices for large quantities (over 10kg)</li>
            <li>Special pricing available for commercial accounts</li>
        </ul>
    </div>
    <br>
    
    <h1 class="text-center mb-4">Scrap Price List</h1>
    
    <!-- Search Box -->
    <div class="search-container">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control" placeholder="Search scrap items...">
        </div>
    </div>
    
    <div class="price-list" id="scrapItemsContainer">
        <?php foreach ($result as $row): ?>
            <div class="card scrap-item" data-name="<?= strtolower(htmlspecialchars($row['scrap_name'])) ?>">
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
                <div class="card-img-container">
                    <img src="<?= $imgPath ?>" alt="Scrap Image" onerror="this.onerror=null; this.src='admin/images/default.png';">
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($row['scrap_name']) ?></h5>
                    <span class="price">₹<?= htmlspecialchars($row['price']) ?> &nbsp;&nbsp;
                    <span> <?= htmlspecialchars($row['unit_type']) ?>KG</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="noResults" class="no-results" style="display: none;">
        <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
        <p>No scrap items found matching your search.</p>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; <?= date('Y') ?> Trashman. All rights reserved.</p>
</footer>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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