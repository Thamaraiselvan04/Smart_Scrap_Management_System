<?php
session_start();
error_reporting(0); // For production, consider enabling error reporting for debugging.
include('includes/dbconnection.php'); // Assuming this path is correct for your admin side

// Check if admin is logged in using the provided session variable 'vamsaid'
if (!isset($_SESSION['vamsaid']) || empty($_SESSION['vamsaid'])) {
    header('location:logout.php'); // Redirect to the provided admin logout page
    exit();
} else {
    // Fetch all daily pre-check records
    $sql_incidents = "SELECT * FROM tblincident_reports ORDER BY ReportedAt DESC"; // Order by most recent first
    $query_incidents = $dbh->prepare($sql_incidents);
    $query_incidents->execute();
    $incidentReports = $query_incidents->fetchAll(PDO::FETCH_OBJ);
?>
<!doctype html>
<html lang="en">
<head>
    <title>Driver Incident Reports</title>
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
            padding: 8px; /* Adjust padding for better look */
        }
        .status-badge {
            display: inline-block;
            padding: .3em .5em;
            font-size: 85%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .status-new { background-color: #007bff; color: white; } /* Blue */
        .status-acknowledged { background-color: #ffc107; color: #343a40; } /* Yellow */
        .status-investigation { background-color: #6c757d; color: white; } /* Gray */
        .status-resolved { background-color: #28a745; color: white; } /* Green */
        .table thead th {
            background-color: #f8f9fa; /* Light background for headers */
            border-bottom: 2px solid #dee2e6;
        }
        .card .body {
            padding: 1.25rem; /* Standard Bootstrap card body padding */
        }
        /* Custom styling for readability of details/comments */
        .details-cell {
            white-space: pre-wrap; /* Preserve whitespace and wrap text */
            word-break: break-word; /* Break long words */
            max-width: 300px; /* Limit width of details column */
        }
        /* Styling for image links/thumbnails */
        .photo-thumbnail {
            max-width: 80px; /* Smaller thumbnail size */
            height: auto;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin: 2px;
            vertical-align: middle;
        }
        .photo-link {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="theme-indigo">
    <?php include_once('includes/header.php');?> <!-- Admin header include -->

    <div class="main_content" id="main-content">
        <?php include_once('includes/sidebar.php');?> <!-- Admin sidebar include -->

        <div class="page">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="javascript:void(0);">Driver Incident Reports</a>
            </nav>
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-lg-12"> <!-- Use col-lg-12 for wider table view on large screens -->
                        <div class="card">
                            <div class="header">
                                <h2>All Driver Incident Reports 🚨</h2>
                            </div>
                            <div class="body">
                                <?php if (empty($incidentReports)) { ?>
                                    <p class="text-center text-muted">No incident reports available yet.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Reported By (ID)</th>
                                                    <th>Driver Name</th>
                                                    <th>Mobile Number</th>
                                                    <th>Trip ID</th>
                                                    <th>Incident Type</th>
                                                    <th>Incident Date/Time</th>
                                                    <th>Location</th>
                                                    <th>Details</th>
                                                    <th>Photos</th>
                                                    <th>Admin Status</th>
                                                    <th>Reported At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cnt = 1;
                                                foreach ($incidentReports as $report) {
                                                    // Decode PhotoPaths JSON string
                                                    $photoPaths = json_decode($report->PhotoPaths, true);
                                                    if (!is_array($photoPaths)) {
                                                        $photoPaths = []; // Ensure it's an array even if decoding fails
                                                    }
                                                ?>
                                                    <tr>
                                                        <td><?php echo $cnt; ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverLoginID); ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverName); ?></td>
                                                        <td><?php echo htmlspecialchars($report->DriverMobileNumber); ?></td>
                                                        <td><?php echo ($report->RelatedComplainID) ? htmlspecialchars($report->RelatedComplainID) : 'N/A'; ?></td>
                                                        <td><?php echo htmlspecialchars($report->IncidentType); ?></td>
                                                        <td><?php echo date("Y-m-d H:i", strtotime($report->IncidentDateTime)); ?></td>
                                                        <td><?php echo htmlspecialchars($report->IncidentLocationDescription); ?></td>
                                                        <td class="details-cell"><?php echo nl2br(htmlspecialchars($report->IncidentDetails)); ?></td>
                                                        <td>
                                                            <?php if (!empty($photoPaths)) {
                                                                foreach ($photoPaths as $path) {
                                                                    // Assuming admin is in 'admin/' folder and uploads are in 'uploads/'
                                                                    // If 'uploads' is parallel to 'admin' (i.e. both are in root), use '../uploads/'
                                                                    // If 'uploads' is within root and admin is also within root but different folder, use '../uploads/'
                                                                    // Adjust this path as per your actual directory structure
                                                                    $display_path = '../' . $path; // Adjust based on your actual path relative to admin_incident_reports.php
                                                            ?>
                                                                <a href="<?php echo htmlspecialchars($display_path); ?>" target="_blank" class="photo-link">
                                                                    <img src="<?php echo htmlspecialchars($display_path); ?>" alt="Incident Photo" class="photo-thumbnail">
                                                                </a>
                                                            <?php
                                                                }
                                                            } else {
                                                                echo 'No Photos';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                $status_class = '';
                                                                switch ($report->AdminStatus) {
                                                                    case 'New': $status_class = 'status-new'; break;
                                                                    case 'Acknowledged': $status_class = 'status-acknowledged'; break;
                                                                    case 'Under Investigation': $status_class = 'status-investigation'; break;
                                                                    case 'Resolved': $status_class = 'status-resolved'; break;
                                                                    default: $status_class = ''; break;
                                                                }
                                                            ?>
                                                            <span class="status-badge <?php echo $status_class; ?>">
                                                                <?php echo htmlspecialchars($report->AdminStatus); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date("Y-m-d H:i", strtotime($report->ReportedAt)); ?></td>
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
