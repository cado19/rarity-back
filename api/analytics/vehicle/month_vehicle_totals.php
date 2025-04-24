<?php
// THIS FILE WILL DELIVER ALL VEHICLE MONTHLY TOTALS TO EXTERNAL REQUESTS

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
include_once '../../../config/Database.php';
include_once '../../../models/Analytics.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$stat = new Analytics($db);

// need to get data from the external request
$data = json_decode(file_get_contents('php://input'));

// set class properties for month and year
$stat->month = $data->month;
$stat->year  = $data->year;

//run the function to get the data
$result = $stat->month_vehicle_totals();

$num = $result->rowCount();

$response = [];

if ($num > 0) {
    $stat_arr['data'] = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $stat_item = [
            'vehicle' => $make . " " . $model . " " . $number_plate,
            // 'make'         => $make,
            // 'model'        => $model,
            // 'number_plate' => $number_plate,
            'total'   => $aggregated_total,
        ];

        // push that stat item to 'data' index of array
        array_push($stat_arr['data'], $stat_item);
    }
    $status              = 'Success';
    $message             = 'Successfully retrieved vehicle totals';
    $response['status']  = $status;
    $response['message'] = $message;
    $response['data']    = $stat_arr;

    echo json_encode($response);
} else {
    $status              = 'Error';
    $message             = 'No vehicle totals for that time period';
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
}
