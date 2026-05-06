<?php
// Deliver single vehicle to external requests
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet     = new Fleet($db);
$fleet->id = $_GET['id'] ?? null;

$result = $fleet->read_single();

echo json_encode($result);
