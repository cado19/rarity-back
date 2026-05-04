<?php
require '../../vendor/autoload.php'; // Composer autoload

use Dompdf\Dompdf;

include_once '../../config/Database.php';

$db = (new Database())->connect();

$id = $_GET['id'] ?? null;
if (! $id) {
    die("Missing work order ID");
}

// Fetch work order
$stmt = $db->prepare("SELECT * FROM work_orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtItems = $db->prepare("SELECT * FROM work_order_items WHERE work_order_id = ?");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Build HTML with logo
$html = "
<div style='text-align:center;'>
  <img src='../../files/rarity_contract_top.png' alt='Company Logo' style='height:80px; margin-bottom:20px;' />
</div>

<h1 style='text-align:center;'>Work Order {$order['work_order_number']}</h1>
<p><strong>Vehicle:</strong> {$order['make']} {$order['model']} ({$order['number_plate']})</p>
<p><strong>Title:</strong> {$order['title']}</p>
<p><strong>Description:</strong> {$order['description']}</p>
<p><strong>Status:</strong> {$order['status']}</p>
<p><strong>Scheduled Date:</strong> {$order['scheduled_date']}</p>
<p><strong>Completed:</strong> {$order['completion_date']}</p>
<p><strong>Total Cost:</strong> {$order['total_cost']}</p>

<h3>Items</h3>
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<tr><th>Item</th><th>Cost</th><th>Quantity</th></tr>";

foreach ($items as $item) {
    $html .= "<tr>
        <td>{$item['item']}</td>
        <td>{$item['cost']}</td>
        <td>{$item['quantity']}</td>
    </tr>";
}

$html .= "</table>";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true); // allow images
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$dompdf->stream("workorder-{$order['work_order_number']}.pdf", ["Attachment" => true]);
