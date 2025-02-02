<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Customer.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$customer = new Customer($db);

// vehicles query as a function
$result = $customer->read();

// get row count
$num = $result->rowCount();

//check if any posts

if ($num > 0) {
    $customers_arr         = [];
    $customers_arr['data'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $post_item = [
            'id'            => $id,
            'make'          => $make,
            'model'         => $model,
            'number_plate'  => $number_plate,
            'daily_rate'    => $daily_rate,
            'category_name' => $category_name,
        ];

        // push that post item to 'data' index of array
        array_push($customers_arr['data'], $post_item);

    }
    // convert the posts to json
    echo json_encode($customers_arr);
} else {
    // No posts found in the database ($num = 0)
    $response = [
        'messsage' => 'No customers found',
    ];
    echo json_encode($response);
}
