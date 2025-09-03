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

// echo json_encode($data);

$fleet->id              = $data->vehicle_id;
$fleet->make            = $data->make;
$fleet->model           = $data->model;
$fleet->number_plate    = $data->number_plate;
$fleet->seats           = $data->seats;
$fleet->fuel            = $data->fuel;
$fleet->transmission    = $data->transmission;
$fleet->category_id     = $data->category_id;
$fleet->colour          = $data->colour;
$fleet->drive_train     = $data->drive_train;
$fleet->capacity        = $data->capacity;
$fleet->cylinders       = $data->cylinders;
$fleet->economy_city    = $data->economy_city;
$fleet->economy_highway = $data->economy_highway;
$fleet->acceleration    = $data->acceleration;
$fleet->aspiration      = $data->aspiration;
$fleet->horsepower      = $data->horsepower;

$response = [];

if ($fleet->update_base()) {
    $status  = "Success";
    $message = "Successfully updated vehicle basics";

    $response['status']  = $status;
    $response['message'] = $message;
} else {
    $status  = "Error";
    $message = "An error occured";

    $response['status']  = $status;
    $response['message'] = $message;
}

echo json_encode($response);
