<?php
// THIS ENDPOINT RETURNS TOTAL NUMBER OF UNREAD MESSAGES OF A CUSTOMER
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Conversation.php';

$db           = (new Database())->connect();
$conversation = new Conversation($db);

$data                      = json_decode(file_get_contents("php://input"));
$conversation->customer_id = $data->customer_id;

$total_unread = $conversation->get_total_unread_for_customer();

echo json_encode([
    "status"       => "Success",
    "total_unread" => $total_unread,
]);
