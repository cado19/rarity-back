<?php
require '../../vendor/autoload.php'; // Dompdf via Composer
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Fleet.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$database = new Database();
$db       = $database->connect();

$fleet                = new Fleet($db);
$fleet->work_order_id = $_GET['id'] ?? null;

$response = $fleet->read_work_order_with_items();

if ($response['status'] !== 'Success') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$workOrder = $response['work_order'];
$items     = $response['items'];

// Build HTML with logo
$html = "
    <div style='text-align:center; margin-bottom:20px;'>
        <img src='../../files/rarity_contract_top.png' alt='Company Logo' style='height:80px;' />
        <h2 style='margin:0;'>Work Order {$workOrder['work_order_number']}</h2>
    </div>
    <p><strong>Vehicle:</strong> {$workOrder['make']} {$workOrder['model']} ({$workOrder['number_plate']})</p>
    <p><strong>Title:</strong> {$workOrder['title']}</p>
    <p><strong>Description:</strong> {$workOrder['description']}</p>
    <p><strong>Status:</strong> {$workOrder['status']}</p>
    <p><strong>Scheduled Date:</strong> {$workOrder['scheduled_date']}</p>
    <p><strong>Completion Date:</strong> {$workOrder['completion_date']}</p>
    <p><strong>Labor Cost:</strong> {$workOrder['labor_cost']}</p>
    <p><strong>Parts Cost:</strong> {$workOrder['parts_cost']}</p>
    <p><strong>Total Cost:</strong> {$workOrder['total_cost']}</p>
    <h3>Items</h3>
    <table border='1' cellpadding='5' cellspacing='0' width='100%'>
        <thead>
            <tr>
                <th>Item</th>
                <th>Cost</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>";

foreach ($items as $item) {
    $html .= "
        <tr>
            <td>{$item['item']}</td>
            <td>{$item['cost']}</td>
            <td>{$item['quantity']}</td>
        </tr>";
}

$html .= "
        </tbody>
    </table>
";

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF to browser
$dompdf->stream("work_order_{$workOrder['work_order_number']}.pdf", ["Attachment" => true]);
