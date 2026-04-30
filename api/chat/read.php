<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Conversation.php';

$db           = (new Database())->connect();
$conversation = new Conversation($db);

$data             = json_decode(file_get_contents("php://input"));
$conversation->id = $data->conversation_id;

$messages = $conversation->get_messages();

echo json_encode([
    "status"   => "Success",
    "messages" => $messages,
]);
