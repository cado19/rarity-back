<?php
// THIS FILE WILL DELIVER A SINGLE issue TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$fleet = new Fleet($db);

// get the id from the url

if (isset($_GET['id'])) {
    $fleet->issue_id = $_GET['id'];
} else {
    die();
}

// get images of the vehicle'S
$result = $fleet->read_issue();

//response array
$response = [];

$issues_arr = [
    // 'id'            => $fleet->id,
    'make'              => $fleet->make,
    'model'             => $fleet->model,
    'number_plate'      => $fleet->number_plate,
    'issue_title'       => $fleet->issue_title,
    'issue_description' => $fleet->issue_description,
    'cost'              => $fleet->resolution_cost,
    'date'              => $fleet->resolution_date,
];
$status  = "Success";
$message = "Successfully retrieved extras";

$response['status']  = $status;
$response['message'] = $message;
$response['issue']   = $issues_arr;

echo json_encode($response);
