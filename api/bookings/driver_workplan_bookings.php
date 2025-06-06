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
$result = $booking->driver_booking_workplan();

// get row count
$num = $result->rowCount();

//check if any posts

if ($num > 0) {
    $booking_arr             = [];
    $booking_arr['bookings'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        // set the background color of the event based on status of the booking
        if ($status == 'active') {
            $color     = 'blue';
            $textColor = 'white';
        } elseif ($status == 'upcoming') {
            $color     = 'green';
            $textColor = 'white';
        } elseif ($status == 'complete') {
            $color     = 'yellow';
            $textColor = 'black';
        } else {
            $color = 'red';
        }

        // single post item array
        $booking_item = [
            'id'         => $id,
            'title'      => $title,
            'resourceId' => $group,
            // 'make'         => $make,
            // 'model'        => $model,
            // 'number_plate' => $number_plate,
            // 'c_fname'       => $c_fname,
            // 'c_lname'       => $c_lname,
            // 'status'     => $status,
            'start'      => $start_time,
            'end'        => $end_time,
            'color'      => $color, // the color represents the status of the booking
            'textColor'  => $textColor,
        ];

        // push that post item to 'data' index of array
        array_push($booking_arr['bookings'], $booking_item);

    }
    // convert the posts to json
    echo json_encode($booking_arr);
} else {
    // No posts found in the database ($num = 0)
    $response = [
        'messsage' => 'No bookings found',
    ];
    echo json_encode($response);
}
