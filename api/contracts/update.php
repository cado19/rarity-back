<?php
// THIS FILE WILL HANDLE SIGNATURE UPLOAD TO BACK END AND SAVE FILE NAME TO DB

// Error handling: suppress output, log to file
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../php-error.log');

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
//header mods for customer request
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// include necessary CLASS files
include_once '../../config/Database.php';
include_once '../../models/Contract.php';
include_once '../../models/Booking.php';
include_once '../../models/Fleet.php';
include_once '../../models/Driver.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

$contract = new Contract($db); // new contract instance
$booking  = new Booking($db);  // new booking instance
$fleet    = new Fleet($db);
$delivery = new Driver($db);

$response = [];

try {
    // get data from post request (booking id, image, cdw flag)
    $data = json_decode(file_get_contents('php://input'));
    // echo json_encode($data->cdw);

    $contract->booking_id = $data->id; // set booking id property of contract class
    $contract->contract_to_sign();     // get id of contract from booking id set above
    $booking->id = $data->id;

    if (empty($data->id)) {
        throw new Exception("Missing booking id");
    }

    $contract->booking_id = $data->id;
    $contract->contract_to_sign(); // sets $contract->id

    if (empty($data->image)) {
        throw new Exception("Please sign before uploading");
    }

    // decode base64 signature
    $imageData = str_replace('data:image/png;base64,', '', $data->image);
    $imageData = str_replace(' ', '+', $imageData);
    $imageData = base64_decode($imageData);

    $name_file = 'signature_' . date("his") . '.png';
    $uploadDir = realpath(__DIR__ . '/../../../client/contract/signatures/');
    $filePath  = $uploadDir . '/' . $name_file;

    if (! file_put_contents($filePath, $imageData)) {
        throw new Exception("Signature not uploaded");
    }

    // save signature to contract
    $contract->signature = $name_file;
    if (! $contract->sign_contract()) {
        throw new Exception("Signature uploaded but not saved to DB");
    }

    // ✅ Handle CDW calculation if cdw is true
    if (! empty($data->cdw) && $data->cdw === "true") {
        // Get booking details
        $resources = $booking->get_cdw_calc_resources();

        if (! $resources) {
            throw new Exception("Booking not found");
        }

        $vehicle_id = $booking->vehicle_id;
        $start_date = new DateTime($booking->start_date);
        $end_date   = new DateTime($booking->end_date);
        $days       = $start_date->diff($end_date)->days + 1;

        // Get cdw_rate from vehicle_pricing
        $fleet->id = $vehicle_id;
        $cdw_rate  = $fleet->get_cdw_rate();

        if ($cdw_rate === null) {
            throw new Exception("Vehicle pricing not found");
        }

        // Calculate and save cdw_total
        if ($booking->calculate_and_save_cdw($cdw_rate)) {
            // success
        } else {
            throw new Exception("Failed to save CDW total");
        }

        $contract->set_cdw_status(); // update cdw to true in contract's table

    }
    // ✅ Mark delivery complete
    $delivery->complete_delivery($data->id);

    $response['status']  = "Success";
    $response['message'] = "Contract successfully signed";

} catch (Exception $e) {
    $response['status']  = "Error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
