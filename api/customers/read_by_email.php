<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Customer.php';

$database = new Database();
$db       = $database->connect();

$customer = new Customer($db);

if (isset($_GET['email'])) {
    $customer->email = $_GET['email'];
} else {
    die(json_encode(['status' => 'Error', 'message' => 'Email not provided']));
}

// $customer->read_by_email();

if (! $customer->read_by_email()) {
    echo json_encode([
        'status'  => 'Not Found',
        'message' => 'No customer found with that email',
    ]);
    exit;
}

$response = [];

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

$response['status']   = "Success";
$response['message']  = "Successfully retrieved customer";
$response['customer'] = $customer_arr;

echo json_encode($response);
