<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

$db      = (new Database())->connect();
$booking = new Booking($db);

$data = json_decode(file_get_contents("php://input"));

if (! isset($data->booking_id) || ! isset($data->amount)) {
    echo json_encode(["status" => "Error", "message" => "Missing booking_id or amount"]);
    exit;
}

$booking->id             = $data->booking_id;
$booking->payment_amount = $data->amount;
$booking->payment_ref    = $data->ref ?? null;

$result = $booking->createBookingPayment();

echo json_encode(["status" => "Success", "payment" => $result]);
