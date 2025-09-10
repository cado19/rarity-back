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
include_once '../../models/Booking.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$fleet   = new Fleet($db);
$booking = new Booking($db);

$targetDir = $_SERVER['DOCUMENT_ROOT'] . "/files/bookings/";
// $targetFile = $targetDir . basename($_FILES["image"]["name"]);
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES["video"]) && $_FILES["video"]["error"] === UPLOAD_ERR_OK) {

        $booking->id = $_POST['booking_id'];
        // get booking number to be used
        $booking->read_single();
        $booking_no = $booking->booking_no;
        $vid_pref   = $booking_no . "_"; // prefix of the video name

        $filename     = basename($_FILES["video"]["name"]);                 // get the filename of file
        $safeName     = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename); // remove any underscores in the names
        $newName      = uniqid($vid_pref) . "_" . $safeName;                //  attach a unique id to the file
        $targetFile   = $targetDir . $newName;
        $booking->url = $newName;

        $fileType     = mime_content_type($_FILES["video"]["tmp_name"]);
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
                // here we save the url and vehicle_id to the database
                $booking->save_booking_video();
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
