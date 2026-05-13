<?php
// THIS FILE WILL UPDATE BASICS OF A VEHICLE

// include necessary files
include_once '../../config/cors.php';
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
$fleet->mileage         = $data->mileage;
$fleet->service         = $data->service;

$response = [];

$result = $fleet->update_base();

if ($result === true) {
    $response = [
        "status"  => "Success",
        "message" => "Successfully updated vehicle basics",
    ];
} elseif (is_array($result)) {
    // Model returned structured error
    $response = $result;
} else {
    // Fallback error
    $response = [
        "status"  => "Error",
        "message" => "An unknown error occurred while updating vehicle basics",
    ];
}

echo json_encode($response);
