<?php
// this file will deliver number of bookings this month to external requests

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../../config/Database.php';
include_once '../../../models/Analytics.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$stat = new Analytics($db);

$result = $stat->booking_count_this_month();

$response = [];

$stat_arr = [
    'total' => $stat->total,
];
$status              = 'Success';
$message             = 'Successfully booking count this month';
$response['status']  = $status;
$response['message'] = $message;
$response['data']    = $stat_arr;

echo json_encode($response);
