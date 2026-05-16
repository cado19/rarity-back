<?php
// THIS FILE WILL DELIVER HANDLE BOOKING CANCELLATION

// include necessary files
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

// Expect JSON body
$data = json_decode(file_get_contents("php://input"), true);

// echo json_encode($data);

// get id from url params
if (! isset($data['id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing parameters"]);
    exit;
}

$booking->id = $data['id'];

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
