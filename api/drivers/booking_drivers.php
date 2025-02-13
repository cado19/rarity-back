<?php
// THIS FILE WILL DELIVER CUSTOMER DETAILS (ID, FIRST NAME, LAST NAME) TO EXTERNAL REQUESTS

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

$response = [];

// vehicles query as a function
$result = $driver->booking_drivers();

// get row count
$num = $result->rowCount();



//check if any posts

if ($num > 0) {
    $driver_arr         = [];
    $driver_arr['drivers'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $driver_item = [
            'id'            => $id,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
        ];

        // push that post item to 'data' index of array
        array_push($driver_arr['drivers'], $driver_item);

    }
    $status = 'Success';
    $message = 'Successfully fetched drivers for booking';
    $response['status'] = $status;
    $response['message'] = $message;
    $response['drivers'] = $driver_arr['drivers'];
    // convert the posts to json
    echo json_encode($response);
} else {
    // No clients found in the database ($num = 0)
    $response = [
        'messsage' => 'No drivers found',
        'status' => 'Error'
    ];
    echo json_encode($response);
}
