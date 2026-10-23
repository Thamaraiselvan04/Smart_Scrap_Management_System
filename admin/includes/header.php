<!-- ===== Top bar (Admin) ===== -->
<header class="ss-topbar">
    <button type="button" class="ss-burger" id="ssMenuBtn" aria-label="Open menu"><i class="fa fa-bars"></i></button>
    <a href="dashboard.php" class="ss-top-brand"><img src="../img1/trash.png" alt="SSMS"><span>SSMS</span></a>
    <span class="ss-role"><i class="fa fa-shield"></i> Admin Panel</span>

    <div class="ss-top-right">
        <a href="../index.php" class="ss-home" title="Home page"><i class="fa fa-globe"></i><span>Website</span></a>

        <!-- Notifications: new pickup requests -->
        <details class="ss-drop">
                            <?php
$sql="SELECT tbllodgedcomplain.ComplainNumber,tbllodgedcomplain.AssignTo,tbllodgedcomplain.ID as compid,tbllodgedcomplain.Status,tbluser.ID as uid,tbluser.FullName,tbluser.MobileNumber,tbluser.Email from tbllodgedcomplain join tbluser on tbluser.ID=tbllodgedcomplain.UserID  where tbllodgedcomplain.Status is null";
$query = $dbh -> prepare($sql);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);

$cnt=1;
$totalreq=$query->rowCount();
?>
            <summary class="ss-icon-btn" aria-label="Notifications">
                <i class="fa fa-bell"></i>
                <?php if ($totalreq > 0): ?><span class="ss-count"><?php echo $totalreq;?></span><?php endif; ?>
            </summary>
            <div class="ss-menu ss-menu-wide">
                <div class="ss-menu-head">New Pickup Requests <span class="badge"><?php echo $totalreq;?></span></div>
                <div class="ss-notes">
                             <?php
                        foreach($results as $row)
{

  ?>
                    <a class="ss-note" href="view-complain-detail.php?editid=<?php echo htmlentities($row->compid); ?>&comid=<?php echo htmlentities($row->ComplainNumber); ?>">
                        <b>Request #<?php echo $row->ComplainNumber; ?></b>
                        <small><i class="fa fa-user"></i> <?php echo $row->FullName;?> <?php echo $row->DateofRequest;?></small>
                    </a>
                          <?php }?>
                </div>
                <a class="ss-menu-foot" href="new-complain.php">View all new requests</a>
            </div>
        </details>

        <!-- Account -->
        <details class="ss-drop">
            <summary class="ss-account" aria-label="Account menu">
                <img src="../assets/images/user.png" alt="Admin">
                <span>Admin</span>
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
    <div><b>SSMS – Smart Scrap Management System</b><small>Admin Panel</small></div>
</div>
