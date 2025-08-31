<?php
// THIS FILE WILL DELIVER A SINGLE vehicle'S images TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate blog fleet object
$fleet = new Fleet($db);

// get the id from the url

if (isset($_GET['id'])) {
    $fleet->id = $_GET['id'];
} else {
    die();
}

// get images of the vehicle'S
$result = $fleet->get_vehicle_images();

// get row count
$num = $result->rowCount();

//response array
$response = [];

//check if any vehicles
if ($num > 0) {
                               // code...
    $image_arr['images'] = []; //this is where the data will go

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        // single post item array
        $image_item = [
            'id'  => $id,
            'url' => $url,

        ];

        // push that image item to 'images' index of array
        array_push($image_arr['images'], $image_item);
        $message             = "Successfully fetched all images";
        $status              = "Success";
        $response['data']    = $image_arr['images'];
        $response['message'] = $message;
        $response['status']  = $status;
    }
} else {
    // Vehicle has no images ($num = 0)
    $message             = "No images for associated vehicle in the database";
    $status              = "Error";
    $response['data']    = [];
    $response['message'] = $message;
    $response['status']  = $status;
}
echo json_encode($response);
