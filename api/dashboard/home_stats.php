<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Home.php';

$response = [];

try {
    $database = new Database();
    $db       = $database->connect();

    $home = new Home($db);

    $data = json_decode(file_get_contents("php://input"));
    if (! isset($data->customer_id)) {
        throw new Exception("Missing customer_id in request");
    }

    $stats    = $home->get_stats($data->customer_id);
    $vehicles = $home->get_available_vehicles(5);

    if (isset($stats['error']) || isset($vehicles['error'])) {
        $response['status']  = "Error";
        $response['message'] = "Failed to fetch home stats";
        $response['logs']    = [
            "stats"    => $stats['message'] ?? null,
            "vehicles" => $vehicles['message'] ?? null,
        ];
    } else {
        $response['status']   = "Success";
        $response['message']  = "Successfully fetched home stats";
        $response['stats']    = $stats;
        $response['vehicles'] = $vehicles;
    }
} catch (Exception $e) {
    $response['status']  = "Error";
    $response['message'] = "Exception occurred";
    $response['logs']    = $e->getMessage();
}

echo json_encode($response);
