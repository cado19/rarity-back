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
include_once '../../models/Contract.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$contract = new Contract($db);

$response = [];

// get data from post request(booking id, image);
$data = json_decode(file_get_contents('php://input'));

$contract->booking_id = $data->id; // set booking id property of contract class 
$contract->contract_to_sign(); // get id of contract from booking id set above

// echo json_encode($data);

if (empty($data->image)) {
    // no signature uploaded
    $message             = "Please sign before uploading";
    $status              = 'Error';
    $response['message'] = $message;
    $response['status']  = $status;
    echo json_encode($response);
} else {
    $imageData = $data->image;
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageData = base64_decode($imageData);

    $name_file = 'signature_' . date("his") . '.png';

    $filePath = '../../files/signatures/' . $name_file;

    if (file_put_contents($filePath, $imageData)) {
    	$contract->signature = $name_file;
    	if($contract->sign_contract()){
		    // signature uploaded and file name saved to db
		    $message             = "Contract successfully signed";
		    $status              = 'Success';
		    $response['message'] = $message;
		    $response['status']  = $status;
		    echo json_encode($response);
    	} else {
    		// signature uploaded but file name not saved to db
		    $message             = "An error occured. Please try again";
		    $status              = 'Error';
		    $response['message'] = $message;
		    $response['status']  = $status;
		    echo json_encode($response);
    	}

        // echo json_encode(['success' => true, 'file' => $filePath]);
    } else {
    	// signature not uploaded 
		    $message             = "An error occured. Signature not uploaded ";
		    $status              = 'Error';
		    $response['message'] = $message;
		    $response['status']  = $status;
		    echo json_encode($response);
        // echo json_encode(['success' => false, 'message' => 'Failed tosavetheimage . ']);
    }
}


