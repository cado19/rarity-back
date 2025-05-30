<?php
class Payment
{
    // DB STUFF
    private $con;
    private $table = "bookings";

    public $id;
    public $booking_no;
    public $currency;
    public $amount;
    public $payment_method;
    public $payment_account;
    public $payment_time;
    public $confirmation_code;
    public $order_tracking_id;
    public $message;
    public $status;
    public $created_at;

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // get all payments
    public function read()
    {
        $sql  = "SELECT * FROM payments ORDER BY booking_no";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}
