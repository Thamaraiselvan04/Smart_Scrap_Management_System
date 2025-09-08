<?php
include('includes/dbconnection.php');
if(isset($_POST['driverid'])) {
    $driverid = $_POST['driverid'];
    $sql = "SELECT Name, MobileNumber FROM tbldriver WHERE DriverID = :driverid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':driverid', $driverid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
}
?>