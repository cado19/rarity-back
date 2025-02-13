<?php
// THIS FILE WILL DELIVER A SINGLE BOOKING TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
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
$booking->get_voucher_details();

$response = [];

$booking_arr = [
    'booking_no'   => $booking->booking_no,
    'c_fname'      => $booking->c_fname,
    'c_lname'      => $booking->c_lname,
    'start_date'   => $booking->start_date,
    'end_date'     => $booking->end_date,
    'start_time'   => $booking->start_time,
    'end_time'     => $booking->end_time,
    'daily_rate'   => $booking->daily_rate,
    'total'        => $booking->total,
    'make'         => $booking->make,
    'model'        => $booking->model,
    'number_plate' => $booking->number_plate,
];
$status  = "Success";
$message = "Successfully retrieved booking voucher";

$response['status']  = $status;
$response['message'] = $message;
$response['booking'] = $booking_arr;

echo json_encode($response);
