<div class="left_sidebar">
    <nav class="sidebar">
        <div class="user-info">
            <div class="image"><a href="javascript:void(0);"><img src="../assets/images/user.png" alt="User"></a></div>
            <div class="detail mt-3">
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
                        <h5 class="mb-0"><?php echo htmlspecialchars($row->AdminName); ?></h5>
                        <small><?php echo htmlspecialchars($row->Email); ?></small>
                <?php 
                    }
                } 
                ?>
            </div>
        </div>
        <ul id="main-menu" class="metismenu">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            
            <!-- Dashboard -->
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="ti-home"></i><span>Dashboard</span></a>
            </li>
            
            <!-- Driver Management -->
            <li class="<?php echo in_array($current_page, ['add-driver.php', 'manage-driver.php']) ? 'active' : ''; ?>">
                <a href="" class="has-arrow"><i class="ti-user"></i><span>Driver</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'add-driver.php') ? 'active' : ''; ?>">
                        <a href="add-driver.php">Add Driver</a>
                    </li>
                    <li class="<?php echo ($current_page == 'manage-driver.php') ? 'active' : ''; ?>">
                        <a href="manage-driver.php">Manage Driver</a>
                    </li>
                </ul>
            </li>
            
            <!-- Request Management -->
            <li class="<?php echo in_array($current_page, ['all-complain.php', 'new-complain.php', 'assign-complain.php', 'rejected-complain.php']) ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-clipboard"></i><span>Request</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'all-complain.php') ? 'active' : ''; ?>">
                        <a href="all-complain.php">All Request</a>
                    </li>
                    <li class="<?php echo ($current_page == 'new-complain.php') ? 'active' : ''; ?>">
                        <a href="new-complain.php">New Request</a>
                    </li>
                    <li class="<?php echo ($current_page == 'assign-complain.php') ? 'active' : ''; ?>">
                        <a href="assign-complain.php">Assign Request</a>
                    </li>
                    <li class="<?php echo ($current_page == 'rejected-complain.php') ? 'active' : ''; ?>">
                        <a href="rejected-complain.php">Rejected Request</a>
                    </li>
                </ul>
            </li>
            
            <!-- Driver Work Status -->
            <li class="<?php echo in_array($current_page, ['ontheway-complain.php', 'completed-complain.php']) ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-check-box"></i><span>Driver Work Status</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'ontheway-complain.php') ? 'active' : ''; ?>">
                        <a href="ontheway-complain.php">On The Way</a>
                    </li>
                    <li class="<?php echo ($current_page == 'completed-complain.php') ? 'active' : ''; ?>">
                        <a href="completed-complain.php">Work Completed</a>
                    </li>
                    <li class="">
    <a href="driver_pre_checks_report.php"><span> View Driver Pre-Checks</span></a>
</li>
<li><a href="admin_incident_reports.php"><span>Incident Reports</span></a></li>
                </ul>
            </li>
            
            <!-- Search -->
            <li class="<?php echo ($current_page == 'search-complain.php') ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-search"></i><span>Search</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'search-complain.php') ? 'active' : ''; ?>">
                        <a href="search-complain.php">Search Lodged Request</a>
                    </li>
                </ul>
            </li>
            
            <!-- Reports -->
            <li class="<?php echo in_array($current_page, ['between-dates-complain-reports.php', 'driverwise-complain-report.php']) ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-bar-chart"></i><span>Reports</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'between-dates-complain-reports.php') ? 'active' : ''; ?>">
                        <a href="between-dates-complain-reports.php">Date Range Reports</a>
                    </li>
                    <li class="<?php echo ($current_page == 'driverwise-complain-report.php') ? 'active' : ''; ?>">
                        <a href="driverwise-complain-report.php">Driver Performance</a>
                    </li>
                     <li class="<?php echo ($current_page == 'completed-complain.php') ? 'active' : ''; ?>">
                        <a href="completed-complain.php">Scrap collection</a>
                    </li>
                </ul>
            </li>
            
            <!-- Scrap Price Management -->
            <li class="<?php echo ($current_page == 'admin_price_list.php') ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-money"></i><span>Scrap Price</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'admin_price_list.php') ? 'active' : ''; ?>">
                        <a href="admin_price_list.php">Manage Prices</a>
                    </li>
                </ul>
            </li>
            
            <!-- Scrap Collection -->
            <li class="<?php echo ($current_page == 'scrap_collection_list.php') ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-truck"></i><span>Scrap Collection</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'scrap_collection_list.php') ? 'active' : ''; ?>">
                        <a href="scrap_collection_list.php">Collection History</a>
                    </li>
                </ul>
            </li>
              <!-- Recycling Center -->
            <li class="<?php echo ($current_page == 'recycle_center.php') ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-loop"></i><span>Recycling Center</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'recycle_center.php') ? 'active' : ''; ?>">
                        <a href="recycle_center.php">Center Management</a>
                    </li>
                </ul>
            </li>
            
            <!-- Profit Reports -->
            <li class="<?php echo ($current_page == 'sell_profit_report.php') ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-stats-up"></i><span>Profit Reports</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'sell_profit_report.php') ? 'active' : ''; ?>">
                        <a href="sell_profit_report.php">Sales Analytics</a>
                    </li>
                </ul>
            </li>
            
          
            
            <!-- Registered Users -->
            <li class="<?php echo ($current_page == 'reg-users.php') ? 'active' : ''; ?>">
                <a href="reg-users.php"><i class="ti-user"></i><span>Registered Users</span></a>
            </li>
            
            <!-- Pages -->
            <li class="<?php echo in_array($current_page, ['aboutus.php', 'contactus.php']) ? 'active' : ''; ?>">
                <a href="javascript:void(0)" class="has-arrow"><i class="ti-files"></i><span>Pages</span></a>
                <ul>
                    <li class="<?php echo ($current_page == 'aboutus.php') ? 'active' : ''; ?>">
                        <a href="aboutus.php">About Us</a>
                    </li>
                    <li class="<?php echo ($current_page == 'contactus.php') ? 'active' : ''; ?>">
                        <a href="contactus.php">Contact Us</a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>