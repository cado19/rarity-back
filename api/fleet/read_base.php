<?php
// THIS FILE WILL DELIVER MAKE, MODEL AND NUMBER PLATE TO EXTERNAL APIs BASED ON vehicle id
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
$fleet->get_vehicle_base();

//response array
$response = [];

$base_arr = [
    'make'            => $fleet->make,
    'model'           => $fleet->model,
    'number_plate'    => $fleet->number_plate,
    'seats'           => $fleet->seats,
    'fuel'            => $fleet->fuel,
    'transmission'    => $fleet->transmission,
    'category_id'     => $fleet->category_id,
    'colour'          => $fleet->colour,
    'drive_train'     => $fleet->drive_train,
    'capacity'        => $fleet->capacity,
    'cylinders'       => $fleet->cylinders,
    'economy_city'    => $fleet->economy_city,
    'economy_highway' => $fleet->economy_highway,
    'acceleration'    => $fleet->acceleration,
    'aspiration'      => $fleet->aspiration,
];

$status  = "Success";
$message = "Successfully retrieved basic of vehicle";

$response['status']  = $status;
$response['message'] = $message;
$response['vehicle'] = $base_arr;

echo json_encode($response);
