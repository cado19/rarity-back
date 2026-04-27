<?php

include_once '../../config/Database.php';
include_once '../../config/cors.php';
include_once '../../models/Booking.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

// Read JSON body
$data = json_decode(file_get_contents("php://input"));

$response = [];

try {
    if (! isset($data->id)) {
        throw new Exception("Missing booking id");
    }
    if (! isset($data->mileage)) {
        throw new Exception("Mileage is required");
    }

    $booking->id         = intval($data->id);
    $booking->vehicle_id = intval($data->vehicle_id ?? 0); // ensure vehicle_id is passed or fetched
    $booking->mileage    = intval($data->mileage);

    if ($booking->complete_booking_with_mileage()) {
        $response['status']  = "Success";
        $response['message'] = "Successfully completed booking and logged mileage";
    } else {
        throw new Exception("Booking completion failed");
    }
} catch (Exception $e) {
    $response['status']  = "Error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
