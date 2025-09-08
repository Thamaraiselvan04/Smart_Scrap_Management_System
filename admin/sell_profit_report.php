<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['delete'])) {
    if (!empty($_POST['group_ids'])) {
        $ids = $_POST['group_ids']; // array of comma separated sale_ids strings

        // Flatten all sale IDs from all selected groups into a single array
        $allIds = [];
        foreach($ids as $groupIdStr) {
            $groupIds = explode(',', $groupIdStr);
            foreach($groupIds as $id) {
                $allIds[] = (int)$id;
            }
        }

        // Remove duplicates just in case
        $allIds = array_unique($allIds);

        if (count($allIds) > 0) {
            // Convert to comma separated string for SQL
            $idList = implode(',', $allIds);

            $sql = "DELETE FROM sell_report WHERE id IN ($idList)";
            $query = $dbh->prepare($sql);
            $result = $query->execute();

            if ($result) {
                $_SESSION['success'] = "Selected records deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete selected records.";
            }
        } else {
            $_SESSION['error'] = "No valid records selected for deletion.";
        }
    } else {
        $_SESSION['error'] = "No records selected for deletion.";
    }

    header("Location: sell_profit_report.php");
    exit();
}



if (strlen($_SESSION['vamsaid']) == 0) {
    header('location:logout.php');
    exit;
}

// Handle Delete
/*if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];
    $stmt = $dbh->prepare("DELETE FROM sell_report WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $_SESSION['success'] = "Sale record deleted successfully!";
    header("Location: sell_profit_report.php");
    exit;
}*/

// Set default filter values
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_owner = isset($_GET['owner']) ? $_GET['owner'] : '';

// Build the base query for grouped results
$query = "SELECT 
            Center_name,
            Owner_name,
            Report_date,
            SUM(Current_total_price) as total_sales,
            SUM(Profit) as total_profit,
            COUNT(*) as transaction_count,
            GROUP_CONCAT(id) as sale_ids
          FROM sell_report
          WHERE 1=1";

// Add filters if set
if (!empty($filter_date)) {
    $query .= " AND Report_date = :filter_date";
}
if (!empty($filter_owner)) {
    $query .= " AND Owner_name LIKE :filter_owner";
}

// Complete the query with grouping
$query .= " GROUP BY Center_name, Owner_name, Report_date ORDER BY Report_date DESC";

// Prepare and execute the query
$stmt = $dbh->prepare($query);

if (!empty($filter_date)) {
    $stmt->bindValue(':filter_date', $filter_date);
}
if (!empty($filter_owner)) {
    $stmt->bindValue(':filter_owner', '%' . $filter_owner . '%');
}

$stmt->execute();
$sales_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch details for view mode
$view_details = [];
if (isset($_GET['viewid'])) {
    $view_id = $_GET['viewid'];
    $stmt = $dbh->prepare("SELECT * FROM sell_report WHERE id = :id");
    $stmt->bindParam(':id', $view_id);
    $stmt->execute();
    $view_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get distinct owners for filter dropdown
$stmt = $dbh->prepare("SELECT DISTINCT Owner_name FROM sell_report ORDER BY Owner_name");
$stmt->execute();
$owners = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Profit Report</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .card {
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
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
        }
        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }
        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .detail-view {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            width: 200px;
        }
        .detail-value {
            flex-grow: 1;
        }
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .text-right {
            text-align: right;
        }
        .ml-2 {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Sales Profit Report</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <!-- Filter Form -->
                        <div class="filter-form">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Filter by Date</label>
                                            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Filter by Owner</label>
                                            <select name="owner" class="form-control">
                                                <option value="">All Owners</option>
                                                <?php foreach ($owners as $owner): ?>
                                                    <option value="<?= htmlspecialchars($owner) ?>" <?= ($filter_owner == $owner) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($owner) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="display: flex; align-items: flex-end;">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="sell_profit_report.php" class="btn btn-secondary ml-2">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (!empty($view_details)): ?>
                            <!-- View Details Section -->
                            <div class="card">
                                <div class="card-header">
                                    <h2>Sale Details</h2>
                                </div>
                                <div class="card-body">
                                    <div class="detail-view">
                                        <div class="detail-row">
                                            <div class="detail-label">Center Name:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['Center_name']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Owner Name:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['Owner_name']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Report Date:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['Report_date']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Scrap Name:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['Scrap_name']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Total KG:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['Total_kg']) ?> kg</div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Fixed Amount:</div>
                                            <div class="detail-value">₹<?= htmlspecialchars($view_details['Fixed_amount']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Current Total Price:</div>
                                            <div class="detail-value">₹<?= htmlspecialchars($view_details['Current_total_price']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Profit:</div>
                                            <div class="detail-value">₹<?= htmlspecialchars($view_details['Profit']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Created At:</div>
                                            <div class="detail-value"><?= htmlspecialchars($view_details['created_at']) ?></div>
                                        </div>
                                        <div class="text-right mt-3">
                                            <a href="sell_profit_report.php" class="btn btn-secondary">Close</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Sales Report Table -->
                        <div class="card">
                            <div class="card-header">
                                <h2>Sales Profit Summary</h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <form method="POST" action="sell_profit_report.php" onsubmit="return confirm('Are you sure you want to delete selected records?');">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                 <th>Select </th>
                <th>S.No</th>
               
                <th>Center Name</th>
                <th>Owner Name</th>
                <th>Report Date</th>
                <th>Transactions</th>
                <th>Total Sales (₹)</th>
                <th>Total Profit (₹)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sales_reports)): ?>
                <tr>
                    <td colspan="9" class="text-center">No sales records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sales_reports as $index => $report): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="group_ids[]" value="<?= htmlspecialchars($report['sale_ids']) ?>">
                    </td>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($report['Center_name']) ?></td>
                    <td><?= htmlspecialchars($report['Owner_name']) ?></td>
                    <td><?= htmlspecialchars($report['Report_date']) ?></td>
                    <td><?= htmlspecialchars($report['transaction_count']) ?></td>
                    <td>₹<?= number_format($report['total_sales'], 2) ?></td>
                    <td>₹<?= number_format($report['total_profit'], 2) ?></td>
                    <td>
                        <a href="profit_info.php?group_id=<?= urlencode($report['sale_ids']) ?>" class="btn btn-info">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <button type="submit" name="delete" class="btn btn-danger mt-2">Delete Selected</button>
</form>

<script>
function toggleSelectAll(source) {
    checkboxes = document.getElementsByName('group_ids[]');
    for(let i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>

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
    <script src="../assets/bundles/datatablescripts.bundle.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
    <script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/pages/tables/jquery-datatable.js"></script>
</body>
</html>