<?php
require '../../vendor/autoload.php'; // Dompdf
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../config/utilities.php';
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

// Fetch exchange rate (KES → USD)
$apiKey   = "YOUR_API_KEY"; // replace with your exchangerate-api key
$req_url  = "https://v6.exchangerate-api.com/v6/$exchange_key/latest/KES";
$response = file_get_contents($req_url);

if ($response === false) {
    echo "Failed to fetch exchange rate";
    exit;
}

$data = json_decode($response);
if ($data->result !== 'success') {
    echo "Error parsing exchange rate data";
    exit;
}

$rate_usd = $data->conversion_rates->USD;

// Convert values
$driver_fee_usd     = round($voucher['driver_fee'] * $rate_usd, 2);
$fuel_fee_usd       = round($voucher['fuel'] * $rate_usd, 2);
$daily_rate_usd     = round($voucher['daily_rate'] * $rate_usd, 2);
$custom_rate_usd    = round($voucher['custom_rate'] * $rate_usd, 2);
$cdw_total_usd      = round($voucher['cdw_total'] * $rate_usd, 2);
$total_usd          = round($voucher['total'] * $rate_usd, 2);
$subtotal_usd       = round($voucher['subtotal'] * $rate_usd, 2);
$vat_usd            = round($voucher['vat'] * $rate_usd, 2);

// Calculate ultimate total
$ultimate_total = 0;
if ($voucher['driver_fee'] > 0) $ultimate_total += $voucher['driver_fee'];
if ($voucher['fuel'] > 0) $ultimate_total += $voucher['fuel'];
if ($voucher['vat'] > 0) {
    $ultimate_total += $voucher['total'];
} else {
    $ultimate_total += $voucher['subtotal'];
}
$ultimate_total_usd = round($ultimate_total * $rate_usd, 2);

// Build HTML
$html = "
    <style>
      p {
        margin: 2px 0;
        padding: 0;
      }
    </style>
    <div style='text-align:center; margin-bottom:20px;'>
        <img src='https://backend.raritycars.com/files/rarity_contract_top.png' 
             alt='Company Logo' style='width:100%;' />
        <h2 style='margin:0;'>Booking Voucher (USD)</h2>
    </div>
    <p><b>Booking No:</b> {$voucher['booking_no']}</p>
    <p><b>Client:</b> {$voucher['customer_first_name']} {$voucher['customer_last_name']}</p>
    <p><b>Vehicle:</b> {$voucher['make']} {$voucher['model']}</p>
    <p><b>Registration:</b> {$voucher['number_plate']}</p>";

if ($voucher['custom_rate'] == 0) {
    $html .= "<p><b>Daily Rate:</b> \${$daily_rate_usd}/-</p>";
} else {
    $html .= "<p><b>Daily Rate:</b> <del>\${$daily_rate_usd}/-</del> 
              <ins>\${$custom_rate_usd}/-</ins></p>";
}

$html .= "
    <p><b>Fuel Fee:</b> \${$fuel_fee_usd}/-</p>";

if ($voucher['driver_fee'] > 0) {
    $html .= "<p><b>Driver Fee:</b> \${$driver_fee_usd}/-</p>
              <p><b>Vehicle Fee:</b> \${$total_usd}/-</p>";
}

if ($voucher['cdw_total'] > 0) {
    $html .= "<p><b>CDW Fee:</b> \${$cdw_total_usd}/-</p>";
}

if ($voucher['vat'] > 0) {
    $html .= "<p><b>Booking Fee:</b> \${$total_usd}/-</p>
              <p><b>VAT:</b> \${$vat_usd}/-</p>";
}

$html .= '
    <p><b>Subtotal:</b> \$" . number_format($ultimate_total_usd, 2) . "/-</p>
    <p><b>Start Date:</b> {$voucher['start_date']}</p>
    <p><b>End Date:</b> {$voucher['end_date']}</p>
    <p><b>Start Time:</b> {$voucher['start_time']}</p>
    <p><b>End Time:</b> {$voucher['end_time']}</p>
    <style>
      #payment-details {
        border: 3px solid #000;
        border-radius: 8px;
        padding: 10px;
        margin-top: 15px;
      }
      #payment-details p {
        margin: 0;
        padding: 2px 0;
      }
      #payment-details h3 {
        margin-bottom: 8px;
        text-align: center;
        text-decoration: underline;
      }
      #payment-details a {
        display: inline-block;
        margin-top: 10px;
        padding: 6px 12px;
        border: 2px solid #198754;
        border-radius: 6px;
        color: #198754;
        text-decoration: none;
        font-weight: bold;
      }
      #payment-details a:hover {
        background-color: #198754;
        color: #fff;
      }
    </style>
    <div id="payment-details">
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
    </div>
';

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$dompdf->stream("booking_voucher_usd_{$voucher['booking_no']}.pdf", ["Attachment" => true]);
