<?php
// THIS FILE WILL DELIVER HANDLE BOOKING CANCELLATION

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);
$fleet   = new Fleet($db);

// get id from url params
// if (isset($_GET['id'])) {
//     $booking->id = $_GET['id'];
// } else {
//     die();
// }

// get data from post request(end_date and id);
$data = json_decode(file_get_contents('php://input'));

$booking->id       = $data->id;
$booking->end_date = $data->end_date;

// get start date so as to calculate the duration
$booking->get_start_date();

$start_date = strtotime($booking->start_date);
$end_date   = strtotime($booking->end_date);
$duration   = ($end_date - $start_date) / 86400;
// check if custom rate was used and use that to calculate total.
$booking->get_custom_rate();

$response = [];

if ($booking->custom_rate == 0) {
    // get vehicle daily rate and multiply by duration to get total
    $booking->get_vehicle_id();
    $fleet->id = $booking->vehicle_id;
    $fleet->get_daily_rate();
    $total          = $fleet->daily_rate * $duration;
    $booking->total = $total;

    if ($booking->extend_booking()) {
        $message             = "Booking successfully extended";
        $status              = "Success";
        $response['message'] = $message;
        $response['status']  = $status;
        echo json_encode($response);
    } else {
        $message             = "An error occured. Please try again later";
        $status              = "Error";
        $response['message'] = $message;
        $response['status']  = $status;
        echo json_encode($response);
    }

} else {
    $total          = $booking->custom_rate * $duration;
    $booking->total = $total;
    if ($booking->extend_booking()) {
        $message             = "Booking successfully extended";
        $status              = "Success";
        $response['message'] = $message;
        $response['status']  = $status;
        echo json_encode($response);
    } else {
        $message             = "An error occured. Please try again later";
        $status              = "Error";
        $response['message'] = $message;
        $response['status']  = $status;
        echo json_encode($response);
    }
}
