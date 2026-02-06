<?php
// THIS FILE WILL DELIVER A SINGLE AGENT TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Agent.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$agent = new Agent($db);

// get the id from the url

if (isset($_GET['id'])) {
    $agent->id = $_GET['id'];
    // echo json_encode("Agent id: " . $_GET['id']);
} else {
    die();
}

// get single fleet
$agent->read_single();

$response = [];

$agent_arr = [
    'id'       => $agent->id,
    'name'     => $agent->name,
    'email'    => $agent->email,
    'phone_no' => $agent->phone_no,
    'country'  => $agent->country,
    'role'     => $agent->role,
];
$status              = 'Success';
$message             = 'Successfully retrieved agent';
$response['status']  = $status;
$response['message'] = $message;
$response['agent']   = $agent_arr;

echo json_encode($response);
