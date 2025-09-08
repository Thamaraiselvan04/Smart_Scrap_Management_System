<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/dbconnection.php');

if (strlen($_SESSION['vamsaid']) == 0) {
    header('location:logout.php');
    exit;
}

// Handle Add
if (isset($_POST['add'])) {
    $center_name = $_POST['center_name'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $query = "INSERT INTO recycle_tb (Center_name, Owner_name, Email, Phone_no, Address) 
              VALUES (:center_name, :owner_name, :email, :phone, :address)";
    $stmt = $dbh->prepare($query);
    $stmt->execute([
        ':center_name' => $center_name,
        ':owner_name' => $owner_name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address
    ]);
    $_SESSION['success'] = "Recycling center added successfully!";
    header("Location: recycle_center.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $dbh->prepare("DELETE FROM recycle_tb WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $_SESSION['success'] = "Recycling center deleted successfully!";
    header("Location: recycle_center.php");
    exit;
}

// Handle Edit Fetch
$editData = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $dbh->prepare("SELECT * FROM recycle_tb WHERE id = :id");
    $stmt->bindParam(':id', $edit_id);
    $stmt->execute();
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $center_name = $_POST['center_name'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $query = "UPDATE recycle_tb SET 
                Center_name = :center_name, 
                Owner_name = :owner_name, 
                Email = :email, 
                Phone_no = :phone, 
                Address = :address 
              WHERE id = :id";
    $stmt = $dbh->prepare($query);
    $stmt->execute([
        ':center_name' => $center_name,
        ':owner_name' => $owner_name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':id' => $id
    ]);
    $_SESSION['success'] = "Recycling center updated successfully!";
    header("Location: recycle_center.php");
    exit;
}

// Handle Sell Operation
if (isset($_POST['sell_submit'])) {
    $selected_items = $_POST['selected_items'] ?? [];
    $selling_prices = $_POST['selling_price'] ?? [];
    $center_id = $_POST['center_id'];
    
    if (empty($selected_items)) {
        $_SESSION['error'] = "Please select at least one scrap item to sell.";
        header("Location: recycle_center.php");
        exit;
    }
    
    // Get center details
    $stmt = $dbh->prepare("SELECT * FROM recycle_tb WHERE id = :id");
    $stmt->bindParam(':id', $center_id);
    $stmt->execute();
    $center = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$center) {
        $_SESSION['error'] = "Recycling center not found!";
        header("Location: recycle_center.php");
        exit;
    }
    
    // Start transaction
    $dbh->beginTransaction();
    
    try {
        foreach ($selected_items as $scrap_id) {
            if (!isset($selling_prices[$scrap_id])) continue;
            
            // Get scrap details
            $stmt = $dbh->prepare("SELECT * FROM scrap_collection_history WHERE id = :id FOR UPDATE");
            $stmt->bindParam(':id', $scrap_id);
            $stmt->execute();
            $scrap = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$scrap) continue;
            
            $selling_price = (float)$selling_prices[$scrap_id];
            $total_value = (float)$scrap['current_total'];
            
            // Calculate profit or loss
            $profit = ($selling_price > $total_value) ? ($selling_price - $total_value) : 0;
            $loss = ($selling_price < $total_value) ? ($total_value - $selling_price) : 0;
            
            // Insert into sell_report
            $stmt = $dbh->prepare("INSERT INTO sell_report (
                Center_name, Owner_name, Phone, Email, Address,
                Report_date, Scrap_name, Total_kg, Fixed_amount,
                Current_total_price, Profit, loss, created_at
            ) VALUES (
                :center_name, :owner_name, :phone, :email, :address,
                CURDATE(), :scrap_name, :total_kg, :fixed_rate,
                :selling_price, :profit, :loss, NOW()
            )");
            
            $stmt->execute([
                ':center_name' => $center['Center_name'],
                ':owner_name' => $center['Owner_name'],
                ':phone' => $center['Phone_no'],
                ':email' => $center['Email'],
                ':address' => $center['Address'],
                ':scrap_name' => $scrap['scrap_name'],
                ':total_kg' => $scrap['current_kg'],
                ':fixed_rate' => $scrap['fixed_rate'],
                ':selling_price' => $selling_price,
                ':profit' => $profit,
                ':loss' => $loss
            ]);
            
            // Update scrap_collection_history to mark as sold
            $stmt = $dbh->prepare("UPDATE scrap_collection_history 
                                  SET current_kg = 0,
                                      current_total = 0
                                  WHERE id = :id");
            $stmt->execute([':id' => $scrap_id]);
        }
        
        // Commit transaction
        $dbh->commit();
        
        $_SESSION['success'] = "Sale recorded successfully!";
        header("Location: recycle_center.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $dbh->rollBack();
        $_SESSION['error'] = "Error recording sale: " . $e->getMessage();
        header("Location: recycle_center.php");
        exit;
    }
}

// Fetch all recycling centers
$stmt = $dbh->prepare("SELECT * FROM recycle_tb ORDER BY created_at DESC");
$stmt->execute();
$centers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available scrap items
$stmt = $dbh->prepare("SELECT * FROM scrap_collection_history WHERE current_kg > 0");
$stmt->execute();
$scrap_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <title>Recycling Center Management</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .card-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .form-header {
            color: #2c3e50;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .form-group label {
            font-weight: 500;
            color: #34495e;
        }
        .btn-custom {
            background: #3498db;
            color: white;
            border-radius: 5px;
            padding: 8px 20px;
            border: none;
            transition: all 0.3s;
        }
        .btn-custom:hover {
            background: #2980b9;
        }
        .info-card {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .action-btns .btn {
            padding: 5px 10px;
            font-size: 13px;
            margin-right: 5px;
        }
        .sell-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .sell-modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .scrap-item-row td {
            padding: 10px;
            vertical-align: middle;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .profit-cell {
            color: green;
            font-weight: bold;
        }
        .loss-cell {
            color: red;
            font-weight: bold;
        }
        .selling-price-input {
            width: 100px;
        }
        .close-modal {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            float: right;
            cursor: pointer;
        }
        .close-modal:hover {
            color: black;
        }
        .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    border: 1px solid #dc3545;
}

.btn-success {
    background-color: #28a745;
    color: white;
    border: 1px solid #28a745;
}

.btn:hover {
    opacity: 0.9;
}
    /* Add these styles to your existing CSS */
    .sell-modal-content {
        background: white;
        margin: 2% auto;
        padding: 20px;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .modal-body-content {
        overflow-y: auto;
        max-height: calc(90vh - 200px); /* Adjust based on your header/footer height */
        padding-right: 10px; /* Prevent content from touching scrollbar */
    }
    
    /* Optional: style the scrollbar */
    .modal-body-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-body-content::-webkit-scrollbar-thumb {
        background: #ccc;
     
    }
    .table-responsive {
    position: relative;
}

.table thead {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php'); ?>

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php'); ?>

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Recycling Center Management</a>
            </nav>
            <div class="container-fluid">            
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                             
                        <!-- Form Card -->
                        <div class="card">
                            <div class="header">
                                <h2><?= $editData ? 'Edit Recycling Center' : 'Add New Recycling Center' ?></h2>
                            </div>
                            <div class="body">
                                <div class="card-form">
                                    <form method="POST" action="">
                                        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Center Name</label>
                                                    <input type="text" name="center_name" class="form-control" 
                                                           value="<?= $editData['Center_name'] ?? '' ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Owner Name</label>
                                                    <input type="text" name="owner_name" class="form-control" 
                                                           value="<?= $editData['Owner_name'] ?? '' ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control" 
                                                           value="<?= $editData['Email'] ?? '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone Number</label>
                                                    <input type="text" name="phone" class="form-control" 
                                                           value="<?= $editData['Phone_no'] ?? '' ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" class="form-control" rows="3" required><?= $editData['Address'] ?? '' ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <?php if ($editData): ?>
                                                <button type="submit" name="update" class="btn btn-custom">Update</button>
                                                <a href="recycle_center.php" class="btn btn-secondary">Cancel</a>
                                            <?php else: ?>
                                                <button type="submit" name="add" class="btn btn-custom">Add Center</button>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Centers List Card -->
                        <div class="card">
                            <div class="header">
                                <h2>Recycling Centers List</h2>
                            </div>
                            <div class="body">
                                <div class="info-card">
                                    <p>Below is the table showing all registered recycling centers</p>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Center Name</th>
                                                <th>Owner</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($centers as $index => $center): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($center['Center_name']) ?></td>
                                                <td><?= htmlspecialchars($center['Owner_name']) ?></td>
                                                <td><?= htmlspecialchars($center['Phone_no']) ?></td>
                                                <td><?= htmlspecialchars($center['Email']) ?></td>
                                                <td><?= htmlspecialchars($center['Address']) ?></td>
                                                <td class="action-btns">
                                                    <a href="?edit=<?= $center['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a href="?delete=<?= $center['id'] ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this center?')">Delete</a>
                                                    <button class="btn btn-sm btn-success sell-btn" 
                                                            data-center-id="<?= $center['id'] ?>"
                                                            data-center-name="<?= htmlspecialchars($center['Center_name']) ?>"
                                                            data-owner-name="<?= htmlspecialchars($center['Owner_name']) ?>"
                                                            data-phone="<?= htmlspecialchars($center['Phone_no']) ?>"
                                                            data-email="<?= htmlspecialchars($center['Email']) ?>"
                                                            data-address="<?= htmlspecialchars($center['Address']) ?>">
                                                        Sell Scrap
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sell Modal -->
      <div id="sellModal" class="sell-modal">
    <div class="sell-modal-content">
        <div class="header">
            <h2>Sell Scrap Items - <span id="modal-center-name"></span></h2>
            <span class="close-modal">&times;</span>
        </div>
        
        <div class="modal-body-content"> <!-- New scrollable container -->
            <form method="POST" action="recycle_center.php" id="sellForm">
                <input type="hidden" name="center_id" id="sell_center_id">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Owner:</strong> <span id="modal-owner-name"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Phone:</strong> <span id="modal-phone"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                       
                    <!-- Your existing table content -->
                      <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Scrap Name</th>
                                    <th>Available KG/Piece</th>
                                    <th>Rate/KG/Piece</th>
                                    <th>Total Value</th>
                                    <th>Selling Price</th>
                                    <th>Profit/Loss</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scrap_items as $item): ?>
                                <tr class="scrap-item-row">
                                    <td><input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" class="scrap-checkbox"></td>
                                    <td><?= $item['scrap_name'] ?></td>
                                    <td><?= $item['current_kg'] ?></td>
                                    <td>₹<?= $item['fixed_rate'] ?></td>
                                    <td>₹<?= $item['current_total'] ?></td>
                                    <td><input type="number" name="selling_price[<?= $item['id'] ?>]" class="form-control selling-price-input" step="0.01" min="0" disabled></td>
                                    <td class="profit-loss-cell" id="profit-loss-<?= $item['id'] ?>">₹0</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($scrap_items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No scrap items available for sale</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                       
                    </table>
                </div>
          
        </div>
                                    
 <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-secondary close-modal">Cancel</button> -->
                         <button type="button" class="btn btn-danger" id="cancelButton">
        <i class="fa fa-times"></i> Cancel
    </button>
                        <button type="submit" name="sell_submit" class="btn btn-primary" <?= empty($scrap_items) ? 'disabled' : '' ?>>Confirm Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>





    
    <!-- JavaScript files -->
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
    
    <script>
    $(document).ready(function() {
        // Initialize modal functionality
        const modal = $('#sellModal');
        $('.close-modal').on('click', function() {
            modal.hide();
        });
        
        $('.sell-btn').on('click', function() {
            $('#sell_center_id').val($(this).data('center-id'));
            $('#modal-center-name').text($(this).data('center-name'));
            $('#modal-owner-name').text($(this).data('owner-name'));
            $('#modal-phone').text($(this).data('phone'));
            modal.show();
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is(modal)) {
                modal.hide();
            }
        });
        
        // Handle checkbox changes
        $('.scrap-checkbox').on('change', function() {
            const input = $(this).closest('tr').find('.selling-price-input');
            input.prop('disabled', !this.checked);
            if (!this.checked) {
                input.val('');
                $('#profit-loss-' + this.value).text('₹0').removeClass('profit-cell loss-cell');
            } else {
                // Auto-fill with total value when checked
                const totalValue = parseFloat($(this).closest('tr').find('td:eq(4)').text().replace('₹', ''));
                input.val(totalValue.toFixed(2));
                $('#profit-loss-' + this.value).text('₹0').removeClass('profit-cell loss-cell');
            }
        });
        
        // Handle selling price input
        $('.selling-price-input').on('input', function() {
            const row = $(this).closest('tr');
            const checkbox = row.find('.scrap-checkbox');
            const scrapId = checkbox.val();
            const totalValue = parseFloat(row.find('td:eq(4)').text().replace('₹', ''));
            const sellingPrice = parseFloat($(this).val()) || 0;
            
            const profitLossCell = $('#profit-loss-' + scrapId);
            
            if (sellingPrice > totalValue) {
                const profit = sellingPrice - totalValue;
                profitLossCell.text('₹' + profit.toFixed(2) + ' (Profit)').removeClass('loss-cell').addClass('profit-cell');
            } else if (sellingPrice < totalValue) {
                const loss = totalValue - sellingPrice;
                profitLossCell.text('₹' + loss.toFixed(2) + ' (Loss)').removeClass('profit-cell').addClass('loss-cell');
            } else {
                profitLossCell.text('₹0').removeClass('profit-cell loss-cell');
            }
        });
        
        // Form submission handler
        $('#sellForm').on('submit', function(e) {
            // No need to prevent default here since we want the form to submit normally
            // e.preventDefault();
            
            // Validate at least one item is selected
            if ($('.scrap-checkbox:checked').length === 0) {
                alert('Please select at least one scrap item to sell.');
                return false;
            }
            
            // Validate all selected items have selling prices
            let valid = true;
            $('.scrap-checkbox:checked').each(function() {
                const priceInput = $(this).closest('tr').find('.selling-price-input');
                if (!priceInput.val() || parseFloat(priceInput.val()) <= 0) {
                    valid = false;
                    return false; // break loop
                }
            });
            
            if (!valid) {
                alert('Please enter valid selling prices for all selected items.');
                return false;
            }
            
            // Allow form to submit normally
            return true;
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
    // Get modal and buttons
    const modal = document.getElementById('sellModal');
    const cancelBtn = document.getElementById('cancelButton');
    const closeBtn = document.querySelector('.close-modal');
    
    // Close modal function
    function closeModal() {
        modal.style.display = 'none';
    }
    
    // Event listeners
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
});
    </script>
</body>
</html>