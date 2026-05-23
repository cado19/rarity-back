<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);
$data    = json_decode(file_get_contents("php://input"), true);

// Set invoice + payment fields from request
$invoice->id           = $data['invoice_id'] ?? $data['id'];
$invoice->amount       = $data['amount'];
$invoice->payment_mode = $data['payment_mode'];
$invoice->payment_code = $data['payment_code'] ?? null;
$invoice->notes        = $data['notes'] ?? null;

// Add payment
$updated = $invoice->addPayment();

if ($updated) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Payment recorded successfully",
        "invoice" => $updated,
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Failed to record payment",
    ]);
}
