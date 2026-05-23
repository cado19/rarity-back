<?php
class Invoice
{
    private $con;

    // Invoice fields
    public $id;
    public $booking_id;
    public $subject;
    public $due_date;
    public $terms;
    public $billed_to;

    // Payment fields (for inserts)
    public $amount;
    public $payment_mode;
    public $payment_code;
    public $notes;

    public function __construct($db)
    {
        $this->con = $db;
    }

    // find basic invoice details by booking id
    public function findByBookingId()
    {
        $stmt = $this->con->prepare("SELECT * FROM invoices WHERE booking_id = ?");
        $stmt->execute([$this->booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        $stmt = $this->con->prepare("
            INSERT INTO invoices (booking_id, subject, due_date, terms, billed_to)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->booking_id,
            $this->subject,
            $this->due_date,
            $this->terms,
            $this->billed_to,
        ]);

        $invoiceId     = $this->con->lastInsertId();
        $invoiceNumber = "INV-" . str_pad($invoiceId, 3, "0", STR_PAD_LEFT);

        $update = $this->con->prepare("UPDATE invoices SET invoice_number = ? WHERE id = ?");
        $update->execute([$invoiceNumber, $invoiceId]);

        return [
            "id"             => $invoiceId,
            "invoice_number" => $invoiceNumber,
            "status"         => "unpaid",
            "booking_id"     => $this->booking_id,
            "subject"        => $this->subject,
            "due_date"       => $this->due_date,
            "terms"          => $this->terms,
            "billed_to"      => $this->billed_to,
        ];
    }

    // Get all invoices
    public function getAll()
    {
        $query = "
        SELECT i.id AS invoice_id,
               i.invoice_number,
               i.status,
               i.subject,
               i.due_date,
               i.terms,
               i.billed_to,
               i.created_at,
               b.booking_no,
               c.first_name AS customer_first_name,
               c.last_name AS customer_last_name,
               v.make,
               v.model,
               v.number_plate
        FROM invoices i
        JOIN bookings b ON i.booking_id = b.id
        JOIN customer_details c ON b.customer_id = c.id
        JOIN vehicle_basics v ON b.vehicle_id = v.id
        ORDER BY i.created_at DESC
    ";
        $stmt = $this->con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function invoice_details()
    {
        // main invoice + booking + customer + vehicle
        $query = "
        SELECT i.id AS invoice_id,
               i.invoice_number,
               i.status,
               i.subject,
               i.due_date,
               i.terms,
               i.billed_to,
               i.created_at,
               b.id AS booking_id,
               b.booking_no,
               b.start_date,
               b.end_date,
               b.duration AS duration_days,
               COALESCE(b.custom_rate, vp.daily_rate) AS daily_rate,
               c.first_name AS customer_first_name,
               c.last_name AS customer_last_name,
               c.email AS customer_email,
               v.make,
               v.model,
               v.number_plate
        FROM invoices i
        JOIN bookings b ON i.booking_id = b.id
        JOIN customer_details c ON b.customer_id = c.id
        JOIN vehicle_basics v ON b.vehicle_id = v.id
        JOIN vehicle_pricing vp ON b.vehicle_id = vp.vehicle_id
        WHERE i.id = ?
        LIMIT 1
    ";

        $stmt = $this->con->prepare($query);
        $stmt->execute([$this->id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invoice) {
            // payments summary
            $pquery = "SELECT IFNULL(SUM(amount),0) AS total_paid
                   FROM invoice_payments
                   WHERE invoice_id = ?";
            $pstmt = $this->con->prepare($pquery);
            $pstmt->execute([$this->id]);
            $summary = $pstmt->fetch(PDO::FETCH_ASSOC);

            $invoice['total_paid'] = $summary['total_paid'];
            $invoice['balance']    = ($invoice['duration_days'] * $invoice['daily_rate']) - $invoice['total_paid'];

            // full payment history
            $pquery2 = "SELECT id, amount, payment_mode, payment_code, notes AS date
                    FROM invoice_payments
                    WHERE invoice_id = ?
                    ORDER BY payment_time ASC";
            $pstmt2 = $this->con->prepare($pquery2);
            $pstmt2->execute([$this->id]);
            $invoice['payments'] = $pstmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $invoice;
    }

    // Get necessary invoice details by booking id
    public function getByBookingId()
    {
        $query = "
        SELECT i.id AS invoice_id,
               i.invoice_number,
               i.status,
               i.subject,
               i.due_date,
               i.terms,
               i.billed_to,
               i.created_at,
               b.id AS booking_id,
               b.booking_no,
               b.start_date,
               b.end_date,
               b.duration AS duration_days,
               b.daily_rate,
               c.first_name AS customer_first_name,
               c.last_name AS customer_last_name,
               c.email AS customer_email,
               v.make,
               v.model,
               v.number_plate,
               IFNULL(SUM(p.amount),0) AS total_paid,
               ( (b.duration * b.daily_rate) - IFNULL(SUM(p.amount),0) ) AS balance
        FROM invoices i
        JOIN bookings b ON i.booking_id = b.id
        JOIN customer_details c ON b.customer_id = c.id
        JOIN vehicle_basics v ON b.vehicle_id = v.id
        LEFT JOIN invoice_payments p ON i.id = p.invoice_id
        WHERE i.booking_id = ?
        GROUP BY i.id, b.id, c.id, v.id
    ";

        $stmt = $this->con->prepare($query);
        $stmt->execute([$this->booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Record a payment
    public function addPayment()
    {
        $stmt = $this->con->prepare("
            INSERT INTO invoice_payments (invoice_id, amount, payment_mode, payment_code, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->id,
            $this->amount,
            $this->payment_mode,
            $this->payment_code,
            $this->notes,
        ]);

        return $this->invoice_details($this->id);
    }

    // Get payments on an invoice
    public function getPayments()
    {
        $stmt = $this->con->prepare("
            SELECT id, invoice_id, amount, payment_mode, payment_code, payment_time, notes
            FROM invoice_payments
            WHERE invoice_id = ?
            ORDER BY payment_time ASC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update invoice status manually
    public function updateStatus($invoiceId, $status)
    {
        $stmt = $this->con->prepare("UPDATE invoices SET status = ? WHERE id = ?");
        $stmt->execute([$status, $invoiceId]);
    }
}
