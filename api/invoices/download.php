<?php
require '../../vendor/autoload.php';
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Invoice.php';

$database = new Database();
$db       = $database->connect();

$invoice = new Invoice($db);
$data    = json_decode(file_get_contents("php://input"), true);

// Set invoice id from request
$invoice->id = $data['invoice_id'] ?? $data['id'];

// Fetch invoice details
$details = $invoice->invoice_details();

// Fetch payment history
$payments = $invoice->getPayments();

$html = "  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      line-height: 1.4;
      margin: 20px;
      position: relative;
    }
    h2 {
      margin: 0;
      padding: 0;
    }
    .header {
      text-align: center;
      margin-bottom: 20px;
    }
    .header img {
      width: 100%;
      max-height: 200px;
    }
    .details p {
      margin: 2px 0;
    }
    .subject {
      margin: 15px 0;
      font-weight: bold;
      font-size: 14px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #000;
      padding: 6px;
      text-align: left;
    }
    th {
      background-color: #333;
      color: #fff;
    }
    .totals {
      margin-top: 15px;
      text-align: right;
    }
    .totals p {
      margin: 4px 0;
      font-weight: bold;
    }
    #payment-details {
      border: 3px solid #000;
      border-radius: 8px;
      padding: 10px;
      margin-top: 25px;
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
    /* Watermark */
    .watermark {
      position: fixed;
      top: 40%;
      left: 20%;
      font-size: 80px;
      color: rgba(200, 0, 0, 0.15);
      transform: rotate(-30deg);
      z-index: -1;
    }
  </style>
";

// Header
$html .= '<div class="header">';
$html .= "<img src='https://backend.raritycars.com/files/rarity_contract_top.png' alt='Company Logo' />
            <h2>Invoice</h2>
          </div>";

$html .= '<div class="details">';

$html .= "<p><b>Invoice No:</b> {$voucher['invoice_number']}</p>
    <p><b>Status:</b> Paid</p>
    <p><b>Billed To:</b> {$invoice['billed_to']}</p>
    <p><b>Due Date:</b> {$invoice['due_date']}</p>
    <p><b>Vehicle:</b> {$invoice['make']}{$invoice['model']}{$invoice['number_plate']}</p>
    <p><b>Booking No:</b> {$invoice['booking_no']}</p>
  </div>";

$html .= '<div class="subject">';
$html .= "    Subject: {$invoice['subject']}
          </div>
          ";

$html .= "
      <table>
        <thead>
          <tr>
            <th>Description</th>
            <th>Qty (Days)</th>
            <th>Rate</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{$invoice['make']}{$invoice['model']}{$invoice['number_plate']}  Hire</td>
            <td>{$invoice['duration_days']}</td>
            <td>{$invoice['daily_rate']}</td>
            <td>{$invoice['balance']}</td>
          </tr>
        </tbody>
      </table>
";

$html .= '<div id="payment-details">';

$html .= "<h3>PAYMENT DETAILS:</h3>
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
          </div>";

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$dompdf->stream("Invoice_{$voucher['invoice_number']}.pdf", ["Attachment" => true]);
