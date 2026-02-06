<?php
// THIS FILE CALCULATES AN AGENT'S COMMISSIONS FOR A SPECIFIC TIME PERIOD
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Agent.php';

// Instantiate DB and connect
$database = new Database();
$db       = $database->connect();

// instantiate objects
$booking = new Booking($db);
$agent   = new Agent($db);

// read JSON input
$data = json_decode(file_get_contents("php://input"));

// validate agent_id
if (isset($data->agent_id)) {
    $booking->account_id = $data->agent_id;
    $agent->id           = $data->agent_id;
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Missing agent_id",
    ]);
    exit;
}

// validate date range
if (isset($data->from) && isset($data->to)) {
    $booking->start_date = $data->from;
    $booking->end_date   = $data->to;
} else {
                                           // default to current month if not provided
    $booking->start_date = date('Y-m-01'); // first day of current month
    $booking->end_date   = date('Y-m-t');  // last day of current month
}

$response = [];

// query for complete bookings of an agent
$result = $booking->read_agent_complete();

if ($result instanceof PDOException) {
    $response['status']  = "Error";
    $response['message'] = "SQL Error: " . $result->getMessage();
    echo json_encode($response);
    exit;
}

$num = $result->rowCount();

if ($num > 0) {
    $booking_arr             = [];
    $booking_arr['bookings'] = [];

    $total_commission = 0;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // get commission type and amount for this agent/category
        $agent->category_id = $category_id;
        $agent->get_commission_type_and_amount();

        $commission = 0;
        if ($agent->commission_type == "percentage") {
            $commission        = ($total * $agent->commission_amount) / 100;
            $total_commission += $commission;
        } else {
            $commission        = $agent->commission_amount;
            $total_commission += $commission;
        }

        $booking_item = [
            'id'         => $id,
            'booking_no' => $booking_no,
            'total'      => $total,
            'commission' => $commission,
            'start_date' => $start_date,
            'end_date'   => $end_date,
        ];

        $booking_arr['bookings'][] = $booking_item;
    }

    $response['bookings']         = $booking_arr['bookings'];
    $response['total_commission'] = $total_commission;
    $response['message']          = "Successfully fetched agent's bookings";
    $response['status']           = "Success";

    echo json_encode($response);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "Agent has no earnings",
    ]);
}
