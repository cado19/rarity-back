<?php
// api/bookings/update.php

include_once '../../config/cors.php';

include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Account.php';
include_once '../../models/Fleet.php';
include_once '../../models/Driver.php';

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);
$account = new Account($db);
$fleet   = new Fleet($db);
$driver  = new Driver($db);

$data = json_decode(file_get_contents('php://input'));

// Assign booking properties
$booking->id         = $data->booking_id;
$booking->c_id       = $data->customer_id;
$booking->vehicle_id = $data->vehicle_id;
$booking->d_id       = $data->driver_id;
$booking->start_date = $data->start_date;
$booking->end_date   = $data->end_date;
$booking->start_time = $data->start_time;
$booking->end_time   = $data->end_time;

// Duration calculation with override
$duration = Booking::calculateDuration(
    $booking->start_date,
    $booking->start_time,
    $booking->end_date,
    $booking->end_time,
    ! empty($data->override) && $data->override === true
);

// Rate calculation
if (! empty($data->custom_rate) && $data->custom_rate > 0) {
    $fleet->id = $booking->vehicle_id;
    $fleet->category();

    $account->id          = $data->account_id;
    $account->category_id = $fleet->category_id;
    $account->fetch_agent_rate();
    $account->fetch_role_id();

    if ($data->custom_rate < $account->agent_rate) {
        echo json_encode([
            "status"  => "Error",
            "message" => "Set amount is too low. Min for selected vehicle: {$account->agent_rate}",
        ]);
        exit;
    }

    $booking->custom_rate = $data->custom_rate;
    $booking->subtotal    = $data->custom_rate * $duration;

} else {
    $fleet->id = $booking->vehicle_id;
    $fleet->get_daily_rate();

    $booking->custom_rate = 0;
    $booking->subtotal    = $fleet->daily_rate * $duration;
}

// VAT calculation
$applyVAT = ! empty($data->vat) && $data->vat === true;
if ($applyVAT) {
    $booking->vat   = round($booking->subtotal * 0.16, 2);
    $booking->total = $booking->subtotal + $booking->vat;
} else {
    $booking->vat   = 0;
    $booking->total = $booking->subtotal;
}

// Driver fee
if ($booking->d_id == 8) {
    $booking->driver_fee = 0;
} else {
    $driver->id = $booking->d_id;
    $driver->get_rate();
    $booking->driver_fee = ($data->in_capital * $driver->rate_in_capital) +
        ($data->out_capital * $driver->rate_out_capital);
}

// Save booking update
if ($booking->update()) {
    echo json_encode([
        "status"  => "Success",
        "message" => "Successfully updated booking",
    ]);
} else {
    echo json_encode([
        "status"  => "Error",
        "message" => "An error occurred. Try again later",
    ]);
}
