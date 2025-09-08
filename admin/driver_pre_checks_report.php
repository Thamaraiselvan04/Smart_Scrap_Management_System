<?php
session_start();
error_reporting(0); // For production, consider enabling error reporting for debugging.
include('includes/dbconnection.php'); // Assuming this path is correct for your admin side

// Check if admin is logged in using the provided session variable 'vamsaid'
if (!isset($_SESSION['vamsaid']) || empty($_SESSION['vamsaid'])) { // CORRECTED SESSION CHECK
    header('location:logout.php'); // Redirect to the provided admin logout page
    exit();
} else {
    // Fetch all daily pre-check records
    $sql_prechecks = "SELECT * FROM tbldailyprecheck ORDER BY CheckDate DESC"; // Order by most recent first
    $query_prechecks = $dbh->prepare($sql_prechecks);
    $query_prechecks->execute();
    $preCheckReports = $query_prechecks->fetchAll(PDO::FETCH_OBJ);
?>
<!doctype html>
<html lang="en">
<head>
    <title>Daily Driver Pre-Check Reports</title>
    <!-- Existing CSS includes from your admin template, adjusted paths -->
    <link rel="stylesheet" href="../assets/vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="../assets/vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/main.css" type="text/css">
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .status-icon {
            font-size: 1.2em;
        }
        .status-ok {
            color: #28a745; /* Green */
        }
        .status-issue {
            color: #dc3545; /* Red */
        }
        .table thead th {
            background-color: #f8f9fa; /* Light background for headers */
            border-bottom: 2px solid #dee2e6;
        }
        .card .body {
            padding: 1.25rem; /* Standard Bootstrap card body padding */
        }
        /* Custom styling for readability of comments */
        .comments-cell {
            white-space: pre-wrap; /* Preserve whitespace and wrap text */
            word-break: break-word; /* Break long words */
            max-width: 250px; /* Limit width of comments column */
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?> <!-- Admin header include, adjusted to your path -->

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?> <!-- Admin sidebar include, adjusted to your path -->

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Daily Driver Pre-Check Reports</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="header">
                                <h2>All Daily Driver Pre-Checks 📋 Drivers</h2>
                            </div>
                            <div class="body">
                                <?php if (empty($preCheckReports)) { ?>
                                    <p class="text-center text-muted">No daily pre-check reports available yet.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Driver ID</th>
                                                    <th>Driver Name</th>
                                                    <th>Mobile Number</th>
                                                    <th>Check Date</th>
                                                    <th>Status</th>
                                                    <th>Tires</th>
                                                    <th>Lights</th>
                                                    <th>Brakes</th>
                                                    <th>Fluids</th>
                                                    <th>Windshield</th>
                                                    <th>Horn</th>
                                                    <th>Wipers</th>
                                                    <th>Safety Gear</th>
                                                    <th>Comments</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                foreach ($preCheckReports as $report) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverLoginID); ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverName); ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverMobileNumber); ?></td>
                                                        <td><?php echo date("Y-m-d H:i", strtotime($report->CheckDate)); ?></td>
                                                        <td>
                                                            <?php if ($report->IsAllOk == 1) { ?>
                                                                <span class="status-ok">✅ OK</span>
                                                            <?php } else { ?>
                                                                <span class="status-issue">❌ Issue</span>
                                                            <?php } ?>
                                                        </td>
                                                        <td><?php echo ($report->TiresChecked == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->LightsWorking == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->BrakesFunctioning == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->FluidLevelsChecked == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->WindshieldMirrorsChecked == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->HornWorking == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->WipersFunctioning == 1) ? '✅' : '❌'; ?></td>
                                                        <td><?php echo ($report->SafetyGearPresent == 1) ? '✅' : '❌'; ?></td>
                                                        <td class="comments-cell"><?php echo nl2br(htmlspecialchars($report->IssuesComments)); ?></td>
                                                    </tr>
                                                <?php
                                                    $cnt++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Javascript files from your template, adjusted paths -->
    <script src="../assets/bundles/libscripts.bundle.js"></script>
    <script src="../assets/bundles/vendorscripts.bundle.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
<?php } ?>
