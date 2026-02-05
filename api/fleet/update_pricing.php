<?php
// THIS FILE WILL UPDATE EXTRAS OF A VEHICLE

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents('php://input'));

$fleet->id                          = $data->vehicle_id;
$fleet->daily_rate                  = $data->daily_rate;
$fleet->vehicle_excess              = $data->vehicle_excess;
$fleet->refundable_security_deposit = $data->refundable_security_deposit;
$fleet->cdw_rate                    = $data->cdw_rate;
$fleet->monthly_target              = $data->monthly_target;

try {
    $success = $fleet->update_pricing();

    if ($success) {
        echo json_encode([
            "status"  => "Success",
            "message" => "Pricing updated successfully",
        ]);
    } else {
        echo json_encode([
            "status"  => "Error",
            "message" => "Failed to update pricing",
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status"  => "Error",
        "message" => $e->getMessage(),
    ]);
}
