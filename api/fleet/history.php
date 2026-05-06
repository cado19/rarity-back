<?php
// SCRIPT FETCHES A VEHICLE'S HISTORY
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents("php://input"), true);

if (! isset($data['vehicle_id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing vehicle_id"]);
    exit;
}

$fleet->id = $data['vehicle_id'];

$history = $fleet->get_vehicle_history();

echo json_encode([
    "status"  => "Success",
    "history" => $history,
]);
