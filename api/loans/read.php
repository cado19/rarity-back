<?php
// Get a single loan structure for a vehicle
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$loan = new Loan($db);
$id   = $_GET['id'] ?? null;

if ($id) {
    $result = $loan->getLoanById($id);
    echo json_encode(["status" => "Success", "data" => $result]);
} else {
    $result = $loan->getAllLoans();
    echo json_encode(["status" => "Success", "data" => $result]);
}
