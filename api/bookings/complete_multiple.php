<?php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

$data = json_decode(file_get_contents('php://input'));

$ids = $data->booking_ids ?? [];

if (empty($ids)) {
    echo json_encode([
        "status"  => "Error",
        "message" => "No bookings selected",
    ]);
    exit;
}

if ($booking->complete_multiple($ids)) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Bookings completed successfully",
    ]);
}
