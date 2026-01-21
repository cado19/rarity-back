<?php
// THIS FILE WILL DELIVER CUSTOMER DETAILS (ID, FIRST NAME, LAST NAME) TO EXTERNAL REQUESTS

function safe_json_encode($value, $options = 0, $depth = 512) {
    $encoded = json_encode($value, $options | JSON_INVALID_UTF8_SUBSTITUTE, $depth);
    if ($encoded === false) {
        return json_encode([
            'status' => 'Error',
            'message' => 'JSON encoding failed',
            'error' => json_last_error_msg()
        ]);
    }
    return $encoded;
}


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
    $status = 'Error';
    $response['message'] = 'No clients found';
    $response['status'] = $status;
    $response['num'] = $num;

    echo json_encode($response);
}
