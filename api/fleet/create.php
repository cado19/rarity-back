<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

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

$fleet->make           = $data->make;
$fleet->model          = $data->model;
$fleet->number_plate   = $data->number_plate;
$fleet->category_id    = $data->category_id;
$fleet->transmission   = $data->transmission;
$fleet->fuel           = $data->fuel;
$fleet->drive_train    = $data->drive_train;
$fleet->colour         = $data->colour;
$fleet->seats          = $data->seats;
$fleet->daily_rate     = $data->daily_rate;
$fleet->vehicle_excess = $data->vehicle_excess;

$response = [];

//check whether the number plate is unique
$result = $fleet->check_unique_number_plate();

if ($result->rowCount() > 0) {
    $status              = "Error";
    $message             = "A vehicle exists with this number plate.";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    if ($fleet->create()) {
        // create the vehicle pricing
        if ($fleet->create_pricing()) {
            $status                 = "Success";
            $message                = "Vehicle Created";
            $response['status']     = $status;
            $response['message']    = $message;
            $response['vehicle_id'] = $fleet->id;
            echo json_encode($response);
        } else {
            $status              = "Error";
            $message             = "An error occured saving vehicle pricing details.";
            $response['status']  = $status;
            $response['message'] = $message;
            echo json_encode($response);
        }

    } else {
        $status              = "Error";
        $message             = "An error occured saving vehicle details.";
        $response['status']  = $status;
        $response['message'] = $message;
        echo json_encode($response);
    }

}
