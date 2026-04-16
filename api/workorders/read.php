<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);
$data  = json_decode(file_get_contents("php://input"), true);

// assign work order id
$fleet->work_order_id = $_GET['id'];

$response = $fleet->read_work_order_with_items();

echo json_encode($response);
