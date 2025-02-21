<?php
// THIS FILE WILL DELIVER ALL AGENTS TO EXTERNAL REQUESTS

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

// vehicles query as a function
$result = $agent->read();

// get row count
$num = $result->rowCount();

//check if any agents

if ($num > 0) {
    $agent_arr           = [];
    $agent_arr['agents'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $agent_item = [
            'id'       => $id,
            'name'     => $name,
            'email'    => $email,
            'phone_no' => $phone_no,
            'country'  => $country,
        ];

        // push that post item to 'data' index of array
        array_push($agent_arr['agents'], $agent_item);

    }
    // convert the posts to json
    echo json_encode($agent_arr);
} else {
    // No posts found in the database ($num = 0)
    $response = [
        'messsage' => 'No agents found',
    ];
    echo json_encode($response);
}
