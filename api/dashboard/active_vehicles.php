<?php
// THIS FILE WILL DELIVER THE NUMMBER OF ACTIVE VEHICLES TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate fleet post object
$fleet = new Fleet($db);

// vehicles query as a function
$result = $fleet->active_vehicles();

// get row count
$num = $result->rowCount();

$response = [];

$message = "Active vehicles received";
$count   = $num;

$response['message']      = $message;
$response['active_count'] = $count;

echo json_encode($response);
