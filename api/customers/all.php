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

$response = [];


//check if any posts

if ($num > 0) {
    $customers_arr         = [];
    $customers_arr['data'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $customer_item = [
            'id'            => $id,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'id_no'         => $id_no,
            'phone_no'      => $phone_no,
        ];

        array_push($customers_arr['data'], $customer_item);
    }
    $message = "Successfully fetched recent clients";
    $status = "Success";
    $response['data'] = $customers_arr['data'];
    $response['message'] = $message;
    $response['status'] = $status;
    // convert the posts to json
    echo json_encode($response);

} else {
    // No posts found in the database ($num = 0)
    $message = "Could not fetch recent clients";
    $status = "Error";
    $response['messsage'] = $message;
    $response['status'] = $status;
    echo json_encode($response);
}
