<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);
$data    = json_decode(file_get_contents("php://input"), true);

$invoice->booking_id = $data['booking_id'] ?? null;

if (! $invoice->booking_id) {
    echo json_encode([
        "status"  => "Error",
        "message" => "booking_id is required",
    ]);
    exit;
}

$existing = $invoice->findByBookingId();

if ($existing) {
    echo json_encode([
        "status"  => "Success",
        "invoice" => $existing,
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "No invoice found for this booking",
    ]);
}
