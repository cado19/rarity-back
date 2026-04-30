<?php
header('Content-Type: application/json');
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Conversation.php';
include_once '../../models/Message.php';

$db           = (new Database())->connect();
$conversation = new Conversation($db);
$message      = new Message($db);

$data        = json_decode(file_get_contents("php://input"));
$agent_id    = $data->agent_id;
$customer_id = $data->customer_id;
$initial_msg = $data->message ?? "Hello, how are you"; // default if none provided

// Check if conversation already exists
$existing = $conversation->find_existing($agent_id, $customer_id);

if ($existing) {
    $conversation_id = $existing;
} else {
    // Create new conversation
    $conversation->agent_id    = $agent_id;
    $conversation->customer_id = $customer_id;
    $conversation_id           = $conversation->create();

    // Insert first message immediately
    $message->conversation_id = $conversation_id;
    $message->sender_role     = "agent";
    $message->sender_id       = $agent_id;
    $message->message         = $initial_msg;
    $message->send();
}

echo json_encode([
    "status"          => "Success",
    "conversation_id" => $conversation_id,
]);
