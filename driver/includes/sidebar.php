<!-- ===== Sidebar (Driver) ===== -->
<aside class="ss-sidebar" id="ssSidebar">
    <a href="dashboard.php" class="ss-brand">
        <img src="../img1/trash.png" alt="SSMS logo">
        <span>SSMS<small>Smart Scrap System</small></span>
    </a>

    <div class="ss-user">
        <img src="../assets/images/user.png" alt="Driver">
        <div class="ss-user-text">
                    <?php
                     $did=$_SESSION['vamsdid'];
                    $sql="SELECT Name,Email from  tbldriver where DriverID=:did";
$query = $dbh -> prepare($sql);
$query->bindParam(':did',$did,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

foreach($results as $row)
{
$email=$row->Email;
$fname=$row->Name;
}   ?>
            <h5><?php  echo $fname ;?></h5>
            <small><?php  echo $email ;?></small>
        </div>
    </div>

    <nav class="ss-nav">
        <?php
        // Menu highlight only: name of the page that is open now
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <span class="ss-label">Main</span>
        <a href="dashboard.php" class="ss-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="ti-home"></i><span>Dashboard</span></a>

        <span class="ss-label">Assigned Requests</span>
        <a href="new-complain-request.php" class="ss-link <?php echo ($current_page == 'new-complain-request.php') ? 'active' : ''; ?>"><i class="ti-bell"></i><span>New Request</span></a>
        <a href="ontheway-complain.php" class="ss-link <?php echo ($current_page == 'ontheway-complain.php') ? 'active' : ''; ?>"><i class="ti-truck"></i><span>On the Way</span></a>
        <a href="completed-complain.php" class="ss-link <?php echo ($current_page == 'completed-complain.php') ? 'active' : ''; ?>"><i class="ti-check-box"></i><span>Completed</span></a>
        <a href="all-complain.php" class="ss-link <?php echo ($current_page == 'all-complain.php') ? 'active' : ''; ?>"><i class="ti-layout-list-thumb"></i><span>All Requests</span></a>

        <span class="ss-label">Search &amp; Reports</span>
        <a href="search-complain.php" class="ss-link <?php echo ($current_page == 'search-complain.php') ? 'active' : ''; ?>"><i class="ti-search"></i><span>Search Request</span></a>
        <a href="lodged-complain-report.php" class="ss-link <?php echo ($current_page == 'lodged-complain-report.php' || $current_page == 'lodegedcomplain-reprtsdetails.php') ? 'active' : ''; ?>"><i class="ti-bar-chart"></i><span>Request Report</span></a>

        <span class="ss-label">Vehicle</span>
        <a href="daily_pre_check.php" class="ss-link <?php echo ($current_page == 'daily_pre_check.php') ? 'active' : ''; ?>"><i class="ti-check"></i><span>Vehicle Pre-Check</span></a>
        <a href="report_incident.php" class="ss-link <?php echo ($current_page == 'report_incident.php') ? 'active' : ''; ?>"><i class="ti-alert"></i><span>Incident Report</span></a>

        <span class="ss-label">Account</span>
        <a href="profile.php" class="ss-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><i class="ti-user"></i><span>My Profile</span></a>
        <a href="change-password.php" class="ss-link <?php echo ($current_page == 'change-password.php') ? 'active' : ''; ?>"><i class="ti-key"></i><span>Change Password</span></a>
        <a href="logout.php" class="ss-link ss-logout"><i class="ti-power-off"></i><span>Sign out</span></a>
    </nav>
</aside>
<div class="ss-overlay" id="ssOverlay"></div>
<script src="../assets/js/ssms-panel.js"></script>
