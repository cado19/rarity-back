<?php
// THIS FILE WILL DELIVER MAKE, MODEL AND NUMBER PLATE TO EXTERNAL APIs BASED ON vehicle id

// include necessary files
include_once '../../config/cors.php';
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
    $result = $fleet->get_vehicle_base();

    if (! $result) {
        echo json_encode([
            "status"  => "Error",
            "message" => "No base data found for this vehicle",
            "base"    => [],
        ]);
        exit;
    } else {
        echo json_encode([
            "status"  => "Success",
            "message" => "Successfully retrieved base data",
            "base"    => $result,
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "status"  => "Error",
        "message" => $e->getMessage(),
    ]);
}
