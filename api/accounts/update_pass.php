<?php
// THIS FILE WILL DEAL WITH LOGGING AN ACCOUNT INTO THE SYSTEM

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//header mods for login request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

// instantiate account object
$account = new Account($db);

// first get data from the external request
$data = json_decode(file_get_contents("php://input"));

$password                 = $data->password;
$account->id              = $data->agent_id;
$account->hashed_password = password_hash($password, PASSWORD_DEFAULT);

$response = [];

if ($account->update_password()) {
    $status              = "Success";
    $message             = "Updated password";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    $status              = "Success";
    $message             = "Updated password";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
}
