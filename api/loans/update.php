<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Loan.php';

$database = new Database();
$db       = $database->connect();

$loan = new Loan($db);
$data = json_decode(file_get_contents("php://input"), true);

$loan->principal        = $data['principal'];
$loan->interest_rate    = $data['interest_rate'];
$loan->start_date       = $data['start_date'];
$loan->end_date         = $data['end_date'];
$loan->repayment_method = $data['repayment_method'];

$result = $loan->updateLoan($data['id']);

echo json_encode(["status" => $result ? "Success" : "Error"]);
