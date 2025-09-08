<?php
session_start();
include('includes/dbconnection.php');
if (strlen($_SESSION['vamsaid']) == 0) {
    header('location:logout.php');
} else {
    // Get all current scrap items from payment_invoice grouped by scrap_name and fixed_rate
    $sql_fetch = "SELECT 
                    Scrap_name, 
                    Fixed_rate, 
                    SUM(Kg) AS total_kg, 
                    SUM(Total) AS total_amount
                FROM 
                    payment_invoice
                GROUP BY 
                    Scrap_name, Fixed_rate
                ORDER BY 
                    Scrap_name ASC";
    
    $query_fetch = $dbh->prepare($sql_fetch);
    $query_fetch->execute();
    $new_results = $query_fetch->fetchAll(PDO::FETCH_OBJ);

    // Get existing items from scrap_collection_history
    $existing_items = $dbh->query("SELECT * FROM scrap_collection_history")->fetchAll(PDO::FETCH_OBJ);

    // First clear all total values (we'll recalculate them)
    $dbh->exec("UPDATE scrap_collection_history SET total_kg = 0, total_amount = 0");

    // Process each new item from payment_invoice
    foreach($new_results as $new_item) {
        $found = false;
        
        // Check if this item already exists in history
        foreach($existing_items as $existing) {
            if ($existing->scrap_name == $new_item->Scrap_name && 
                $existing->fixed_rate == $new_item->Fixed_rate) {
                $found = true;
                
                // Calculate the difference between new total and previous total
                $diff_kg = $new_item->total_kg - $existing->total_kg;
                $diff_amount = $new_item->total_amount - $existing->total_amount;
                
                // Update totals and adjust current values by the difference
                $update_sql = "UPDATE scrap_collection_history SET 
                                total_kg = :total_kg,
                                total_amount = :total_amount,
                                current_kg = current_kg + :diff_kg,
                                current_total = current_total + :diff_amount
                              WHERE scrap_name = :scrap_name AND fixed_rate = :fixed_rate";
                $update_query = $dbh->prepare($update_sql);
                $update_query->execute([
                    ':total_kg' => $new_item->total_kg,
                    ':total_amount' => $new_item->total_amount,
                    ':diff_kg' => $diff_kg,
                    ':diff_amount' => $diff_amount,
                    ':scrap_name' => $new_item->Scrap_name,
                    ':fixed_rate' => $new_item->Fixed_rate
                ]);
                break;
            }
        }
        
        // If not found, insert new record with current values equal to totals
        if (!$found) {
            $insert_sql = "INSERT INTO scrap_collection_history (
                            scrap_name, 
                            fixed_rate, 
                            total_kg, 
                            total_amount,
                            current_kg,
                            current_total,
                            collection_date
                        ) VALUES (
                            :scrap_name,
                            :fixed_rate,
                            :total_kg,
                            :total_amount,
                            :total_kg,
                            :total_amount,
                            CURDATE()
                        )";
            
            $insert_query = $dbh->prepare($insert_sql);
            $insert_query->execute([
                ':scrap_name' => $new_item->Scrap_name,
                ':fixed_rate' => $new_item->Fixed_rate,
                ':total_kg' => $new_item->total_kg,
                ':total_amount' => $new_item->total_amount
            ]);
        }
    }
?>
<!doctype html>
<html lang="en">
<head>
    <title>Scrap Collection History - Admin Panel</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .current-values {
            color: #3498db;
            font-weight: 500;
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Scrap Collection History</a>
            </nav>
            <div class="container-fluid">            
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header">
                                <h2>Scrap Collection History</h2>
                            </div>
                            <div class="body">
                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                    <?php unset($_SESSION['success']); ?>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Scrap Name</th>
                                                <th>Fixed Rate (₹)</th>
                                                <th>Total Kg/Piece</th>
                                                <th>Current Kg/Piece</th>
                                                <th>Total Amount (₹)</th>
                                                <th>Current Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch data from scrap_collection_history table grouped by scrap_name and fixed_rate
                                            $sql_display = "SELECT 
                                                            scrap_name, 
                                                            fixed_rate, 
                                                            SUM(total_kg) AS total_kg, 
                                                            SUM(current_kg) AS current_kg,
                                                            SUM(total_amount) AS total_amount,
                                                            SUM(current_total) AS current_total
                                                        FROM 
                                                            scrap_collection_history 
                                                        GROUP BY
                                                            scrap_name, fixed_rate
                                                        ORDER BY 
                                                            scrap_name ASC";
                                            $query_display = $dbh->prepare($sql_display);
                                            $query_display->execute();
                                            $display_results = $query_display->fetchAll(PDO::FETCH_OBJ);

                                            $cnt = 1;
                                            $grand_total_kg = 0;
                                            $grand_total_amount = 0;
                                            $grand_current_kg = 0;
                                            $grand_current_amount = 0;

                                            if($query_display->rowCount() > 0) {
                                                foreach($display_results as $row) {
                                                    $grand_total_kg += $row->total_kg;
                                                    $grand_total_amount += $row->total_amount;
                                                    $grand_current_kg += $row->current_kg;
                                                    $grand_current_amount += $row->current_total;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($cnt);?></td>
                                                        <td><?php echo htmlentities($row->scrap_name);?></td>
                                                        <td><?php echo number_format($row->fixed_rate, 2);?></td>
                                                        <td><?php echo number_format($row->total_kg, 2);?></td>
                                                        <td class="current-values"><?php echo number_format($row->current_kg, 2);?></td>
                                                        <td><?php echo number_format($row->total_amount, 2);?></td>
                                                        <td class="current-values">₹<?php echo number_format($row->current_total, 2);?></td>
                                                    </tr>
                                                    <?php
                                                    $cnt++;
                                                }
                                                ?>
                                                <tr class="total-row">
                                                    <td colspan="3" class="text-end">Grand Total:</td>
                                                    <td><?php echo number_format($grand_total_kg, 2); ?> kg</td>
                                                    <td class="current-values"><?php echo number_format($grand_current_kg, 2); ?> kg</td>
                                                    <td>₹<?php echo number_format($grand_total_amount, 2); ?></td>
                                                    <td class="current-values">₹<?php echo number_format($grand_current_amount, 2); ?></td>
                                                </tr>
                                                <?php
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No scrap collection records found</td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery Core Js --> 
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
<?php } ?>