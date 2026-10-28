<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

$db      = (new Database())->connect();
$booking = new Booking($db);

$data = json_decode(file_get_contents("php://input"));

if (! isset($_GET['booking_id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing booking_id"]);
    exit;
}

$booking->id = $_GET['booking_id'];

$payments = $booking->getBookingPayments();

echo json_encode(["status" => "Success", "payments" => $payments]);
