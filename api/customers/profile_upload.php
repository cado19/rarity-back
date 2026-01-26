<?php
// THIS FILE WILL HANDLE profile UPLOAD TO BACK END AND SAVE FILE NAME TO DB
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

// get data from post request(booking id, image);
$data = json_decode(file_get_contents('php://input'));

// echo json_encode($data);
// echo json_encode($_FILES['file']);
// echo json_encode($_POST['id']);

if (empty($data->image)) {
    // no profile uploaded
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

    $name_file = 'profile_' . date("his") . '.png';

    // $filePath = '../../files/customers/profile/' . $name_file;
    $uploadDir = realpath(__DIR__ . '/../../files/customers/profile/');
    $filePath  = $uploadDir . $name_file;

    if (file_put_contents($filePath, $imageData)) {
        $customer->profile_image = $name_file;
        $customer->id            = $data->id;
        if ($customer->save_profile_image()) {
            // profile uploaded and file name saved to db
            $message             = "Profile successfully saved";
            $status              = 'Success';
            $response['message'] = $message;
            $response['status']  = $status;
            echo json_encode($response);
        } else {
            // profile uploaded but file name not saved to db
            $message             = "An error occured. Please try again";
            $status              = 'Error';
            $response['message'] = $message;
            $response['status']  = $status;
            echo json_encode($response);
        }

        // echo json_encode(['success' => true, 'file' => $filePath]);
    } else {
        // profile not uploaded
        $message             = "An error occured. Profile not uploaded ";
        $status              = 'Error';
        $response['message'] = $message;
        $response['status']  = $status;
        echo json_encode($response);
        // echo json_encode(['success' => false, 'message' => 'Failed tosavetheimage . ']);
    }
}
