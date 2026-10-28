<?php
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$db    = (new Database())->connect();
$fleet = new Fleet($db);

$data   = json_decode(file_get_contents("php://input"));
$limit  = $data->limit ?? 10;
$page   = $data->page ?? 1;
$offset = ($page - 1) * $limit;

$records = $fleet->getVehicleWorkOrderStats($limit, $offset);
$total   = $fleet->getVehicleWorkOrderCount();

echo json_encode([
    "status"     => "Success",
    "data"       => $records,
    "pagination" => [
        "page"       => $page,
        "limit"      => $limit,
        "total"      => $total,
        "totalPages" => ceil($total / $limit),
    ],
]);
