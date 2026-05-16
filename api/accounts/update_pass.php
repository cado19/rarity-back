<?php
// THIS FILE WILL DEAL WITH UPDATING AN ACCOUNT'S PASSWORD

// api/accounts/update_pass.php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';
include_once '../../models/Driver.php';

$database = new Database();
$db       = $database->connect();

$data = json_decode(file_get_contents("php://input"));

$response = [];

if (empty($data->password) || empty($data->agent_id)) {
    $response['status']  = "Error";
    $response['message'] = "Missing required fields";
    echo json_encode($response);
    exit;
}

$hashed_password          = password_hash($data->password, PASSWORD_DEFAULT);
$account                  = new Account($db);
$account->id              = $data->agent_id;
$account->hashed_password = $hashed_password;

if ($account->update_password()) {
    $response['status']  = "Success";
    $response['message'] = "Agent password updated";
} else {
    $response['status']  = "Error";
    $response['message'] = "Failed to update agent password";
}

echo json_encode($response);
