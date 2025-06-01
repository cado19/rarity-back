<?php
// THIS FILE WILL DELIVER UPDATE THE DAILY RATE OF DRIVER

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for driver request
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate driver object
$driver = new Driver($db);

$response = [];

// Get the raw posted data
$data = json_decode(file_get_contents("php://input"));

// echo json_encode($data);

$driver->id       = $data->driver_id;
$driver->location = $data->location;
$driver->rate     = $data->rate_amount;

if ($driver->update_rate()) {
    $status              = "Success";
    $message             = "Driver rate updated";
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
} else {
    $status              = "Error";
    $message             = "Driver rate not updated";
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
}
