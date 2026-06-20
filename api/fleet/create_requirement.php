<?php
// Save a vehicle's requirements to the database
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$fleet = new Fleet($db);

$data = json_decode(file_get_contents("php://input"), true);

$fleet->id                = $data['vehicle_id'];
$fleet->req_title         = $data['req_title'];
$fleet->req_description   = $data['req_description'];
$fleet->req_priority      = $data['req_priority'];
$fleet->req_status        = $data['req_status'];
$fleet->req_category      = $data['req_category'];
$fleet->agent_id          = $data['req_assigned_to'] ?: null;
$fleet->req_cost_estimate = $data['req_cost_estimate'] ?: null;
$fleet->req_actual_cost   = $data['req_actual_cost'] ?: null;
$fleet->req_due_date      = $data['req_due_date'] ?: null;
$fleet->req_completed_at  = $data['req_completed_at'] ?: null;
$fleet->req_notes         = $data['req_notes'] ?: null;

$response = $fleet->save_requirements();

echo json_encode($response);
