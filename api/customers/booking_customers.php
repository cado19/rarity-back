<?php
// THIS FILE WILL DELIVER CUSTOMER DETAILS (ID, FIRST NAME, LAST NAME) TO EXTERNAL REQUESTS

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

$response = [];

// vehicles query as a function
$result = $customer->booking_customers();

// get row count
$num = $result->rowCount();



//check if any posts

if ($num > 0) {
    $customer_arr         = [];
    $customer_arr['customers'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $customer_item = [
            'id'            => $id,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
        ];

        // push that post item to 'data' index of array
        array_push($customer_arr['customers'], $customer_item);

    }
    $status = 'Success';
    $message = 'Successfully fetched clients for booking';
    $response['status'] = $status;
    $response['message'] = $message;
    $response['clients'] = $customer_arr['customers'];
    // convert the posts to json
    echo json_encode($response);
} else {
    // No clients found in the database ($num = 0)
    $response = [
        'messsage' => 'No clients found',
        'status' => 'Error'
    ];
    echo json_encode($response);
}
