<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Home.php';

$db = (new Database())->connect();

// For web, you may not need agent_id — instead, use account_id or global stats
$data       = json_decode(file_get_contents("php://input"));
$account_id = $data->account_id ?? null;

if (! $account_id) {
    echo json_encode(["status" => "Error", "message" => "Missing account_id"]);
    exit;
}

$home             = new Home($db);
$home->account_id = $account_id;

$stats = $home->get_web_stats();

echo json_encode([
    "status"              => "Success",
    "active_bookings"     => $stats["active_bookings"],
    "upcoming_bookings"   => $stats["upcoming_bookings"],
    "revenue_total"       => $stats["revenue_total"],
    "recent_bookings"     => $stats["recent_bookings"],
    "available_vehicles"  => $stats["available_vehicles"],
    "top_customers"       => $stats["top_customers"],
    "top_vehicles"        => $stats["top_vehicles"],
    "revenue_by_customer" => $stats["revenue_by_customer"],
    "revenue_by_vehicle"  => $stats["revenue_by_vehicle"],
]);
