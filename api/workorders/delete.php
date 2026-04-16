<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);
$data  = json_decode(file_get_contents("php://input"), true);

// assign work order id
$fleet->work_order_id = $data['work_order_id'];

// delete the work order
$response = $fleet->delete_work_order();

echo json_encode($response);
