<?php
// this file will deliver earned revenue this month to external requests

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

$result = $stat->earned_revenue();

$response = [];

$stat_arr = [
    'total' => $stat->total,
    'month' => $stat->month,
];
$status              = 'Success';
$message             = 'Successfully earnings this month';
$response['status']  = $status;
$response['message'] = $message;
$response['data']    = $stat_arr;

echo json_encode($response);
