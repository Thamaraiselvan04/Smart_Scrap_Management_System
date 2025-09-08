<div class="page-loader-wrapper">
    <div class="loader">
        <div class="m-t-30">
            <img src="../assets/images/brand/icon_black.svg" width="48" height="48" alt="ArrOw">
        </div>
        <p>Please wait...</p>
    </div>
</div>

<nav class="navbar custom-navbar navbar-expand-lg py-2 bg-success">
    <div class="container-fluid px-0">
        <a href="javascript:void(0);" class="menu_toggle"><i class="fa fa-align-left"></i></a>
        <a href="dashboard.php" class="navbar-brand"><strong>Smart </strong>Scrap Management System</a>

        <div id="navbar_main">
            <ul class="navbar-nav mr-auto hidden-xs">
                <li class="nav-item page-header">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
               
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-icon" href="javascript:void(0);" id="navbar_complain_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                            <span class="badge badge-danger notification-badge"><?php echo $result->total; ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-xl py-0" aria-labelledby="navbar_complain_dropdown">
                        <div class="py-3 px-3">
                            <?php
                            // Get full notification details
                            $sql = "SELECT * FROM tbllodgedcomplain WHERE Status='Approved' AND AssignTo=:did ORDER BY ComplainDate DESC LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':did', $did, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            ?>
                            <h5 class="heading h6 mb-0">Complaint Notifications 
                                <span class="badge badge-pill badge-primary text-uppercase float-right"><?php echo $result->total; ?></span>
                            </h5>
                        </div>

                        <div class="list-group">
                            <?php if(count($results) > 0): ?>
                                <?php foreach($results as $row): ?>
                                    <a href="view-complain-detail.php?editid=<?php echo htmlentities($row->ID); ?>&comid=<?php echo htmlentities($row->ComplainNumber); ?>" class="list-group-item list-group-item-action d-flex">
                                        <div class="list-group-content">
                                            <div class="list-group-heading"><?php echo htmlentities($row->ComplainNumber); ?> 
                                                <small><?php echo htmlentities($row->AssignDate ?? 'No date'); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">No new notifications</div>
                            <?php endif; ?>
                        </div>

                        <div class="py-3 text-center">
                            <a href="new-complain-request.php" class="link link-sm link--style-3">View all notifications</a>
                        </div>
                    </div>
                </li>

                <!-- Profile Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-icon" href="javascript:void(0);" id="navbar_profile_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-user"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbar_profile_dropdown">
                        <h6 class="dropdown-header">Driver Menu</h6>
                        <a class="dropdown-item" href="profile.php"><i class="fa fa-user text-primary"></i> My Profile</a>
                        <a class="dropdown-item" href="change-password.php"><i class="fa fa-cog text-primary"></i> Settings</a>
                        <div class="dropdown-divider" role="presentation"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out text-primary"></i> Sign out</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    font-size: 10px;
    padding: 3px 6px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
}
.nav-link-icon {
    position: relative;
}
</style>