<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);
$data    = json_decode(file_get_contents("php://input"), true);

// Set invoice id from request
$invoice->id = $data['invoice_id'] ?? $data['id'];

// Fetch payments
$payments = $invoice->getPayments();

if ($payments) {
    echo json_encode([
        "status"   => "Success",
        "payments" => $payments,
    ]);
} else {
    echo json_encode([
        "status"   => "Success",
        "payments" => [],
    ]);
}
