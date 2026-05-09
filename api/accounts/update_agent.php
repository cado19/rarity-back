<?php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);
$data    = json_decode(file_get_contents("php://input"), true);

$account->id       = $data['id'];
$account->name     = $data['name'];
$account->email    = $data['email'];
$account->phone_no = $data['phone_number'];
$account->country  = $data['country'];
$roleIds           = $data['role_ids'] ?? [];

$response = $account->update_agent_with_roles($roleIds);

echo json_encode($response);
