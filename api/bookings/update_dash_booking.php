<?php
// THIS FILE WILL UPDATE A BOOKING DRAGGED ON RESOURCE TIMELINE CALENDAR

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
include_once '../../models/Booking.php';
include_once '../../models/Account.php';
include_once '../../models/Fleet.php';
include_once '../../models/Contract.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking  = new Booking($db);
$account  = new Account($db);
$fleet    = new Fleet($db);
$contract = new Contract($db);

$data = json_decode(file_get_contents('php://input'));

$booking->id         = $data->booking_id;
$booking->start_date = $data->start_date;
$booking->end_date   = $data->end_date;

$response = [];

if ($booking->update_dash_dates()) {
    $message             = "Successfully updated booking";
    $status              = "Success";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    $message             = "Could not update booking";
    $status              = "Error";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
}
