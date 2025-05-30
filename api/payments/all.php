<?php
// THIS FILE WILL DELIVER ALL PAYMENTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Payment.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$payment = new Payment($db);

// vehicles query as a function
$result = $payment->read();

// get row count
$num = $result->rowCount();

$response = [];

if ($num > 0) {
    $payment_arr             = [];
    $payment_arr['payments'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $payment_item = [
            'id'                => $id,
            'booking_no'        => $booking_no,
            'currency'          => $currency,
            'amount'            => $amount,
            'payment_method'    => $payment_method,
            'payment_account'   => $payment_account,
            'payment_time'      => $payment_time,
            'confirmation_code' => $confirmation_code,
            'order_tracking_id' => $order_tracking_id,
            'message'           => $message,
            'status'            => $status,
        ];

        // push that post item to 'data' index of array
        array_push($payment_arr['payments'], $payment_item);

    }
    $message             = "Successfully fetched recent payments";
    $status              = "Success";
    $response['data']    = $payment_arr['payments'];
    $response['message'] = $message;
    $response['status']  = $status;
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $message             = "No payments in the database";
    $status              = "Error";
    $response['message'] = $message;
    $response['status']  = $status;

    echo json_encode($response);
}
