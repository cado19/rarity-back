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

if (isset($_FILES['idFront']) && isset($_FILES['idBack']) && $_FILES['idFront']['error'] === UPLOAD_ERR_OK && $_FILES['idBack']['error'] === UPLOAD_ERR_OK) {
	$name_file = 'license_' . date("his") . '.png';
	$id_front_file = 'id_front_' . date("his") . '.png';
	$id_back_file = 'id_back_' . date("his") . '.png';
	$front_upload_dir = '../../files/customers/id/' . $id_front_file;
	$back_upload_dir = '../../files/customers/id/' . $id_back_file;
	$customer->id_image = $id_front_file;
	$customer->id_back_image = $id_back_file;
	$customer->id = $_POST['id'];

	if ((move_uploaded_file($_FILES['idFront']['tmp_name'], $front_upload_dir)) && (move_uploaded_file($_FILES['idBack']['tmp_name'], $back_upload_dir))) {
			$customer->save_id();
      echo "Files uploaded successfully!";
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
