<?php
// THIS FILE WILL DELIVER ALL VEHICLES ISSUES TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$fleet = new Fleet($db);

// vehicle issuess query as a function
$result = $fleet->read_issues();

//response array
$response = [];

// get row count
$num = $result->rowCount();

//check if any vehicle issues
if ($num > 0) {
    $fleet_arr           = [];
    $fleet_arr['issues'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $fleet_item = [
            'id'      => $id,
            'vehicle' => $make . " " . $model . " " . $number_plate,
            'title'   => $title,
            'cost'    => $resolution_cost,
            'date'    => $created_at,
        ];

        // push that post item to 'data' index of array
        array_push($fleet_arr['issues'], $fleet_item);

    }
    $status  = "Success";
    $message = "Successfully retrieved extras";

    $response['status']  = $status;
    $response['message'] = $message;
    $response['issues']  = $fleet_arr['issues'];
} else {
    // No issues found in the database ($num = 0)
    $message             = "No vehicle issues in the database";
    $status              = "Error";
    $response['issues']  = [];
    $response['message'] = $message;
    $response['status']  = $status;
}
echo json_encode($response);
