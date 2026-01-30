<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Reservation.php';

$database = new Database();
$db       = $database->connect();

$reservation     = new Reservation($db);
$reservation->id = isset($_GET['id']) ? $_GET['id'] : die();

$result = $reservation->readOne();
$row    = $result->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode([
        "status" => "Success",
        "data"   => $row,
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Reservation not found",
    ]);
}
