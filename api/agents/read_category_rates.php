<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Account.php';

$db      = (new Database())->connect();
$account = new Account($db);

if (! isset($_GET['agent_id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing agent_id"]);
    exit;
}

$account->id = $_GET['agent_id'];

$category_rates = $account->fetch_agent_rates();

echo json_encode(["status" => "Success", "category_rates" => $category_rates]);
