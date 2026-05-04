<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Message.php';

$db      = (new Database())->connect();
$message = new Message($db);

$data = json_decode(file_get_contents("php://input"));

$message->conversation_id = $data->conversation_id;
$message->sender_role     = $data->sender_role; // "agent" or "customer"
$message->sender_id       = $data->sender_id;
$message->message         = $data->message;

// echo json_encode($data->sender_role);

$response = [];

if ($message->send()) {
    $response['status']  = "Success";
    $response['message'] = "Message sent";
} else {
    $response['status']  = "Error";
    $response['message'] = "Failed to send message";
}

echo json_encode($response);
