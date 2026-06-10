<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/LoanRepayment.php';

$database = new Database();
$db       = $database->connect();

$loan = new LoanRepayment($db);
$data = json_decode(file_get_contents("php://input"), true);

$result = $loan->deleteLoan($data['id']);

echo json_encode(["status" => $result ? "Success" : "Error"]);
