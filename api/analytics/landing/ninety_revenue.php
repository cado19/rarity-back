<?php
// this file will deliver number of bookings this month to external requests

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../../config/Database.php';
include_once '../../../models/Analytics.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$stat = new Analytics($db);

$result = $stat->revenue_ninety_days();

// get row count
$num = $result->rowCount();

if ($num > 0) {
    $stat_arr['data'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $stat_item = [
            'month' => $Month,
            'total' => $Total,
        ];

        // push that post item to 'data' index of array
        array_push($stat_arr['data'], $stat_item);

    }
    $message             = "Successfully fetched revenue last 3 months";
    $status              = "Success";
    $response['data']    = $stat_arr['data'];
    $response['message'] = $message;
    $response['status']  = $status;
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $message             = "No revenue in the database";
    $status              = "Error";
    $response['message'] = $message;
    $response['status']  = $status;

    echo json_encode($response);
}
