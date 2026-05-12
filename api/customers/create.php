<?php
// FILE FOR CREATING A CUSTOMER
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Customer.php';

$db       = (new Database())->connect();
$customer = new Customer($db);

$data = json_decode(file_get_contents("php://input"), true);

if (! $data) {
    echo json_encode([
        "status"  => "Error",
        "message" => "Invalid request payload",
    ]);
    exit;
}

// Map input to model
$customer->first_name          = $data['f_name'] ?? null;
$customer->last_name           = $data['l_name'] ?? null;
$customer->email               = $data['email'] ?? null;
$customer->id_type             = $data['id_type'] ?? null;
$customer->id_no               = $data['id_number'] ?? null;
$customer->phone_no            = $data['phone_number'] ?? null;
$customer->dl_no               = $data['dl_number'] ?? null;
$customer->dl_expiry           = $data['dl_expiry'] ?? null;
$customer->residential_address = $data['residential_address'] ?? null;
$customer->work_address        = $data['work_address'] ?? null;
$customer->date_of_birth       = $data['date_of_birth'] ?? null;
$customer->account_id          = $data['account_id'] ?? null; // salesperson ID

// Check unique email
$result = $customer->check_unique_email();
if ($result->rowCount() > 0) {
    echo json_encode([
        "status"  => "Error",
        "message" => "An account exists with this email.",
    ]);
    exit;
}

// Create customer
$response = $customer->create();
echo json_encode($response);
