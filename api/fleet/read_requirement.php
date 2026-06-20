<?php
// Deliver a single requirements to external requests
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$fleet = new Fleet($db);

// get the id from the url

if (isset($_GET['vehicle_id'])) {
    $fleet->id = $_GET['vehicle_id'];
} else {
    die();
}

try {
    $result = $fleet->read_requirement();

    if (! $result) {
        echo json_encode([
            "status"      => "Error",
            "message"     => "Requirement not found",
            "requirement" => [],
        ]);
        exit;
    } else {
        echo json_encode([
            "status"      => "Success",
            "message"     => "Successfully retrieved requirement",
            "requirement" => $result,
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "status"  => "Error",
        "message" => $e->getMessage(),
    ]);
}
