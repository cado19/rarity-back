<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$db    = (new Database())->connect();
$fleet = new Fleet($db);

$vehicle_id = $_GET['vehicle_id'] ?? null;

if (! $vehicle_id) {
    echo json_encode(["status" => "Error", "message" => "Missing vehicle_id"]);
    exit;
}

$fleet->id = $vehicle_id;

$response = $fleet->getByVehicle();
echo json_encode([
    "status" => "Success",
    "data"   => $response,
]);
