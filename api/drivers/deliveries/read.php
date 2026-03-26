<?php
include_once '../../../config/cors.php';
include_once '../../../config/Database.php';
include_once '../../../models/Driver.php';

$database = new Database();
$db       = $database->connect();

$driver = new Driver($db);

// Get driver_id from query string
$driverId = isset($_GET['driver_id']) ? $_GET['driver_id'] : null;

$result = $driver->read_deliveries($driverId);
$num    = $result->rowCount();

$response = [];

if ($num > 0) {
    $delivery_arr               = [];
    $delivery_arr['deliveries'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $delivery_item = [
            'id'           => $id,
            'booking_id'   => $booking_id,
            'booking_no'   => $booking_no,
            'driver_id'    => $driver_id,
            'delivered'    => $delivered,
            'delivered_at' => $delivered_at,
            'start_date'   => $start_date,
            'end_date'     => $end_date,
        ];

        array_push($delivery_arr['deliveries'], $delivery_item);
    }

    $response['status']  = "Success";
    $response['message'] = "Successfully fetched driver deliveries";
    $response['data']    = $delivery_arr['deliveries'];
} else {
    $response['status']  = "Error";
    $response['message'] = "No deliveries found for this driver";
    $response['data']    = [];
}

echo json_encode($response);
