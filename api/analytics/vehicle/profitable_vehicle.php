<?php
// THIS FILE WILL RETURN MOST PROFITABLE VEHICLE

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../../config/Database.php';
include_once '../../../models/Analytics.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$stat = new Analytics($db);

$stat->profitable_vehicle();

$response = [];

$stat_arr = [
    'make'         => $stat->make,
    'model'        => $stat->model,
    'number_plate' => $stat->number_plate,
    'total'        => $stat->total,
];

$status              = 'Success';
$message             = 'Successfully retrived most profitable vehicle';
$response['status']  = $status;
$response['message'] = $message;
$response['data']    = $stat_arr;

echo json_encode($response);
