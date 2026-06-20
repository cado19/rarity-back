<?php
// ADD A VEHICLE TO THE DATABASE
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

$database = new Database();
$db       = $database->connect();

$fleet = new Fleet($db);

$data = json_decode(file_get_contents('php://input'));

$fleet->make           = $data->make;
$fleet->model          = $data->model;
$fleet->number_plate   = $data->number_plate;
$fleet->category_id    = $data->category_id;
$fleet->transmission   = $data->transmission;
$fleet->fuel           = $data->fuel;
$fleet->drive_train    = $data->drive_train;
$fleet->colour         = $data->colour;
$fleet->seats          = $data->seats;
$fleet->daily_rate     = $data->daily_rate;
$fleet->vehicle_excess = $data->vehicle_excess;

// add other pricing/extras fields as needed
$fleet->refundable_security_deposit = $data->refundable_security_deposit ?? null;
$fleet->cdw_rate                    = $data->cdw_rate ?? null;
$fleet->monthly_target              = $data->monthly_target ?? null;
// $fleet->comfort_features            = $data->comfort_features ?? null;
// $fleet->safety_features             = $data->safety_features ?? null;

$response = [];

try {
    // check uniqueness first
    $result = $fleet->check_unique_number_plate();
    if ($result->rowCount() > 0) {
        echo json_encode([
            "status"  => "Error",
            "message" => "A vehicle exists with this number plate.",
        ]);
        exit();
    }

    // begin transaction
    $db->beginTransaction();

    if (! $fleet->create()) {
        throw new Exception("An error occurred saving vehicle details.");
    }

    if (! $fleet->create_pricing()) {
        throw new Exception("An error occurred saving vehicle pricing details.");
    }

    // if (! $fleet->create_extras()) {
    //     throw new Exception("An error occurred saving vehicle extra details.");
    // }

    // commit if all succeeded
    $db->commit();

    echo json_encode([
        "status"     => "Success",
        "message"    => "Vehicle Created",
        "vehicle_id" => $fleet->id,
    ]);

} catch (Exception $e) {
    // rollback if any step fails
    $db->rollBack();
    echo json_encode([
        "status"  => "Error",
        "message" => $e->getMessage(),
    ]);
}
