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
include_once '../../models/Customer.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate customer object
$customer = new Customer($db);

$response = [];

// Get the raw posted data
$data = json_decode(file_get_contents("php://input"));

// var_dump($data);

// echo json_encode($data);

// echo $data->f_name;

$customer->id                  = $data->id;
$customer->first_name          = $data->f_name;
$customer->last_name           = $data->l_name;
$customer->email               = $data->email;
$customer->id_type             = $data->id_type;
$customer->id_no               = $data->id_number;
$customer->phone_no            = $data->phone_number;
$customer->dl_number           = $data->dl_number;
$customer->dl_expiry           = $data->dl_expiry;
$customer->residential_address = $data->residential_address;
$customer->work_address        = $data->work_address;
$customer->date_of_birth       = $data->date_of_birth;

// code to update customer..
if ($customer->update_customer()) {
    $status              = "Success";
    $message             = "Customer updated";
    $response['status']  = $status;
    $response['message'] = $message;

} else {
    $status              = "Error";
    $message             = "Customer Not Updated.";
    $response['status']  = $status;
    $response['message'] = $message;

}
echo json_encode($response);
