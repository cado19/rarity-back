<?php
// THIS FILE WILL HANDLE CUSTOMER DELETION

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Customer.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$customer = new Customer($db);

// get id from url params
if (isset($_GET['id'])) {
    $customer->id = $_GET['id'];
} else {
    die();
}

$response = [];

if ($customer->delete_customer()) {
    $message             = "Successfully deleted customer";
    $status              = "Success";
    $response['status']  = $status;
    $response['message'] = $message;
} else {
    $message             = "An error occured";
    $status              = "Error";
    $response['status']  = $status;
    $response['message'] = $message;
}
echo json_encode($response);
