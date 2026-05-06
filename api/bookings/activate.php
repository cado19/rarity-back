<?php
// SCRIPT HANDLING BOOKING ACTIVATION

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

// Expect JSON body
$data = json_decode(file_get_contents("php://input"), true);

// echo json_encode($data);

if (! isset($data['id']) || ! isset($data['user_id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing parameters"]);
    exit;
}

$booking->id         = $data['id'];
$booking->account_id = $data['user_id'];

$response = [];

if ($booking->activate_booking()) {
    $response['status']  = "Success";
    $response['message'] = "Successfully activated booking";
} else {
    $response['status']  = "Error";
    $response['message'] = "An error occurred";
}

echo json_encode($response);
