<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$repayment = new Loan($db);
$data      = json_decode(file_get_contents("php://input"), true);

$repayment->amount     = $data['amount'];
$repayment->source     = $data['source'];
$repayment->booking_id = $data['booking_id'];
$repayment->paid_at    = $data['paid_at'];

$result = $repayment->updateRepayment($data['id']);

echo json_encode(["status" => $result ? "Success" : "Error"]);
