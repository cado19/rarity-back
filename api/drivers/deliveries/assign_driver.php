<?php

// include necessary files
include_once '../../../config/cors.php';
include_once '../../../config/Database.php';
include_once '../../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate driver object
$driver = new Driver($db);

$response = [];

// Get the raw posted data
$data = json_decode(file_get_contents("php://input"));

$driver->booking_id = $data->booking_id;
$driver->id         = $data->driver_id;

if ($driver->upsert_delivery()) {
    // code...
    $status              = "Success";
    $message             = "Delivery designated";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    $status              = "Error";
    $message             = "Delivery Not Created";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
}
