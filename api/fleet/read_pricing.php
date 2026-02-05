<?php
// THIS FILE WILL DELIVER A SINGLE vehicle'S images TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$fleet = new Fleet($db);

// get the id from the url

if (isset($_GET['id'])) {
    $fleet->id = $_GET['id'];
} else {
    die();
}

try {
    $result = $fleet->get_vehicle_pricing();

    if (! $result) {
        echo json_encode([
            "status"  => "Error",
            "message" => "No pricing data found for this vehicle",
            "extras"  => [],
        ]);
        exit;
    }

    $pricing_arr = [
        'daily_rate'                  => $fleet->daily_rate,
        'vehicle_excess'              => $fleet->vehicle_excess,
        'refundable_security_deposit' => $fleet->refundable_security_deposit,
        'cdw_rate'                    => $fleet->cdw_rate,
        'monthly_target'              => $fleet->monthly_target,
    ];

    echo json_encode([
        "status"  => "Success",
        "message" => "Successfully retrieved pricing data",
        "pricing" => $pricing_arr,
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status"  => "Error",
        "message" => $e->getMessage(),
    ]);
}
