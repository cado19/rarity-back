<?php
// THIS FILE WILL DELIVER A SINGLE DRIVER TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog driver object
$driver = new Driver($db);

// get the id from the url

if (isset($_GET['id'])) {
    $driver->id = $_GET['id'];
} else {
    die();
}

// get single driver
$driver->read_single();

$response = [];

// create the array
$driver_arr = [
    'id'            => $driver->id,
    'first_name'    => $driver->first_name,
    'last_name'     => $driver->last_name,
    'email'         => $driver->email,
    'dl_no'         => $driver->dl_no,
    'id_no'         => $driver->id_no,
    'phone_no'      => $driver->phone_no,
    'dl_expiry'     => $driver->dl_expiry,
    'date_of_birth' => $driver->date_of_birth,
];
$status              = 'Success';
$message             = 'Successfully retrieved driver';
$response['status']  = $status;
$response['message'] = $message;
$response['driver']  = $driver_arr;

echo json_encode($response);
