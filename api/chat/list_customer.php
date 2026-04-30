<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Conversation.php';

$db           = (new Database())->connect();
$conversation = new Conversation($db);

$data                      = json_decode(file_get_contents("php://input"));
$conversation->customer_id = $data->customer_id;

$conversations = $conversation->get_conversations_by_customer();

echo json_encode([
    "status"        => "Success",
    "conversations" => $conversations,
]);
