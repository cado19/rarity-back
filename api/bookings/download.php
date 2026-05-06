<?php
require '../../vendor/autoload.php'; // Dompdf
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Booking.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$database = new Database();
$db       = $database->connect();

$booking = new Booking($db);
$id      = $_GET['id'] ?? null;

$voucher = $booking->voucher_details($id);

if (! $voucher) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "Error", "message" => "Voucher not found"]);
    exit;
}

// Calculate ultimate total
$ultimate_total = 0;
if ($voucher['driver_fee'] > 0) {
    $ultimate_total += $voucher['driver_fee'];
}

if ($voucher['fuel'] > 0) {
    $ultimate_total += $voucher['fuel'];
}

if ($voucher['subtotal'] > 0) {
    $ultimate_total += $voucher['subtotal'];
} else {
    $ultimate_total += $voucher['total'];
}

// Build HTML
$html = "
    <div style='text-align:center; margin-bottom:20px;'>
        <img src='https://backend.raritycars.com/files/rarity_contract_top.png'
             alt='Company Logo' style='width:100%;' />
        <h2 style='margin:0;'>Booking Voucher</h2>
    </div>
    <p><b>Booking No:</b> {$voucher['booking_no']}</p>
    <p><b>Client:</b> {$voucher['customer_first_name']} {$voucher['customer_last_name']}</p>
    <p><b>Vehicle:</b> {$voucher['make']} {$voucher['model']}</p>
    <p><b>Registration:</b> {$voucher['number_plate']}</p>";

if ($voucher['custom_rate'] == 0) {
    $html .= "<p><b>Daily Rate:</b> Ksh. {$voucher['daily_rate']}/-</p>";
} else {
    $html .= "<p><b>Daily Rate:</b> <del>Ksh. {$voucher['daily_rate']}/-</del>
              <ins>Ksh. {$voucher['custom_rate']}/-</ins></p>";
}

$html .= "
    <p><b>Fuel Fee:</b> Ksh. {$voucher['fuel']}/-</p>";

if ($voucher['driver_fee'] > 0) {
    $html .= "<p><b>Driver Fee:</b> Ksh. {$voucher['driver_fee']}/-</p>
              <p><b>Vehicle Fee:</b> Ksh. {$voucher['total']}/-</p>";
}

if ($voucher['cdw_total'] > 0) {
    $html .= "<p><b>CDW Fee:</b> Ksh. {$voucher['cdw_total']}/-</p>";
}

if ($voucher['vat'] > 0) {
    $html .= "<p><b>Booking Fee:</b> Ksh. {$voucher['total']}/-</p>
              <p><b>VAT:</b> Ksh. {$voucher['vat']}/-</p>";
}

$html .= "
    <p><b>Subtotal:</b> Ksh. " . number_format($ultimate_total) . "/-</p>
    <p><b>Start Date:</b> {$voucher['start_date']}</p>
    <p><b>End Date:</b> {$voucher['end_date']}</p>
    <p><b>Start Time:</b> {$voucher['start_time']}</p>
    <p><b>End Time:</b> {$voucher['end_time']}</p>
    <h3>PAYMENT DETAILS:</h3>
    <p><b>BANK NAME:</b> I&M BANK</p>
    <p><b>BRANCH:</b> VALLEY ARCADE</p>
    <p><b>Account Name:</b> RARITY TRAVEL LTD</p>
    <p><b>ACCOUNT NUMBER:</b> 01605023636350 (KES)</p>
    <p><b>ACCOUNT NUMBER:</b> 01605023631250 (USD)</p>
    <p><b>S.W.I.F.T BIC:</b> IMBLKENA</p>
    <p><b>BRANCH CODE:</b> 016</p>
    <p><b>BANK CODE:</b> 057</p>
    <p><b>MPESA PAYBILL:</b> 400200</p>
    <p><b>ACCOUNT:</b> 40044610</p>
";

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$dompdf->stream("booking_voucher_{$voucher['booking_no']}.pdf", ["Attachment" => true]);
