<?php
// THIS FILE WILL DELIVER ALL POSTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Account.php';
include_once '../../models/Fleet.php';
include_once '../../models/Contract.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);
$account = new Account($db);
$fleet   = new Fleet($db);
$contract = new Contract($db);

// 1. get the custom rate or check if it is 0
// 2. if custom rate is greater than 0, get the vehicle's category_id
// 3. get the agent's rate
// 4. if custom rate is lower than agent rate throw an error
// 5. if custom rate is 0 pick up vehicle's daily rate from vehicle_id
// 6. multiply duration by daily rate

$data = json_decode(file_get_contents('php://input'));

// properties of booking class
$booking->c_id = $data->customer_id;
$booking->vehicle_id  = $data->vehicle_id;
$booking->d_id        = $data->driver_id;
$booking->start_date  = $data->start_date;
$booking->end_date    = $data->end_date;
$booking->start_time  = $data->start_time;
$booking->end_time    = $data->end_time;
$booking->account_id  = $data->account_id;

// properties of fleet class
$fleet->id = $data->vehicle_id;

// properties of account class
$account->id = $data->account_id;

// get the duration of the booking
$start_date = strtotime($data->start_date);
$end_date   = strtotime($data->end_date);
$duration   = ($end_date - $start_date) / 86400;

$response = [];

if (! empty($data->custom_rate)) {
                        // $booking->custom_rate = $data->custom_rate;
                        // get the category_id of the vehicle
    $fleet->category(); // running this sets category_id of fleet class

    // assign category_id of fleet class to category_id of account class
    $account->category_id = $fleet->category_id;

    // next get agent rate
    $account->fetch_agent_rate();

    //next get role_id
    $account->fetch_role_id();

    if ($data->custom_rate == 0 && $account->role_id == 2) {
        $message = "You cannot set custom rate for this category. Contact Admin";
        $status  = "Error";
        array_push($response, $status);
        array_push($response, $message);
        echo json_encode($response);
    } elseif ($data->custom_rate < $account->agent_rate) {
        $message = "Set amount is too low. Min for selected vehicle: $account->agent_rate";
        $status  = "Error";
        array_push($response, $status);
        array_push($response, $message);
        echo json_encode($response);
    } else {
        $total                = $data->custom_rate * $duration;
        $booking->total       = $total;
        $booking->custom_rate = $data->custom_rate;
        if ($booking->create_custom_booking()) {
            $no                  = "B-" . str_pad($booking->id, 3, "0", STR_PAD_LEFT);
            $booking->booking_no = $no;
            $booking->save_booking_number();
            // create contract
            $contract->booking_id = $booking->id;
            $contract->create();
            // return response 
            $message = "Successfully created booking";
            $status = "Success";
            $booking_id = $booking->id;

            $response['message'] = $message;
            $response['status'] = $status;
            $response['booking_id'] = $booking_id;
            echo json_encode($response);
        } else {
            $message = "An error occured. Try again later";
            $status  = "Error";
            array_push($response, $status);
            array_push($response, $message);
            echo json_encode($response);
        }

    }

} else {
    // custom rate has not been set
    $fleet->get_daily_rate();
    $booking->total       = $fleet->daily_rate * $duration;
    $booking->custom_rate = 0;
    if ($booking->create_booking()) {
        $no                  = "B-" . str_pad($booking->id, 3, "0", STR_PAD_LEFT);
        $booking->booking_no = $no;
        $booking->save_booking_number();

        // create contract
        $contract->booking_id = $booking->id;
        $contract->create();
        // return response 
        $message = "Successfully created booking";
        $status = "Success";
        $booking_id = $booking->id;

        $response['message'] = $message;
        $response['status'] = $status;
        $response['booking_id'] = $contract->booking_id;
        echo json_encode($response);
    } else {
        $message = "An error occured. Try again later";
        $status  = "Error";
        array_push($response, $status);
        array_push($response, $message);
        echo json_encode($response);
    }

}
