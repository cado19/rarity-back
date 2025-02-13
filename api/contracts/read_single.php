<?php
// THIS FILE WILL DELIVER A SINGLE contract TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Contract.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$contract = new Contract($db);

// get the id from the url

if (isset($_GET['id'])) {
    $contract->booking_id = $_GET['id'];
} else {
    die();
}

// get single contract
$contract->read_single();

$response = [];

$contract_arr = [
    'booking_no'   => $contract->booking_no,
    'customer_id'  => $contract->c_id,
    'c_fname'      => $contract->c_fname,
    'c_lname'      => $contract->c_lname,
    'c_residential_address'      => $contract->c_residential_address,
    'd_fname'      => $contract->d_fname,
    'd_lname'      => $contract->d_lname,
    'd_phone_no'   => $contract->d_phone_no,
    'start_date'   => $contract->start_date,
    'end_date'     => $contract->end_date,
    'start_time'   => $contract->start_time,
    'end_time'     => $contract->end_time,
    'status'       => $contract->status,
    'ct_status'    => $contract->ct_status,
    'make'         => $contract->make,
    'model'        => $contract->model,
    'number_plate' => $contract->number_plate,
    'vehicle_excess' => $contract->vehicle_excess,
    'custom_rate'  => $contract->custom_rate,
    'daily_rate'   => $contract->daily_rate,
    'total'        => $contract->total,
    'signature'    => $contract->signature,
    'created_at'   => $contract->created_at,
];
$status  = "Success";
$message = "Successfully retrieved contract";

$response['status']  = $status;
$response['message'] = $message;
$response['contract'] = $contract_arr;

echo json_encode($response);
