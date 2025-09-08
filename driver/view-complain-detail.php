
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('includes/dbconnection.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (strlen($_SESSION['vamsid'] == 0)) {
    header('location:logout.php');
    exit;
}

if (isset($_POST['submit'])) {
    $eid = $_GET['editid'];
    $comid = $_GET['comid'];
    $status = $_POST['status'];
    $admin_remark = $_POST['admin_remark'];
    $driver_remark = isset($_POST['driver_remark']) ? $_POST['driver_remark'] : null;
    $remark = ($status == 'Completed' && $driver_remark !== null) ? $driver_remark : $admin_remark;

    // First get FullName from joined tables
    $user_sql = "SELECT u.FullName 
                 FROM tbllodgedcomplain lc 
                 JOIN tbluser u ON lc.UserID = u.ID 
                 WHERE lc.ID = :eid";
    $user_query = $dbh->prepare($user_sql);
    $user_query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $user_query->execute();
    $user_row = $user_query->fetch(PDO::FETCH_ASSOC);
    $user_fullname = $user_row ? $user_row['FullName'] : "Unknown";

    if ($status == 'Completed') {
        $paid_amount = $_POST['paid_amount'];
        $payment_datetime = date("Y-m-d H:i:s");
        $payment_mode = $_POST['payment_mode'];
        $item_count = $_POST['item_count'];

        // Update main complaint
        $sql = "UPDATE tbllodgedcomplain 
                SET Status=:status, Remark=:remark, Amount=:paid_amount, 
                    PaymentDateTime=:payment_datetime 
                WHERE ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':paid_amount', $paid_amount, PDO::PARAM_STR);
        $query->bindParam(':payment_datetime', $payment_datetime, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();

        // Save each item to payment_invoice
        for ($i = 1; $i <= $item_count; $i++) {
            $scrap_id = $_POST['scrap_type_'.$i];
            $quantity = $_POST['quantity_'.$i];
            $rate = $_POST['rate_'.$i];
            $item_total = $_POST['item_total_'.$i];
            $item_remark = $_POST['item_remark_'.$i];

            $invoice_sql = "INSERT INTO payment_invoice (
                ComplainID, User_name, Paymentmode, No_of_items, Scrap_name, 
                Fixed_rate, Kg, Total, Remark, PaymentDateTime
            ) VALUES (
                :complainID, :user_name, :payment_mode, :no_of_items, 
                (SELECT scrap_name FROM scrap_price_list WHERE id = :scrap_id),
                :fixed_rate, :kg, :total, :remark, :payment_datetime
            )";
            $invoice_query = $dbh->prepare($invoice_sql);
            $invoice_query->bindParam(':complainID', $eid, PDO::PARAM_INT);
            $invoice_query->bindParam(':user_name', $user_fullname, PDO::PARAM_STR);
            $invoice_query->bindParam(':payment_mode', $payment_mode, PDO::PARAM_STR);
            $invoice_query->bindParam(':no_of_items', $item_count, PDO::PARAM_INT);
            $invoice_query->bindParam(':scrap_id', $scrap_id, PDO::PARAM_INT);
            $invoice_query->bindParam(':fixed_rate', $rate, PDO::PARAM_STR);
            $invoice_query->bindParam(':kg', $quantity, PDO::PARAM_STR);
            $invoice_query->bindParam(':total', $item_total, PDO::PARAM_STR);
            $invoice_query->bindParam(':remark', $item_remark, PDO::PARAM_STR);
            $invoice_query->bindParam(':payment_datetime', $payment_datetime, PDO::PARAM_STR);
            $invoice_query->execute();
        }

        // Tracking history
        $tracking_sql = "INSERT INTO tblcomtracking(ComplainNumber, Remark, Status) 
                         VALUES(:comid, :remark, :status)";
        $tracking_query = $dbh->prepare($tracking_sql);
        $tracking_query->bindParam(':comid', $comid, PDO::PARAM_STR);
        $tracking_query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $tracking_query->bindParam(':status', $status, PDO::PARAM_STR);
        $tracking_query->execute();
    } else {
        // Original non-completed status handling
        $sql = "INSERT INTO tblcomtracking(ComplainNumber, Remark, Status) 
                VALUES(:comid, :remark, :status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':comid', $comid, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();

        $sql = "UPDATE tbllodgedcomplain 
                SET Status=:status, Remark=:remark 
                WHERE ID=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
    }

    // Email sending logic (unchanged)
    $sql = "SELECT u.Email, u.FullName
            FROM tbllodgedcomplain lc 
            JOIN tbluser u ON u.ID = lc.UserID 
            WHERE lc.ID = :eid
            LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':eid', $eid, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['Email']) {
        $email = $user['Email'];
        $name  = $user['FullName'];

        if ($status == "Completed") {
            $body = "Dear {$name},\n\n✨Your scrap collection request has been marked as COMPLETED.\n\nAmount Paid: ₹{$paid_amount}\n\nDriver Remark: {$driver_remark}\n\nThank you for using our service.";
        } elseif ($status == "Rejected") {
            $body = "Dear {$name},\n\n☹️ We're sorry to inform you that your scrap collection request has been REJECTED.\n\nPlease contact support for more information.";
        } elseif ($status == "On the way") {
            $body = "Dear {$name},\n\n⏰ The driver is ON THE WAY to collect your scrap.\n\nPlease be available at your location.";
        } else {
            $body = "Dear {$name},\n\n☑️Your Request status has been updated to: {$status}.\n\nThank you.";
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'removed';
            $mail->Password   = 'removed';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('removed', 'Smart Scrap Management');
            $mail->addAddress($email, $name);

            $mail->isHTML(false);
            $mail->Subject = 'Complaint Status Update';
            $mail->Body    = $body;

            $mail->send();
        } catch (Exception $e) {
            // Error handling
        }
    }
    echo '<script>alert("Remark has been updated and email sent.")</script>';
    echo "<script>window.location.href ='all-complain.php'</script>";
    exit;
}

// Rest of your HTML/PHP code remains the same...
?>

  <!doctype html>
  <html lang="en">
  <head>
    <title>View Lodged Request</title>
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">

       <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
      .item-row {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
      }
      .item-row h5 {
        margin-top: 0;
        color: #333;
      }
    </style>
  </head>
  <body class="theme-indigo">
  <?php include_once('includes/header.php'); ?>
  <div class="main_content" id="main-content">
    <?php include_once('includes/sidebar.php'); ?>
    <div class="page">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="javascript:void(0);">View Lodged Request</a>
      </nav>
      <div class="container-fluid">
        <div class="card">
          <div class="header">
            <h2><strong>View Lodged</strong> Request</h2>
          </div>
          <div class="body table-responsive">
            <?php
            $eid = isset($_GET['editid']) ? $_GET['editid'] : null;
            if (!$eid) {
                echo "<p style='color:red; padding: 20px;'>No Request ID provided.</p>";
                exit;
            }

            $sql = "SELECT lc.*, u.FullName, u.MobileNumber, u.Email 
                    FROM tbllodgedcomplain lc 
                    JOIN tbluser u ON u.ID = lc.UserID 
                    WHERE lc.ID = :eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            $status = $result->Status;
            



                                      $cnt=1;
                                      if($query->rowCount() > 0) {
                                          foreach($results as $row) {               
                                      ?>
            <!-- Original Complaint Details Table -->
            <table class="table table-bordered nowrap">
              <tr><th style="color:orange;">Request Number</th>
                  <td colspan="4" style="color:orange;font-weight:bold;"><?php echo $result->ComplainNumber; ?></td></tr>
              <tr><th>Name</th><td><?php echo $result->FullName; ?></td>
                  <th>Email</th><td><?php echo $result->Email; ?></td></tr>

                  <tr>
                                              <th>Mobile Number</th>
                                              <td><?php echo $result->MobileNumber;?></td>
                                              <th>Address of Scrap</th>
                                              <td><?php echo $result->Address;?></td>
                                          </tr>
                                          <tr>
                                              <th>Area</th>
                                              <td><?php echo $result->Area;?></td>
                                              <th>Locality</th>
                                              <td><?php echo $result->Locality;?></td>
                                          </tr>
                                          <tr>
                                              <th>Landmark</th>
                                              <td><?php echo $result->Landmark;?></td>
                                              <th>Note</th>
                                              <?php if($result->Note==""){ ?>
                                                  <td><?php echo "No Notes"; ?></td>
                                              <?php } else { ?>
                                                  <td><?php echo htmlentities($result->Note);?></td>
                                              <?php } ?>
                                          </tr>
                                          <tr>
                                              <th>UPI-linked mobile No</th>
                                              <td><?php echo $result->Upi;?></td>
                                              <th>Pin Number</th>
                                              <td><?php echo $result->Pin;?></td>
                                          </tr>
                                          <tr>

                                              <th>Type of Scrap</th>
                                              <td><?php echo $result->Scraptype;?></td>
                                              <th>Pick-up date</th>
                                              <td><?php echo $result->Date;?></td>
                                          </tr>
                                          <tr>
                                              <th>Pick-up Timing</th>
                                              <td><?php echo $result->Time;?></td>
                                              <th>Payment Mode</th>
                                              <td colspan="3"><?php echo !empty($result->Paymentmode) ? htmlentities($result->Paymentmode) : "Not provided"; ?></td>
                                          </tr>
                                          <tr>
                                              <th>Image</th>
                                              <td colspan="4">
                                              <a href="../user/images/<?php echo $result->Photo;?>">  
                                              <img src="../user/images/<?php echo $result->Photo;?>" width="200" height="150" value="<?php echo $result->Photo;?>"></td>
                                              </a>  
                                          </tr>

                                    
                                          <tr>
                                              <th>Driver Name</th>
                                              <td>
                                                  <?php 
                                                  if(!empty($result->DriverName)) {
                                                      echo htmlentities($result->DriverName);
                                                  } else {
                                                      echo "Not assigned yet";
                                                  }
                                                  ?>
                                              </td>
                                              <th>Driver Phone</th>
                                              <td>
                                                  <?php 
                                                  if(!empty($result->DriverMobile)) {
                                                      echo htmlentities($result->DriverMobile);
                                                  } else {
                                                      echo "Not assigned yet";
                                                  }
                                                  ?>
                                              </td>
                                          </tr>
                                          <tr>
                                              <th>Assign To</th>
                                              <?php if($result->AssignTo==""){ ?>
                                                  <td><?php echo "Not Updated Yet"; ?></td>
                                              <?php } else { ?>
                                                  <td><?php echo htmlentities($result->AssignTo);?></td>
                                              <?php } ?>  
                                              <th>Request Date</th>
                                              <td><?php echo $result->ComplainDate;?></td>     
                                          </tr>
                                          <tr>
                                              <th>Request Final Status</th>
                                              <td> 
                                                  <?php  
                                                  $status=$result->Status;
                                                  if($result->Status=="Approved")
                                                  {
                                                    echo "Your request has been approved";
                                                  }
                                                
                                                  if($result->Status=="Rejected")
                                                  {
                                                  echo "Your request has been cancelled";
                                                  }
                                                  if($result->Status=="On the way")
                                                  {
                                                  echo "Driver is on the way";
                                                  }
                                                  if($result->Status=="Completed")
                                                  {
                                                  echo "Scrap has been collected";
                                                  }
                                                
                                                  if($result->Status=="")
                                                  {
                                                    echo "Not Response Yet";
                                                  }
                                                  ?>
                                              </td>
                                              <th> Remark</th>
                                              <?php if($result->Status==""){ ?>
                                                  <td colspan="4"><?php echo "Not Updated Yet"; ?></td>
                                              <?php } else { ?>
                                                  <td><?php echo htmlentities($result->Status);?></td>
                                              <?php } ?>  
                                          </tr>
                                          <?php $cnt=$cnt+1;}} ?>

            
            </table>

            <!-- Tracking History -->
            <?php if (!empty($status)) { ?>
              <table class="table table-bordered">
                <tr align="center"><th colspan="4" style="color:blue">Tracking History</th></tr>
                <tr><th>#</th><th>Remark</th><th>Status</th><th>Time</th></tr>
                <?php
                $ret = "SELECT Remark, Status, RemarkDate FROM tblcomtracking WHERE ComplainNumber = :comid ORDER BY RemarkDate DESC";
                $q2 = $dbh->prepare($ret);
                $q2->bindParam(':comid', $result->ComplainNumber, PDO::PARAM_STR);
                $q2->execute();
                $i=1;
                foreach ($q2->fetchAll(PDO::FETCH_OBJ) as $h) { ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlentities($h->Remark); ?></td>
                    <td><?php echo htmlentities($h->Status); ?></td>
                    <td><?php echo htmlentities($h->RemarkDate); ?></td>
                  </tr>
                <?php } ?>
              </table>
            <?php } ?>

           

              <!-- Display Invoice Items -->
              <?php
              $invoice_sql = "SELECT * FROM payment_invoice WHERE ComplainID = :eid";
              $invoice_query = $dbh->prepare($invoice_sql);
              $invoice_query->bindParam(':eid', $eid, PDO::PARAM_INT);
              $invoice_query->execute();
              if ($invoice_query->rowCount() > 0) { ?>
                <h5 style='color:green'>Scrap Items Collected</h5>
                <table class="table table-bordered">
                  <tr>
                    <th>#</th>
                    <th>Scrap Type</th>
                    <th>Rate (₹)</th>
                    <th>Quantity</th>
                    <th>Total (₹)</th>
                    <th>Remark</th>
                  </tr>
                  <?php $i=1; foreach ($invoice_query->fetchAll(PDO::FETCH_OBJ) as $item) { ?>
                    <tr>
                      <td><?php echo $i++; ?></td>
                      <td><?php echo htmlentities($item->Scrap_name); ?></td>
                      <td><?php echo htmlentities($item->Fixed_rate); ?></td> 
                      <td><?php echo htmlentities($item->Kg); ?> kg</td>
                      <td>₹<?php echo htmlentities($item->Total); ?></td>
                      <td><?php echo htmlentities($item->Remark); ?></td>
                    </tr>
                  <?php } ?>
                  <tr>
                    <td colspan="4" class="text-right"><strong>Grand Total:</strong></td>
                    <td colspan="2"><strong>₹<?php echo htmlentities($result->Amount); ?></strong></td>
                  </tr>
                </table>
              <?php } ?>
           
             <!-- Payment Receipt Section -->
            <?php if ($status === 'Completed') { ?>
              <h5 style='color:red'>Payment Receipt</h5>
              <table class="table table-bordered">
                <tr><th>Payment Mode</th><td><?php echo htmlentities($result->Paymentmode); ?></td></tr>
                <tr><th>Total Amount</th><td>₹<?php echo isset($result->Amount) ? htmlentities($result->Amount) : '—'; ?></td></tr>
                 <tr><th>Driver Remark</th>
      <td><?php echo isset($result->Remark) ? htmlentities($result->Remark) : '—'; ?></td></tr>
                <tr><th>Payment Date/Time</th><td><?php echo isset($result->PaymentDateTime) ? htmlentities($result->PaymentDateTime) : '—'; ?></td></tr>
              </table>
 <?php } ?>
            <!-- Take Action Button -->
            <?php if ($status != 'Completed' && $status != 'Rejected') { ?>
              <div class="text-center mt-4">
                <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#takeActionModal">
                  <i class="fa fa-tasks"></i> Take Action
                </button>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- Enhanced Take Action Modal -->
      <div class="modal fade" id="takeActionModal" tabindex="-1" role="dialog" aria-labelledby="takeActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <form method="post" action="">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="takeActionModalLabel">Update Request Status</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                  <label for="statusSelect">Select Status</label>
                  <select class="form-control" name="status" id="statusSelect" required>
                    <option value="On the way">On the way</option>
                    <option value="Completed">Completed</option>
                    <option value="Rejected">Rejected</option>
                  </select>
                </div>

                <!-- Admin Remark Section -->
                <div id="admin-remark-section">
                  <div class="form-group">
                    <label for="admin_remark">Remark</label>
                    <textarea class="form-control" name="admin_remark" id="admin_remark" required></textarea>
                  </div>
                </div>

                <!-- Completed Details Section -->
                <div id="completed-details" style="display:none;">
                  <div class="form-group">
                    <label>Payment Mode</label>
                    <input type="text" class="form-control" name="payment_mode" 
                          value="<?php echo htmlentities($result->Paymentmode); ?>" readonly>
                  </div>

                  <div class="form-group">
                    <label for="item_count">Number of Scrap Items</label>
                    <input type="number" class="form-control" name="item_count" id="item_count" min="1" value="1">
                  </div>

                  <!-- Dynamic Item Rows Container -->
                  <div id="item_rows_container"></div>

                  <div class="form-group">
                    <label for="paid_amount">Total Amount (₹)</label>
                    <input type="text" class="form-control" name="paid_amount" id="paid_amount" readonly>
                  </div>

                  <div class="form-group">
                    <label for="driver_remark">Driver Remark</label>
                    <textarea class="form-control" name="driver_remark" id="driver_remark"></textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" name="submit" class="btn btn-primary">Update Status</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="../assets/vendor/jquery/jquery.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/jquery.dataTables.min.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>
$(document).ready(function() {
    // Toggle sections based on status selection
    $('#statusSelect').change(function() {
        if ($(this).val() === 'Completed') {
            $('#completed-details').show();
            $('#admin-remark-section').hide();
            $('#admin_remark').prop('required', false);
            generateItemRows($('#item_count').val());
        } else {
            $('#completed-details').hide();
            $('#admin-remark-section').show();
            $('#admin_remark').prop('required', true);
        }
    });

    // Generate item rows when number changes
    $('#item_count').change(function() {
        generateItemRows($(this).val());
    });

    // Function to generate item rows
    function generateItemRows(count) {
        $.ajax({
            url: 'fetch_scrap_items.php',
            type: 'GET',
            success: function(scrapItems) {
                let html = '';
                let totalAmount = 0;
                
                // Get current values before regenerating rows
                let currentValues = {};
                $('.item-row').each(function(index) {
                    currentValues[index] = {
                        scrap_type: $(this).find('.scrap-type').val(),
                        quantity: $(this).find('.quantity').val(),
                        rate: $(this).find('.rate').val(),
                        item_total: $(this).find('.item-total').val(),
                        item_remark: $(this).find('input[name^="item_remark"]').val()
                    };
                });
                
                for (let i = 1; i <= count; i++) {
                    // Use saved values if they exist
                    const savedValues = currentValues[i-1] || {};
                    
                    html += `
                    <div class="item-row">
                        <h5>Item ${i}</h5>
                        <div class="form-group">
                            <label>Scrap Type</label>
                            <select name="scrap_type_${i}" class="form-control scrap-type" required>
                                <option value="">Select Scrap Type</option>
                                ${scrapItems.map(item => 
                                    `<option value="${item.id}" data-price="${item.price}" 
                                     ${savedValues.scrap_type == item.id ? 'selected' : ''}>
                                        ${item.scrap_name} (₹${item.price}/kg)
                                    </option>`
                                ).join('')}
                            </select>
                        </div>
                       
                        <div class="form-group">
                            <label>Rate (₹)</label>
                            <input type="text" name="rate_${i}" class="form-control rate" 
                                   value="${savedValues.rate || ''}" >
                        </div>
                         <div class="form-group">
                            <label>Quantity (kg/piece)</label>
                            <input type="text" name="quantity_${i}" class="form-control quantity" 
                                   min="0.1" step="0.1" value="${savedValues.quantity || '1'}" required>
                        </div>
                        <div class="form-group">
                            <label>Total (₹)</label>
                            <input type="text" name="item_total_${i}" class="form-control item-total" 
                                   value="${savedValues.item_total || ''}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Remark</label>
                            <input type="text" name="item_remark_${i}" class="form-control" 
                                   value="${savedValues.item_remark || ''}">
                        </div>
                    </div>`;
                }
                
                $('#item_rows_container').html(html);
                
                // Initialize event listeners
                $('.scrap-type').change(function() {
                    const price = $(this).find(':selected').data('price');
                    $(this).closest('.item-row').find('.rate').val(price);
                    calculateTotals();
                });
                
                $('.quantity').on('input', calculateTotals);
                
                // Calculate totals if we have existing values
                if (Object.keys(currentValues).length > 0) {
                    calculateTotals();
                }
            }
        });
    }
    
    // Calculate totals for all items
    function calculateTotals() {
        let grandTotal = 0;
        
        $('.item-row').each(function() {
            const rate = parseFloat($(this).find('.rate').val()) || 0;
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const total = rate * quantity;
            
            $(this).find('.item-total').val(total.toFixed(2));
            grandTotal += total;
        });
        
        $('#paid_amount').val(grandTotal.toFixed(2));
    }
    
    // Initialize the form based on current status
    if ($('#statusSelect').val() === 'Completed') {
        $('#completed-details').show();
        $('#admin-remark-section').hide();
        $('#admin_remark').prop('required', false);
        generateItemRows($('#item_count').val());
    }
});
</script>

<!-- Jquery Core Js --> 
<script src="../assets/bundles/libscripts.bundle.js"></script> 
  <!-- Jquery Core Js --> 
  <script src="../assets/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 
  <script src="../assets/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 

  <!-- Jquery DataTable Plugin Js --> 
  <script src="../assets/bundles/datatablescripts.bundle.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/buttons.flash.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/buttons.html5.min.js"></script>
  <script src="../assets/vendor/jquery-datatable/buttons/buttons.print.min.js"></script>

  <script src="../assets/js/theme.js"></script><!-- Custom Js --> 
  <script src="../assets/js/pages/tables/jquery-datatable.js"></script>
      <script src="../assets/bundles/libscripts.bundle.js"></script>
<script src="../assets/bundles/vendorscripts.bundle.js"></script>
<script src="../assets/bundles/datatablescripts.bundle.js"></script>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/pages/tables/jquery-datatable.js"></script>

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