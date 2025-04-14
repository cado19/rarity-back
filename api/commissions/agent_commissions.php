<?php
// THIS FILE WILL PROVIDE AN AGENT'S COMMISSION TYPES AND COMMISSION AMOUNTS FOR VARIOUS VEHICLE CATEGORIES

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Agent.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$agent = new Agent($db);
// get the id from the url

if (isset($_GET['agent_id'])) {
    $agent->id = $_GET['agent_id'];
} else {
    die();
}

// vehicles query as a function
$result = $agent->get_agent_commissions();

// get row count
$num = $result->rowCount();

$response = [];

//check if any agents

if ($num > 0) {
    $agent_arr         = [];
    $agent_arr['data'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $agent_item = [
            'name'              => $name,
            'commission_type'   => $commission_type,
            'commission_amount' => $commission_amount,
        ];

        // push that post item to 'data' index of array
        array_push($agent_arr['data'], $agent_item);

    }
    $message             = "Successfully agent's commission plan";
    $status              = "Success";
    $response['data']    = $agent_arr['data'];
    $response['message'] = $message;
    $response['status']  = $status;
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $message             = "Agent has no commission plans";
    $status              = "Error";
    $response['message'] = $message;
    $response['status']  = $status;

    echo json_encode($response);
}
