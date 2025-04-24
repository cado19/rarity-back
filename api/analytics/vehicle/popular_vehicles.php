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

$stat->popular_vehicle();

$status              = 'Success';
$message             = 'Successfully retrived most popular vehicle';
$response['status']  = $status;
$response['message'] = $message;
$response['data']    = $stat_arr;

echo json_encode($response);
