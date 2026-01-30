<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Reservation.php';

$database = new Database();
$db       = $database->connect();

$reservation = new Reservation($db);
$result      = $reservation->readAll();

$reservations = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $reservations[] = $row;
}

echo json_encode([
    "status"  => "Success",
    "message" => "Fetched reservations",
    "count"   => count($reservations),
    "data"    => $reservations,
]);
