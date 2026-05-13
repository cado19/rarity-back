<?php
// RETURNS ALL WORK ORDERS
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);
$data  = json_decode(file_get_contents("php://input"), true);

// optional filters
$status    = $data['status'] ?? null;
$vehicleId = $data['vehicle_id'] ?? null;

$response = $fleet->list_work_orders($status, $vehicleId);

echo json_encode($response);
