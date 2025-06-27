<?php
// THIS FILE WILL DELIVER ALL DELETED VEHICLES TO EXTERNAL REQUESTS

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

// vehicles query as a function
$result = $fleet->deleted_vehicles();

// get row count
$num = $result->rowCount();

//response array
$response = [];

//check if any posts

if ($num > 0) {
    $fleet_arr             = [];
    $fleet_arr['vehicles'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $fleet_item = [
            'id'            => $id,
            'make'          => $make,
            'model'         => $model,
            'number_plate'  => $number_plate,
            'daily_rate'    => $daily_rate,
            'category_name' => $category_name,
        ];

        // push that post item to 'data' index of array
        array_push($fleet_arr['vehicles'], $fleet_item);

    }
    $message             = "Successfully fetched deleted vehicles";
    $status              = "Success";
    $response['data']    = $fleet_arr['vehicles'];
    $response['message'] = $message;
    $response['status']  = $status;
} else {
    // No posts found in the database ($num = 0)
    $message = "No deleted vehicles";
    $status  = "Success";
}
echo json_encode($response);
