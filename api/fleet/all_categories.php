<?php
// THIS FILE WILL DELIVER ALL categories TO EXTERNAL REQUESTS

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// include necessary files
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

// Instantiate The DB and connect to it
$database = new Database();
$db       = $database->connect();

// instantiate fleet object
$fleet = new Fleet($db);

// vehicles query as a function
$result = $fleet->categories();

// get row count
$num = $result->rowCount();

// check if any categories
if ($num > 0) {
    $categories_arr         = [];
    $categories_arr['data'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $categories_item = [
            'id'   => $row['id'],
            'name' => $row['name'],
        ];
        $categories_arr['data'][] = $categories_item;
    }

    echo json_encode([
        "status" => "Success",
        "count"  => $num,
        "data"   => $categories_arr['data'],
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "No categories found",
    ]);
}
