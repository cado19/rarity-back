<?php
// THIS FILE WILL UPLOAD A VEHICLE IMAGE

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

$targetDir  = "../../files/fleet/";
$filename   = basename($_FILES["image"]["name"]);
$safeName   = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename);
$targetFile = $targetDir . uniqid() . "_" . $safeName;
$fleet->url = $safeName;
// $targetFile = $targetDir . basename($_FILES["image"]["name"]);
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        $fleet->id    = $_POST['vehicle_id'];
        $fileType     = mime_content_type($_FILES["image"]["tmp_name"]);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                // here we save the url and vehicle_id to the database
                $fleet->save_image();
                $response['status'] = 'success';
                $response['path']   = $targetFile;
            } else {
                $response['status']  = 'error';
                $response['message'] = 'Failed to move uploaded file.';
            }
        } else {
            $response['status']  = 'error';
            $response['message'] = 'Invalid file type.';
        }
    } else {
        $response['status']  = 'error';
        $response['message'] = 'No file uploaded or upload error.';
    }
    echo json_encode($response);
}
