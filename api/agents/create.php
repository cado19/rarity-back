<?php
// THIS FILE WILL DELIVER SAVE AGENT DATA FROM EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for agent request
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

// instantiate agent object
$agent = new Agent($db);

$response = [];

// Get the raw posted data
$data = json_decode(file_get_contents("php://input"));

$agent->name     = $data->name;
$agent->email    = $data->email;
$agent->country  = $data->country;
$agent->role_id  = $data->role_id;
$agent->password = $data->password;
$agent->phone_no = $data->phone_no;

//check if client exists in db with associated email
$result = $customer->check_unique_email();

if ($result->rowCount() > 0) {
    $status              = "Error";
    $message             = "An agent exists with this email.";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    // code...
    if ($agent->create()) {
        $status               = "Success";
        $message              = "Agent Created";
        $response['status']   = $status;
        $response['message']  = $message;
        $response['agent_id'] = $agent->id;

        echo json_encode($response);

    } else {
        $status              = "Error";
        $message             = "Agent Not Created.";
        $response['status']  = $status;
        $response['message'] = $message;

        echo json_encode($response);

    }
}
