<?php
// THIS FILE WILL DEAL WITH LOGGING AN ACCOUNT INTO THE SYSTEM

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);

// get request data
$data           = json_decode(file_get_contents("php://input"));
$account->email = $data->email;

// fetch account by email
$email_result = $account->fetch_account_with_roles();
$num          = $email_result->rowCount();

$response = [];

if ($num == 0) {
    $response['status']  = "Error";
    $response['message'] = "Incorrect email/password combination";
    echo json_encode($response);
    exit;
}

// account found
$user                     = $email_result->fetch(PDO::FETCH_ASSOC);
$account->id              = $user['id'];
$account->hashed_password = $user['password'];
$account->name            = $user['name'];
$account->email           = $user['email'];

// fetch roles using model function
$roles = $account->fetch_role_ids_and_names();

// build user array
$user_arr = [
    'id'    => $account->id,
    'name'  => $account->name,
    'email' => $account->email,
    'roles' => $roles,
];

// verify password
if (password_verify($data->password, $account->hashed_password)) {
    $response['status']  = "Success";
    $response['message'] = "Logged in";
    $response['user']    = $user_arr;
    echo json_encode($response);
} else {
    $response['status']  = "Error";
    $response['message'] = "Incorrect email/password combination";
    echo json_encode($response);
}
