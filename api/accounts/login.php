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

$account->email = $data->email;

//first fetch the email (verify if the email exists)
$email_result = $account->fetch_account();

$num = $email_result->rowCount();

$response = [];

if ($num == 0) {
    //account was not found with the provided email
    $message             = "Incorrect email/password combination";
    $status              = "Error";
    $response['status']  = $status;
    $response['message'] = $message;
    // array_push($response, $status);
    // array_push($response, $message);
    echo json_encode($response);
} else {
    // account was found and now we fetch user data and verify the password
    $user                     = $email_result->fetch(PDO::FETCH_ASSOC);
    $account->id              = $user['id'];
    $account->hashed_password = $user['password'];
    $account->name            = $user['name'];
    $account->email           = $user['email'];
    $account->role_id         = $user['role_id'];
    $user_arr                 = [
        'id'      => $account->id,
        'name'    => $account->name,
        'email'   => $account->email,
        'role_id' => $account->role_id,
    ];

    if (password_verify($data->password, $account->hashed_password)) {
        $status              = "Success";
        $message             = "Logged in";
        $response['status']  = $status;
        $response['message'] = $message;
        $response['user']    = $user_arr;
        // array_push($response, $status);
        // array_push($response, $message);
        // array_push($response, $user_arr);
        echo json_encode($response);
    } else {
        //wrong password given
        $message             = "Incorrect email/password combination";
        $status              = "Error";
        $response['status']  = $status;
        $response['message'] = $message;
        echo json_encode($response);
    }

}
