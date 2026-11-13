<?php
// api/bookings/revert_status.php

include_once '../../config/Database.php';
include_once '../../config/cors.php';
include_once '../../models/Booking.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

// Read JSON body
$data = json_decode(file_get_contents("php://input"));

if (! isset($data->booking_id) || ! isset($data->user_id)) {
    http_response_code(400);
    echo json_encode([
        "status"  => "Error",
        "message" => "Missing booking_id or user id",
    ]);
    exit;
}

$booking->id         = $data->booking_id;
$booking->account_id = $data->user_id;
$revertedStatus      = $booking->revert_status();

if ($revertedStatus) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Booking status reverted",
        "old"     => $revertedStatus['old_status'],
        "new"     => $revertedStatus['new_status'],
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        "status"  => "Error",
        "message" => "Unable to revert booking",
    ]);
}
