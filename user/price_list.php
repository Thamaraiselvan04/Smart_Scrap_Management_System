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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Scrap Items</title>
    <link rel="icon" href="../img1/trash.png">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- SSMS theme: same colours and fonts as the home page -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/ssms-panel.css">
    <style>
        /* ---- Select scrap items page ---- */
        .pick-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin-bottom:22px}
        .pick-head h2{font:700 1.5rem 'Poppins',sans-serif;margin:0}
        .pick-head p{color:var(--muted);margin:4px 0 0}
        .action-buttons{display:flex;flex-wrap:wrap;gap:10px}
        .btn-submit,.btn-back{display:inline-flex;align-items:center;gap:8px;border-radius:50px;padding:11px 22px;font-weight:600;font-size:.95rem;border:1.5px solid transparent;transition:.2s;cursor:pointer}
        .btn-submit{background:var(--g-700);color:#fff}
        .btn-submit:hover{background:var(--g-800);transform:translateY(-2px);box-shadow:0 8px 20px rgba(46,125,50,.3)}
        .btn-back{background:#fff;color:var(--ink) !important;border-color:#dbe6dc}
        .btn-back:hover{background:var(--g-50);border-color:var(--g-200)}

        .search-container{max-width:560px;margin:0 0 26px}
        .search-box{position:relative}
        .search-box i{position:absolute;left:18px;top:50%;transform:translateY(-50%);color:var(--g-600)}
        .search-box input.form-control{height:52px;padding-left:46px;border-radius:50px;background:#fff;box-shadow:var(--shadow)}

        .scrap-card{position:relative;height:100%;background:#fff;border:2px solid #e6efe7;border-radius:18px;padding:14px 14px 16px;text-align:center;cursor:pointer;
            box-shadow:var(--shadow);transition:transform .2s,box-shadow .2s,border-color .2s;user-select:none}
        .scrap-card:hover{transform:translateY(-4px);box-shadow:0 16px 32px rgba(13,59,30,.12);border-color:var(--g-200)}
        .scrap-card.selected{border-color:var(--g-600);background:var(--g-50);box-shadow:0 0 0 4px rgba(56,142,60,.15)}
        .scrap-card::after{content:"\f00c";font:normal 13px/1 FontAwesome;position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;
            display:grid;place-items:center;background:#fff;color:transparent;border:2px solid #dbe6dc;transition:.2s}
        .scrap-card.selected::after{background:var(--g-700);border-color:var(--g-700);color:#fff}
        .scrap-img-container{height:150px;display:flex;align-items:center;justify-content:center;background:#f7faf7;border-radius:14px;margin-bottom:14px;overflow:hidden;padding:10px}
        .scrap-img{max-width:100%;max-height:100%;object-fit:contain}
        .scrap-name{font:600 1rem 'Poppins',sans-serif;color:var(--ink);text-transform:capitalize;margin-bottom:6px;line-height:1.3;word-break:break-word}
        .scrap-price{display:inline-block;background:var(--g-100);color:var(--g-800);font-weight:700;font-size:.9rem;padding:4px 12px;border-radius:50px}
        .no-results{display:none;text-align:center;padding:40px 20px;color:var(--muted);background:#fff;border-radius:18px;border:1px dashed #c9d9cb;margin-bottom:24px}
        .no-results i{font-size:2rem;color:var(--g-600);display:block;margin-bottom:8px}

        @media (max-width:767.98px){
            .pick-head h2{font-size:1.25rem}
            .action-buttons{width:100%}
            .btn-submit,.btn-back{flex:1;justify-content:center;padding:11px 14px;font-size:.9rem}
            .scrap-img-container{height:110px}
            .scrap-card{padding:10px 10px 14px}
            .scrap-name{font-size:.9rem}
            .scrap-price{font-size:.8rem;padding:3px 10px}
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>
    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>
        <div class="page">
            <div class="container-fluid">
                <div class="pick-head">
                    <div>
                        <h2>Select Scrap Items</h2>
                        <p>Tap the items you want to sell, then press <b>Submit Selected</b>.</p>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" form="scrapForm" class="btn-submit">
                            <i class="fa fa-check-circle"></i> Submit Selected
                        </button>
                        <a href="lodged-complain.php" class="btn-back">
                            <i class="fa fa-arrow-left"></i> Back to Form
                        </a>
                    </div>
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
                        <div class="col-6 col-md-4 col-xl-3 mb-4">
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