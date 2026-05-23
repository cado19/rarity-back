<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);
$data    = json_decode(file_get_contents("php://input"), true);

$invoice->booking_id = $data['booking_id'];
$invoice->subject    = $data['subject'] ?? null;
$invoice->due_date   = $data['due_date'] ?? null;
$invoice->terms      = $data['terms'] ?? null;
$invoice->billed_to  = $data['billed_to'] ?? null;

// Check if invoice exists
$existing = $invoice->findByBookingId();

if ($existing) {
    $response = [
        "status"  => "Success",
        "message" => "Invoice already exists",
        "invoice" => $existing,
    ];
} else {
    $created  = $invoice->create();
    $response = [
        "status"  => "Success",
        "message" => "Invoice created successfully",
        "invoice" => $created,
    ];
}

echo json_encode($response);
