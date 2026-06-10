<?php
// CHECK IF A VEHICLE HAS A LOAN GOING ON
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$loan             = new Loan($db);
$loan->vehicle_id = $_GET['vehicle_id'];

$result = $loan->getLoanByVehicleId(); // implement in model

echo json_encode([
    "status" => "Success",
    "exists" => $result ? true : false,
    "loan"   => $result,
]);
