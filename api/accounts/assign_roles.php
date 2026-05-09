<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);
$data    = json_decode(file_get_contents("php://input"), true);

$account->id = $data['account_id'];
$roleIds     = $data['role_ids'] ?? [];

$response = $account->assign_roles($roleIds);

// Merge legacy + new roles for response
$allRoles          = $account->fetch_all_roles();
$response['roles'] = $allRoles;

echo json_encode($response);
