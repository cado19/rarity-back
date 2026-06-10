<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$repayment               = new LoanR($db);
$repayment->repayment_id = $_GET['repayment_id'] ?? null;

// if (!$repayment_id) {
//     echo json_encode(["status" => "Error", "message" => "repayment_id is required"]);
//     exit;
// }

$result = $repayment->getRepaymentById();

if ($result) {
    echo json_encode(["status" => "Success", "data" => $result]);
} else {
    echo json_encode(["status" => "Error", "message" => "Repayment not found"]);
}
