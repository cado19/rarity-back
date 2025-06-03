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

$booking->id   = $data->id;
$booking->fuel = $data->fuel;

$response = [];

if ($booking->update_fuel()) {
    $message             = "Fuel successfully updated";
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
