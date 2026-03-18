<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Driver.php';

$database = new Database();
$db       = $database->connect();

$driver = new Driver($db);

$data          = json_decode(file_get_contents("php://input"));
$driver->email = $data->email;

$result   = $driver->fetch_driver();
$response = [];

if ($result->rowCount() == 0) {
    $response['status']  = "Error";
    $response['message'] = "Incorrect email/password combination";
    echo json_encode($response);
    exit();
}

$user                    = $result->fetch(PDO::FETCH_ASSOC);
$driver->id              = $user['id'];
$driver->hashed_password = $user['password'];

if (password_verify($data->password, $driver->hashed_password)) {
    $response['status']  = "Success";
    $response['message'] = "Logged in";
    $response['user']    = [
        'id'    => $driver->id,
        'name'  => $user['first_name'] . ' ' . $user['last_name'],
        'email' => $user['email'],
        'role'  => "driver",
    ];
} else {
    $response['status']  = "Error";
    $response['message'] = "Incorrect email/password combination";
}
echo json_encode($response);
