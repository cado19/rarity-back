<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

include_once '../../config/Database.php';
include_once '../../models/Reservation.php';

$database = new Database();
$db       = $database->connect();

$reservation = new Reservation($db);

$data = json_decode(file_get_contents("php://input"));

$reservation->customer_id         = $data->customer_id;
$reservation->vehicle_category_id = $data->vehicle_category_id;
$reservation->start_date          = $data->start_date;
$reservation->end_date            = $data->end_date;
$reservation->opened              = $data->opened ? 1 : 0;

if ($reservation->create()) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Reservation created",
        "data"    => [
            "id" => $reservation->id,
        ],

    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Unable to create reservation",
    ]);
}
