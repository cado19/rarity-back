<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);

$invoices = $invoice->getAll();

echo json_encode([
    "status"   => "Success",
    "invoices" => $invoices,
]);
