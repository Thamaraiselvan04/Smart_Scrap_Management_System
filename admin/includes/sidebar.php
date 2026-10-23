<!-- ===== Sidebar (Admin) ===== -->
<aside class="ss-sidebar" id="ssSidebar">
    <a href="dashboard.php" class="ss-brand">
        <img src="../img1/trash.png" alt="SSMS logo">
        <span>SSMS<small>Smart Scrap System</small></span>
    </a>

    <div class="ss-user">
        <img src="../assets/images/user.png" alt="Admin">
        <div class="ss-user-text">
                <?php
                $aid = $_SESSION['vamsaid'];
                $sql = "SELECT AdminName, Email FROM tbladmin WHERE ID = :aid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':aid', $aid, PDO::PARAM_STR);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                if ($query->rowCount() > 0) {
                    foreach ($results as $row) {
                ?>
            <h5><?php echo htmlspecialchars($row->AdminName); ?></h5>
            <small><?php echo htmlspecialchars($row->Email); ?></small>
                <?php
                    }
                }
                ?>
        </div>
    </div>

    <nav class="ss-nav">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
        <span class="ss-label">Main</span>
        <a href="dashboard.php" class="ss-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="ti-home"></i><span>Dashboard</span></a>

        <span class="ss-label">Manage</span>
        <!-- Drivers -->
        <details class="ss-group <?php echo in_array($current_page, ['add-driver.php', 'manage-driver.php', 'edit-driver-detail.php']) ? 'has-active' : ''; ?>" <?php echo in_array($current_page, ['add-driver.php', 'manage-driver.php', 'edit-driver-detail.php']) ? 'open' : ''; ?>>
            <summary><i class="ti-id-badge"></i><span>Drivers</span><i class="ti-angle-right ss-chev"></i></summary>
            <div class="ss-sub">
                <a href="add-driver.php" class="<?php echo ($current_page == 'add-driver.php') ? 'active' : ''; ?>">Add Driver</a>
                <a href="manage-driver.php" class="<?php echo ($current_page == 'manage-driver.php' || $current_page == 'edit-driver-detail.php') ? 'active' : ''; ?>">Manage Drivers</a>
            </div>
        </details>

        <!-- Pickup requests -->
        <details class="ss-group <?php echo in_array($current_page, ['all-complain.php', 'new-complain.php', 'assign-complain.php', 'rejected-complain.php']) ? 'has-active' : ''; ?>" <?php echo in_array($current_page, ['all-complain.php', 'new-complain.php', 'assign-complain.php', 'rejected-complain.php']) ? 'open' : ''; ?>>
            <summary><i class="ti-clipboard"></i><span>Pickup Requests</span><i class="ti-angle-right ss-chev"></i></summary>
            <div class="ss-sub">
                <a href="all-complain.php" class="<?php echo ($current_page == 'all-complain.php') ? 'active' : ''; ?>">All Requests</a>
                <a href="new-complain.php" class="<?php echo ($current_page == 'new-complain.php') ? 'active' : ''; ?>">New Requests</a>
                <a href="assign-complain.php" class="<?php echo ($current_page == 'assign-complain.php') ? 'active' : ''; ?>">Assigned Requests</a>
                <a href="rejected-complain.php" class="<?php echo ($current_page == 'rejected-complain.php') ? 'active' : ''; ?>">Rejected Requests</a>
            </div>
        </details>

        <!-- Driver work status -->
        <details class="ss-group <?php echo in_array($current_page, ['ontheway-complain.php', 'completed-complain.php', 'driver_pre_checks_report.php', 'admin_incident_reports.php']) ? 'has-active' : ''; ?>" <?php echo in_array($current_page, ['ontheway-complain.php', 'completed-complain.php', 'driver_pre_checks_report.php', 'admin_incident_reports.php']) ? 'open' : ''; ?>>
            <summary><i class="ti-truck"></i><span>Driver Work Status</span><i class="ti-angle-right ss-chev"></i></summary>
            <div class="ss-sub">
                <a href="ontheway-complain.php" class="<?php echo ($current_page == 'ontheway-complain.php') ? 'active' : ''; ?>">On The Way</a>
                <a href="completed-complain.php" class="<?php echo ($current_page == 'completed-complain.php') ? 'active' : ''; ?>">Work Completed</a>
                <a href="driver_pre_checks_report.php" class="<?php echo ($current_page == 'driver_pre_checks_report.php') ? 'active' : ''; ?>">Driver Pre-Checks</a>
                <a href="admin_incident_reports.php" class="<?php echo ($current_page == 'admin_incident_reports.php') ? 'active' : ''; ?>">Incident Reports</a>
            </div>
        </details>

        <a href="search-complain.php" class="ss-link <?php echo ($current_page == 'search-complain.php') ? 'active' : ''; ?>"><i class="ti-search"></i><span>Search Request</span></a>

        <span class="ss-label">Reports</span>
        <details class="ss-group <?php echo in_array($current_page, ['between-dates-complain-reports.php', 'between-date-reprtscomplaindetails.php', 'driverwise-complain-report.php', 'driverwise-comlodged-reportsdetails.php']) ? 'has-active' : ''; ?>" <?php echo in_array($current_page, ['between-dates-complain-reports.php', 'between-date-reprtscomplaindetails.php', 'driverwise-complain-report.php', 'driverwise-comlodged-reportsdetails.php']) ? 'open' : ''; ?>>
            <summary><i class="ti-bar-chart"></i><span>Reports</span><i class="ti-angle-right ss-chev"></i></summary>
            <div class="ss-sub">
                <a href="between-dates-complain-reports.php" class="<?php echo ($current_page == 'between-dates-complain-reports.php' || $current_page == 'between-date-reprtscomplaindetails.php') ? 'active' : ''; ?>">Date Range Report</a>
                <a href="driverwise-complain-report.php" class="<?php echo ($current_page == 'driverwise-complain-report.php' || $current_page == 'driverwise-comlodged-reportsdetails.php') ? 'active' : ''; ?>">Driver Performance</a>
                <a href="completed-complain.php">Scrap Collection</a>
            </div>
        </details>

        <span class="ss-label">Scrap &amp; Sales</span>
        <a href="admin_price_list.php" class="ss-link <?php echo ($current_page == 'admin_price_list.php') ? 'active' : ''; ?>"><i class="ti-money"></i><span>Scrap Prices</span></a>
        <a href="scrap_collection_list.php" class="ss-link <?php echo ($current_page == 'scrap_collection_list.php') ? 'active' : ''; ?>"><i class="ti-package"></i><span>Collection History</span></a>
        <a href="recycle_center.php" class="ss-link <?php echo ($current_page == 'recycle_center.php') ? 'active' : ''; ?>"><i class="ti-reload"></i><span>Recycling Center</span></a>
        <a href="sell_profit_report.php" class="ss-link <?php echo ($current_page == 'sell_profit_report.php' || $current_page == 'profit_info.php') ? 'active' : ''; ?>"><i class="ti-stats-up"></i><span>Sales Analytics</span></a>

        <span class="ss-label">Users &amp; Website</span>
        <a href="reg-users.php" class="ss-link <?php echo ($current_page == 'reg-users.php' || $current_page == 'user-lodged-complain.php') ? 'active' : ''; ?>"><i class="ti-user"></i><span>Registered Users</span></a>
        <details class="ss-group <?php echo in_array($current_page, ['aboutus.php', 'contactus.php']) ? 'has-active' : ''; ?>" <?php echo in_array($current_page, ['aboutus.php', 'contactus.php']) ? 'open' : ''; ?>>
            <summary><i class="ti-files"></i><span>Website Pages</span><i class="ti-angle-right ss-chev"></i></summary>
            <div class="ss-sub">
                <a href="aboutus.php" class="<?php echo ($current_page == 'aboutus.php') ? 'active' : ''; ?>">About Us</a>
                <a href="contactus.php" class="<?php echo ($current_page == 'contactus.php') ? 'active' : ''; ?>">Contact Us</a>
            </div>
        </details>

        <span class="ss-label">Account</span>
        <a href="profile.php" class="ss-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><i class="ti-settings"></i><span>My Profile</span></a>
        <a href="change-password.php" class="ss-link <?php echo ($current_page == 'change-password.php') ? 'active' : ''; ?>"><i class="ti-key"></i><span>Change Password</span></a>
        <a href="logout.php" class="ss-link ss-logout"><i class="ti-power-off"></i><span>Sign out</span></a>
    </nav>
</aside>
<div class="ss-overlay" id="ssOverlay"></div>
<script src="../assets/js/ssms-panel.js"></script>
