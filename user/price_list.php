<?php
session_start();
include('includes/dbconnection.php');

// Handle scrap selection submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_scraps'])) {
    $_SESSION['selected_scraps'] = explode(',', $_POST['selected_scraps']);
    header("Location: lodged-complain.php");
    exit();
}

// Fetch scrap items
$sql = "SELECT * FROM scrap_price_list ORDER BY scrap_name ASC";
$query = $dbh->prepare($sql);
$query->execute();
$scraps = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Scrap Items</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css">
    <link rel="stylesheet" href="../assets/vendor/parsleyjs/css/parsley.css">
    <style>
    body {
        background-color: #f3f4f6;
    }

    .scrap-card {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        background-color: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        cursor: pointer;
    }

    .scrap-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .scrap-card.selected {
        border: 2px solid #007bff;
        background-color: #e9f2ff;
    }

    .scrap-img-container {
        width: 100%;
        height: 160px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 15px;
        overflow: hidden;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .scrap-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .scrap-name {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        text-transform: capitalize;
        margin-bottom: 5px;
    }

    .scrap-price {
        font-size: 16px;
        color: #28a745;
        font-weight: 500;
    }

    .action-buttons {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 1000;
        display: flex;
        gap: 10px;
    }

    .search-container {
        margin: 0 auto 30px;
        max-width: 600px;
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
        display: none;
    }

    @media (max-width: 576px) {
        .scrap-img-container {
            height: 120px;
        }
        .action-buttons {
            position: static;
            margin-bottom: 20px;
            justify-content: flex-end;
        }
    }

    <style>
    /* ... (previous styles remain the same) ... */

    .action-buttons {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 1000;
        display: flex;
        gap: 15px;
    }

    .btn-submit {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 30px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        background-color: #3e8e41;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(76, 175, 80, 0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-back {
        background-color: #f44336;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 30px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-back:hover {
        background-color: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(244, 67, 54, 0.4);
        color: white;
    }

    .btn-back:active {
        transform: translateY(0);
    }

    @media (max-width: 768px) {
        .action-buttons {
            position: static;
            margin-bottom: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-submit, .btn-back {
            padding: 10px 20px;
            font-size: 14px;
        }
    }

    /* ... (previous styles remain the same) ... */

    .action-buttons {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 1000;
        display: flex;
        gap: 15px;
    }

    .btn-submit {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 30px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit:hover {
        background-color: #3e8e41;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(76, 175, 80, 0.4);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-back {
        background-color: #f44336;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 30px;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-back:hover {
        background-color: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(244, 67, 54, 0.4);
        color: white;
    }

    .btn-back:active {
        transform: translateY(0);
    }

    @media (max-width: 768px) {
        .action-buttons {
            position: static;
            margin-bottom: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-submit, .btn-back {
            padding: 10px 20px;
            font-size: 14px;
        }
    }
</style>

</styl>
</style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="page">
            <div class="container mt-5">
                <!-- Action Buttons at Top Right -->
                
                
                <h2 class="mb-4 text-center">Select Scrap Items</h2>
                 
                <div class="action-buttons">
    <button type="submit" form="scrapForm" class="btn-submit">
        <i class="fa fa-check-circle"></i> Submit Selected
    </button>
    <a href="lodged-complain.php" class="btn-back">
        <i class="fa fa-arrow-left"></i> Back to Form
    </a>
</div>
                
                <!-- Search Box -->
                <div class="search-container">
                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search scrap items...">
                    </div>
                </div>
                
                <div id="noResults" class="no-results">
                    <i class="fa fa-exclamation-circle"></i>
                    <p>No scrap items found matching your search.</p>
                </div>

                <form method="POST" id="scrapForm">
                    <div class="row" id="scrapItemsContainer">
                        <?php foreach ($scraps as $scrap): 
                            $scrapName = htmlspecialchars($scrap['scrap_name']);
                            $imageName = htmlspecialchars($scrap['image']);
                            $imageFilePath = "../admin/images/" . $imageName;
                            $serverImagePath = __DIR__ . "/../admin/images/" . $imageName;
                            $finalImagePath = (!empty($imageName) && file_exists($serverImagePath)) 
                                              ? $imageFilePath 
                                              : "../admin/images/default.png";
                            $price = htmlspecialchars($scrap['price'] ?? 'N/A');
                            $unit = htmlspecialchars($scrap['unit_type'] ?? 'unit');
                        ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="scrap-card h-100" data-scrap-name="<?php echo strtolower($scrapName); ?>">
                                <div class="scrap-img-container">
                                    <img src="<?php echo $finalImagePath; ?>" class="scrap-img" alt="Scrap Image"
                                         onerror="this.onerror=null; this.src='../admin/images/default.png';">
                                </div>
                                <div class="scrap-name"><?php echo $scrapName; ?></div>
                                <div class="scrap-price">₹<?php echo $price; ?> <?php echo $unit; ?>KG</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Hidden input to capture selected scraps -->
                    <input type="hidden" name="selected_scraps" id="selectedScrapsInput" />
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <script src="../assets/vendor/parsleyjs/js/parsley.min.js"></script>
    <script src="../assets/js/theme.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scrap selection functionality
            const scrapCards = document.querySelectorAll('.scrap-card');
            scrapCards.forEach(card => {
                card.addEventListener('click', () => {
                    card.classList.toggle('selected');
                    updateSelectedScraps();
                });
            });

            function updateSelectedScraps() {
                const selectedScraps = [];
                document.querySelectorAll('.scrap-card.selected').forEach(card => {
                    const scrapName = card.querySelector('.scrap-name').textContent.toLowerCase();
                    selectedScraps.push(scrapName);
                });
                document.getElementById('selectedScrapsInput').value = selectedScraps.join(',');
            }

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let hasResults = false;
                
                document.querySelectorAll('.col-xl-3, .col-lg-4, .col-md-6, .col-sm-12').forEach(container => {
                    const card = container.querySelector('.scrap-card');
                    const cardName = card.getAttribute('data-scrap-name');
                    
                    if (cardName.includes(searchTerm)) {
                        container.style.display = 'block';
                        hasResults = true;
                    } else {
                        container.style.display = 'none';
                    }
                });
                
                document.getElementById('noResults').style.display = hasResults ? 'none' : 'block';
            });
        });
    </script>
</body>
</html>