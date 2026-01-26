<?php
// THIS FILE WILL HANDLE license UPLOAD TO BACK END AND SAVE FILE NAME TO DB
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary CLASS files
include_once '../../config/Database.php';
include_once '../../models/Customer.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$customer = new Customer($db);

$response = [];

// get data from post request(customer id, image);
$data = json_decode(file_get_contents('php://input'));

if (empty($data->image)) {
    // no license uploaded
    $message             = "Please take picture before uploading";
    $status              = 'Error';
    $response['message'] = $message;
    $response['status']  = $status;
    echo json_encode($response);
} else {
    $imageData = $data->image;
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageData = base64_decode($imageData);

    $name_file = 'license_' . date("his") . '.png';

    $uploadDir = realpath(__DIR__ . '/../../files/customers/license/');
    $filePath  = $uploadDir . '/' . $name_file;

    $filePath = '../../files/customers/license/' . $name_file;

    if (file_put_contents($filePath, $imageData)) {
        $customer->license_image = $name_file;
        $customer->id            = $data->id;
        if ($customer->save_license()) {
            // license uploaded and file name saved to db
            $message             = "License successfully saved";
            $status              = 'Success';
            $response['message'] = $message;
            $response['status']  = $status;
        } else {
            // license uploaded but file name not saved to db
            $message             = "An error occured. Please try again";
            $status              = 'Error';
            $response['message'] = $message;
            $response['status']  = $status;
        }

        // echo json_encode(['success' => true, 'file' => $filePath]);
    } else {
        // license not uploaded
        $message             = "An error occured. license not uploaded ";
        $status              = 'Error';
        $response['message'] = $message;
        $response['status']  = $status;
        // echo json_encode(['success' => false, 'message' => 'Failed tosavetheimage . ']);
    }
    echo json_encode($response);
}
