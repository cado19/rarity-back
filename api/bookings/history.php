<?php
// api/bookings/history.php

include_once '../../config/Database.php';
include_once '../../config/cors.php';
include_once '../../models/Booking.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : null;
$history   = $booking->get_history($bookingId);

echo json_encode([
    "status" => "Success",
    "count"  => count($history),
    "data"   => $history,
]);
