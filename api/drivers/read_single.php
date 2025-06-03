<?php
// THIS FILE WILL DELIVER A SINGLE DRIVER TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Driver.php';
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog driver object
$driver  = new Driver($db);
$booking = new Booking($db);

// get the id from the url

if (isset($_GET['id'])) {
    $driver->id    = $_GET['id'];
    $booking->d_id = $_GET['id'];
} else {
    die();
}

// get single driver
$driver->read_single();
$result = $booking->upcoming_driver_bookings();
$num    = $result->rowCount();

$response = [];

// create the array
$driver_arr = [
    'id'               => $driver->id,
    'first_name'       => $driver->first_name,
    'last_name'        => $driver->last_name,
    'email'            => $driver->email,
    'dl_no'            => $driver->dl_no,
    'id_no'            => $driver->id_no,
    'phone_no'         => $driver->phone_no,
    'rate_in_capital'  => $driver->rate_in_capital,
    'rate_out_capital' => $driver->rate_out_capital,
    'dl_expiry'        => $driver->dl_expiry,
    'date_of_birth'    => $driver->date_of_birth,
];
$booking_status = "";
$bookings       = [];

if ($num > 0) {

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single booking item
        $booking_item = [
            'booking_no' => $booking_no,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'vehicle'    => $make . " " . $model . " " . $number_plate,
        ];

        array_push($bookings, $booking_item);
    }
    $booking_status = "Success";
} else {
    $booking_status = "Error";
}

$status                     = 'Success';
$message                    = 'Successfully retrieved driver';
$response['status']         = $status;
$response['message']        = $message;
$response['driver']         = $driver_arr;
$response['booking_status'] = $booking_status;
$response['bookings']       = $bookings;

echo json_encode($response);
