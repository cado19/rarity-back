<?php
// Update a vehicle's requirement
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents("php://input"), true);

// requirement id (primary key)
$fleet->req_id            = $data['id'];
$fleet->req_title         = $data['req_title'];
$fleet->req_description   = $data['req_description'];
$fleet->req_priority      = $data['req_priority'];
$fleet->req_status        = $data['req_status'];
$fleet->req_category      = $data['req_category'];
$fleet->req_assigned_to   = $data['agent_id'];
$fleet->req_cost_estimate = $data['req_cost_estimate'];
$fleet->req_actual_cost   = $data['req_actual_cost'];
$fleet->req_due_date      = $data['req_due_date'];
$fleet->req_completed_at  = $data['req_completed_at'];
$fleet->req_notes         = $data['req_notes'];

$response = $fleet->update_requirement();

echo json_encode($response);
