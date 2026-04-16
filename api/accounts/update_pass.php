<?php
// THIS FILE WILL DEAL WITH LOGGING AN ACCOUNT INTO THE SYSTEM

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//header mods for login request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// api/accounts/update_pass.php

include_once '../../config/Database.php';
include_once '../../models/Account.php';
include_once '../../models/Driver.php'; 

$database = new Database();
$db       = $database->connect();

$data = json_decode(file_get_contents("php://input"));

$response = [];

if (empty($data->password) || empty($data->id) || empty($data->role)) {
    $response['status'] = "Error";
    $response['message'] = "Missing required fields";
    echo json_encode($response);
    exit;
}

$hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

if ($data->role === "agent") {
    $account = new Account($db);
    $account->id = $data->id;
    $account->hashed_password = $hashed_password;

    if ($account->update_password()) {
        $response['status'] = "Success";
        $response['message'] = "Agent password updated";
    } else {
        $response['status'] = "Error";
        $response['message'] = "Failed to update agent password";
    }
} elseif ($data->role === "driver") {
    $driver = new Driver($db);
    $driver->id = $data->id;
    $driver->hashed_password = $hashed_password;

    if ($driver->update_password()) {
        $response['status'] = "Success";
        $response['message'] = "Driver password updated";
    } else {
        $response['status'] = "Error";
        $response['message'] = "Failed to update driver password";
    }
} else {
    $response['status'] = "Error";
    $response['message'] = "Invalid role";
}

echo json_encode($response);