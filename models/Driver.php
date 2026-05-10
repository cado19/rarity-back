<?php

class Driver
{

    // DB STUFF
    private $con;
    private $table = "drivers";

    // Driver properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;        // password that comes from the user
    public $hashed_password; // password that comes from the db
    public $id_type;
    public $id_no;
    public $phone_no;
    public $dl_no;
    public $dl_expiry;
    public $date_of_birth;
    public $location;         // in capital or out capital
    public $rate_in_capital;  // for rate inside capital or outside capital
    public $rate_out_capital; // for rate outside capital or outside capital
    public $id_image;
    public $id_back_image;
    public $profile_image;
    public $license_image;
    public $created_at;

    // Delivery properties
    public $booking_id;
    public $delivered_at;
    public $delivered; // delivered status 'true' / 'false'

    // Constructor with DB
    public function __construct($db)
    {
        // when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // get drivers
    public function read()
    {
        $status = "false";
        $sql    = "SELECT id, first_name, last_name, email, id_no, phone_no FROM drivers WHERE deleted = ?";
        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$status]);

        return $stmt;
    }

    // get booking drivers
    public function booking_drivers()
    {
        $sql = "SELECT a.id, a.name, a.email, a.phone_no, a.country
            FROM accounts a
            INNER JOIN account_roles ar ON a.id = ar.account_id
            INNER JOIN roles r ON ar.role_id = r.id
            WHERE r.name = 'driver'
              AND a.deleted = 'false'
            ORDER BY a.id DESC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function read_single()
    {
        $sql  = "SELECT * FROM drivers WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->first_name       = $row['first_name'];
        $this->last_name        = $row['last_name'];
        $this->email            = $row['email'];
        $this->dl_no            = $row['dl_no'];
        $this->id_no            = $row['id_no'];
        $this->phone_no         = $row['phone_no'];
        $this->rate_in_capital  = $row['rate_in_capital'];
        $this->rate_out_capital = $row['rate_out_capital'];
        $this->dl_expiry        = $row['dl_expiration_date'];
        $this->date_of_birth    = $row['date_of_birth'];
    }

    public function check_unique_email()
    {
        $sql = "SELECT id FROM drivers WHERE email LIKE ?";

        // prepare the statement
        $stmt = $this->con->prepare($sql);

        $stmt->execute([$this->email]);

        return $stmt;
    }

    public function fetch_driver()
    {
        $sql = "SELECT id, first_name, last_name, email, password, role_id
            FROM drivers WHERE email = ? LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->email]);
        return $stmt;
    }

    public function create()
    {
        $sql = "INSERT INTO drivers
                (first_name, last_name, email, id_no, dl_no, phone_no, date_of_birth)
                VALUES (?,?,?,?,?,?,?)";

        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->first_name, $this->last_name, $this->email, $this->id_no, $this->dl_no, $this->phone_no, $this->date_of_birth])) {
            $this->id = $this->con->lastInsertId();
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function update_password()
    {
        $sql  = "UPDATE drivers SET password = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute([$this->hashed_password, $this->id]);
    }

    public function update_rate()
    {
        $sql  = "UPDATE drivers SET $this->location = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->rate, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    public function get_rate()
    {
        $sql  = "SELECT rate_in_capital, rate_out_capital FROM accounts WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->rate_in_capital  = $row['rate_in_capital'];
        $this->rate_out_capital = $row['rate_out_capital'];
    }

    public function workplan_drivers()
    {
        $sql  = "SELECT id, CONCAT(first_name, ' ', last_name) AS title FROM drivers";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    // ---------------------------------------- DELIVERY FUNCTIONS  ----------------------------------------
    // create delivery
    public function upsert_delivery()
    {
        try {
            $sql = "
          INSERT INTO deliveries (booking_id, driver_id)
          VALUES (:booking_id, :driver_id)
          ON DUPLICATE KEY UPDATE driver_id = VALUES(driver_id)
        ";
            $stmt = $this->con->prepare($sql);
            $stmt->execute([
                ':booking_id' => $this->booking_id,
                ':driver_id'  => $this->id,
            ]);
            return true;
        } catch (PDOException $e) {
            throw new Exception("Error creating/updating delivery: " . $e->getMessage());
        }
    }

    public function read_deliveries($driverId)
    {
        $query = "SELECT d.id, d.booking_id, d.driver_id, d.delivered, d.delivered_at,
                     b.booking_no, b.customer_id, b.start_date, b.end_date
              FROM deliveries d
              INNER JOIN bookings b ON d.booking_id = b.id
              WHERE d.driver_id = ? AND d.delivered = 'false'
              ORDER BY d.created_at DESC";

        $stmt = $this->con->prepare($query);
        $stmt->execute([$driverId]);

        return $stmt;
    }

    public function complete_delivery($booking_id)
    {
        $query = "UPDATE deliveries SET delivered = 'true', delivered_at = NOW() WHERE booking_id = ?";
        $stmt  = $this->con->prepare($query);
        return $stmt->execute([$booking_id]);
    }
}
