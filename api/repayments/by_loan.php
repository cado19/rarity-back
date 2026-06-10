<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$repayment          = new Loan($db);
$repayment->loan_id = $_GET['loan_id'];

$result = $repayment->getRepaymentsByLoan();

echo json_encode(["status" => "Success", "data" => $result]);
