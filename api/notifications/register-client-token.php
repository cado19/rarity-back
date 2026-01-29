<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Includes
include_once '../../config/Database.php';
include_once '../../models/Notification.php';

// Instantiate DB & connect
$database = new Database();
$db       = $database->connect();

// Get raw POST data
$data = json_decode(file_get_contents("php://input"));

if (! isset($data->user_id) || ! isset($data->expo_token)) {
    echo json_encode([
        "status"  => "Error",
        "message" => "Missing required fields",
    ]);
    exit();
}

// Instantiate Notification model
$notification             = new Notification($db);
$notification->user_id    = $data->user_id;
$notification->user_type  = "customer"; // fixed for client registration
$notification->expo_token = $data->expo_token;

// Register token
if ($notification->register()) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Client token registered successfully",
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Failed to register client token",
    ]);
}
