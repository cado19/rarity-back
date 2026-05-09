<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$database = new Database();
$db       = $database->connect();

$account = new Account($db);
$roles   = $account->fetch_all_roles();

echo json_encode([
    "status" => "Success",
    "roles"  => $roles,
]);
