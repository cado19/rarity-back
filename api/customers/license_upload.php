<?php
// THIS FILE WILL HANDLE SIGNATURE UPLOAD TO BACK END AND SAVE FILE NAME TO DB
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
echo json_encode($_POST['id']);

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
	$name_file = 'license_' . date("his") . '.png';
	$upload_dir = '../../files/customers/license/' . $name_file;
	$customer->license_image = $name_file;
	$customer->id = $_POST['id'];

	if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir)) {
			$customer->save_license();
      echo "File uploaded successfully!";
    } else {
      echo "An error occurred while uploading the file.";
    }
} else {
	$status = 'Error';
	$message = 'An error occured';
	$response['status'] = $status;
	$response['message'] = $message;
	echo json_encode($response);
}
