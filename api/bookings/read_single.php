<?php
// THIS FILE WILL DELIVER A SINGLE BOOKING TO EXTERNAL REQUESTS

// include necessary files
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$booking = new Booking($db);

// get the id from the url

if (isset($_GET['id'])) {
    $booking->id = $_GET['id'];
} else {
    die();
}

// get single fleet
$bookingData = $booking->read_single();

if ($bookingData) {
    $response = [
        'status'  => 'Success',
        'message' => 'Successfully retrieved booking',
        'booking' => $bookingData,
    ];
} else {
    $response = [
        'status'  => 'Error',
        'message' => 'Booking not found',
        'booking' => null,
    ];
}

echo json_encode($response);
