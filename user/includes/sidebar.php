<div class="left_sidebar">
    <nav class="sidebar">
        <div class="user-info">
            <div class="image">
                <a href="javascript:void(0);">
                    <img src="../assets/images/user.png" alt="User">
                </a>
            </div>
            <div class="detail mt-3">
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
                <h5 class="mb-0"><?php echo htmlspecialchars($fname); ?></h5>
                <small><?php echo htmlspecialchars($email); ?></small>
            </div>
        </div>
        <ul id="main-menu" class="metismenu">
            <?php
            // Get current page filename
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="ti-home"></i><span>Dashboard</span></a>
            </li>
            <li class="<?php echo ($current_page == 'lodged-complain.php') ? 'active' : ''; ?>">
                <a href="lodged-complain.php"><i class="ti-files"></i><span>Pickup Request</span></a>
            </li>
             <li class="<?php echo ($current_page == 'lodged-complain-history.php') ? 'active' : ''; ?>">
                <a href="lodged-complain-history.php?type=new"><i class="ti-folder"></i><span>New Request</span></a>
            </li>
            <li class="<?php echo ($current_page == 'lodged-complain-history.php') ? 'active' : ''; ?>">
                <a href="lodged-complain-history.php"><i class="ti-folder"></i><span>Request History</span></a>
            </li>
            <li class="<?php echo ($current_page == 'search.php') ? 'active' : ''; ?>">
                <a href="search.php"><i class="ti-search"></i><span>Request Status</span></a>
            </li>
            
            <li class="<?php echo ($current_page == 'payment.php') ? 'active' : ''; ?>">
                <?php 
                $sql ="SELECT ID FROM tbllodgedcomplain WHERE UserID=:uid AND Status='Completed'";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->execute();
                $completedRequest = $query->rowCount();
                ?>
                <a href="payment.php?type=completed"><i class="ti-receipt"></i><span>Payment Receipt</span></a>
            </li> 
        </ul>
    </nav>
</div>