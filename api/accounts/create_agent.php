<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);
$data    = json_decode(file_get_contents("php://input"), true);

$response = $account->create_agent_with_roles($data, $data['role_ids'] ?? []);

echo json_encode($response);
