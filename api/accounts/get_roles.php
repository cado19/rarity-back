<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account     = new Account($db);
$account->id = $_GET['id'] ?? null;

if (! $account->id) {
    echo json_encode(["status" => "Error", "message" => "Account ID required"]);
    exit;
}

$roles = $account->fetch_role_ids_and_names();

echo json_encode([
    "status"     => "Success",
    "account_id" => $account->id,
    "roles"      => $roles,
]);
