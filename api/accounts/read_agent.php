<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);

if (isset($_GET['id'])) {
    $account->id = $_GET['id'];
} else {
    echo json_encode(["status" => "Error", "message" => "Account ID required"]);
    exit;
}

$details = $account->read_agent_details();

if (! $details) {
    echo json_encode(["status" => "Error", "message" => "Agent not found"]);
    exit;
}

echo json_encode([
    "status"  => "Success",
    "message" => "Successfully retrieved agent",
    "agent"   => $details,
]);
