<?php
// THIS FILE WILL GET BOOKINGS AND CALCULATE COMMISSIONS PER BOOKING IN THE CURRENT MONTH

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Agent.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// print_r($db);

// instantiate blog post object
$booking = new Booking($db);
$agent   = new Agent($db);

// get the id from the url

if (isset($_GET['agent_id'])) {
    $booking->account_id = $_GET['agent_id'];
    $agent->id           = $_GET['agent_id'];
} else {
    die();
}
$response = [];
// vehicles query as a function
$result = $booking->read_agent_complete();

// get row count
$num = $result->rowCount();

//check if any bookings

if ($num > 0) {
    $booking_arr             = [];
    $booking_arr['bookings'] = []; //this is where the data will go

    $total_commission = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // in the row there's category_id as a variable and agent->id we can get the commission type and then if it's a percentage calculate the amount.
        $agent->category_id = $category_id;
        $agent->get_commission_type_and_amount();
        $commission = 0;

        if ($agent->commission_type == "percentage") {
            $commission = ($total * $agent->commission_amount) / 100;
            $total_commission += $commission;
        } else {
            $commission = $agent->commission_amount;
            $total_commission += $commission;
        }

        // single post item array
        $booking_item = [
            'id'         => $id,
            'booking_no' => $booking_no,
            'total'      => $total,
            'commission' => $commission,
            'start_date' => $start_date,
            'end_date'   => $end_date,
        ];

        // push that post item to 'data' index of array
        array_push($booking_arr['bookings'], $booking_item);

    }
    $message                      = "Successfully fetched recent agent's bookings";
    $status                       = "Success";
    $response['bookings']         = $booking_arr['bookings'];
    $response['total_commission'] = $total_commission;
    $response['message']          = $message;
    $response['status']           = $status;
    // convert the posts to json
    echo json_encode($response);
} else {
    // No posts found in the database ($num = 0)
    $message             = "Agent has no earnings";
    $status              = "Error";
    $response['message'] = $message;
    $response['status']  = $status;

    echo json_encode($response);
}
