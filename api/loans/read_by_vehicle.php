<?php
// Get a single loan structure for a vehicle by vehicle_id
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$loan             = new Loan($db);
$loan->vehicle_id = $_GET['id'] ?? null; // id here is vehicle_id

$result = $loan->getLoanByVehicleId();
echo json_encode(["status" => "Success", "data" => $result]);
