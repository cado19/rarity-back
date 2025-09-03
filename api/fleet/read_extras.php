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

// get images of the vehicle'S
$result = $fleet->get_vehicle_extras();

//response array
$response = [];

$extras_arr = [
    // 'id'            => $fleet->id,
    'bluetooth'     => $fleet->bluetooth,
    'keyless_entry' => $fleet->keyless_entry,
    'reverse_cam'   => $fleet->reverse_cam,
    'audio_input'   => $fleet->audio_input,
    'gps'           => $fleet->gps,
    'android_auto'  => $fleet->android_auto,
    'apple_carplay' => $fleet->apple_carplay,
    'sunroof'       => $fleet->sunroof,
];

$status  = "Success";
$message = "Successfully retrieved extras";

$response['status']  = $status;
$response['message'] = $message;
$response['extras']  = $extras_arr;

echo json_encode($response);
