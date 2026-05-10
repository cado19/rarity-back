<?php
// THIS FILE WILL DELIVER CUSTOMER DETAILS (ID, FIRST NAME, LAST NAME) TO EXTERNAL REQUESTS

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Driver.php';

$database = new Database();
$db       = $database->connect();

$driver = new Driver($db);

$drivers = $driver->booking_drivers();

if (! empty($drivers)) {
    $response = [
        'status'  => 'Success',
        'message' => 'Successfully fetched drivers for booking',
        'drivers' => $drivers,
    ];
} else {
    $response = [
        'status'  => 'Error',
        'message' => 'No drivers found',
        'drivers' => [],
    ];
}

echo json_encode($response);
