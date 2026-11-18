<?php

include_once '../../config/Database.php';
include_once '../../config/cors.php';
include_once '../../models/Booking.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);

$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
$action = $_GET['action'] ?? null;
$date   = $_GET['date'] ?? null;

$data = $booking->get_history_paginated(
    $page,
    $limit,
    $action,
    $date
);

$total = $booking->get_history_count(
    $action,
    $date
);

echo json_encode([
    'status' => 'Success',
    'page'   => $page,
    'limit'  => $limit,
    'total'  => $total,
    'pages'  => ceil($total / $limit),
    'data'   => $data,
]);
