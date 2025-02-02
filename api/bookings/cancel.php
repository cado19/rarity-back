<?php
// THIS FILE WILL DELIVER HANDLE BOOKING CANCELLATION

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

// get id from url params
if (isset($_GET['id'])) {
    $booking->id = $_GET['id'];
} else {
    die();
}

$response = [];

if ($booking->cancel_booking()) {
    $message             = "Successfully cancelled booking";
    $status              = "Success";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    $message             = "An error occured";
    $status              = "Error";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
}
