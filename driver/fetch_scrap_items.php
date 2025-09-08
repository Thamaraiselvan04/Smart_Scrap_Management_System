<?php
include('includes/dbconnection.php');
header('Content-Type: application/json');

$sql = "SELECT id, scrap_name, price FROM scrap_price_list";
$query = $dbh->prepare($sql);
$query->execute();
echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
?>