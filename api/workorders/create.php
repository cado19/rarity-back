<?php
// SAVE A WORK ORDER TO THE DATABASE
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);
$data  = json_decode(file_get_contents("php://input"), true);

// assign properties
$fleet->id                        = $data['vehicle_id'];
$fleet->work_order_title          = $data['title'];
$fleet->work_order_description    = $data['description'];
$fleet->work_order_status         = $data['status'];
$fleet->work_order_mileage        = $data['mileage'];
$fleet->work_order_service        = $data['service'];
$fleet->work_order_scheduled_date = $data['scheduled_date'];
$fleet->work_order_labor_cost     = $data['labor_cost'];
$fleet->work_order_parts_cost     = $data['parts_cost'];

// create the work order
$response = $fleet->create_work_order($data['items'] ?? []);

echo json_encode($response);
