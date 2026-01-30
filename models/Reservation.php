<?php
class Reservation
{
    private $conn;
    private $table            = "reservations";
    private $customers_table  = "customer_details";
    private $categories_table = "vehicle_categories";

    public $id;
    public $customer_id;
    public $vehicle_category_id;
    public $start_date;
    public $end_date;
    public $opened;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Create reservation
    public function create()
    {
        $query = "INSERT INTO " . $this->table . "
                  (customer_id, vehicle_category_id, start_date, end_date, opened)
                  VALUES (:customer_id, :vehicle_category_id, :start_date, :end_date, :opened)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":vehicle_category_id", $this->vehicle_category_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":opened", $this->opened);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all reservations
    public function readAll()
    {
        $query = "SELECT
                r.id,
                r.customer_id,
                r.vehicle_category_id,
                r.start_date,
                r.end_date,
                r.opened,
                r.created_at,
                CONCAT(c.first_name, ' ', c.last_name) AS client,
                v.name AS category
              FROM " . $this->table . " r
              LEFT JOIN " . $this->customers_table . " c
                ON r.customer_id = c.id
              LEFT JOIN " . $this->categories_table . " v
                ON r.vehicle_category_id = v.id
              ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single reservation
    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt;
    }
}
