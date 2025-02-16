<?php
// THIS FILE WILL DELIVER A SINGLE fleetS TO EXTERNAL REQUESTS

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

// get single fleet
$fleet->read_single();

// create the array
$fleet_arr = [
    'id'             => $fleet->id,
    'make'           => $fleet->make,
    'model'          => $fleet->model,
    'number_plate'   => $fleet->number_plate,
    'seats'          => $fleet->seats,
    'drive_train'    => $fleet->drive_train,
    'category_id'    => $fleet->category_id,
    'category_name'  => $fleet->category_name,
    'daily_rate'     => $fleet->daily_rate,
    'vehicle_excess' => $fleet->vehicle_excess,
    'transmission'   => $fleet->transmission,
    'fuel'           => $fleet->fuel,

];

echo json_encode($fleet_arr);
