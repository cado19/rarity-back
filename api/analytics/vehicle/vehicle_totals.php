<?php
// THIS FILE WILL RETURN MOST POPULAR VEHICLE

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../../config/Database.php';
include_once '../../../models/Analytics.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$stat = new Analytics($db);

$result   = $stat->vehicle_totals();
$num      = $result->rowCount();
$response = [];

if ($num > 0) {
    $stat_arr['data'] = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $stat_item = [
            'id'           => $id,
            'make'         => $make,
            'model'        => $model,
            'number_plate' => $number_plate,
            'total'        => $total,
            'daily_rate'   => $daily_rate,
            'adr'          => $ADR,
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
    $message             = 'Could not retrieve vehicle totals';
    $response['status']  = $status;
    $response['message'] = $message;

    echo json_encode($response);
}
