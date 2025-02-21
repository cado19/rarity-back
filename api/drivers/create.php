<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for driver request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate driver object
$driver = new Driver($db);

$response = [];

// Get the raw posted data
$data = json_decode(file_get_contents("php://input"));

// var_dump($data);

// echo json_encode($data);

// echo $data->f_name;

$driver->first_name    = $data->f_name;
$driver->last_name     = $data->l_name;
$driver->email         = $data->email;
$driver->id_no         = $data->id_number;
$driver->phone_no      = $data->phone_number;
$driver->dl_no         = $data->dl_number;
$driver->dl_expiry     = $data->dl_expiry;
$driver->date_of_birth = $data->date_of_birth;

// echo json_encode($driver->last_name);

//check if client exists in db with associated email
$result = $driver->check_unique_email();

if ($result->rowCount() > 0) {
    $status              = "Error";
    $message             = "A driver exists with this email.";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    // code...
    if ($driver->create()) {
        $status                = "Success";
        $message               = "Driver Created";
        $response['status']    = $status;
        $response['message']   = $message;
        $response['driver_id'] = $driver->id;

        echo json_encode($response);

    } else {
        $status              = "Error";
        $message             = "Driver Not Created.";
        $response['status']  = $status;
        $response['message'] = $message;

        echo json_encode($response);

    }
}
