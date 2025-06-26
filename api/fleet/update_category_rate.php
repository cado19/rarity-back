<?php
// THIS FILE WILL UPDATE DAILY RATE OF ALL VEHICLES IN A GIVEN CATEGORY

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
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

// first assign category_id data from posted data and assign to category_id property of fleet class

$data = json_decode(file_get_contents("php://input"));

$fleet->category_id = $data->category_id;
$fleet->rate        = $data->rate_amount;

$result = $fleet->vehicles_in_category();

$num = $result->rowCount();

$response = [];

if ($num > 0) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $fleet->id         = $row['id'];
        $fleet->daily_rate = $fleet->rate;
        $fleet->update_rate();
    }

    $status              = "Success";
    $message             = "Vehicle rates updated successfully";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);
} else {
    $status              = "Error";
    $message             = "No vehicles found in this category";
    $response['status']  = $status;
    $response['message'] = $message;
    echo json_encode($response);

}
