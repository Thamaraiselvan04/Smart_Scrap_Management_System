<!-- ===== Top bar (User) ===== -->
<header class="ss-topbar">
    <button type="button" class="ss-burger" id="ssMenuBtn" aria-label="Open menu"><i class="fa fa-bars"></i></button>
    <a href="dashboard.php" class="ss-top-brand"><img src="../img1/trash.png" alt="SSMS"><span>SSMS</span></a>
    <span class="ss-role"><i class="fa fa-user"></i> User Area</span>

    <div class="ss-top-right">
        <a href="../index.php" class="ss-home" title="Home page"><i class="fa fa-globe"></i><span>Website</span></a>

        <details class="ss-drop">
            <summary class="ss-account" aria-label="Account menu">
                <img src="../assets/images/user.png" alt="User">
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
    <div><b>SSMS – Smart Scrap Management System</b><small>Doorstep scrap pickup, Chennai</small></div>
</div>
