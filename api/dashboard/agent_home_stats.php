<?php
header('Content-Type: application/json');
include_once '../../config/Database.php';
include_once '../../models/Home.php';

$db       = (new Database())->connect();
$data     = json_decode(file_get_contents("php://input"));
$agent_id = $data->agent_id ?? null;

if (! $agent_id) {
    echo json_encode(["status" => "Error", "message" => "Missing agent_id"]);
    exit;
}

$home           = new Home($db);
$home->agent_id = $agent_id;

$stats = $home->get_agent_stats();

echo json_encode([
    "status"             => "Success",
    "active_bookings"    => $stats["active_bookings"],
    "upcoming_bookings"  => $stats["upcoming_bookings"],
    "revenue_total"      => $stats["revenue_total"],
    "recent_bookings"    => $stats["recent_bookings"],
    "available_vehicles" => $stats["available_vehicles"],
    "recent_messages"    => $stats["recent_messages"],
]);
