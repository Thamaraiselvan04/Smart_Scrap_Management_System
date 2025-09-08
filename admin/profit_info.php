<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['vamsaid']) == 0) {
    header('location:logout.php');
    exit;
}

// Get the group ID from URL
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : '';

if (empty($group_id)) {
    header("Location: sell_profit_report.php");
    exit;
}

// Fetch all transactions in this group
$stmt = $dbh->prepare("SELECT * FROM sell_report WHERE id IN ($group_id)");
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$gross_paid_total = 0;
$gross_resell_total = 0;
$gross_profit = 0;
$gross_loss = 0;

foreach ($transactions as $transaction) {
    $investment = $transaction['Total_kg'] * $transaction['Fixed_amount'];
    $gross_paid_total += $investment;
    
    // Use the actual selling price from the database
    $sale_price = $transaction['Current_total_price'];
    $gross_resell_total += $sale_price;
    
    $profit = $sale_price - $investment;
    
    if ($profit > 0) {
        $gross_profit += $profit;
    } else {
        $gross_loss += abs($profit);
    }
}

// Calculate net profit (profit - loss)
$net_profit = $gross_profit - $gross_loss;

// Get center info from first transaction
$center_info = $transactions[0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Profit Report</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .card {
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            background-color: white;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            padding: 15px 20px;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        .card-body {
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            color: #495057;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        .gross-totals {
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }
        .back-btn {
            margin-bottom: 20px;
            padding: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        .text-right {
            text-align: right;
        }
        .center-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .center-info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .center-info-label {
            font-weight: bold;
            width: 150px;
        }
        .profit {
            color: #28a745;
            font-weight: bold;
        }
        .loss {
            color: #dc3545;
            font-weight: bold;
        }
        .net-profit {
            font-size: 1.2em;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }
        .net-profit-value {
            font-size: 1.3em;
            color: <?= ($net_profit >= 0) ? '#28a745' : '#dc3545' ?>;
        }
        .dash {
            color: #6c757d;
        }
        .summary-card {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-value {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>

        <div class="page">
            <div class="back-btn">
                <a href="sell_profit_report.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Summary
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Enhanced Profit Report</h2>
                </div>
                <div class="card-body">
                    <?php if ($center_info): ?>
                    <div class="center-info">
                        <div class="center-info-row">
                            <div class="center-info-label">Center Name:</div>
                            <div><?= htmlspecialchars($center_info['Center_name']) ?></div>
                        </div>
                        <div class="center-info-row">
                            <div class="center-info-label">Owner Name:</div>
                            <div><?= htmlspecialchars($center_info['Owner_name']) ?></div>
                        </div>
                        <div class="center-info-row">
                            <div class="center-info-label">Report Date:</div>
                            <div><?= htmlspecialchars($center_info['Report_date']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div>Total Investment</div>
                                <div class="summary-value">₹<?= number_format($gross_paid_total, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div>Total Sales</div>
                                <div class="summary-value">₹<?= number_format($gross_resell_total, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div>Gross Profit</div>
                                <div class="summary-value profit">₹<?= number_format($gross_profit, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <div>Gross loss</div>
                                <div class="summary-value loss">₹<?= number_format($gross_loss, 2) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Scrap Name</th>
                                    <th>KG</th>
                                    <th>Per KG or Piece Rate</th>
                                    <th>Investment</th>
                                    <th>Sale Price</th>
                                    <th>Profit/loss</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $index => $transaction): 
                                    $investment = $transaction['Total_kg'] * $transaction['Fixed_amount'];
                                    $sale_price = $transaction['Current_total_price'];
                                    $profit = $sale_price - $investment;
                                    $status_class = $profit > 0 ? 'profit' : 'loss';
                                    $status_text = $profit > 0 ? 'Profit' : 'Loss';
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($transaction['Scrap_name']) ?></td>
                                    <td><?= number_format($transaction['Total_kg'], 2) ?> kg</td>
                                    <td>₹<?= number_format($transaction['Fixed_amount'], 2) ?></td>
                                    <td>₹<?= number_format($investment, 2) ?></td>
                                    <td>₹<?= number_format($sale_price, 2) ?></td>
                                    <td class="<?= $status_class ?>">
                                        ₹<?= number_format(abs($profit), 2) ?>
                                    </td>
                                    <td class="<?= $status_class ?>">
                                        <?= $status_text ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="gross-totals">
                        <div class="row">
                            <div class="col-md-3">
                                <div>Total Investment: ₹<?= number_format($gross_paid_total, 2) ?></div>
                            </div>
                            <div class="col-md-3">
                                <div>Total Sales: ₹<?= number_format($gross_resell_total, 2) ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="profit">Gross Profit: ₹<?= number_format($gross_profit, 2) ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="loss">Gross loss: ₹<?= number_format($gross_loss, 2) ?></div>
                            </div>
                        </div>
                        <div class="row net-profit">
                            <div class="col-md-12 text-right">
                                <div>Net Profit/loss: 
                                    <span class="net-profit-value">
                                        ₹<?= number_format($net_profit, 2) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <script>
        // Additional JavaScript for enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add any additional JavaScript functionality here
            console.log('Enhanced Profit Report loaded');
        });
    </script>
</body>
</html>