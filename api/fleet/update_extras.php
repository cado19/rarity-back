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

$fleet->id            = $data->vehicle_id;
$fleet->bluetooth     = $data->bluetooth;
$fleet->keyless_entry = $data->keyless_entry;
$fleet->reverse_cam   = $data->reverse_cam;
$fleet->audio_input   = $data->audio_input;
$fleet->apple_carplay = $data->apple_carplay;
$fleet->android_auto  = $data->android_auto;
$fleet->gps           = $data->gps;
$fleet->sunroof       = $data->sunroof;

$response = [];

if ($fleet->save_extras()) {
    $status  = "Success";
    $message = "Successfully updated vehicle extras";

    $response['status']  = $status;
    $response['message'] = $message;
} else {
    $status  = "Error";
    $message = "An error occured";

    $response['status']  = $status;
    $response['message'] = $message;
}

echo json_encode($response);
