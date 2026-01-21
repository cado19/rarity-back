<?php
// THIS FILE WILL DELIVER ALL BOOKINGS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$booking = new Booking($db);

// vehicles query as a function
$result = $booking->read();

// get row count
$num = $result->rowCount();

$response = [];

//check if any posts

if ($num > 0) {
    $booking_arr             = [];
    $booking_arr['bookings'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $booking_item = [
            'id'           => $id,
            'booking_no'   => $booking_no,
            'vehicle'      => $make . ' ' . $model,
            'number_plate' => $number_plate,
            'client'       => $c_fname . ' ' . $c_lname,
            'agent_id'     => $agent_id,
            'status'       => $status,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
        ];

        // push that post item to 'data' index of array
        array_push($booking_arr['bookings'], $booking_item);

    }
    $message             = "Successfully fetched all bookings";
    $status              = "Success";
    $response['data']    = $booking_arr['bookings'];
    $response['message'] = $message;
    $response['status']  = $status;
} else {
    // No bookings found in the database ($num = 0)
    $message             = "No bookings in the database";
    $status              = "Error";
    $response['data']    = [];
    $response['message'] = $message;
    $response['status']  = $status;
}
echo json_encode($response);
