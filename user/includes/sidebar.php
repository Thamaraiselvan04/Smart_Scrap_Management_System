<!-- ===== Sidebar (User) ===== -->
<aside class="ss-sidebar" id="ssSidebar">
    <a href="dashboard.php" class="ss-brand">
        <img src="../img1/trash.png" alt="SSMS logo">
        <span>SSMS<small>Smart Scrap System</small></span>
    </a>

    <div class="ss-user">
        <img src="../assets/images/user.png" alt="User">
        <div class="ss-user-text">
                <?php
                // Safely get session UUID
                $uid = isset($_SESSION['uuid']) ? $_SESSION['uuid'] : null;

                // Initialize variables to avoid warnings
                $fname = "";
                $email = "";

                if ($uid) {
                    $sql = "SELECT FullName, Email FROM tbluser WHERE ID = :uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);

                    foreach ($results as $row) {
                        $fname = $row->FullName;
                        $email = $row->Email;
                    }
                }
                ?>
            <h5><?php echo htmlspecialchars($fname); ?></h5>
            <small><?php echo htmlspecialchars($email); ?></small>
        </div>
    </div>

    <nav class="ss-nav">
            <?php
            // Get current page filename
            $current_page = basename($_SERVER['PHP_SELF']);
            // Menu highlight only: "New Request" and "Request History" open the same page,
            // so the ?type value decides which one is marked as active.
            $menu_type = isset($_GET['type']) ? $_GET['type'] : '';
            ?>
        <span class="ss-label">Main</span>
        <a href="dashboard.php" class="ss-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="ti-home"></i><span>Dashboard</span></a>
        <a href="lodged-complain.php" class="ss-link <?php echo ($current_page == 'lodged-complain.php' || $current_page == 'price_list.php') ? 'active' : ''; ?>"><i class="ti-truck"></i><span>Pickup Request</span></a>

        <span class="ss-label">My Requests</span>
        <a href="lodged-complain-history.php?type=new" class="ss-link <?php echo ($current_page == 'lodged-complain-history.php' && $menu_type == 'new') ? 'active' : ''; ?>"><i class="ti-files"></i><span>New Request</span></a>
        <a href="lodged-complain-history.php" class="ss-link <?php echo (($current_page == 'lodged-complain-history.php' && $menu_type != 'new') || $current_page == 'view-lodged-complain-detail.php') ? 'active' : ''; ?>"><i class="ti-folder"></i><span>Request History</span></a>
        <a href="search.php" class="ss-link <?php echo ($current_page == 'search.php') ? 'active' : ''; ?>"><i class="ti-search"></i><span>Request Status</span></a>
                <?php
                $sql ="SELECT ID FROM tbllodgedcomplain WHERE UserID=:uid AND Status='Completed'";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->execute();
                $completedRequest = $query->rowCount();
                ?>
        <a href="payment.php?type=completed" class="ss-link <?php echo ($current_page == 'payment.php' || $current_page == 'payment_receipt.php') ? 'active' : ''; ?>"><i class="ti-receipt"></i><span>Payment Receipt</span></a>

        <span class="ss-label">Account</span>
        <a href="profile.php" class="ss-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><i class="ti-user"></i><span>My Profile</span></a>
        <a href="change-password.php" class="ss-link <?php echo ($current_page == 'change-password.php') ? 'active' : ''; ?>"><i class="ti-key"></i><span>Change Password</span></a>
        <a href="logout.php" class="ss-link ss-logout"><i class="ti-power-off"></i><span>Sign out</span></a>
    </nav>
</aside>
<div class="ss-overlay" id="ssOverlay"></div>
<script src="../assets/js/ssms-panel.js"></script>
