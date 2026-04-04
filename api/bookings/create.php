<?php
// api/bookings/create.php
// Unified endpoint for creating bookings (normal + one-day)

include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';
include_once '../../models/Account.php';
include_once '../../models/Fleet.php';
include_once '../../models/Contract.php';
include_once '../../models/Driver.php';
include_once '../../models/Notification.php';

$database = new Database();
$db       = $database->connect();

$booking  = new Booking($db);
$account  = new Account($db);
$fleet    = new Fleet($db);
$contract = new Contract($db);
$driver   = new Driver($db);

$data = json_decode(file_get_contents('php://input'), true);

// Assign booking properties
$booking->c_id        = $data['customer_id'];
$booking->vehicle_id  = $data['vehicle_id'];
$booking->d_id        = $data['driver_id'];
$booking->start_date  = $data['start_date'];
$booking->end_date    = $data['oneday'] ? $data['start_date'] : $data['end_date'];
$booking->start_time  = $data['start_time'];
$booking->end_time    = $data['end_time'];
$booking->account_id  = $data['account_id'];
$booking->in_capital  = $data['in_capital'];
$booking->out_capital = $data['out_capital'];

// Calculate duration
$start_date = strtotime($booking->start_date);
$end_date   = strtotime($booking->end_date);
$duration   = $data['oneday'] ? 1 : (($end_date - $start_date) / 86400);

// Handle custom rate vs daily rate
if (! empty($data['custom_rate']) && $data['custom_rate'] > 0) {
    $fleet->id = $booking->vehicle_id;
    $fleet->category();
    $account->id          = $booking->account_id;
    $account->category_id = $fleet->category_id;
    $account->fetch_agent_rate();
    $account->fetch_role_id();

    if ($data['custom_rate'] < $account->agent_rate) {
        echo json_encode(["status" => "Error", "message" => "Rate too low. Min: {$account->agent_rate}"]);
        exit;
    }

    $booking->custom_rate = $data['custom_rate'];
    $booking->total       = $data['custom_rate'] * $duration;
} else {
    $fleet->id = $booking->vehicle_id;
    $fleet->get_daily_rate();
    $booking->custom_rate = 0;
    $booking->total       = $fleet->daily_rate * $duration;
}

// After calculating base total
$booking->subtotal = $booking->total; // store base amount

// VAT calculation
$applyVAT = ! empty($data['vat']) && $data['vat'] === true;
if ($applyVAT) {
    $booking->vat   = round($booking->subtotal * 0.16, 2); // 16% VAT
    $booking->total = $booking->subtotal + $booking->vat;  // grand total
} else {
    $booking->vat   = 0;
    $booking->total = $booking->subtotal; // no VAT applied
}

// Driver fee
if ($booking->d_id == 8) {
    $booking->driver_fee = 0;
} else {
    $driver->id = $booking->d_id;
    $driver->get_rate();
    $booking->driver_fee = ($booking->in_capital * $driver->rate_in_capital) +
        ($booking->out_capital * $driver->rate_out_capital);
}

// Save booking
$response = [];
if ($booking->create()) {
    $booking->booking_no = "B-" . str_pad($booking->id, 3, "0", STR_PAD_LEFT);
    $booking->save_booking_number();

    // Create contract
    $contract->booking_id = $booking->id;
    $contract->create();

    // Send notification
    $notification            = new Notification($db);
    $notification->user_id   = $booking->c_id;
    $notification->user_type = "customer";
    foreach ($notification->getTokens() as $token) {
        $notification->expo_token = $token;
        $notification->send(
            "Booking Confirmed",
            "Your booking {$booking->booking_no} has been created.",
            ["bookingId" => $booking->id]
        );
    }

    $response = [
        "status"     => "Success",
        "message"    => "Booking created successfully",
        "booking_id" => $booking->id,
    ];
} else {
    $response = ["status" => "Error", "message" => "An error occurred. Try again later"];
}

echo json_encode($response);
