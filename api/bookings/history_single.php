<?php

include_once '../../config/Database.php';
include_once '../../config/cors.php';
include_once '../../models/Booking.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

if (! isset($_GET['id'])) {

    echo json_encode([
        'status'  => 'Error',
        'message' => 'Missing history id',
    ]);

    exit;
}

$history = $booking->get_history_single(
    (int) $_GET['id']
);

if (! $history) {

    echo json_encode([
        'status'  => 'Error',
        'message' => 'History record not found',
    ]);

    exit;
}

echo json_encode([
    'status' => 'Success',
    'data'   => $history,
]);
