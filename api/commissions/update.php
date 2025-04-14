<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

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
include_once '../../models/Agent.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate customer object
$agent = new Agent($db);

$response = [];

// get the data from the frontend
$data = json_decode(file_get_contents("php://input"));

$agent->id                = $data->agent_id;
$agent->category_id       = $data->category_id;
$agent->commission_type   = $data->commission_type;
$agent->commission_amount = $data->commission_amount;

if ($agent->update_commission()) {
    // code...
    $status              = "Success";
    $message             = "Agent commission updated";
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
} else {
    // code...
    $status              = "Error";
    $message             = "Agent commission not be updated";
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
}
