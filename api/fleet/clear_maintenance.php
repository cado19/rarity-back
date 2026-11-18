<?php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents("php://input"), true);

if (! isset($data['id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing vehicle ID"]);
    exit;
}

$fleet->id = $data['id'];
$result    = $fleet->clear_maintenance($data['user_id']);

echo json_encode($result);
