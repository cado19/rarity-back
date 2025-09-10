<?php
// THIS FILE WILL DELIVER A SINGLE customerS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Customer.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog customer object
$customer = new Customer($db);

// get the id from the url

if (isset($_GET['id'])) {
    $customer->id = $_GET['id'];
} else {
    die();
}

// get single customer
$customer->read_single();

$response = [];

// create the array
$customer_arr = [
    'id'                  => $customer->id,
    'first_name'          => $customer->first_name,
    'last_name'           => $customer->last_name,
    'email'               => $customer->email,
    'id_type'             => $customer->id_type,
    'id_no'               => $customer->id_no,
    'phone_no'            => $customer->phone_no,
    'dl_no'               => $customer->dl_no,
    'dl_expiry'           => $customer->dl_expiry,
    'residential_address' => $customer->residential_address,
    'work_address'        => $customer->work_address,
    'date_of_birth'       => $customer->date_of_birth,
    'id_image'            => $customer->id_image,
    'id_back_image'       => $customer->id_back_image,
    'profile_image'       => $customer->profile_image,
    'license_image'       => $customer->license_image,
];

$status  = "Success";
$message = "Successfully retrieved booking";

$response['status']   = $status;
$response['message']  = $message;
$response['customer'] = $customer_arr;

echo json_encode($response);
