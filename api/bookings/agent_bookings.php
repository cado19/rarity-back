<?php
// THIS FILE SHOULD DELIVER AGENT BOOKINGS IN THE CURRENT MONTH TO EXTERNAL REQUESTS

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

// get the id from the url

if (isset($_GET['agent_id'])) {
    $booking->account_id = $_GET['agent_id'];
} else {
    die();
}
$response = [];
// vehicles query as a function
$result = $booking->read_agent();

// get row count
$num = $result->rowCount();

//check if any bookings

if ($num > 0) {
    $booking_arr             = [];
    $booking_arr['bookings'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $booking_item = [
            'id'           => $id,
            'booking_no'   => $booking_no,
            'make'         => $make,
            'model'        => $model,
            'number_plate' => $number_plate,
            'c_fname'      => $c_fname,
            'c_lname'      => $c_lname,
            'status'       => $status,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
        ];

        // push that post item to 'data' index of array
        array_push($booking_arr['bookings'], $booking_item);

    }
    $message              = "Successfully fetched recent agent's bookings";
    $status               = "Success";
    $response['bookings'] = $booking_arr['bookings'];
    $response['message']  = $message;
    $response['status']   = $status;
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $message             = "Agent has no bookings";
    $status              = "Error";
    $response['message'] = $message;
    $response['status']  = $status;

    echo json_encode($response);
}
