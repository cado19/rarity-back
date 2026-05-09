<?php
// THIS FILE WILL DELIVER ALL ACCOUNTS TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Account.php';

// Instantiate DB and connect
$database = new Database();
$db       = $database->connect();

// Instantiate Account object
$account = new Account($db);

// Query accounts
$result = $account->read_all();
$num    = $result->rowCount();

$response = [];

if ($num > 0) {
    $accounts = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $accounts[] = [
            'id'       => $id,
            'name'     => $name,
            'email'    => $email,
            'phone_no' => $phone_no,
            'country'  => $country,
            'role'     => $role,
        ];
    }

    $response = [
        'status'  => 'Success',
        'message' => 'Successfully fetched accounts',
        'data'    => $accounts,
    ];
} else {
    $response = [
        'status'  => 'Error',
        'message' => 'No accounts found',
        'data'    => [],
    ];
}

echo json_encode($response);
