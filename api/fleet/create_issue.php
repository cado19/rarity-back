<?php
// THIS FILE WILL DELIVER SAVE AN ISSUE TO THE DATABASE

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

$fleet->id              = $data->vehicle_id;
$fleet->title           = $data->title;
$fleet->description     = $data->description;
$fleet->resolution_cost = $data->cost;

$response = [];

if ($fleet->create_issue()) {
    $status               = "Success";
    $message              = "Issue Created";
    $response['status']   = $status;
    $response['message']  = $message;
    $response['issue_id'] = $fleet->issue_id;
} else {
    $status              = "Error";
    $message             = "An error occured saving vehicle issue.";
    $response['status']  = $status;
    $response['message'] = $message;

}
echo json_encode($response);
