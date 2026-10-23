<!-- ===== Top bar (Driver) ===== -->
<header class="ss-topbar">
    <button type="button" class="ss-burger" id="ssMenuBtn" aria-label="Open menu"><i class="fa fa-bars"></i></button>
    <a href="dashboard.php" class="ss-top-brand"><img src="../img1/trash.png" alt="SSMS"><span>SSMS</span></a>
    <span class="ss-role"><i class="fa fa-truck"></i> Driver Panel</span>

    <div class="ss-top-right">
        <a href="../index.php" class="ss-home" title="Home page"><i class="fa fa-globe"></i><span>Website</span></a>

        <!-- Notifications -->
        <details class="ss-drop">
            <summary class="ss-icon-btn" aria-label="Notifications">
                <i class="fa fa-bell"></i>
                        <?php
                        // Get notification count
                        $did = $_SESSION['vamsdid'];
                        $sql = "SELECT COUNT(*) as total FROM tbllodgedcomplain WHERE Status='Approved' AND AssignTo=:did";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':did', $did, PDO::PARAM_STR);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_OBJ);

                        if ($result->total > 0): ?>
                            <span class="ss-count"><?php echo $result->total; ?></span>
                        <?php endif; ?>
            </summary>
            <div class="ss-menu ss-menu-wide">
                            <?php
                            // Get full notification details
                            $sql = "SELECT * FROM tbllodgedcomplain WHERE Status='Approved' AND AssignTo=:did ORDER BY ComplainDate DESC LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':did', $did, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            ?>
                <div class="ss-menu-head">New Pickup Requests <span class="badge"><?php echo $result->total; ?></span></div>
                <div class="ss-notes">
                            <?php if(count($results) > 0): ?>
                                <?php foreach($results as $row): ?>
                    <a class="ss-note" href="view-complain-detail.php?editid=<?php echo htmlentities($row->ID); ?>&comid=<?php echo htmlentities($row->ComplainNumber); ?>">
                        <b>Request #<?php echo htmlentities($row->ComplainNumber); ?></b>
                        <small><i class="fa fa-clock-o"></i> <?php echo htmlentities($row->AssignDate ?? 'No date'); ?></small>
                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                    <div class="ss-empty">No new notifications</div>
                            <?php endif; ?>
                </div>
                <a class="ss-menu-foot" href="new-complain-request.php">View all requests</a>
            </div>
        </details>

        <!-- Account -->
        <details class="ss-drop">
            <summary class="ss-account" aria-label="Account menu">
                <img src="../assets/images/user.png" alt="Driver">
                <span>My Account</span>
                <i class="fa fa-chevron-down"></i>
            </summary>
            <div class="ss-menu">
                <a href="profile.php"><i class="fa fa-user"></i>My Profile</a>
                <a href="change-password.php"><i class="fa fa-key"></i>Change Password</a>
                <div class="ss-sep"></div>
                <a href="logout.php"><i class="fa fa-sign-out"></i>Sign out</a>
            </div>
        </details>
    </div>
</header>

<!-- Shown only when a page is printed -->
<div class="ss-print-head">
    <img src="../img1/trash.png" alt="">
    <div><b>SSMS – Smart Scrap Management System</b><small>Driver Panel</small></div>
</div>
