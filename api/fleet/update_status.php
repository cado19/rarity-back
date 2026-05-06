<?php
// api/fleet/update_status.php

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents("php://input"), true);

if (! isset($data['id']) || ! isset($data['status']) || ! isset($data['user_id'])) {
    echo json_encode(["status" => "Error", "message" => "Missing parameters"]);
    exit;
}

$fleet->id = $data['id'];
$result    = $fleet->update_status($data['status'], $data['user_id']);

echo json_encode($result);
