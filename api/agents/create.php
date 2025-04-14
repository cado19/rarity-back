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

$tmp_password = "1234";

$agent->name     = $data->name;
$agent->email    = $data->email;
$agent->country  = $data->country;
$agent->role_id  = $data->role_id;
$agent->password = password_hash($tmp_password, PASSWORD_DEFAULT);
$agent->phone_no = $data->phone_number;

//check if client exists in db with associated email
$result = $agent->check_unique_email();

if ($result->rowCount() > 0) {
    $status              = "Error";
    $message             = "An agent exists with this email.";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    // code...
    if ($agent->create()) {
        // create agent commissions
        $agent->create_suv_commission();
        $agent->create_mid_size_suv_commission();
        $agent->create_medium_car_commission();
        $agent->create_small_car_commission();
        $agent->create_safari_commission();
        $agent->create_luxury_commission();
        $agent->create_commercial_commission();

        //create agent rates
        $agent->create_suv_rate();
        $agent->create_mid_size_suv_rate();
        $agent->create_medium_car_rate();
        $agent->create_small_car_rate();
        $agent->create_safari_rate();
        $agent->create_luxury_rate();
        $agent->create_commercial_rate();

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
