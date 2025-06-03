<?php
// THIS FILE WILL DELIVER ID, MAKE, MODEL AND NUMBER PLATE OF VEHICLES TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$driver = new Driver($db);

//response object
$response = [];

// vehicles query as a function
$result = $driver->workplan_drivers();

// get row count
$num = $result->rowCount();

//check if any posts

if ($num > 0) {
    $driver_arr            = [];
    $driver_arr['drivers'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $driver_item = [
            'id'    => $id,
            'title' => $title,
        ];

        // push that post item to 'data' index of array
        array_push($driver_arr['drivers'], $driver_item);

    }
    $status              = "Success";
    $message             = "Successfully retrieved drivers";
    $response['status']  = $status;
    $response['message'] = $message;
    $response['drivers'] = $driver_arr['drivers'];
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $status              = "Error";
    $message             = "No drivers found in the database";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
}
