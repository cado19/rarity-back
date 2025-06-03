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
$booking->read_single();

$response = [];

$booking_arr = [
    'booking_no'   => $booking->booking_no,
    'customer_id'  => $booking->c_id,
    'c_fname'      => $booking->c_fname,
    'c_lname'      => $booking->c_lname,
    'd_fname'      => $booking->d_fname,
    'd_lname'      => $booking->d_lname,
    'start_date'   => $booking->start_date,
    'end_date'     => $booking->end_date,
    'start_time'   => $booking->start_time,
    'end_time'     => $booking->end_time,
    'status'       => $booking->status,
    'ct_status'    => $booking->ct_status,
    'daily_rate'   => $booking->daily_rate,
    'custom_rate'  => $booking->custom_rate,
    'total'        => $booking->total,
    'make'         => $booking->make,
    'model'        => $booking->model,
    'number_plate' => $booking->number_plate,
    'agent'        => $booking->agent,
];
$status  = "Success";
$message = "Successfully retrieved booking";

$response['status']  = $status;
$response['message'] = $message;
$response['booking'] = $booking_arr;

echo json_encode($response);
