<?php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents('php://input'));

$ids = $data->vehicle_ids ?? [];

if (empty($ids)) {
    echo json_encode([
        "status"  => "Error",
        "message" => "No vehicles selected",
    ]);
    exit;
}

if ($fleet->delete_multiple($ids)) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Vehicles deleted successfully",
    ]);
}
